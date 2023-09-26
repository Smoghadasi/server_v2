<?php

namespace App\Models;

use App\Http\Controllers\FleetController;
use Illuminate\Database\Eloquent\Model;

class FleetRatioToDriverActivityReport extends Model
{
    protected $appends = ['fleetName', 'countOfAllDrivers'];

    public function getFleetNameAttribute()
    {
        try {
            return FleetController::getFleetName($this->fleet_id);
        } catch (\Exception $exception) {
        }
        return '';
    }

    public function getCountOfAllDriversAttribute()
    {
        try {
            return Driver::where('fleet_id', $this->fleet_id)->count();
        } catch (\Exception $exception) {
        }
        return 0;
    }

}
