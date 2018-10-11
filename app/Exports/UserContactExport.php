<?php
namespace App\Exports;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use App\MasterUserContact;
class UserContactExport implements FromCollection, WithHeadings
{
    private $user_id;
    private $sheet_id;
    
    public function __construct($user_id,$sheet_id) {
        $this->user_id = $user_id;
        $this->sheet_id = $sheet_id;
    }

    public function collection(){
        return MasterUserContact::where('User_ID',$this->user_id)->where('Sheet_ID',$this->sheet_id)->get(['Contact_Full_Name AS Full Name','First_Name','Last_Name','Company_Name','Company_Url','Job_Title','Experience','Location','Contact_Email','Email_Status','Validatation_Date']);
    }
 
    public function headings(): array
    {
        return [
            'Full Name',
            'First Name',
            'Last Name',
            'Company',
            'Company Url',
            'Title',
            'Experience',
            'Location',
            'Email',
            'Email Status',
            'Email Validation Date'
        ];
    }
}
?>