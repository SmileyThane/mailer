<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CampaignItem extends Model
{
    use \Backpack\CRUD\app\Models\Traits\CrudTrait;
    use HasFactory;

    const STATUSES = [ 1 => 'waiting', 2 => 'pushed', 3 => 'on hold', 4 => 'failed'];

    protected $fillable = ['campaign_id', 'user_id', 'template_id', 'processed_at'];

    protected $appends = ['status_name', 'full_name'];


    public function campaign()
    {
        return $this->hasOne(Campaign::class, 'id', 'campaign_id');
    }

    public function template()
    {
        return $this->hasOne(Template::class, 'id', 'template_id');
    }

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function getFullNameAttribute()
    {
        return
            $this->template->name . " at ".
            Carbon::parse($this->processed_at)->format('d-m-Y') .
            " (" . self::STATUSES[$this->status] . ")";
    }

    public function getStatusNameAttribute()
    {
        return self::STATUSES[$this->status];
    }

}
