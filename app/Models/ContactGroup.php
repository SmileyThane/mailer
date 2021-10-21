<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactGroup extends Model
{
    use CrudTrait;
    use HasFactory;

    protected $fillable = ['name', 'user_id'];

    protected $hidden = ['user_id'];

    public function contacts()
    {
        return $this->hasMany(Contact::class, 'group_id', 'id');
    }
}
