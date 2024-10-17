<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Model;

class ComplaintDriver extends Model
{
    //

    protected $appends = [
        'driver',
        'shamsiCreatedDate',
        'shamsiUpdatedDate',
    ];

    public function getShamsiCreatedDateAttribute()
    {
        try {
            $time = explode(' ', $this->created_at);
            return gregorianDateToPersian($this->created_at, '-', true) . ' ( ' . $time[1] . ' ) ';
        } catch (Exception $exception) {
        }

        return '';
    }

    public function getShamsiUpdatedDateAttribute()
    {
        try {
            $time = explode(' ', $this->updated_at);
            return gregorianDateToPersian($this->updated_at, '-', true) . ' ( ' . $time[1] . ' ) ';
        } catch (Exception $exception) {
        }

        return '';
    }

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
