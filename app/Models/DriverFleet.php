<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverFleet extends Model
{
    use HasFactory;

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function fleet()
    {
        return $this->belongsTo(Fleet::class);
    }
}
