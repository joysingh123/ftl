<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Excel;
use File;
use Session;
use DB;
use App\MasterUserSheet;
use App\Helpers\UtilString;
use App\CompaniesWithDomain;
use App\MasterUserContact;
use App\CompaniesWithoutDomain;

class ImportDataController extends Controller {

    public function importContactView() {
        $user_id = Auth::id();
        $master_user_sheet_data = MasterUserSheet::where('User_ID', $user_id)->orderBY('created_at', 'DESC')->get();
        $sheet_stats = DB::table('master_user_contacts')->select(DB::raw("Sheet_ID,Email_Status,Count(Email_Status) AS sheet_stats"))->where('User_ID',$user_id)->groupBy('Sheet_ID')->groupBy('Email_Status')->get();
        $sheet_stats_array = array();
        if($sheet_stats->count() > 0){
            foreach($sheet_stats AS $k=>$ss){
                $sheet_stats_array[$ss->Sheet_ID][$ss->Email_Status] =  $ss->sheet_stats ;
            }
        }
        //SELECT Sheet_ID,Email_Status,Count(Email_Status) FROM `master_user_contacts` WHERE Sheet_ID = 29 GROUP BY Sheet_ID,Email_Status
        $hide_download = false;
        $data = MasterUserSheet::where('User_ID', $user_id)->where('Status', '!=', 'Completed')->get();
        if ($data->count() > 0) {
            $hide_download = true;
        }
        $estimated_time = DB::table('master_user_sheets')
                    ->select(DB::raw("avg((total_count)/(timestampdiff(MINUTE, created_at,updated_at))) AS estimated_time"))
                     ->get();
        $estimated_time = $estimated_time->first()->estimated_time;
        $estimated_time = round($estimated_time);
        $data_progress = array();
        $completion_progress = array();
        if($master_user_sheet_data->count() > 0){
            $data_progress['Contact Uploading'] = '';
            $data_progress['Contact Added'] = '';
            $data_progress['Under Processing'] = '';
            $data_progress['Completed'] = '';
            $completion_progress['Contact Uploading'] = '';
            $completion_progress['Contact Added'] = '';
            $completion_progress['Under Processing'] = '';
            $completion_progress['Completed'] = '';
            if($master_user_sheet_data->first()->Status == 'Contact Uploading'){
                $data_progress['Contact Uploading'] = 'completed';
            }
            if($master_user_sheet_data->first()->Status == 'Contact Added'){
                $completion_progress['Contact Uploading'] = '<i class="fa fa-check-circle"></i>';
                $data_progress['Contact Uploading'] = 'completed';
                $data_progress['Contact Added'] = 'completed';
            }
            if($master_user_sheet_data->first()->Status == 'Under Processing'){
                $completion_progress['Contact Uploading'] = '<i class="fa fa-check-circle"></i>';
                $completion_progress['Contact Added'] = '<i class="fa fa-check-circle"></i>';
                $data_progress['Contact Uploading'] = 'completed';
                $data_progress['Contact Added'] = 'completed';
                $data_progress['Under Processing'] = 'completed';
            }
            if($master_user_sheet_data->first()->Status == 'Completed'){
                $completion_progress['Contact Uploading'] = '<i class="fa fa-check-circle"></i>';
                $completion_progress['Contact Added'] = '<i class="fa fa-check-circle"></i>';
                $completion_progress['Under Processing'] = '<i class="fa fa-check-circle"></i>';
                $completion_progress['Completed'] = '<i class="fa fa-check-circle"></i>';
                $data_progress['Contact Uploading'] = 'completed';
                $data_progress['Contact Added'] = 'completed';
                $data_progress['Under Processing'] = 'completed';
                $data_progress['Completed'] = 'completed';
            }
        }
        return view('importusercontact')->with('completion_progress',$completion_progress)->with('data_progress',$data_progress)->with('estimated_time',$estimated_time)->with('sheet_data', $master_user_sheet_data)->with('hide_download', $hide_download)->with('sheet_stats', $sheet_stats_array);
    }

