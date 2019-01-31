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
        'App\Console\Commands\SearchEmailForUserSheet1',
        'App\Console\Commands\LookupUserContactEmail',
        'App\Console\Commands\CreateEmailForDomainImport',
        'App\Console\Commands\ValidateDomainEmail',
        'App\Console\Commands\ValidateDomainEmail1',
        'App\Console\Commands\ValidateDomainEmail2',
        'App\Console\Commands\ValidateDomainEmail3',
        'App\Console\Commands\ValidateDomainEmail4',
        'App\Console\Commands\ValidateDomainEmail5',
        'App\Console\Commands\ValidateDomainEmail6',
        'App\Console\Commands\ValidateDomainEmail7',
        'App\Console\Commands\ValidateDomainEmail8',
        'App\Console\Commands\ValidateDomainEmail9',
        'App\Console\Commands\ValidateVerificationEmail'
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('search:emailforusersheet')->cron('*/2 * * * *')->withoutOverlapping()->before(function () {
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
        
        $schedule->command('search:emailforusersheet1')->everyFiveMinutes()->withoutOverlapping()->before(function () {
            $cronjobs = CronJobs::where('cron_name', UtilConstant::CRON_SEARCH_EMAIL_FOR_USER_SHEET_1)->get();
            $cronjobs->first()->current_status = "Running";
            $cronjobs->first()->save();
        })->after(function () {
            $cronjobs = CronJobs::where('cron_name', UtilConstant::CRON_SEARCH_EMAIL_FOR_USER_SHEET_1)->get();
            $cronjobs->first()->current_status = "Not Running";
            $cronjobs->first()->save();
        })->when(function(){
            $cronjobs = CronJobs::where('cron_name', UtilConstant::CRON_SEARCH_EMAIL_FOR_USER_SHEET_1)->get();
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
        
        $schedule->command('create:email')->everyFiveMinutes()->withoutOverlapping()->before(function () {
            $cronjobs = CronJobs::where('cron_name', UtilConstant::CRON_CREATE_EMAIL_FOR_DOMAIN_USER_SHEET)->get();
            $cronjobs->first()->current_status = "Running";
            $cronjobs->first()->save();
        })->after(function () {
            $cronjobs = CronJobs::where('cron_name', UtilConstant::CRON_CREATE_EMAIL_FOR_DOMAIN_USER_SHEET)->get();
            $cronjobs->first()->current_status = "Not Running";
            $cronjobs->first()->save();
        })->when(function(){
            $cronjobs = CronJobs::where('cron_name', UtilConstant::CRON_CREATE_EMAIL_FOR_DOMAIN_USER_SHEET)->get();
            if($cronjobs->first()->is_run == 'yes' && $cronjobs->first()->current_status == "Not Running"){
                return true;
            }
            return false;
        });
        
        $schedule->command('validate:email')->everyFiveMinutes()->withoutOverlapping()->before(function () {
            $cronjobs = CronJobs::where('cron_name', UtilConstant::CRON_VALIDATE_EMAIL_FOR_DOMAIN_USER_SHEET)->get();
            $cronjobs->first()->current_status = "Running";
            $cronjobs->first()->save();
        })->after(function () {
            $cronjobs = CronJobs::where('cron_name', UtilConstant::CRON_VALIDATE_EMAIL_FOR_DOMAIN_USER_SHEET)->get();
            $cronjobs->first()->current_status = "Not Running";
            $cronjobs->first()->save();
        })->when(function(){
            $cronjobs = CronJobs::where('cron_name', UtilConstant::CRON_VALIDATE_EMAIL_FOR_DOMAIN_USER_SHEET)->get();
            if($cronjobs->first()->is_run == 'yes' && $cronjobs->first()->current_status == "Not Running"){
                return true;
            }
            return false;
        });
        $schedule->command('validate:email1')->everyFiveMinutes()->withoutOverlapping()->before(function () {
            $cronjobs = CronJobs::where('cron_name', UtilConstant::CRON_VALIDATE_EMAIL_FOR_DOMAIN_USER_SHEET_1)->get();
            $cronjobs->first()->current_status = "Running";
            $cronjobs->first()->save();
        })->after(function () {
            $cronjobs = CronJobs::where('cron_name', UtilConstant::CRON_VALIDATE_EMAIL_FOR_DOMAIN_USER_SHEET_1)->get();
            $cronjobs->first()->current_status = "Not Running";
            $cronjobs->first()->save();
        })->when(function(){
            $cronjobs = CronJobs::where('cron_name', UtilConstant::CRON_VALIDATE_EMAIL_FOR_DOMAIN_USER_SHEET_1)->get();
            if($cronjobs->first()->is_run == 'yes' && $cronjobs->first()->current_status == "Not Running"){
                return true;
            }
            return false;
        });
        $schedule->command('validate:email2')->everyFiveMinutes()->withoutOverlapping()->before(function () {
            $cronjobs = CronJobs::where('cron_name', UtilConstant::CRON_VALIDATE_EMAIL_FOR_DOMAIN_USER_SHEET_2)->get();
            $cronjobs->first()->current_status = "Running";
            $cronjobs->first()->save();
        })->after(function () {
            $cronjobs = CronJobs::where('cron_name', UtilConstant::CRON_VALIDATE_EMAIL_FOR_DOMAIN_USER_SHEET_2)->get();
            $cronjobs->first()->current_status = "Not Running";
            $cronjobs->first()->save();
        })->when(function(){
            $cronjobs = CronJobs::where('cron_name', UtilConstant::CRON_VALIDATE_EMAIL_FOR_DOMAIN_USER_SHEET_2)->get();
            if($cronjobs->first()->is_run == 'yes' && $cronjobs->first()->current_status == "Not Running"){
                return true;
            }
            return false;
        });
        $schedule->command('validate:email3')->everyFiveMinutes()->withoutOverlapping()->before(function () {
            $cronjobs = CronJobs::where('cron_name', UtilConstant::CRON_VALIDATE_EMAIL_FOR_DOMAIN_USER_SHEET_3)->get();
            $cronjobs->first()->current_status = "Running";
            $cronjobs->first()->save();
        })->after(function () {
            $cronjobs = CronJobs::where('cron_name', UtilConstant::CRON_VALIDATE_EMAIL_FOR_DOMAIN_USER_SHEET_3)->get();
            $cronjobs->first()->current_status = "Not Running";
            $cronjobs->first()->save();
        })->when(function(){
            $cronjobs = CronJobs::where('cron_name', UtilConstant::CRON_VALIDATE_EMAIL_FOR_DOMAIN_USER_SHEET_3)->get();
            if($cronjobs->first()->is_run == 'yes' && $cronjobs->first()->current_status == "Not Running"){
                return true;
            }
            return false;
        });
        
        $schedule->command('validate:email4')->everyFiveMinutes()->withoutOverlapping()->before(function () {
            $cronjobs = CronJobs::where('cron_name', UtilConstant::CRON_VALIDATE_EMAIL_FOR_DOMAIN_USER_SHEET_4)->get();
            $cronjobs->first()->current_status = "Running";
            $cronjobs->first()->save();
        })->after(function () {
            $cronjobs = CronJobs::where('cron_name', UtilConstant::CRON_VALIDATE_EMAIL_FOR_DOMAIN_USER_SHEET_4)->get();
            $cronjobs->first()->current_status = "Not Running";
            $cronjobs->first()->save();
        })->when(function(){
            $cronjobs = CronJobs::where('cron_name', UtilConstant::CRON_VALIDATE_EMAIL_FOR_DOMAIN_USER_SHEET_4)->get();
            if($cronjobs->first()->is_run == 'yes' && $cronjobs->first()->current_status == "Not Running"){
                return true;
            }
            return false;
        });
        
        $schedule->command('validate:email5')->everyFiveMinutes()->withoutOverlapping()->before(function () {
            $cronjobs = CronJobs::where('cron_name', UtilConstant::CRON_VALIDATE_EMAIL_FOR_DOMAIN_USER_SHEET_5)->get();
            $cronjobs->first()->current_status = "Running";
            $cronjobs->first()->save();
        })->after(function () {
            $cronjobs = CronJobs::where('cron_name', UtilConstant::CRON_VALIDATE_EMAIL_FOR_DOMAIN_USER_SHEET_5)->get();
            $cronjobs->first()->current_status = "Not Running";
            $cronjobs->first()->save();
        })->when(function(){
            $cronjobs = CronJobs::where('cron_name', UtilConstant::CRON_VALIDATE_EMAIL_FOR_DOMAIN_USER_SHEET_5)->get();
            if($cronjobs->first()->is_run == 'yes' && $cronjobs->first()->current_status == "Not Running"){
                return true;
            }
            return false;
        });
        
        $schedule->command('validate:email6')->everyFiveMinutes()->withoutOverlapping()->before(function () {
            $cronjobs = CronJobs::where('cron_name', UtilConstant::CRON_VALIDATE_EMAIL_FOR_DOMAIN_USER_SHEET_6)->get();
            $cronjobs->first()->current_status = "Running";
            $cronjobs->first()->save();
        })->after(function () {
            $cronjobs = CronJobs::where('cron_name', UtilConstant::CRON_VALIDATE_EMAIL_FOR_DOMAIN_USER_SHEET_6)->get();
            $cronjobs->first()->current_status = "Not Running";
            $cronjobs->first()->save();
        })->when(function(){
            $cronjobs = CronJobs::where('cron_name', UtilConstant::CRON_VALIDATE_EMAIL_FOR_DOMAIN_USER_SHEET_6)->get();
            if($cronjobs->first()->is_run == 'yes' && $cronjobs->first()->current_status == "Not Running"){
                return true;
            }
            return false;
        });
        
        $schedule->command('validate:email7')->everyFiveMinutes()->withoutOverlapping()->before(function () {
            $cronjobs = CronJobs::where('cron_name', UtilConstant::CRON_VALIDATE_EMAIL_FOR_DOMAIN_USER_SHEET_7)->get();
            $cronjobs->first()->current_status = "Running";
            $cronjobs->first()->save();
        })->after(function () {
            $cronjobs = CronJobs::where('cron_name', UtilConstant::CRON_VALIDATE_EMAIL_FOR_DOMAIN_USER_SHEET_7)->get();
            $cronjobs->first()->current_status = "Not Running";
            $cronjobs->first()->save();
        })->when(function(){
            $cronjobs = CronJobs::where('cron_name', UtilConstant::CRON_VALIDATE_EMAIL_FOR_DOMAIN_USER_SHEET_7)->get();
            if($cronjobs->first()->is_run == 'yes' && $cronjobs->first()->current_status == "Not Running"){
                return true;
            }
            return false;
        });
        
        $schedule->command('validate:email8')->everyFiveMinutes()->withoutOverlapping()->before(function () {
            $cronjobs = CronJobs::where('cron_name', UtilConstant::CRON_VALIDATE_EMAIL_FOR_DOMAIN_USER_SHEET_8)->get();
            $cronjobs->first()->current_status = "Running";
            $cronjobs->first()->save();
        })->after(function () {
            $cronjobs = CronJobs::where('cron_name', UtilConstant::CRON_VALIDATE_EMAIL_FOR_DOMAIN_USER_SHEET_8)->get();
            $cronjobs->first()->current_status = "Not Running";
            $cronjobs->first()->save();
        })->when(function(){
            $cronjobs = CronJobs::where('cron_name', UtilConstant::CRON_VALIDATE_EMAIL_FOR_DOMAIN_USER_SHEET_8)->get();
            if($cronjobs->first()->is_run == 'yes' && $cronjobs->first()->current_status == "Not Running"){
                return true;
            }
            return false;
        });
        
        $schedule->command('validate:email9')->everyFiveMinutes()->withoutOverlapping()->before(function () {
            $cronjobs = CronJobs::where('cron_name', UtilConstant::CRON_VALIDATE_EMAIL_FOR_DOMAIN_USER_SHEET_9)->get();
            $cronjobs->first()->current_status = "Running";
            $cronjobs->first()->save();
        })->after(function () {
            $cronjobs = CronJobs::where('cron_name', UtilConstant::CRON_VALIDATE_EMAIL_FOR_DOMAIN_USER_SHEET_9)->get();
            $cronjobs->first()->current_status = "Not Running";
            $cronjobs->first()->save();
        })->when(function(){
            $cronjobs = CronJobs::where('cron_name', UtilConstant::CRON_VALIDATE_EMAIL_FOR_DOMAIN_USER_SHEET_9)->get();
            if($cronjobs->first()->is_run == 'yes' && $cronjobs->first()->current_status == "Not Running"){
                return true;
            }
            return false;
        });
        
        $schedule->command('validate:verificationemail')->everyFiveMinutes()->withoutOverlapping()->before(function () {
            $cronjobs = CronJobs::where('cron_name', UtilConstant::CRON_VALIDATE_EMAIL_FOR_EMAIL_SHEET)->get();
            $cronjobs->first()->current_status = "Running";
            $cronjobs->first()->save();
        })->after(function () {
            $cronjobs = CronJobs::where('cron_name', UtilConstant::CRON_VALIDATE_EMAIL_FOR_EMAIL_SHEET)->get();
            $cronjobs->first()->current_status = "Not Running";
            $cronjobs->first()->save();
        })->when(function(){
            $cronjobs = CronJobs::where('cron_name', UtilConstant::CRON_VALIDATE_EMAIL_FOR_EMAIL_SHEET)->get();
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
