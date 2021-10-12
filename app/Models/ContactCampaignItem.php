<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactCampaignItem extends Model
{
    use HasFactory;

    protected $fillable = ['campaign_item_id', 'contact_id', 'external_service_id', 'external_service_status'];
}
