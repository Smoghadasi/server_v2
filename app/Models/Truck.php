<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Truck extends Model
{
    public function color()
    {
        return $this->hasOne(Color::class);
    }

    public function fleet()
    {
        return $this->hasOne(Fleet::class);
    }

}
