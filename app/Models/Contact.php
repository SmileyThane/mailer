<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Contact extends Model
{
    use \Backpack\CRUD\app\Models\Traits\CrudTrait;
    use HasFactory;

    protected $fillable = ['email', 'name', 'lastname', 'user_id'];

    protected $hidden = ['user_id'];

    protected $appends = ['full_name'];

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function getFullNameAttribute()
    {
        return $this->name . ' ' . $this->lastname;
    }
}
