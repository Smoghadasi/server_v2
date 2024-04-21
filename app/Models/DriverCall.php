<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DriverCall extends Model
{
    protected $appends = [
        'isAccepted',
        'isAnyOneSelectedDriver',
        'avarageRateDriver',
        'ownerScore'
    ];
    /**
     * Get the user that owns the DriverCall
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    /**
     * Get the user that owns the DriverCall
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function driverSelect(): BelongsTo
    {
        return $this->belongsTo(Driver::class, 'driver_id')->select('id', 'fleet_id', 'name', 'lastName', 'mobileNumber');
    }

    public function getIsAcceptedAttribute()
    {
        if (DriverLoad::where('load_id', $this->load_id)->where('driver_id', $this->driver_id)->count() > 0) {
            return true;
        } else {
            return false;
        }
    }
    public function getIsAnyOneSelectedDriverAttribute()
    {
        if (DriverLoad::where('load_id', $this->load_id)->count() > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function getAvarageRateDriverAttribute()
    {
        return Score::where('type', 'Owner')->where('driver_id', $this->driver_id)->avg('value');
    }

    public function getOwnerScoreAttribute()
    {
        $load = Load::where('id', $this->load_id)->withTrashed()->first();
        $score = Score::where('type', 'Owner')->where('driver_id', $this->driver_id)->where('owner_id', $load->user_id)->first();
        if ($score === null) {
            return null;
        } else {
            return $score->value;
        }
    }
}
