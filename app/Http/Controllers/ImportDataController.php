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
        $data = MasterUserSheet::where('User_ID', $user_id)->where('Status', '!=', 'Completed')->count();
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
                $data = Excel::load($path, function($reader) {
                            
                        })->get();
                $header = $data->getHeading();
                if (in_array('full_name', $header) && in_array('first_name', $header) && in_array('last_name', $header) && in_array('company', $header) && in_array('company_url', $header) && in_array('title', $header) && in_array('experience', $header) && in_array('location', $header)) {
                    if (!empty($data) && $data->count() < 20000) {
                        $total_count = $data->count();
                        $master_user_sheet = MasterUserSheet::create(["sheet_name" => $filename, "user_id" => Auth::id(), "total_count" => $total_count, "sheet_tag" => $sheet_tag, "status" => "Contact Uploading"]);
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
                                    } else {
                                        $email_status = "domain not found";
                                    }
                                } else {
                                    $email_status = "company not found";
                                }

                                if (UtilString::is_empty_string($first_name) && UtilString::is_empty_string($last_name)) {
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
                            if (in_array($value, $duplicate)) {
                                $duplicate_in_sheet ++;
                            } else {
                                $duplicate[] = $value;
                                if (!UtilString::contains($value, "\u")) {
                                    if ((isset($value->company_domain) && isset($value->linkedin_id)) && (UtilString::contains($value->company_domain, ".") && $value->linkedin_id > 0)) {
                                        $linkedin_id = ($value->linkedin_id != "") ? $value->linkedin_id : 0;
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
                                    }
                                } else {
                                    $junk_count ++;
                                    $junk_data_array[] = [
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
                                }
                            }
                        }
                        if (!empty($insert)) {
                            $insert_chunk = array_chunk($insert, 100);
                            foreach ($insert_chunk AS $ic) {
                                $insertData = DB::table('companies_with_domain')->insert($ic);
                            }
                            if ($insertData) {
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
}
