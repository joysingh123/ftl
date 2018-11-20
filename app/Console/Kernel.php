<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Helpers\UtilConstant;
use App\CronJobs;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        'App\Console\Commands\SearchEmailForUserSheet',
        'App\Console\Commands\LookupUserContactEmail'
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('search:emailforusersheet')->everyFiveMinutes()->withoutOverlapping()->before(function () {
            $cronjobs = CronJobs::where('cron_name', UtilConstant::CRON_SEARCH_EMAIL_FOR_USER_SHEET)->get();
            $cronjobs->first()->current_status = "Running";
            $cronjobs->first()->save();
        })->after(function () {
            $cronjobs = CronJobs::where('cron_name', UtilConstant::CRON_SEARCH_EMAIL_FOR_USER_SHEET)->get();
            $cronjobs->first()->current_status = "Not Running";
            $cronjobs->first()->save();
        })->when(function(){
            $cronjobs = CronJobs::where('cron_name', UtilConstant::CRON_SEARCH_EMAIL_FOR_USER_SHEET)->get();
            if($cronjobs->first()->is_run == 'yes' && $cronjobs->first()->current_status == "Not Running"){
                return true;
            }
            return false;
        });
        
        
        $schedule->command('lookup:contactemail')->everyFiveMinutes()->withoutOverlapping()->before(function () {
            $cronjobs = CronJobs::where('cron_name', UtilConstant::CRON_LOOKUP_EMAIL_FOR_USER_SHEET)->get();
            $cronjobs->first()->current_status = "Running";
            $cronjobs->first()->save();
        })->after(function () {
            $cronjobs = CronJobs::where('cron_name', UtilConstant::CRON_LOOKUP_EMAIL_FOR_USER_SHEET)->get();
            $cronjobs->first()->current_status = "Not Running";
            $cronjobs->first()->save();
        })->when(function(){
            $cronjobs = CronJobs::where('cron_name', UtilConstant::CRON_LOOKUP_EMAIL_FOR_USER_SHEET)->get();
            if($cronjobs->first()->is_run == 'yes' && $cronjobs->first()->current_status == "Not Running"){
                return true;
            }
            return false;
        });
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
