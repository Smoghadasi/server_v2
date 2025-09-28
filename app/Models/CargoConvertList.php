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

    public function checkExists()
    {
        if (CargoConvertList::where('cargo_orginal', '=', $this->cargo_orginal)->where('id', '!=', $this->id)->exists()) {
            return 1;
        }
        return 0;
    }
}
