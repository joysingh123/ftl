<?php
namespace App\Helpers;
class UtilString {
    
    public static function trim_string($str) {
        $str = trim($str, '",(),-');
        return $str;
    }

    public static function remove_non_english_charector($str) {
        $str = preg_replace('/[^\00-\255]+/u', '', $str);
        return $str;
    }
    
    public static function get_domain_from_url($url){
        $url = rtrim($url,"/");
        $find = array('www.','WWW.','info@','http://','https://');
        $url = str_replace($find,"",$url);
        if(strpos($url,"/") != false){
            $lastpos = strpos($url,"/");
            $url = substr($url,0,$lastpos);
        }
        return $url;
    }

    public static function clean_string($str) {
        $str = self::trim_string($str);
        $str = self::remove_non_english_charector($str);
        return $str;
    }

    public static function get_company_id_from_url($url) {
        $company_id = NULL;
        if (filter_var($url, FILTER_VALIDATE_URL)) {
            $parts = parse_url($url);
            if(isset($parts['query'])){
                parse_str($parts['query'], $query);
                if(isset($query['companyId'])){
                    $company_id = str_replace("/", "", $query['companyId']);
                }
            }else{
                $company_id = str_replace("https://www.linkedin.com/sales/company/", "", $url);
                $company_id = str_replace("https://www.linkedin.com/company/", "", $url);
                $company_id = str_replace("/", "",$company_id);
                if(is_numeric($company_id) && $company_id > 0){
                    $company_id = $company_id;
                }
            }
        }else if(is_numeric($url) && $url > 0){
            $company_id = $url;
        }
        return $company_id;
    }
    public static function starts_with($haystack, $needle) {
        $length = strlen($needle);
        return (substr($haystack, 0, $length) === $needle);
    }

    public static function ends_with($haystack, $needle) {
        $length = strlen($needle);
        if ($length == 0) {
            return true;
        }
        return (substr($haystack, -$length) === $needle);
    }
    
    public static function is_empty_string($str) {
        if ($str == NULL || strlen(trim($str)) == 0) {
            return true;
        }
        return false;
    }
    
    public static function contains($haystack, $needle) {
        if($haystack != "" && $needle!= ""){
            if (strpos($haystack, $needle) !== false) {
                return true;
            }
        }
        return false;
    }
    
    public static function is_email($email){
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
          return true;
        }
        return false;
    }
    
    public static function explode_email_string($str){
        $explode_array = UtilConstant::$EXPLODE_EMAIL_VALUE;
        $explode = array();
        foreach($explode_array AS $ea){
            if(self::contains($str, $ea)){
                $explode['explode_data'] = explode($ea, $str);
                $explode['explode_by'] = $ea;
                break;
            }
        }
        if(count($explode) > 0){
            return $explode;
        }
        return $str;
    } 
    
    public static function IND_money_format($number){        
        $decimal = (string)($number - floor($number));
        $money = floor($number);
        $length = strlen($money);
        $delimiter = '';
        $money = strrev($money);
 
        for($i=0;$i<$length;$i++){
            if(( $i==3 || ($i>3 && ($i-1)%2==0) )&& $i!=$length){
                $delimiter .=',';
            }
            $delimiter .=$money[$i];
        }
 
        $result = strrev($delimiter);
        $decimal = preg_replace("/0\./i", ".", $decimal);
        $decimal = substr($decimal, 0, 3);
 
        if( $decimal != '0'){
            $result = $result.$decimal;
        }
 
        return $result;
    }
    public static function estimated_time($total_count,$estimated_time){
        $time = $total_count/$estimated_time;
        $time_stirng = "";
        if($time < 30){
            $time_stirng = "30 Minutes";
        }else{
            $time_stirng = round($time)." Minutes"; 
        }
        if($time >= 60){
            $time_stirng = round($time/60,PHP_ROUND_HALF_DOWN)." Hours";
        }
        return $time_stirng;
    }
}
?>

