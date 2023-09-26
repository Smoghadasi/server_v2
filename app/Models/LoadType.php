<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoadType extends Model
{
    public function loads()
    {
        return $this->belongsTo(Load::class);
    }
}
