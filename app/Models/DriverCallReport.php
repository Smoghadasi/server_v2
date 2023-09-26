<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DriverCallReport extends Model
{
    public function fleet()
    {
        return $this->belongsTo(Fleet::class);
    }
}
