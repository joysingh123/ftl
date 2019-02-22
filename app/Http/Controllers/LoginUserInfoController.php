<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;

class LoginUserInfoController extends Controller
{
    public function loginuserinfo(){
        $data = array();
        $data['email'] = Auth::user()->email;
        $data['id'] = Auth::id();
        return response()->json($data);
    }
}
