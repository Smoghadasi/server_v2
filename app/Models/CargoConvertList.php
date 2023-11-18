<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CargoConvertList extends Model
{
    //

    public function operator()
    {
        return $this->hasOne(User::class, 'id', 'operator_id');
    }

}
