<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DomainSheet extends Model
{
    protected $table = "domain_sheets";
    protected $fillable = ["sheet_name","user_id","total","sheet_header","status"];
}
