<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivationCode extends Model
{
    protected $fillable = ['mobileNumber', 'code', 'status'];
}
