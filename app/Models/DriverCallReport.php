<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DriverCallReport extends Model
{
    protected $guarded = [];
    public function fleet()
    {
        return $this->belongsTo(Fleet::class);
    }
}
