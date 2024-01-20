<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Model;

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

    public function driverApp()
    {
        return $this->belongsTo(Driver::class, 'driver_id');
    }

}
