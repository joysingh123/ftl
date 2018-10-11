<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MasterUserSheet extends Model
{
    protected $table = "master_user_sheets";
    protected $fillable = ["sheet_name","user_id","total_count","sheet_tag","status"];
}
