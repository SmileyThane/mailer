<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactCampaignItem extends Model
{
    use HasFactory;

    protected $fillable = ['campaign_item_id', 'contact_id', 'external_service_id', 'external_service_status'];

    protected $appends = ['contact_plus_status', 'campaign_item_plus_status'];

    public function campignItem()
    {
        return $this->hasOne(CampaignItem::class, 'id', 'campaign_item_id');
    }

    public function contact()
    {
        return $this->hasOne(Contact::class, 'id', 'contact_id');
    }

    public function getContactPlusStatusAttribute()
    {
        return $this->contact->email . '[' . ($this->external_service_status ?? 'none') . ']';
    }

    public function getCampaignItemPlusStatusAttribute()
    {
        return $this->campignItem->name . '[' . ($this->external_service_status ?? 'none') . ']';
    }
}
