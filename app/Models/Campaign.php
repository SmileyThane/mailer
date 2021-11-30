<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Campaign extends Model
{
    use CrudTrait;
    use HasFactory;

    const CAMPAIGN_ITEM_ROUTE = 'campaign-item/';
    const CONTACT_ROUTE = 'contact/';

    const STATUSES = [1 => 'pending', 2 => 'started', 3 => 'finished', 4 => 'failed'];

    protected $fillable = ['name', 'user_id', 'status', 'started_at', 'finished_at'];

    protected $hidden = ['user_id'];

    protected $appends = ['status_name'];

    public function campaignItems()
    {
        return $this->hasMany(CampaignItem::class, 'campaign_id', 'id');
    }

    public function linksToCampaignItems()
    {
        $links = '';
        $key = 1;
        foreach ($this->campaignItems as $campaignItem) {

            $links .= '<a href="' . backpack_url(self::CAMPAIGN_ITEM_ROUTE . $campaignItem->id . '/show') . '">' .
                $key . ') ' . $campaignItem->processed_at . '</a><br/>';
            $key++;
        }
        return $links;
    }

    public function linksToContacts()
    {
        $links = '';
        $key = 1;
        foreach ($this->contacts as $contact) {

            $links .= '<a href="' . backpack_url(self::CONTACT_ROUTE . $contact->id . '/show') . '">' .
                $key . ') ' . $contact->contact->email . '</a><br/>';
            $key++;
        }
        return $links;
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(CampaignContact::class, 'campaign_id', 'id');
    }

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }


    public function getStatusNameAttribute()
    {
        return self::STATUSES[$this->status];
    }
}
