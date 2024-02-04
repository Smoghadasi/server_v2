<?php

namespace App\Console;

use App\Http\Controllers\ReportingController;
use App\Models\CargoConvertList;
use App\Models\FleetLoad;
use App\Models\Load;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->call(function () {

            try {
                Load::where('created_at', '<', date('Y-m-d h:i:s', strtotime('-2 hours', time())))
                    ->update(['urgent' => 0]);
            } catch (\Exception $exception) {
                // Log::emergency("-------------------------------- ConsoleKernel -----------------------------");
                // Log::emergency($exception->getMessage());
                // Log::emergency("------------------------------------------------------------------------------");
            }

            try {
                Load::where([
                    ['created_at', '<', date('Y-m-d h:i:s', strtotime('-1 day', time()))],
                    ['operator_id', '>', 0]
                ])->forceDelete();

            } catch (\Exception $exception) {
                // Log::emergency("-------------------------------- ConsoleKernel -----------------------------");
                // Log::emergency($exception->getMessage());
                // Log::emergency("------------------------------------------------------------------------------");
            }

            // حذف نیسان ها بعد از 24 ساعت
            try {
                $ids = FleetLoad::whereIn('fleet_id', [82, 83, 84])->pluck('load_id');

               Load::where([
                    ['created_at', '<', date('Y-m-d h:i:s', strtotime('-24 hours', time()))],
                    ['operator_id', '>', 0]
                ])
                    ->whereIn('id', $ids)
                    ->forceDelete();

            } catch (\Exception $exception) {
                // Log::emergency("-------------------------------- ConsoleKernel -----------------------------");
                // Log::emergency($exception->getMessage());
                // Log::emergency("------------------------------------------------------------------------------");
            }


            try {
                CargoConvertList::where('created_at', '<', date('Y-m-d h:i:s', strtotime('-1 day', time())))->forceDelete();
            } catch (\Exception $exception) {
                // Log::emergency("-------------------------------- ConsoleKernel -----------------------------");
                // Log::emergency($exception->getMessage());
                // Log::emergency("------------------------------------------------------------------------------");
            }
        })->everyMinute();


        //        $schedule->call(function () {
        //
        //            $numOfDriver = Driver::count();
        //
        //            for ($i = 0; $i < $numOfDriver; $i += 1000) {
        //
        //                // FCM_token
        //                $driverFCM_tokens = Driver::whereRaw('LENGTH(FCM_token)>10')->skip($i)->take($i + 999)->pluck('FCM_token');
        //
        //                $data = [
        //                    'title' => 'ایران ترابر',
        //                    'body' => "با ایران ترابر، بار خود را هوشمندانه انتخاب کنید.",
        //                    'notificationType' => 'newLoad',
        //                ];
        //                if (count($driverFCM_tokens))
        //                    $this->sendNotification($driverFCM_tokens, $data, API_ACCESS_KEY_DRIVER);
        //            }
        //        })->dailyAt('16:00');

        $schedule->call(function () {
            $reportingController = new ReportingController();
            $reportingController->storeFleetRatioToDriverActivityReportData();
            // Log::emergency("--------------------------------------------------------------------------------");
            // Log::emergency("storeFleetRatioToDriverActivityReportData");
            // Log::emergency("--------------------------------------------------------------------------------");
        })->dailyAt('01:00');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }

}
