<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use CrudTrait;
    use HasFactory;

    protected $fillable = ['email', 'name', 'lastname', 'user_id', 'group_id'];

    protected $hidden = ['user_id'];

    protected $appends = ['full_name'];

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function contactGroup()
    {
        return $this->hasOne(ContactGroup::class, 'id', 'group_id');
    }

    public function contacts()
    {
        return $this->hasMany(ContactCampaignItem::class, 'contact_id', 'id');
    }

    public function getFullNameAttribute()
    {
        return $this->name . ' ' . $this->lastname;
    }
}
