<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Campaign extends Model
{
    use \Backpack\CRUD\app\Models\Traits\CrudTrait;
    use HasFactory;

    const STATUSES = [ 1 => 'pending', 2 => 'started', 3 => 'finished', 4 => 'failed'];

    protected $fillable = ['name', 'user_id', 'status', 'started_at', 'finished_at'];

    protected $hidden = ['user_id'];

    protected $appends = ['status_name'];

    public function campaignItems()
    {
        return $this->hasMany(CampaignItem::class, 'campaign_id', 'id' );
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(CampaignContact::class, 'campaign_id', 'id' );
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
