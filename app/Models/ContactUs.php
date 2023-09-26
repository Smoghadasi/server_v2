<?php

namespace App\Models;

use App\Http\Controllers\FleetController;
use Illuminate\Database\Eloquent\Model;

class ContactUs extends Model
{
    protected $table = 'contact_uses';

    protected $appends = ['nameAndLastName', 'fleetName', 'messageDateAndTime', 'userId'];

    public function getNameAndLastNameAttribute()
    {
        if ($this->role == ROLE_DRIVER) {
            $driver = Driver::where('mobileNumber', $this->mobileNumber)->first();
            if (isset($driver->name) || isset($driver->lastName))
                return $driver->name . ' ' . $driver->lastName;
        }

        return "بدون نام";
    }

    public function getFleetNameAttribute()
    {
        if ($this->role == ROLE_DRIVER) {
            $driver = Driver::where('mobileNumber', $this->mobileNumber)->first();
            if (isset($driver->fleet_id))
                return FleetController::getFleetName($driver->fleet_id);
        }

        return "بدون ناوگان";
    }

    public function getMessageDateAndTimeAttribute()
    {
        try {
            $date = explode(' ', $this->created_at);
            return str_replace('-', '/', gregorianDateToPersian($date[0], '-')) . ' ' . $date[1];
        } catch (\Exception $exception) {
        }

        return $this->created_at;
    }

    public function getUserIdAttribute()
    {
        if ($this->role == ROLE_DRIVER) {
            $driver = Driver::where('mobileNumber', $this->mobileNumber)->first();
            if (isset($driver->id))
                return $driver->id;
        }
        return 0;
    }
}
