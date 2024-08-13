<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class FleetLoad extends Model
{
    protected $appends = ['numOfSelectedDrivers', 'title'];


    public function getNumOfSelectedDriversAttribute()
    {
        return DriverLoad::where([
            ['load_id', $this->load_id],
            ['fleet_id', $this->fleet_id]
        ])->count();
    }

    public function getTitleAttribute()
    {
        try {
            return Fleet::find($this->fleet_id)->title;
        } catch (\Exception $exception) {
        }
        return '';
    }
    public function fleet(){
        return $this->belongsTo(Fleet::class);
    }

   public function cargo()
   {
       return $this->belongsTo(Load::class, 'load_id');
   }
}
