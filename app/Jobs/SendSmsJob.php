<?php

namespace App\Jobs;

use App\Models\Driver;
use App\Models\FleetLoad;
use App\Models\ProvinceCity;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendSmsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $load;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($load)
    {
        $this->load = $load;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $load = $this->load;

        $fleet = FleetLoad::where('load_id', $load->id)->first();
        $cityFrom = ProvinceCity::where('id', $load->origin_city_id)->first();
        $cityTo = ProvinceCity::where('id', $load->destination_city_id)->first();

        $drivers = Driver::where('province_id', $cityFrom->parent_id)
            // ->where('id', 45172)
            ->where('fleet_id', $fleet->fleet_id)
            ->where('sendMessage', 0)
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
                // ->where('id', 45172)
                ->where('sendMessage', 1)
                ->update(['sendMessage' => 0]);

            $drivers = Driver::where('province_id', $cityFrom->parent_id)
                ->where('fleet_id', $fleet->fleet_id)
                // ->where('id', 45172)
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
