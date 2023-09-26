<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SOS extends Model
{
    protected $table = 's_o_s';

    //
    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }
}
