<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\MatchedContact;
use App\MappingUserContacts;
use App\MasterUserContact;
use App\MasterUserSheet;
use App\Helpers\UtilDebug;

class LookupUserContactEmail extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lookup:contactemail';

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
        $sheet_info = MasterUserSheet::where('Status', 'Under Processing')->get();
        if ($sheet_info->count() > 0) {
            foreach ($sheet_info AS $si){
                $sheet_id = $si->ID;
                $mapping_data = MappingUserContacts::where('Sheet_Id', $sheet_id)->where('status','not processed')->get();
                if ($mapping_data->count() > 0) {
                    echo "Data Found For Processing: " . $mapping_data->count();
                    foreach ($mapping_data AS $md) {
                        $mapping_id = $md->ID;
                        $contact_id = $md->Contacts_Id;
                        $user_contact_id = $md->User_Contact_Id;
                        $matched_data = MatchedContact::where('contact_id', $contact_id)->get();
                        echo $matched_data;
                        if ($matched_data->count() > 0) {
                            $matched_email = $matched_data->first()->email;
                            $matched_email_status = $matched_data->first()->email_status;
                            $matched_email_validation_date = $matched_data->first()->email_validation_date;
                            if (!empty($matched_email) && !empty($matched_email_status) && !empty($matched_email_validation_date)) {
                                $master_user_contacts = MasterUserContact::where('ID', $user_contact_id)->update(['Contact_Email' => $matched_email, "Email_Status" => $matched_email_status, "Validatation_Date" => $matched_email_validation_date]);
                                if ($master_user_contacts) {
                                    MappingUserContacts::where('ID', $mapping_id)->update(['Status' => 'processed']);
                                }
                            }else if($matched_email_status == 'invalid' || $matched_email_status == 'bounce' || $matched_email_status == 'unrecognized'){
                                $master_user_contacts = MasterUserContact::where('ID', $user_contact_id)->update(["Email_Status" => $matched_email_status, "Validatation_Date" => $matched_email_validation_date]);
                                if ($master_user_contacts) {
                                    MappingUserContacts::where('ID', $mapping_id)->update(['Status' => 'processed']);
                                }
                            }else{
                                
                            }
                        }
                    }
                }else{
                    MasterUserSheet::where('ID', $sheet_id)->update(['Status'=>'Completed']);
                }
            }
        }
        UtilDebug::debug("end processing");
    }
}
