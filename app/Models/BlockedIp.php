<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlockedIp extends Model
{
    //

    protected  $fillable = ['user_id', 'ip', 'userType', 'name'];

    public function getNameAttribute()
    {
        if ($this->userType == ROLE_TRANSPORTATION_COMPANY) {
            $user = Bearing::find($this->user_id);
            if (isset($user->id))
                return $user->title;
        }
        if ($this->userType == ROLE_CUSTOMER) {
            $user = Customer::find($this->user_id);
            if (isset($user->id))
                return $user->name . ' ' . $user->lastName;
        }
        if ($this->userType == ROLE_DRIVER) {
            $user = Driver::find($this->user_id);
            if (isset($user->id))
                return $user->name . ' ' . $user->lastName;
        }


        return 'بدون نام';
    }
}
