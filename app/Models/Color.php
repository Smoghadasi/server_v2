<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Color extends Model
{
    public function truck()
    {
        return $this->belongsTo(Truck::class);
    }
}
