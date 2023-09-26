<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Lcobucci\JWT\Exception;

class ComplaintDriver extends Model
{
    //

    protected $appends = ['driver'];

    public function getDriverAttribute()
    {
        try {

            $driver = Driver::find($this->driver_id);
            if (isset($driver->id))
                return $driver->name . ' ' . $driver->lastName;

        } catch (Exception $exception) {
        }

        return '';
    }
}
