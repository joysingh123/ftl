<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\MasterUserSheet;
use App\MasterUserContact;
use App\DomainSheet;
use App\DomainUserContact;
use Excel;
use DB;
use App\EmailSheet;
use App\EmailVerification;

class ExportController extends Controller {

    public function export(Request $request) {
        ini_set('max_execution_time', -1);
        ini_set('memory_limit', -1);
        ini_set('mysql.connect_timeout', 600);
        ini_set('default_socket_timeout', 600);
        if ($request->id > 0) {
            $id = $request->id;
            $sheet_info = MasterUserSheet::where('ID', $id)->get();
            if ($sheet_info->count()) {
                $sheet_name = $sheet_info->first()->Sheet_Name;
                $sheet_name_array = explode(".",$sheet_name);
                $sheet_name = $sheet_name_array[0];
                $sheet_id = $sheet_info->first()->ID;
                $user_id = $sheet_info->first()->User_ID;
                $data = MasterUserContact::where('User_ID', $user_id)->where('Sheet_ID', $sheet_id)->get(['Contact_Full_Name AS Full Name', 'First_Name AS First Name', 'Last_Name AS Last Name', 'Company_Name AS Company', 'Company_Url AS Company Url', 'Job_Title AS Title', 'Experience', 'Location', 'Contact_Email', 'Email_Status', 'Validatation_Date']);
                $type = $sheet_name_array[1];
                MasterUserSheet::where('ID', $id)->update(['download'=>'yes']);
                return Excel::create($sheet_name, function($excel) use ($data) {
                            $excel->sheet('mySheet', function($sheet) use ($data) {
                                $sheet->fromArray($data);
                            });
                        })->download($type);
            } else {
                echo "Invalid Export";
            }
        } else {
            echo "Invalid Export";
        }
    }
    public function exportEmail(Request $request) {
        if ($request->id > 0) {
            $id = $request->id;
            $sheet_info = EmailSheet::where('id', $id)->get();
            if ($sheet_info->count()) {
                $sheet_name = $sheet_info->first()->sheet_name;
                $sheet_name_array = explode(".",$sheet_name);
                $sheet_name = $sheet_name_array[0];
                $sheet_id = $sheet_info->first()->id;
                $user_id = $sheet_info->first()->user_id;
                $data = EmailVerification::where('user_id', $user_id)->where('sheet_id', $sheet_id)->get(['email AS Email','email_validation_date AS Email Validation Date','status AS Status']);
                $type = $sheet_name_array[1];
                return Excel::create($sheet_name, function($excel) use ($data) {
                            $excel->sheet('mySheet', function($sheet) use ($data) {
                                $sheet->fromArray($data);
                            });
                        })->download($type);
            } else {
                echo "Invalid Export";
            }
        } else {
            echo "Invalid Export";
        }
    }
    public function exportDomain(Request $request){
        if ($request->id > 0) {
            $id = $request->id;
            $sheet_info = DomainSheet::where('id', $id)->get();
            if ($sheet_info->count()) {
                $sheet_name = $sheet_info->first()->sheet_name;
                $sheet_name_array = explode(".",$sheet_name);
                $sheet_name = $sheet_name_array[0];
                $type = $sheet_name_array[1];
                $sheet_id = $sheet_info->first()->id;
                $user_id = $sheet_info->first()->user_id;
                $sheet_header = json_decode($sheet_info->first()->sheet_header);
                $sheet_header_array = array();
                foreach ($sheet_header AS $sh){
                    if($sh == "full_name"){
                        $sheet_header_array[] = "full_name as Full Name";
                    }
                    if($sh == "first_name"){
                        $sheet_header_array[] = "first_name as First Name";
                    }
                    if($sh == "title"){
                        $sheet_header_array[] = "title AS Title";
                    }
                    if($sh == "last_name"){
                        $sheet_header_array[] = "last_name as Last Name";
                    }
                    if($sh == "domain"){
                        $sheet_header_array[] = "domain as Domain";
                    }
                    if($sh == "company"){
                        $sheet_header_array[] = "company as Company";
                    }
                    if($sh == "industry"){
                        $sheet_header_array[] = "industry as Industry";
                    }
                    if($sh == "location"){
                        $sheet_header_array[] = "location as Location";
                    }
                    if($sh == "employee_count"){
                        $sheet_header_array[] = "employee_count as Employee Count";
                    }
                    if($sh == "country"){
                        $sheet_header_array[] = "country as Country";
                    }
                }
                $sheet_header_array[] = "email as Email";
                $sheet_header_array[] = "email_validation_date as Email Validation Date";
                $sheet_header_array[] = "status as Status";
                $data = DomainUserContact::where('user_id', $user_id)->where('sheet_id', $sheet_id)->get($sheet_header_array);
                $data_exl =  Excel::create($sheet_name, function($excel) use ($data) {
                            $excel->sheet('mySheet', function($sheet) use ($data) {
                                $sheet->fromArray($data);
                            });
                        })->download($type);
            } else {
                echo "Invalid Export";
            }
        }else {
            echo "Invalid Export";
        }
    }

