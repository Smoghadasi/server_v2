<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class Bearing extends Authenticatable
{

    protected $appends = [
        'theDaysBeforeEndOfTheSubscription',
        'blockedIp'
    ];


    public function city()
    {
        return $this->hasOne(ProvinceCity::class);
    }

    public function tender()
    {
        return $this->belongsTo(Tender::class);
    }

    public function getTheDaysBeforeEndOfTheSubscriptionAttribute()
    {
        try {
            $validityDate = new \DateTime($this->validityDate);
            $today = new \DateTime(date('Y-m-d'));
            $interval = $validityDate->diff($today);
            return $interval->format('%a');;
        } catch (\Exception $exception) {
        }
        return 0;
    }


    /**
     * @return bool
     */
    public function getBlockedIpAttribute(): bool
    {
        if (BlockedIp::where('user_id', $this->id)->where('userType', ROLE_TRANSPORTATION_COMPANY)->count())
            return true;

        return false;
    }
}
