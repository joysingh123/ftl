<?php

namespace App\Traits;
use App\EmailValidationApi;
use App\Helpers\UtilConstant;
use Ixudra\Curl\Facades\Curl;
use App\Helpers\UtilEmailValidation;
use App\EmailValidation;
use App\Helpers\UtilString;

trait ValidateEmailTraits {
    
    public function validateEmail($email) {
        $validation_api = EmailValidationApi::where('status','enable')->get();
        $email_validation_status = array();
        $exist_in_email_validation = EmailValidation::where('email',$email)->get();
        if($exist_in_email_validation->count() > 0){
            $email_validation_status = array(
                                            'email_status'=>$exist_in_email_validation->first()->status,
                                            'verified_by'=>$exist_in_email_validation->first()->verified_by,
                                            'response'=>$exist_in_email_validation->first()->raw_data
            );
            return $email_validation_status;
        }
        foreach ($validation_api AS $va) {
            $api_name = $va->name;
            $api_url = $va->api_url;
            $api_key = $va->api_key;
            $email_validation_url = UtilEmailValidation::getValidationUrl($email, $api_name, $api_url, $api_key);
            $url = $email_validation_url['email_validation_url'];
            $response = Curl::to($url)->get();
            echo "$url:". $response;
            $email_validation_status = array('email_status'=>"",'verified_by'=>$api_name,'response'=>$response);
            $response_array = json_decode($response, true);
            if (isset($response_array['email']) || isset($response_array['address'])) {
                $email_status = "";
                if ($email_validation_url['verified_by'] == UtilConstant::EMAIL_VALIDATION_API_MAILBOXLAYER_NAME) {
                    $email_validation_status['verified_by'] = UtilConstant::EMAIL_VALIDATION_API_MAILBOXLAYER_NAME;
                    if ($response_array['smtp_check'] && $response_array['score'] >= 0.96 && !$response_array['disposable']) {
                        $email_validation_status['email_status'] = "valid";
                    } else if ($response_array['smtp_check'] && $response_array['score'] < 0.96 && !$response_array['disposable']) {
                        $email_validation_status['email_status'] = "catch all";
                    } else {
                        $email_validation_status['email_status'] = "invalid";
                    }
                }

                if ($email_validation_url['verified_by'] == UtilConstant::EMAIL_VALIDATION_API_ZEROBOUNCE_NAME) {
                    $email_validation_status['verified_by'] = UtilConstant::EMAIL_VALIDATION_API_ZEROBOUNCE_NAME;
                    if ($response_array['status'] == 'valid') {
                        $email_validation_status['email_status'] = "valid";
                    } else if ($response_array['status'] == 'catch-all') {
                        $email_validation_status['email_status'] = "catch all";
                    } else {
                        $email_validation_status['email_status'] = "invalid";
                    }
                }
                if(!UtilString::is_empty_string($email_validation_status['email_status'])){
                    $email_validation = new EmailValidation();
                    $email_validation->email = $email;
                    $email_validation->status = $email_validation_status['email_status'];
                    $email_validation->verified_by = $email_validation_status['verified_by'];
                    $email_validation->raw_data = $email_validation_status['response'];
                    if($email_validation_status['verified_by'] == UtilConstant::EMAIL_VALIDATION_API_MAILBOXLAYER_NAME){
                        $email_validation->format_valid = $response_array['format_valid'];
                        $email_validation->mx_found = ($response_array['mx_found']) ? 'true' : 'false';
                        $email_validation->smtp_check = $response_array['smtp_check'];
                        $email_validation->catch_all = (empty($response_array['catch_all'])) ? 'null' : $response_array['catch_all'];
                        $email_validation->disposable = $response_array['disposable'];
                        $email_validation->score = $response_array['score'];
                    }
                    $email_added = $email_validation->save();
                }
                break;
            }
        }
        return $email_validation_status;
    }
}
?>

