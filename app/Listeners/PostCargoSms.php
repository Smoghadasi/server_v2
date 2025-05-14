<?php

namespace App\Listeners;

use App\Event\PostCargoSmsEvent;
use App\Models\Driver;
use App\Models\FleetLoad;
use App\Models\ProvinceCity;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class PostCargoSms implements ShouldQueue
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  \App\Event\PostCargoSmsEvent  $event
     * @return void
     */
    public function handle(PostCargoSmsEvent $event)
    {
        try {
            $load = $event->load;

            $latitude = $load->latitude;
            $longitude = $load->longitude;

            $fleets = FleetLoad::where('load_id', $load->id)->pluck('fleet_id');
            $cityFrom = ProvinceCity::whereId($load->origin_city_id)->first();
            $cityTo = ProvinceCity::whereId($load->destination_city_id)->first();

            $haversine = "(6371 * acos(cos(radians($latitude))
                * cos(radians(`latitude`))
                * cos(radians(`longitude`)
                - radians($longitude))
                + sin(radians($latitude))
                * sin(radians(`latitude`))))";
            $count = 7;
            $radius = 120;

            $drivers = $this->getDrivers($cityFrom, $fleets, $haversine, $radius, $count);

            foreach ($drivers as $driver) {
                $this->sendMessage($driver, $cityFrom, $cityTo);
            }

            if ($drivers->isEmpty()) {
                $this->resetSendMessage($cityFrom, $fleets, $haversine, $radius);
                $drivers = $this->getDrivers($cityFrom, $fleets, $haversine, $radius, $count, 0);
                foreach ($drivers as $driver) {
                    $this->sendMessage($driver, $cityFrom, $cityTo);
                }
            }
        } catch (\Throwable $th) {
            //throw $th;
        }
    }
    private function resetSendMessage($cityFrom, $fleets, $haversine, $radius)
    {
        Driver::where('province_id', $cityFrom->parent_id)
            ->where('location_at', '!=', null)
            ->where('location_at', '>=', Carbon::now()->subMinutes(360))
            ->whereIn('fleet_id', $fleets)
            ->selectRaw("{$haversine} AS distance")
            ->whereRaw("{$haversine} < ?", $radius)
            ->where('sendMessage', 1)
            ->update(['sendMessage' => 0]);
    }

    private function sendMessage($driver, $cityFrom, $cityTo)
    {
        $driver->sendMessage = 1;
        $driver->save();
        $sms = new Driver();
        $sms->subscriptionLoadSmsIr(
            $driver->mobileNumber,
            $driver->name,
            $cityFrom->name,
            $cityTo->name
        );
    }

    private function getDrivers($cityFrom, $fleets, $haversine, $radius, $count, $sendMessageStatus = 0)
    {
        return Driver::select('drivers.*')
            ->where('location_at', '!=', null)
            ->where('location_at', '>=', Carbon::now()->subMinutes(360))
            ->whereIn('fleet_id', $fleets)
            ->where('sendMessage', $sendMessageStatus)
            ->where('province_id', $cityFrom->parent_id)
            ->selectRaw("{$haversine} AS distance")
            ->whereRaw("{$haversine} < ?", $radius)
            ->orderBy('distance', 'asc')
            ->take($count)
            ->get();
    }
}
