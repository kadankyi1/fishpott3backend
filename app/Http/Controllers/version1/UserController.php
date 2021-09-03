<?php

namespace App\Http\Controllers\version1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserController extends Controller
{

      
    /*
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    | THIS FUNCTION CHECKS IF A POTTNAME IS AVAILABLE
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    */
    public function pottnameIsAvailable($keyword){
        $user = User::where('pottname', '=', $keyword)->first();
        if ($user !== null || $keyword == "mylinkups") {
            return false;
        } else {
            // user doesn't exist
            return true;
        }
    }

    /*
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    | THIS FUNCTION CHECKS IF A STRING HAS NO XML TAGS
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    */
    public function stringContainsNoTags($input) {
        if($input != ""){
            if($input != strip_tags($input)) {
                $validation = false;
            } else {
                $validation = true;
            }
            return $validation;
        } else {
            return false;
        }
    }

    /*
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    | THIS FUNCTION CHECKS IF A STRING IS MORE THAN A GIVEN LENGTH
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    */
    public function stringIsNotMoreThanMaxLength($input, $max_allowed_input_length){
        if($input != "" && $max_allowed_input_length > 0){
            if(strlen($input) > $max_allowed_input_length){
                $validation = false;
            } else {
                if($validation != false){
                    $validation = true;
                }
            }
            return $validation;
        } else {
            return false;
        }
    }

    /*
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    | THIS FUNCTION CHECKS IF AN INPUT CONTAINS ONLY NUMBERS
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    */
    public function inputContainsOnlyNumbers($input){
        if(trim($input) == ""){
            return false;
        }
        if (ctype_digit($input)) {
            return true;
        } else {
            return false;
        }
    }

    /*
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    | THIS FUNCTION CHECKS IF AN INPUT CONTAINS ONLY ALPHABETS AND UNDERSCORE
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    */

    public function inputContainsOnlyAlphabetsWithListedSpecialCharacters($input, $include_some_special_characters, $special_characters_array){
        if(trim($input) == ""){
            return false;
        }
        if($include_some_special_characters === true){
            for ($i=0; $i < count($special_characters_array); $i++) {
                $input = str_replace($special_characters_array[$i],"",$input);
            }
        }
        if (!preg_match('/[^A-Za-z0-9]/', $input)) {
            return true;
        } else {
            return false;
        }
    }


    /*
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    | THIS FUNCTION REGISTES A USER AND PROVIDES THEM WITH AN ACCESS TOKEN
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    */
    public function register(Request $request)
    {

        // MAKING SURE THE INPUT HAS THE EXPECTED VALUES
        $validatedData = $request->validate([
            "user_firstname" => "bail|required|alpha_dash|max:15",
            "user_surname" => "bail|required|alpha_dash|max:15",
            "user_pottname" => "bail|required|alpha_dash|max:15",
            "user_gender" => "bail|required|max:6",
            "user_language" => "bail|required|max:3",
            "user_country" => "bail|required|max:55",
            "user_dob" => "bail|required|max:10",
            "user_phone_number" => "bail|required|regex:/(+)[0-9]/|min:10|max:15",
            "password" => "bail|required|max:20",
            "user_referred_by" => "bail|required|alpha_dash|max:15",
            "app_version_code" => "bail|required|integer|max:15"
        ]);

        // CHECKING POTTNAME AVAILABILITY
        if(!pottnameIsAvailable($request->user_pottname)){
            return response([
                "status" => "error", 
                "message" => "Registration failed. The pott name is already taken"
            ]);
        } 

        //GETTING COUNTRY ID
        $gender = Gender::where('gender_name', '=', $request->user_gender)->first();
        if($gender === null){
            return response([
                "status" => "error", 
                "message" => "Registration failed. Gender validation error."
            ]);
        }

        //GETTING COUNTRY ID
        $country = Country::where('country_real_name', '=', $request->user_country)->first();
        if($country === null){
            return response([
                "status" => "error", 
                "message" => "Registration failed. Country validation error."
            ]);
        }

        //GETTING LANGUAGE ID
        $language = Language::where('language_short_name', '=', $request->user_language)->first();
        if($language === null){
            return response([
                "status" => "error", 
                "message" => "Registration failed. Language validation error."
            ]);
        }

        
        $validatedData["user_gender_id"] = $gender->gender_id;
        $validatedData["user_country_id"] = $country->country_id;
        $validatedData["user_language_id"] = $language->language_id;
        $validatedData["user_email"] = "";
        $validatedData["password"] = bcrypt($request->password);
        $validatedData["user_profile_picture"] = "";

        // PRE-POPULATING FIELDS
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


        $user1 = User::create($validatedData);
        
        // GENERATING THE ACCESS TOKEN FOR THE REGISTERED USER
        $accessToken = $administrator->createToken("authToken", [$validatedData["admin_scope"]])->accessToken;

        return response([
            "administrator" => $administrator, 
            "access_token" => $accessToken
        ]);
    
    }

}
