<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EmailSheet extends Model
{
    protected $table = "email_sheets";
    protected $fillable = ["sheet_name","user_id","total","sheet_header","status"];
}
