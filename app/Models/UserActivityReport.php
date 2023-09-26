<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserActivityReport extends Model
{
    protected $fillable = ['user_id'];

    public function user()
    {
        return $this->hasOne(User::class);
    }

}