    public function importContactData(Request $request) {
        ini_set('max_execution_time', -1);
        ini_set('memory_limit', -1);
        ini_set('upload_max_filesize', -1);
        ini_set('post_max_size ', -1);
        ini_set('mysql.connect_timeout', 600);
        ini_set('default_socket_timeout', 600);
        $this->validate($request, array(
            'file' => 'required'
        ));

        if ($request->hasFile('file')) {
            $extension = File::extension($request->file->getClientOriginalName());
            if ($extension == "xlsx" || $extension == "xls") {
                $path = $request->file->getRealPath();
                $filename = $request->file->getClientOriginalName();
                $sheet_tag = (UtilString::is_empty_string($request->sheet_tag)) ? NULL : $request->sheet_tag;
                $data = Excel::load($path, function($reader) {
                            
                        })->get();
                $header = $data->getHeading();
                if (in_array('full_name', $header) && in_array('first_name', $header) && in_array('last_name', $header) && in_array('company', $header) && in_array('company_url', $header) && in_array('title', $header) && in_array('experience', $header) && in_array('location', $header)) {
                    if (!empty($data) && $data->count() < 20000) {
                        $total_count = $data->count();
                        $master_user_sheet = MasterUserSheet::create(["sheet_name" => $filename, "user_id" => Auth::id(), "total_count" => $total_count, "sheet_tag" => $sheet_tag, "status" => "Contact Uploading"]);
                        $sheet_id = $master_user_sheet->id;
                        if ($sheet_id > 0) {
                            $duplicate_array = array();
                            foreach ($data as $key => $value) {
                                $full_name = trim($value->full_name);
                                $first_name = trim($value->first_name);
                                $last_name = trim($value->last_name);
                                $job_title = trim($value->title);
                                $company = trim($value->company);
                                $location = trim($value->location);
                                $experience = trim($value->experience);
                                $company_url = trim($value->company_url);
                                $company_linkedin_id = UtilString::get_company_id_from_url($company_url);
                                $email_status = "contact added";

                                //logic for first name and last name
                                if ($first_name == "" || $last_name == "") {
                                    $explode_name = explode(" ", $full_name);
                                    if (count($explode_name) == 1) {
                                        $first_name = $explode_name[0];
                                    } else if (count($explode_name) == 2) {
                                        $first_name = $explode_name[0];
                                        $last_name = $explode_name[1];
                                    }
                                }
                                $company_domain = NULL;
                                if ($company_linkedin_id > 0) {
                                    $company_info = CompaniesWithDomain::where('linkedin_id', '=', $company_linkedin_id)->get();
                                    if ($company_info->count() > 0) {
                                        $email_status = "domain found";
                                        $company_domain = $company_info->first()->company_domain;
                                        $valid_email = "$first_name.$last_name@$company_domain";
                                        if(UtilString::is_email($valid_email)){
                                            
                                        }else{
                                            $email_status = "unknown";
                                        }
                                    } else {
                                        $email_status = "domain not found";
                                    }
                                } else {
                                    $email_status = "company not found";
                                }

                                $exsting_string = "$first_name$last_name$company_domain";
                                if (in_array($exsting_string, $duplicate_array)) {
                                    $email_status = "duplicate";
                                } else {
                                    $duplicate_array[] = $exsting_string;
                                }
                                if (UtilString::is_empty_string($first_name) && UtilString::is_empty_string($last_name)) {
                                    $email_status = "unknown";
                                }
                                if(UtilString::is_empty_string($first_name) && !UtilString::is_empty_string($last_name)){
                                    $email_status = "unknown";
                                }
                                if(UtilString::contains($first_name, "(") || UtilString::contains($first_name, ")") || UtilString::contains($last_name, "(") || UtilString::contains($last_name, ")")){
                                    $email_status = "unknown";
                                }
                                if(strlen($first_name) == 1 || strlen($last_name == 1)){
                                    $email_status = "unknown";
                                }
                                $insert_array = [
                                    'user_id' => Auth::id(),
                                    'sheet_id' => $sheet_id,
                                    'contact_full_name' => $full_name,
                                    'first_name' => $first_name,
                                    'last_name' => $last_name,
                                    'job_title' => $job_title,
                                    'company_name' => $company,
                                    'company_url' => $company_url,
                                    'location' => $location,
                                    'experience' => $experience,
                                    'company_linkedin_id' => $company_linkedin_id,
                                    'email_status' => $email_status,
                                    'company_domain' => $company_domain
                                ];
                                $insert[] = $insert_array;
                            }
                            if (!empty($insert)) {
                                $insert_chunk = array_chunk($insert, 100);
                                foreach ($insert_chunk AS $ic) {
                                    $insertData = DB::table('master_user_contacts')->insert($ic);
                                }
                                if ($insertData) {
                                    Session::flash('success', 'Your Data has successfully imported');
                                    $master_user_sheet->status = "Contact Added";
                                    $master_user_sheet->save();
                                    return back();
                                } else {
                                    Session::flash('error', 'Error inserting the data..');
                                    return back();
                                }
                            }
                        } else {
                            Session::flash('error', 'Error inserting the data..');
                            return back();
                        }
                    } else {
                        Session::flash('error', "The Sheet contains Only 20,000 records");
                        return back();
                    }
                } else {
                    Session::flash('error', "The Sheet Header contain wrong column name");
                    return back();
                }
            } else {
                Session::flash('error', 'File is a ' . $extension . ' file.!! Please upload a valid xls file..!!');
                return back();
            }
        }
    }

