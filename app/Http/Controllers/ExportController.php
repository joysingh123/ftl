<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\MasterUserSheet;
use App\MasterUserContact;
use Excel;
use DB;
class ExportController extends Controller {

    public function export(Request $request) {
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
                $data = MasterUserContact::where('User_ID', $user_id)->where('Sheet_ID', $sheet_id)->where('Email_Status', "domain not found")->distinct()->get(["Company_Linkedin_ID AS Linkedin Id", "Company_Url AS Linkedin url"]);
                $data->each(function ($item, $key) {
                    $item['Company Domain'] = "";
                    $item['Company Name'] = "";
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
