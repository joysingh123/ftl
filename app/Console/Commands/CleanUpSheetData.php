<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Helpers\UtilDebug;
use App\MappingUserContacts;
use App\MasterUserContact;
use App\MasterUserSheet;
class CleanUpSheetData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cleanup:sheetdata';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        UtilDebug::debug("start processing");
            ini_set('max_execution_time', -1);
            ini_set('memory_limit', -1);
            ini_set('upload_max_filesize', -1);
            ini_set('post_max_size ', -1);
            ini_set('mysql.connect_timeout', 600);
            ini_set('default_socket_timeout', 600);
            $date = date("Y-m-d", strtotime(date("Y-m-d") . " -15 days"));
            $sheet_data = MasterUserSheet::whereDate('Created_At','<',$date)->get();
            if($sheet_data->count() > 0){
                foreach($sheet_data AS $sd){
                    $sheet_id = $sd->ID;
                    MappingUserContacts::where('Sheet_Id',$sheet_id)->delete();
                    MasterUserContact::where('Sheet_ID',$sheet_id)->delete();
                    MasterUserSheet::where('ID',$sheet_id)->delete();
                }
            }
            
        UtilDebug::debug("end processing");
    }
}
