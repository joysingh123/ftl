<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MappingUserContacts extends Model
{
    protected $table = "mapping_user_contacts";
    protected $fillable = ["Sheet_Id","User_Contact_Id","Contacts_Id","Matched_Id"];
}
