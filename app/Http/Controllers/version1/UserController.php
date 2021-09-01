<?php

namespace App\Http\Controllers\version1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserController extends Controller
{
      
    /*
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    | THIS FUNCTION REGISTES A USER AND PROVIDES THEM WITH AN ACCESS TOKEN
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    |
    */
    public function register(Request $request)
    {

        $validatedData = $request->validate([
            "user_surname" => "bail|required",
            "user_firstname" => "bail|required",
            "user_country" => "bail|required",
            "user_phone_number" => "bail|required",
            "user_email" => "bail|required",
            "password" => "bail|required",
        ]);

        $validatedData["user_scope"] = "";

}
