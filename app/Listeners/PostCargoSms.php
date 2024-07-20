<?php

namespace App\Listeners;

use App\Event\PostCargoSmsEvent;
use App\Models\Driver;
use App\Models\FleetLoad;
use App\Models\ProvinceCity;
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
        $load = $event->load;

        $fleet = FleetLoad::where('load_id', $load->id)->first();
        $cityFrom = ProvinceCity::where('id', $load->origin_city_id)->first();
        $cityTo = ProvinceCity::where('id', $load->destination_city_id)->first();

        $drivers = Driver::where('province_id', $cityFrom->parent_id)
            ->where('fleet_id', $fleet->fleet_id)
            ->where('sendMessage', 0)
            ->where('version', '<' , 67)
            ->take(15)
            ->get();

        if (count($drivers) != 0) {
            foreach ($drivers as $driver) {
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
        } else {
            Driver::where('province_id', $cityFrom->parent_id)
                ->where('fleet_id', $fleet->fleet_id)
                ->where('version', '<' , 67)
                ->where('sendMessage', 1)
                ->update(['sendMessage' => 0]);

            $drivers = Driver::where('province_id', $cityFrom->parent_id)
                ->where('fleet_id', $fleet->fleet_id)
                ->where('version', '<' , 67)
                ->where('sendMessage', 0)
                ->take(40)
                ->get();
            if (count($drivers) != 0) {
                foreach ($drivers as $driver) {
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
            }
        }
    }
}
