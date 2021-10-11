<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    use \Backpack\CRUD\app\Models\Traits\CrudTrait;
    use HasFactory;

    protected $fillable = ['name', 'data', 'user_id'];

    protected $hidden = ['user_id'];

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
}
