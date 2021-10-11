<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\CampaignItem;
use App\Models\Template;
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

        foreach ($campaignItems as $campaignItem) {
            $campaignItem->status = 2;
            $campaignItem->campaign->status = 2;

//            Log::info('processed: ' . $campaignItem->id);
            foreach ($campaignItem->campaign->contacts as $contactItem) {
                try {
                    $campaignItem->template->data = $this->parse($campaignItem->template->data, $campaignItem->campaign->user, $contactItem->contact);
                    Mail::send('mail.invite', ['content' => $campaignItem->template->data], function ($message) use ($contactItem, $campaignItem) {
                        $message->to($contactItem->contact->email)->subject($campaignItem->template->name);
                    });

                } catch (\Throwable $throwable) {
                    $campaignItem->status = 4;
                    $campaignItem->campaign->status = 4;
                    Log::error($throwable->getMessage());
                    $campaignItem->status_log .= "\n" . $throwable;
                }
            }
            $campaignItem->campaign->save();
            $campaignItem->save();
        }

        $campaigns = Campaign::query()->where('status', '!=', 1)->get();

        foreach ($campaigns as $campaign) {
            if ($campaign->campaignItems()->where('status', '=',1)->where('status', '!=', 4)->count() === 0) {
                $campaign->finished_at = now();
                $campaign->status = 3;
                $campaign->save();
            }
        }
    }

}
