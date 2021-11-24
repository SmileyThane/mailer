<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\CampaignItem;
use App\Models\Contact;
use App\Models\ContactCampaignItem;
use Carbon\Carbon;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class EmailProcessController extends Controller
{
    protected $isParsedTemplate = false;
    protected $shortcuts = ["{{sender_name}}", "{{recipient_name}}", "{{sender_email}}", "{{recipient_email}}"];

    public function parse($template, $sender, $recipient)
    {
        foreach ($this->shortcuts as $shortcut) {
            if (strpos($shortcut, $template) !== false) {
                $this->isParsedTemplate = true;
            }
        }

        return str_replace(
            $this->shortcuts,
            [$sender->full_name, $recipient->full_name, $sender->email, $recipient->email],
            $template);
    }

    public function process()
    {
        $campaignItems = CampaignItem::query()->where([
            ['processed_at', '<', now()],
            ['status', '!=', 2],
            ['status', '!=', 4],
        ])->get();

        $campaignIds = $this->handleCampaignItems($campaignItems);

        $campaigns = Campaign::query()->whereIn('id', array_unique($campaignIds))->get();
        $this->updateCampaignStatus($campaigns);
    }

    private function handleCampaignItems($campaignItems)
    {
        $campaignIds = [];
        foreach ($campaignItems as $campaignItem) {
            $campaignItem->status = 2;
            $campaignItem->campaign->status = 2;
            $recipientsArray = [];
            if ($campaignItem->campaign->contacts) {
                $contactArray = $campaignItem->campaign->contacts->pluck('contact');
                foreach ($contactArray as $contactItem) {
                    try {
                        $campaignItem->template->data = $this->parse($campaignItem->template->data, $campaignItem->user, $contactItem);
                        $campaignIds[] = $campaignItem->campaign_id;

                        if ($this->isParsedTemplate === true) {
                            ContactCampaignItem::query()->create([
                                'campaign_item_id' => $campaignItem->id,
                                'contact_id' => $contactItem->id,
                            ]);
                            $externalToken = $this->sendGridTransfer(
                                $campaignItem->user,
                                ['email' => $contactItem->email, 'name' => $contactItem->name],
                                $campaignItem->template->subject ?? $campaignItem->template->name,
                                $campaignItem->template->data
                            );
                            $campaignItem->external_service_id = $externalToken;
                            $campaignItem->save();
                        } else {
                            $recipientsArray[] = ['email' => $contactItem->email, 'name' => $contactItem->name];
                            ContactCampaignItem::query()->create([
                                'campaign_item_id' => $campaignItem->id,
                                'contact_id' => $contactItem->id,
                            ]);
                        }
                    } catch (Throwable $throwable) {
                        $campaignItem->status = 4;
                        $campaignItem->campaign->status = 4;
                        Log::error($throwable->getMessage());
                        $campaignItem->status_log .= "\n" . $throwable;
                    }
                }

                $externalToken = $this->sendGridTransfer(
                    $campaignItem->user,
                    $recipientsArray,
                    $campaignItem->template->subject ?? $campaignItem->template->name,
                    $campaignItem->template->data
                );
                $campaignItem->external_service_id = $externalToken;
            }

            $campaignItem->campaign->save();
            $campaignItem->save();
        }
        return $campaignIds;
    }

    private function sendGridTransfer($from, $to, $subject, $body)
    {
        $guzzleClient = new Client([
            'base_uri' => env('SG_URL'),
        ]);

        $result = $guzzleClient->request('POST', 'mail/send', [
            RequestOptions::HEADERS => [
                'Authorization' => 'Bearer ' . env('SG_TOKEN'),
            ],
            RequestOptions::JSON => [
                "personalizations" => [
                    [
                        "to" => $to,
                        "subject" => $subject,
                    ],
                ],
                "content" => [
                    [
                        "type" => "text/html",
                        "value" => $body,
                    ],
                ],
                "from" => [
                    "email" => $from->sender_email ?? $from->email,
                    "name" => $from->sender_name ?? $from->name,
                ],
                "reply_to" => [
                    "email" => $from->email,
                    "name" => $from->full_name,
                ],
            ],
        ]);
        if ($result->getHeader('X-Message-Id')) {
            return $result->getHeader('X-Message-Id')[0];
        }

        throw new RuntimeException('Sending error');
    }

    private function updateCampaignStatus($campaigns)
    {
        foreach ($campaigns as $campaign) {
            if ($campaign->campaignItems()->where('status', '=', 1)->where('status', '!=', 4)->count() === 0) {
                $campaign->finished_at = now();
                $campaign->status = 3;
                $campaign->save();
            }
        }
    }

    public function checkSendGridTransferStatus()
    {
        $campaignItems = CampaignItem::query()
            ->where('external_service_id', '!=', null)
            ->where('is_responded_from_external_service', false)->get();

        foreach ($campaignItems as $campaignItem) {
            $this->getSendGridTransferStatus($campaignItem);
        }
    }

    public function getSendGridTransferStatus($campaignItem)
    {
        $guzzleClient = new Client([
            'base_uri' => env('SG_URL'),
        ]);

        $result = $guzzleClient->request('GET',
            'messages?limit=10000&query=msg_id+LIKE+"' . $campaignItem->external_service_id . '%"',
            [
                RequestOptions::HEADERS => [
                    'Authorization' => 'Bearer ' . env('SG_TOKEN'),
                ],
            ]);
        try {
            $messages = json_decode($result->getBody()->getContents())->messages;
            if ($messages !== []) {
                foreach ($messages as $message) {
                    $contact = Contact::query()->where('email', $message->to_email)->first();
                    $contactCampaignItem = ContactCampaignItem::query()
                        ->where('campaign_item_id', $campaignItem->id)
                        ->where('contact_id', $contact->id)->first();
                    $contactCampaignItem->update([
                        'external_service_id' => $message->msg_id,
                        'external_service_status' => $message->status,
                    ]);
                }
                $campaignItem->status_log = json_encode($messages);
                if (Carbon::parse($campaignItem->processed_at)->diffInDays(now()) > 1) {
                    $campaignItem->is_responded_from_external_service = true;
                }
                $campaignItem->save();
            }
        } catch (Throwable $throwable) {
            Log::error($throwable);
        }
    }

}
