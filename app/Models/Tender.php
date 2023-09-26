<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tender extends Model
{

    public function loads()
    {
        return $this->hasOne(Load::class);
    }

    public function bearing()
    {
        return $this->hasOne(Bearing::class);
    }
}
