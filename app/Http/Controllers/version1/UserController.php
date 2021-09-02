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
            "user_firstname" => "bail|required|max:55",
            "user_surname" => "bail|required|max:55",
            "user_pottname" => "bail|required",
            "user_gender" => "bail|required",
            "user_language" => "bail|required",
            "user_country" => "bail|required",
            "user_dob" => "bail|required",
            "user_phone_number" => "bail|required",
            "password" => "bail|required",
            "user_referred_by" => "bail|required"
        ]);

        $validatedData["user_email"] = "";
        $validatedData["password"] = bcrypt($request->password);
        $validatedData["user_profile_picture"] = "";
        $validatedData["user_currency_id"] = 1;
        $validatedData["user_net_worth"] = 0;
        $validatedData["user_verified_tag"] = 0;
        $validatedData["user_shield_date"] = date("Y-m-d H:i:s");
        $validatedData["user_pott_ruler"] = "";
        $validatedData["user_fcm_token_android"] = "";
        $validatedData["user_fcm_token_web"] = "";
        $validatedData["user_fcm_token_ios"] = "";
        $validatedData["user_added_to_sitemap"] = false;
        $validatedData["user_flagged"] = false;
        $validatedData["user_scope"] = "";

    }

}
