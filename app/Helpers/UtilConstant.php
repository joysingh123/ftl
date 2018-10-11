<?php
namespace App\Helpers;
class UtilConstant {
     
    public static $EXCLUDE_FROM_NAME = array("dr.","dr","kumar");
    
    public static $EXPLODE_EMAIL_VALUE = array(".","_","-");
    
    //constants For Email Formate
    
    const DOMAIN = "DOMAIN";
    const FIRST_NAME = "FIRSTNAME";
    const LAST_NAME = "LASTNAME";
    const FIRST_NAME_FIRST_CHARACTER = "F";
    const LAST_NAME_FIRST_CHARACTER = "L";
    const FIRST_NAME_FIRST_TWO_CHARACTER = "FI";
    const LAST_NAME_FIRST_TWO_CHARACTER = "LA";
    
    
    // cron jobs
    
    
    const MATCHED_CRON_JOB_NAME = "Matched Contacts";
    const MATCHED_CRON_EMAIL_FORMAT = "Email Format";
    const MATCHED_CRON_EMAIL_CREATE = "Email Create";
    const CRON_EMAIL_VALIDATION = "Email Validation";
    const CRON_REOMOVE_EMAIL_FROM_EMAILS = "Remove Email";
    const CRON_REOMOVE_API_VALID_EMAIL_FROM_EMAILS = "Remove Api Valid Email";
    const CRON_SCRAPE_URL_HUNTER = "Scrape Url Hunter";
    const CRON_SCRAPE_DOMAIN_HUNTER = "Scrape Domain Hunter";
    const CRON_SCRAPE_DOMAIN_EMAIL_FORMAT = "Scrape Domain Email Format";
    const CRON_CALCULATE_DOMAIN_EMAIL_FORMAT_PERCENTAGE = "Calculate Domain Email Format Percentage";
    
    const CRON_SEARCH_EMAIL_FOR_USER_SHEET = "Search Email For User Sheet";
    const CRON_LOOKUP_EMAIL_FOR_USER_SHEET = "Lookup Email For User Sheet";
    
    const EMAIL_VALIDATION_API_MAILBOXLAYER_NAME = "mailboxlayer";
    const EMAIL_VALIDATION_API_ZEROBOUNCE_NAME = "zerobounce";
}
?>