    public function importCompanyView() {
        return view('importcompaniesdata');
    }

    public function importCompanyData(Request $request) {
        ini_set('max_execution_time', -1);
        ini_set('memory_limit', -1);
        ini_set('upload_max_filesize', -1);
        ini_set('post_max_size ', -1);
        ini_set('mysql.connect_timeout', 600);
        ini_set('default_socket_timeout', 600);
        $this->validate($request, array(
            'file' => 'required'
        ));
        if ($request->hasFile('file')) {
            $extension = File::extension($request->file->getClientOriginalName());
            if ($extension == "xlsx" || $extension == "xls") {
                $path = $request->file->getRealPath();
                $data = Excel::load($path, function($reader) {
                            
                        })->get();
                $header = $data->getHeading();
                if (in_array('linkedin_id', $header) && in_array('linkedin_url', $header) && in_array('company_domain', $header) && in_array('company_name', $header) && in_array('company_type', $header) && in_array('industry', $header) && in_array('city', $header) && in_array('employee_size', $header)) {
                    if (!empty($data) && $data->count()) {
                        $duplicate_in_sheet = 0;
                        $already_exist_in_db = 0;
                        $inserted = 0;
                        $domain_not_exist = 0;
                        $junk_count = 0;
                        $junk_data_array = array();
                        $domain_not_found = array();
                        $insert = array();
                        $duplicate = array();
                        foreach ($data as $key => $value) {
                            if (UtilString::is_empty_string($value->company_domain) && UtilString::is_empty_string($value->linkedin_id) && UtilString::is_empty_string($value->company_name)) {
                                
                            } else {
                                if (in_array($value->linkedin_id, $duplicate)) {
                                    $duplicate_in_sheet ++;
                                } else {
                                    $duplicate[] = $value->linkedin_id;
                                        if ((isset($value->company_domain) && isset($value->linkedin_id)) && UtilString::contains($value->company_domain, ".")) {
                                            $linkedin_id = ($value->linkedin_id != "") ? UtilString::get_company_id_from_url($value->linkedin_id) : 0;
                                            $linkedin_url = ($value->linkedin_url != "") ? $value->linkedin_url : "";
                                            $company_domain = ($value->company_domain != "") ? $value->company_domain : "";
                                            $company_name = ($value->company_name != "") ? $value->company_name : "";
                                            $company_type = ($value->company_type != "") ? $value->company_type : "";
                                            $employee_count_at_linkedin = ($value->employee_count_at_linkedin != "") ? (int) $value->employee_count_at_linkedin : 0;
                                            $industry = ($value->industry != "") ? $value->industry : "";
                                            $city = ($value->city != "") ? $value->city : "";
                                            $postal_code = ($value->postal_code != "") ? trim($value->postal_code) : "";
                                            $employee_size = ($value->employee_size != "") ? trim($value->employee_size) : "";
                                            $country = ($value->country != "") ? trim($value->country) : "";
                                            $linkedin_url = UtilString::clean_string($linkedin_url);
                                            $company_domain = UtilString::clean_string($company_domain);
                                            $company_domain = UtilString::get_domain_from_url($company_domain);
                                            $company_name = UtilString::clean_string($company_name);
                                            $industry = UtilString::clean_string($industry);
                                            $city = UtilString::clean_string($city);
                                            $employee_size = UtilString::clean_string($employee_size);
                                            $country = UtilString::clean_string($country);
                                            $contact_exist = CompaniesWithDomain::where('linkedin_id', $linkedin_id)->count();
                                            if ($contact_exist == 0) {
                                                if(!empty($linkedin_id)){
                                                    $insert_array = [
                                                        'user_id' => Auth::id(),
                                                        'linkedin_id' => $linkedin_id,
                                                        'linkedin_url' => $linkedin_url,
                                                        'company_domain' => $company_domain,
                                                        'company_name' => $company_name,
                                                        'company_type' => $company_type,
                                                        'employee_count_at_linkedin' => $employee_count_at_linkedin,
                                                        'industry' => $industry,
                                                        'city' => $city,
                                                        'postal_code' => $postal_code,
                                                        'employee_size' => $employee_size,
                                                        'country' => $country
                                                    ];
                                                    $insert[] = $insert_array;
                                                    $inserted ++;
                                                }
                                            } else {
                                                $already_exist_in_db ++;
                                            }
                                        } else {
                                            $domain_not_exist ++;
                                            $domain_not_found[] = [
                                                'linkedin_id' => $value->linkedin_id,
                                                'linkedin_url' => $value->linkedin_url,
                                                'company_domain' => $value->company_domain,
                                                'company_name' => $value->company_name,
                                                'company_type' => $value->company_type,
                                                'employee_count_at_linkedin' => $value->employee_count_at_linkedin,
                                                'industry' => $value->industry,
                                                'city' => $value->city,
                                                'postal_code' => $value->postal_code,
                                                'employee_size' => $value->employee_size,
                                                'country' => $value->country
                                            ];
                                            $company_without_domain = CompaniesWithoutDomain::where('company_name',$value->company_name)->get();
                                            if($company_without_domain->count() <= 0){
                                                $company_d = new CompaniesWithoutDomain();
                                                $linkedin_id = ($value->linkedin_id != "") ? UtilString::get_company_id_from_url($value->linkedin_id) : 0;
                                                $company_d->linkedin_id = $linkedin_id;
                                                $company_d->company_domain = $value->company_domain;
                                                $company_d->company_name = $value->company_name;
                                                $company_d->employee_count_at_linkedin = $value->employee_count_at_linkedin;
                                                $company_d->industry = $value->industry;
                                                $company_d->city = $value->city;
                                                $company_d->employee_size = $value->employee_size;
                                                $company_d->country = $value->country;
                                                $company_d->save();
                                            }
                                        }
                                }
                            }
                        }
                        if (!empty($insert)) {
                            $insert_chunk = array_chunk($insert, 100);
                            foreach ($insert_chunk AS $ic) {
                                $insertData = DB::table('companies_with_domain')->insert($ic);
                            }
                            if ($insertData) {
                                CompaniesWithDomain::where('employee_size','0-1 employees')->update(['employee_size'=>'1 to 10']);
                                CompaniesWithDomain::where('employee_size','1,001-5,000 employees')->update(['employee_size'=>'1001 to 5000']);
                                CompaniesWithDomain::where('employee_size','1-10 employees')->update(['employee_size'=>'1 to 10']);
                                CompaniesWithDomain::where('employee_size','10')->update(['employee_size'=>'1 to 10']);
                                CompaniesWithDomain::where('employee_size','10,001+ employees')->update(['employee_size'=>'10000 above']);
                                CompaniesWithDomain::where('employee_size','10001 + Employees')->update(['employee_size'=>'10000 above']);
                                CompaniesWithDomain::where('employee_size','1001-5000 employees')->update(['employee_size'=>'1001 to 5000']);
                                CompaniesWithDomain::where('employee_size','11-50 employees')->update(['employee_size'=>'11 to 50']);
                                CompaniesWithDomain::where('employee_size','2-10 employees')->update(['employee_size'=>'1 to 10']);
                                CompaniesWithDomain::where('employee_size','201-500 employees')->update(['employee_size'=>'201 to 500']);
                                CompaniesWithDomain::where('employee_size','5,001-10,000 employees')->update(['employee_size'=>'5001 to 10000']);
                                CompaniesWithDomain::where('employee_size','5001 - 10000 employees')->update(['employee_size'=>'5001 to 10000']);
                                CompaniesWithDomain::where('employee_size','5001-10,000 employees')->update(['employee_size'=>'5001 to 10000']);
                                CompaniesWithDomain::where('employee_size','5001-10000 employees')->update(['employee_size'=>'5001 to 10000']);
                                CompaniesWithDomain::where('employee_size','501-1,000 employees')->update(['employee_size'=>'501 to 1000']);
                                CompaniesWithDomain::where('employee_size','501-1000 employees')->update(['employee_size'=>'501 to 1000']);
                                CompaniesWithDomain::where('employee_size','51-200 employees')->update(['employee_size'=>'51 to 200']);
                                CompaniesWithDomain::where('employee_size','Myself Only')->update(['employee_size'=>'1 to 10']);
                                CompaniesWithDomain::where('employee_size','NA')->update(['employee_size'=>'Invalid']);
                                DB::statement("update contacts A inner join companies_with_domain B on A.linkedin_id = B.linkedin_id set A.process_for_contact_match = 'not processed' where A.process_for_contact_match = 'company not found'");
                                Session::flash('success', 'Your Data has successfully imported');
                            } else {
                                Session::flash('error', 'Error inserting the data..');
                                return back();
                            }
                        }
                    }
                    $stats_data = array(
                        "inserted" => $inserted,
                        "duplicate_in_sheet" => $duplicate_in_sheet,
                        "already_exist_in_db" => $already_exist_in_db,
                        "domain_not_exist" => $domain_not_exist,
                        "junk_count" => $junk_count,
                        "domain_not_found" => $domain_not_found,
                        "junk_data_array" => $junk_data_array
                    );
                    Session::flash('stats_data', $stats_data);
                    return back();
                } else {
                    Session::flash('error', "The Sheet Header contain wrong column name");
                    return back();
                }
            } else {
                Session::flash('error', 'File is a ' . $extension . ' file.!! Please upload a valid xls file..!!');
                return back();
            }
        }
    }
    
