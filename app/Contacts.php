<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Contacts extends Model
{
    protected $table = "contacts";
    protected $fillable = ["user_id","linkedin_id","full_name","first_name","last_name","company_name","job_title","experience","location","profile_link","location","status","process_for_contact_match"];
}
