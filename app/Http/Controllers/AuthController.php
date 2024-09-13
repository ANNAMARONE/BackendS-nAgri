<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AuthController extends Controller
{
   public function register() {
    $valudator=validator:make(request()->all(),[
        "name"=>'required',
        "email"=>'required|email|unique:users',
        'password' => 'required|confirmed|min:8',
    ]);
   }
    
}
