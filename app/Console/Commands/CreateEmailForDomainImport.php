<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Helpers\UtilDebug;
use App\DomainSheet;
use App\DomainUserContact;
use App\AvailableEmail;
use App\Contacts;
use App\EmailFormat;
use App\Helpers\UtilConstant;
use App\Helpers\UtilString;
use App\DomainEmail;

class CreateEmailForDomainImport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:email';

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
        $response = array();
        $domain_user_sheet = DomainSheet::where("status", "!=", "Completed")->get();
        if ($domain_user_sheet->count() > 0) {
            foreach ($domain_user_sheet AS $sheet) {
                $sheet_id = $sheet->id;
                DomainSheet::where("id", $sheet_id)->update(['Status' => 'Under Processing']);
                $data_for_email_processing = DomainUserContact::where('sheet_id', $sheet_id)->where('status', 'company found')->orWhere('status', 'company not found')->get();
                $sheet_name = $sheet->sheet_name;
                echo "For Domain Found : => $sheet_id -- $sheet_name -- " . $data_for_email_processing->count();
                $found_in_available_email = 0;
                $found_in_contact_email = 0;
                $exist_in_contact_but_no_email = 0;
                $rejected = 0;
                UtilDebug::debug("start email search processing");
                if ($data_for_email_processing->count() > 0) {
                    foreach ($data_for_email_processing AS $dep) {
                        $first_name = $dep->first_name;
                        $last_name = $dep->last_name;
                        $domain = $dep->domain;
                        echo "$first_name---$last_name----$domain";
                        UtilDebug::debug("Start Fetching From Available Email");
                        $available_email = AvailableEmail::where('first_name', $first_name)->where('last_name', $last_name)->where('company_domain', $domain)->get();
                        UtilDebug::debug("End Fetching From Available Email");
                        if ($available_email->count() > 0) {
                            $found_in_available_email ++;
                            $email_status = 'valid';
                            $validation_date = $available_email->first()->created_at;
                            $available_email_status = $available_email->first()->email_status;
                            if($available_email_status == 'valid' || $available_email_status == 'invalid' || $available_email_status == 'catch all'){
                                $email_status = $available_email_status;
                                $validation_date = $available_email->first()->email_validation_date;
                            }
                            $update_data = DomainUserContact::where('id', $dep->id)->update(['email' => $available_email->first()->email, 'status' => $email_status, 'email_validation_date' => $validation_date]);
                        }else {
                            UtilDebug::debug("Start Fetching From Contacts Table");
                            $contacts = Contacts::where('first_name', $first_name)->where('last_name', $last_name)->where('domain', $domain)->get();
                            UtilDebug::debug("End Fetching From Contacts Table");
                            if ($contacts->count() > 0) {
                                $contact_email = $contacts->first()->email;
                                $contact_email_status = $contacts->first()->email_status;
                                $contact_email_validation_date = $contacts->first()->email_validation_date;
                                if(!empty($contact_email) && !empty($contact_email_status) && !empty($contact_email_validation_date)){
                                    $update_data = DomainUserContact::where('id', $dep->id)->update(['email' => $contact_email, 'status' => $contact_email_status, 'email_validation_date' => $contact_email_validation_date]);
                                    if($update_data){
                                        $found_in_contact_email ++ ;
                                    }
                                }else if($contact_email_status == 'invalid' || $contact_email_status == 'bounce' || $contact_email_status == 'unrecognized'){
                                    $update_data = DomainUserContact::where('id', $dep->id)->update(['status' => $contact_email_status, 'email_validation_date' => $contact_email_validation_date]);
                                    if($update_data){
                                        $found_in_contact_email ++ ;
                                    }
                                }else{
                                    $exist_in_contact_but_no_email ++;
                                }
                            }else{
                                $first_name = strtolower(trim($first_name));
                                $last_name = strtolower(trim($last_name));
                                $first_name_first_char = substr($first_name, 0, 1);
                                $last_name_first_char = substr($last_name, 0, 1);
                                $first_name_first_two_char = substr($first_name, 0, 2);
                                $last_name_first_two_char = substr($last_name, 0, 2);
                                $domain = strtolower(trim($domain));
                                $vars = array(
                                    UtilConstant::FIRST_NAME => $first_name,
                                    UtilConstant::LAST_NAME => $last_name,
                                    UtilConstant::FIRST_NAME_FIRST_CHARACTER => $first_name_first_char,
                                    UtilConstant::LAST_NAME_FIRST_CHARACTER => $last_name_first_char,
                                    UtilConstant::FIRST_NAME_FIRST_TWO_CHARACTER => $first_name_first_two_char,
                                    UtilConstant::LAST_NAME_FIRST_TWO_CHARACTER => $last_name_first_two_char,
                                    UtilConstant::DOMAIN => $domain
                                );
                                UtilDebug::debug("Start Fetching From Email Format Table");
                                $available_format_for_domain = EmailFormat::where("company_domain", $domain)->orderBY('format_percentage', 'DESC')->take(2)->get();
                                if ($available_format_for_domain->count() > 0) {
                                    $email_created_status = false;
                                    $data = $available_format_for_domain->pluck('format_percentage')->values();
                                    $process_data = [];
                                    if (count($data) == 1) {
                                        if ($data[0] >= 10) {
                                            $process_data = $available_format_for_domain;
                                        }else{
                                            $available_format_new = new EmailFormat();
                                            $available_format_new->email_format = "FIRSTNAME.LASTNAME@DOMAIN";
                                            $available_format_new->format_percentage = 100;
                                            $process_data[] = $available_format_new; 
                                        }
                                    }
                                    if (count($data) == 2) {
                                        if ($data[0] >= 10 && $data[1] <= 10) {
                                            $process_data = $available_format_for_domain->forget(1);
                                        } else if ($data[0] > 10 && $data[1] > 10) {
                                            $process_count = $data[0] - $data[1];
                                            if ($process_count <= 40) {
                                                $process_data = $available_format_for_domain;
                                            } else {
                                                $process_data = $available_format_for_domain->forget(1);
                                            }
                                        }else if($data[0] < 10 && $data[1] < 10){
                                            $available_format_new = new EmailFormat();
                                            $available_format_new->email_format = "FIRSTNAME.LASTNAME@DOMAIN";
                                            $available_format_new->format_percentage = 100;
                                            $process_data[] = $available_format_new;
                                        }
                                    }
                                    if (count($process_data) > 0) {
                                        $is_bounce = false;
                                        foreach ($process_data AS $av) {
                                            $email_format = $av->email_format;
                                            $email_format = "$email_format";
                                            $email = str_replace("'", "", strtr($email_format, $vars));
                                            if (UtilString::is_email($email)) {
                                                $newemail = new DomainEmail();
                                                $newemail->domain_user_contact_id = $dep->id;
                                                $newemail->email = trim($email);
                                                $newemail->status = "unverified";
                                                $newemail->created_from = "Email Format";
                                                $newemail->save();
                                                $email_created_status = true;
                                            }
                                        }
                                    }
                                    if ($email_created_status) {
                                        $dep->status = "created";
                                        $dep->save();
                                    }else{
                                        $dep->status = "unrecognized";
                                        $dep->save();
                                    }
                                }else{
                                    $default_format = array("FIRSTNAME.LASTNAME@DOMAIN","FIRSTNAME@DOMAIN","FLASTNAME@DOMAIN");
                                    foreach ($default_format AS $df){
                                        $email_format = "$df";
                                        $email = str_replace("'", "", strtr($email_format, $vars));
                                        $newemail = new DomainEmail();
                                        $newemail->domain_user_contact_id = $dep->id;
                                        $newemail->email = trim($email);
                                        $newemail->status = "unverified";
                                        $newemail->created_from = "guess";
                                        $newemail->save();
                                    }
                                    $dep->status = "created";
                                    $dep->save();
                                }
                            }
                        }
                    }
                }
                $process_count_sheet_count = DomainUserContact::where('sheet_id', $sheet_id)->where('status', 'company found')->orWhere('status', 'company not found')->orWhere('status', 'created')->get();
                if($process_count_sheet_count->count() == 0){
                    DomainSheet::where("id", $sheet_id)->update(['status' => 'Completed']);
                }
            }
        }
        UtilDebug::debug("end processing");
    }
}
