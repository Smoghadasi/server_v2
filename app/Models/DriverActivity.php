<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DriverActivity extends Model
{
    protected $fillable = ['driver_id', 'persianDate', 'driverCalls'];
    protected $appends = ['driverInfo'];

    public function getDriverInfoAttribute()
    {
        try {
            return Driver::where('id', $this->driver_id)->select('id', 'fleet_id', 'name', 'mobileNumber','lastName')->first();
        } catch (\Exception $exception) {
        }
        return null;
    }

    public function getDriverCallsAttribute()
    {
        return DriverCall::where([
            ['driver_id', $this->driver_id],
            ['callingDate', date("Y-m-d")]
        ])
            ->select('phoneNumber')
            ->get();
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }
}
