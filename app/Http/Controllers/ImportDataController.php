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

class ImportDataController extends Controller {

    public function importContactView() {
        $user_id = Auth::id();
        $master_user_sheet_data = MasterUserSheet::where('User_ID', $user_id)->orderBY('created_at', 'DESC')->get();
        $hide_download = false;
        $data = MasterUserSheet::where('User_ID', $user_id)->where('Status', '!=','Completed')->count();
        if ($data > 0) {
            $hide_download = true;
        }
        return view('importusercontact')->with('sheet_data', $master_user_sheet_data)->with('hide_download', $hide_download);
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
                $data = Excel::load($path, function($reader) {})->get();
                $header = $data->getHeading();
                if (in_array('full_name', $header) && in_array('first_name', $header) && in_array('last_name', $header) && in_array('company', $header) && in_array('company_url', $header) && in_array('title', $header) && in_array('experience', $header) && in_array('location', $header)) {
                    if (!empty($data) && $data->count() < 20000) {
                        $total_count = $data->count();
                        $master_user_sheet = MasterUserSheet::create(["sheet_name" => $filename, "user_id" => Auth::id(), "total_count" => $total_count,"sheet_tag" => $sheet_tag, "status" => "Contact Uploading"]);
                        $sheet_id = $master_user_sheet->id;
                        if ($sheet_id > 0) {
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
                                if ($first_name == "" && $last_name == "") {
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
                                    } else {
                                        $email_status = "domain not found";
                                    }
                                } else {
                                    $email_status = "company not found";
                                }
                                
                                if(UtilString::is_empty_string($first_name) && UtilString::is_empty_string($full_name)){
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

}
