<?php

namespace App\Models;

use App\Traits\UuidForKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoginHistory extends Model
{
    use HasFactory, UuidForKey;


    protected $fillable = ['user_id','ip_address', 'status', 'action', 'unsuccess'];


    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