    public function reprocessSheet(Request $request){
        $response = array();
        $message = "";
        if ($request->id > 0) {
            $id = $request->id;
            $sheet_data = MasterUserContact::where('Sheet_Id', $id)->where('Email_Status','domain not found')->distinct()->get(['Company_Linkedin_ID']);
            if ($sheet_data->count() > 0) {
                $data_count = $sheet_data->count();
                $update_exist = false;
                foreach($sheet_data AS $sd){
                   $linkedin_id =  $sd['Company_Linkedin_ID'];
                   $exist = CompaniesWithDomain::where('linkedin_id',$linkedin_id)->get();
                    if($exist->count() > 0){
                       $exist_company_domain = $exist->first()->company_domain;
                       MasterUserContact::where('Sheet_Id', $id)->where('Company_Linkedin_ID',$linkedin_id)->update(['company_domain'=>$exist_company_domain,'Email_Status'=>'domain found']);
                       $update_exist = true;
                    }
                }
                if($update_exist){
                    MasterUserSheet::where('Id', $id)->update(['Status'=>'Under Processing']);
                    $message = "$data_count record is in re-processing";
                    Session::flash('success', $message);
                }else{
                    $message = "domain not found for re-processing";
                    Session::flash('fail', $message);
                }
            } else {
                $message = "no domain found for re - processing";
                Session::flash('fail', $message);
            }
        } else {
            $message = "invalid request.";
            Session::flash('fail', $message);
        }
        return $message;
    }
}
