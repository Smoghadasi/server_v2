<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LegalPersonality extends Model
{
    //

    public function user()
    {
        return $this->belongsTo(Customer::class);
    }
}
