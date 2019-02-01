<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Helpers\UtilDebug;
use App\Helpers\UtilConstant;
use App\Helpers\UtilString;
use App\EmailValidationApi;
use App\EmailSheet;
use App\EmailVerification;
use App\Traits\ValidateEmailTraits;

class ValidateVerificationEmail extends Command
{
    use ValidateEmailTraits;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'validate:verificationemail';

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
        $email_sheet = EmailSheet::where("status", "!=", "Completed")->get();
        if ($email_sheet->count() > 0) {
            foreach ($email_sheet AS $sheet) {
                $sheet_id = $sheet->id;
                EmailSheet::where("id", $sheet_id)->update(['status' => 'Under Processing']);
                $data_for_email_processing = EmailVerification::where('sheet_id', $sheet_id)->where('status', 'unverified')->get();
                if ($data_for_email_processing->count() > 0) {
                    $validation_api = EmailValidationApi::where('active','yes')->where('status','enable')->orderBy('cron_count','ASC')->get();
                    if($validation_api->count() > 0){
                        $validation_api = $validation_api->first();
                        $validation_api->cron_count = $validation_api->cron_count + 1;
                        $validation_api->save();
                        foreach ($data_for_email_processing AS $dep) {
                            $email = $dep->email;
                            $v_response = $this->validateEmail($email,$validation_api);
                            if($v_response['email_status'] == ""){
                                $response_api_array = json_decode($v_response['response'],TRUE);
                                if(isset($response_api_array['error']) && $response_api_array['error']['code'] == 104){
                                    $validation_api->active = 'No';
                                    break 2;
                                }
                                if(isset($response_api_array['error']) && $response_api_array['error']['code'] == 999){
                                    $dep->status = 'Timeout';
                                    $dep->save(); 
                                }
                            }else{
                                $status = $v_response['email_status'];
                                $validation_date = $v_response['validation_date'];
                                $dep->status = $status;
                                $dep->email_validation_date = $validation_date;
                                $dep->save();
                            }
                        }
                        $validation_api->cron_count = $validation_api->cron_count - 1;
                        $validation_api->save();
                    }else{
                        echo "No, usable api key found..";
                    }
                }else{
                    EmailSheet::where("id", $sheet_id)->update(['status' => 'Completed']);
                }
                
            }
        }
        UtilDebug::debug("end processing");
    }
}
