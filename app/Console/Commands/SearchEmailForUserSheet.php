<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\MasterUserSheet;
use App\Helpers\UtilDebug;
use App\MasterUserContact;
use App\MappingUserContacts;
use App\AvailableEmail;
use App\MatchedContact;
use App\Contacts;

class SearchEmailForUserSheet extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'search:emailforusersheet';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'search email for user uploaded sheet';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {
        UtilDebug::debug("start processing");
        ini_set('max_execution_time', -1);
        ini_set('memory_limit', -1);
        ini_set('upload_max_filesize', -1);
        ini_set('post_max_size ', -1);
        ini_set('mysql.connect_timeout', 600);
        ini_set('default_socket_timeout', 600);
        $response = array();
        $master_user_sheet = MasterUserSheet::where("Status", "!=", "Completed")->get();
        if ($master_user_sheet->count() > 0) {
            foreach ($master_user_sheet AS $sheet) {
                $sheet_id = $sheet->ID;
                MasterUserSheet::where("ID", $sheet_id)->update(['Status' => 'Under Processing']);
                $data_for_email_processing = MasterUserContact::where('sheet_id', $sheet_id)->where('email_status', 'domain found')->get();
                $sheet_name = $sheet->Sheet_Name;
                echo "For Domain Found : => $sheet_id -- $sheet_name -- ".$data_for_email_processing->count();
                
                // serach in available email and matchedcontacts
                UtilDebug::debug("start email search processing");
                if ($data_for_email_processing->count() > 0) {
                    foreach ($data_for_email_processing AS $dep) {
                        $first_name = $dep->First_Name;
                        $last_name = $dep->Last_Name;
                        $company_domain = $dep->Company_Domain;
                        $title = $dep->Job_Title;
                        $available_email = AvailableEmail::where('first_name', $first_name)->where('last_name', $last_name)->where('company_domain', $company_domain)->where('job_title', $title)->get();
                        $update_data = false;
                        if ($available_email->count() > 0) {
                            $update_data = MasterUserContact::where('ID', $dep->ID)->update(['Contact_Email' => $available_email->first()->email, 'Email_Status' => 'valid', 'Validatation_Date' => $available_email->first()->created_at]);
                        } else {
                            $matched_contacts = MatchedContact::where('first_name', $first_name)->where('last_name', $last_name)->where('domain', $company_domain)->where('job_title', $title)->get();
                            if ($matched_contacts->count() > 0) {
                                $update_data = MasterUserContact::where('ID', $dep->ID)->update(['Contact_Email' => $matched_contacts->first()->email, 'Email_Status' => $matched_contacts->first()->email_status, 'Validatation_Date' => $matched_contacts->first()->email_validation_date]);
                            }else{
                                $full_name = trim($dep->Contact_Full_Name);
                                $job_title = trim($dep->Job_Title);
                                $company_name = trim($dep->Company_Name);
                                $user_id = $dep->User_ID;
                                $linkedin_id = $dep->Company_Linkedin_ID;
                                $full_name = (empty($full_name)) ? "" : $full_name;
                                $first_name = (empty($dep->First_Name)) ? "" : $dep->First_Name;
                                $last_name = (empty($dep->Last_Name)) ? "" : $dep->Last_Name;
                                $company_name = (empty($company_name)) ? "" : $company_name;
                                $job_title = (empty($job_title)) ? "" : $job_title;
                                $experience = (empty($dep->Exprience)) ? "" : $dep->Exprience;
                                $profile_link = "";
                                $location = (empty($dep->Location)) ? "" : $dep->Location;
                                $status = "invalid";
                                $process_for_contact_match = "not processed";
                                $contact = Contacts::create([
                                    "user_id" => $user_id,
                                    "linkedin_id" => $linkedin_id,
                                    "full_name" => $full_name,
                                    "first_name" => $first_name,
                                    "last_name" => $last_name,
                                    "company_name" => $company_name,
                                    "job_title" => $job_title,
                                    "experience" => $experience,
                                    "profile_link" => $profile_link,
                                    "location" => $location,
                                    "status" => $status,
                                    "process_for_contact_match" => $process_for_contact_match,
                                ]);
                                if ($contact) {
                                    $exist_in_map = MappingUserContacts::where('User_Contact_Id', $dep->ID)->where('Contacts_Id', $contact->id)->count();
                                    if ($exist_in_map <= 0) {
                                        $mapping_contact = MappingUserContacts::create(['Sheet_Id'=>$sheet_id,'User_Contact_Id' => $dep->ID, 'Contacts_Id' => $contact->id, 'Matched_Id' => 0]);
                                        if ($mapping_contact) {
                                            MasterUserContact::where('ID', $dep->ID)->update(['Email_Status' => 'under processing']);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            UtilDebug::debug("end email search processing");   
            }
        } else {
            $response['status'] = "fail";
            $response['message'] = "No, Sheet found for processing";
        }
        UtilDebug::debug("end processing");
    }
}
