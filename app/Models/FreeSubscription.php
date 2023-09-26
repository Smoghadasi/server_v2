<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FreeSubscription extends Model
{
    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }
}
