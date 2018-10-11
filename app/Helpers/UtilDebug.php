<?php
namespace App\Helpers;
class UtilDebug {
    
    
    public static function print_query($query) {
        $time = self::formatedDateTimeFull(self::getUpdateTime());
        echo "<fieldset style='font-family:courier;color:blue;text-align:left'><legend style='color:red'>Query ($time)</legend>$query</fieldset>\n";
    }
    public static function print_message($name, $message) {
        $time = self::formatedDateTimeFull(self::getUpdateTime());
        echo "<fieldset style='font-family:courier;color:gray;text-align:left'><legend style='color:black'>$name ($time)</legend>$message</fieldset>\n";
    }

    public static function print_error($name, $message) {
        $time = self::formatedDateTimeFull(self::getUpdateTime());
        echo "<fieldset style='font-family:courier;color:red;text-align:left'><legend style='color:red'>Error:: $name ($time)</legend>$message</fieldset>\n";
    }

    public static function print_pre($name, $data) {
        $time = self::formatedDateTimeFull(self::getUpdateTime());
        echo "<fieldset style='font-family:courier;color:gray;text-align:left'><legend style='color:black'>$name ($time)</legend><pre>$data<pre></fieldset>\n";
    }

    public static function print_r_array($name, $array) {
        $time = self::formatedDateTimeFull(self::getUpdateTime());
        echo "<fieldset style='font-family:courier;color:gray;text-align:left'><legend style='color:black'>$name ($time)</legend><pre>";
        print_r($array);
        echo "<pre></fieldset>\n";
    }
    
    public static function header($name) {
        echo "<center><h2>" . $name . "</h2> <hr> </center>";
    }
    
    public static function debug($msg) {
        $time = self::formatedDateTimeFull(self::getUpdateTime());
        print "DEBUG:($time)" . $msg . "<br>\n";
    }

    public static function debug_no_nl($msg) {
        print $msg;
    }

    public static function debug_with_nl($msg) {
        print $msg . "<br>\n";
    }
    
    public static function formatedDateTimeFull($sec) {
        $time = date('d M Y H:i:s', $sec);
        return $time;
    }
    
    public static function getUpdateTime() {
        $time = time();
        return $time;
    }
}
?>
