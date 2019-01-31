<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Helpers\UtilDebug;
use App\Helpers\UtilConstant;
use App\EmailValidationApi;
use App\Helpers\UtilString;
use App\DomainEmail;
use App\DomainUserContact;
use DB;
use App\Traits\ValidateEmailTraits;

class ValidateDomainEmail4 extends Command
{
    use ValidateEmailTraits;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'validate:email4';

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
        $limit = 250;
        $emails = DB::table('domain_emails')
                        ->select('domain_user_contact_id', DB::raw("group_concat(email) AS emails"))
                        ->groupBy('domain_user_contact_id')
                        ->where('status', 'unverified')
                        ->take($limit)->get();
        
        if ($emails->count() > 0) {
            $plucked_email = $emails->pluck('domain_user_contact_id');
            $plucked_email_array = $plucked_email->all();
            $result = DomainEmail::whereIn('domain_user_contact_id', $plucked_email_array)->update(['status' => 'cron4']);
            $validation_api = EmailValidationApi::where('active','yes')->where('status','enable')->orderBy('cron_count','ASC')->get();
            if($validation_api->count() > 0){
                $validation_api = $validation_api->first();
                $validation_api->cron_count = $validation_api->cron_count + 1;
                $validation_api->save();
                if($result > 0){
                    foreach ($emails AS $email_record) {
                        $domain_user_contact_id = $email_record->domain_user_contact_id;
                        DomainEmail::where('domain_user_contact_id', $domain_user_contact_id)->update(['status' => 'Not Checked']);
                        $emails_db = $email_record->emails;
                        $emails_array = array();
                        if (UtilString::contains($emails_db, ",")) {
                            $emails_array = explode(",", $emails_db);
                        } else {
                            $emails_array[] = $emails_db;
                        }
                        $email_array_count = count($emails_array);
                        $email_data = array();
                        $is_valid = false;
                        foreach ($emails_array AS $email) {
                            $v_response = $this->validateEmail($email,$validation_api);
                            $email_status = $v_response['email_status'];
                            $email_validation_date = date("Y-m-d H:i:s");
                            if ($email_status == 'valid'){
                                $is_valid = true;
                                $updated_as = DomainEmail::where('domain_user_contact_id', $domain_user_contact_id)->where('email', $email)->update(['status' => $email_status]);
                                if($updated_as){
                                    $user_contact_updated = DomainUserContact::where('id',$domain_user_contact_id)->update(['email'=>$email,'email_validation_date'=>$email_validation_date,'status'=>$email_status]);
                                }
                                break;
                            }
                            if($email_status = ""){
                                $response_api_array = json_decode($v_response['response'],TRUE);
                                if(isset($response_api_array['error']) && $response_api_array['error']['code'] == 104){
                                    DomainEmail::where('status', 'cron2')->orWhere('email',$email)->update(['status' => 'unverified']);
                                    $validation_api->active = 'No';
                                    break 2;
                                }
                            }
                            DomainEmail::where('domain_user_contact_id', $domain_user_contact_id)->where('email', $email)->update(['status' => $email_status]);
                            $email_data[$email] = $email_status;
                        }
                        if(!$is_valid){
                            $email_validation_date = date("Y-m-d H:i:s");
                            if(in_array("catch all", $email_data)){
                                $email = array_search('catch all',$email_data);
                                DomainUserContact::where('id',$domain_user_contact_id)->update(['email'=>$email,'email_validation_date'=>$email_validation_date,'status'=>'catch all']);
                            }else{
                                $email = array_search('invalid',$email_data);
                                DomainUserContact::where('id',$domain_user_contact_id)->update(['email'=>$email,'email_validation_date'=>$email_validation_date,'status'=>'invalid']);
                            }
                        }
                        print_r($email_data);
                    }
                    $validation_api->cron_count = $validation_api->cron_count - 1;
                    $validation_api->save();
                }
            }else{
                $response['status'] = "fail";
                $response['status'] = "No, usable api key found..";
            }
        }
        UtilDebug::debug("end processing");
    }
}