    public function exportDomainNotFound(Request $request) {
        if ($request->id > 0) {
            $id = $request->id;
            $sheet_info = MasterUserSheet::where('ID', $id)->get();
            if ($sheet_info->count()) {
                $sheet_name = $sheet_info->first()->Sheet_Name;
                $sheet_name_array = explode(".",$sheet_name);
                $sheet_name = $sheet_name_array[0];
                $sheet_id = $sheet_info->first()->ID;
                $user_id = $sheet_info->first()->User_ID;
                $data = MasterUserContact::where('User_ID', $user_id)->where('Sheet_ID', $sheet_id)->where('Email_Status', "domain not found")->distinct()->get(["Company_Url AS Linkedin url","Company_Name AS Company Name"]);
                $data->each(function ($item, $key) {
                    $item['Company Domain'] = "";
                    $item['Company Type'] = "";
                    $item['Employee Count at LinkedIN'] = "";
                    $item['Industry'] = "";
                    $item['City'] = "";
                    $item['Postal Code'] = "";
                    $item['Employee Size'] = "";
                    $item['Country'] = "";
                });
                $type = $sheet_name_array[1];
                $data_exl =  Excel::create($sheet_name."_dnf", function($excel) use ($data) {
                            $excel->sheet('mySheet', function($sheet) use ($data) {
                                $sheet->fromArray($data);
                            });
                        })->download($type);
            } else {
                echo "Invalid Export";
            }
        } else {
            echo "Invalid Export";
        }
    }
    
    public function exportCompanyNotFound(Request $request) {
        if ($request->id > 0) {
            $id = $request->id;
            $sheet_info = MasterUserSheet::where('ID', $id)->get();
            if ($sheet_info->count()) {
                $sheet_name = $sheet_info->first()->Sheet_Name;
                $sheet_name_array = explode(".",$sheet_name);
                $sheet_name = $sheet_name_array[0];
                $sheet_id = $sheet_info->first()->ID;
                $user_id = $sheet_info->first()->User_ID;
                $data = MasterUserContact::where('User_ID', $user_id)->where('Sheet_ID', $sheet_id)->where('Email_Status', "company not found")->get(['Contact_Full_Name AS Full Name', 'First_Name AS First Name', 'Last_Name AS Last Name', 'Company_Name AS Company', 'Company_Url AS Company Url', 'Job_Title AS Title', 'Experience', 'Location']);
                $type = $sheet_name_array[1];
                return Excel::create($sheet_name."_cnf", function($excel) use ($data) {
                            $excel->sheet('mySheet', function($sheet) use ($data) {
                                $sheet->fromArray($data);
                            });
                        })->download($type);
            } else {
                echo "Invalid Export";
            }
        } else {
            echo "Invalid Export";
        }
    }

}
