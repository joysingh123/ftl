<?php
namespace App\Helpers;

use App\Helpers\UtilConstant;
class UtilEmailValidation {
    
    public static function getValidationUrl($email,$api_name,$api_url,$api_key){
        $final_url = array();
        $IPToValidate = '99.123.12.122';
        if($api_name == UtilConstant::EMAIL_VALIDATION_API_MAILBOXLAYER_NAME){
            $final_url['email_validation_url'] = $api_url.'?access_key='.$api_key.'&email='.$email;
            $final_url['verified_by'] = $api_name;
        }else if($api_name == UtilConstant::EMAIL_VALIDATION_API_ZEROBOUNCE_NAME){
            $final_url['email_validation_url'] = $api_url.'?api_key='.$api_key.'&email='.urlencode($email).'&ip_address='.urlencode($IPToValidate);
            $final_url['verified_by'] = $api_name;
        }
        return $final_url;
    }
}
?>