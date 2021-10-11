<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CampaignContact extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'campaign_id', 'contact_id'];

    public function contact()
    {
        return $this->hasOne(Contact::class, 'id', 'contact_id');
    }
}
