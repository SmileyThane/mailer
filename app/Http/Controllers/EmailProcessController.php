<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\CampaignItem;
use App\Models\Template;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EmailProcessController extends Controller
{

    public function parse($template, $sender, $recipient)
    {
        return str_replace(
            ["{{sender_name}}", "{{recipient_name}}", "{{sender_email}}", "{{recipient_email}}"],
            [$sender->full_name, $recipient->full_name, $sender->email, $recipient->email],
            $template);
    }

    public function process()
    {
        $campaignItems = CampaignItem::query()->where([
            ['processed_at', '<', now()],
            ['status', '!=', 2],
            ['status', '!=', 4]
        ])->get();

        $campaignIds = [];

        foreach ($campaignItems as $campaignItem) {
            $campaignItem->status = 2;
            $campaignItem->campaign->status = 2;
            $recipientsArray = [];

            if ($campaignItem->campaign->contacts) {
                $contactArray = $campaignItem->campaign->contacts->pluck('contact');
                foreach ($contactArray as $contactItem) {
                    try {
                        $recipientsArray[] = ['email' => $contactItem->email, 'name' => $contactItem->name];
                        $campaignIds[] = $campaignItem->campaign_id;
                    } catch (\Throwable $throwable) {
                        $campaignItem->status = 4;
                        $campaignItem->campaign->status = 4;
                        Log::error($throwable->getMessage());
                        $campaignItem->status_log .= "\n" . $throwable;
                    }
                }

                $campaignItem->template->data = $this->parse($campaignItem->template->data, $campaignItem->campaign->user, $contactItem);

                $this->send($campaignItem->user, $recipientsArray, $campaignItem->template->name, $campaignItem->template->data);
            }

            $campaignItem->campaign->save();
            $campaignItem->save();
        }

        $campaigns = Campaign::query()->whereIn('id', array_unique($campaignIds))->get();

        foreach ($campaigns as $campaign) {
            if ($campaign->campaignItems()->where('status', '=', 1)->where('status', '!=', 4)->count() === 0) {
                $campaign->finished_at = now();
                $campaign->status = 3;
                $campaign->save();
            }
        }
    }

    private function send($from, $to, $subject, $body)
    {
        $guzzleClient = new Client([
            'base_uri' => env('SG_URL'),
        ]);

        $result = $guzzleClient->request('POST', 'mail/send', [
            RequestOptions::HEADERS => [
                'Authorization' => 'Bearer ' . env('SG_TOKEN')
            ],
            RequestOptions::JSON => [
                "personalizations" => [
                    [
                        "to" => $to,
                        "subject" => $subject
                    ]
                ],
                "content" => [
                    [
                        "type" => "text/html",
                        "value" => $body
                    ]
                ],
                "from" => [
                    "email" => $from->email,
                    "name" => $from->full_name
                ],
                "reply_to" => [
                    "email" => $from->email,
                    "name" => $from->full_name
                ]
            ],
        ]);
        $result->getHeader('X-Message-Id');
    }

}
