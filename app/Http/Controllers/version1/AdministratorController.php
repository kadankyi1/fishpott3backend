<?php

namespace App\Http\Controllers\version1;

use DB;
use DateTime;
use App\Models\version1\Administrator;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\version1\User;
use App\Models\version1\Gender;
use App\Models\version1\Country;
use App\Models\version1\Language;
use App\Models\version1\ResetCode;
use App\Mail\version1\ResetCodeMail;
use App\Models\version1\Drill;
use App\Models\version1\Suggesto;
use Illuminate\Support\Facades\File; 
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class AdministratorController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    | THIS FUNCTION VALIDATES A REQUEST AND THE USER MAKING IT
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    */

    public function validateAdminWithAuthToken($request, $user, $admin, $actions)
    {
        // CHECKING IF USER FLAGGED
        if ($user->user_flagged) {
            $request->user()->token()->revoke();
            return [
                "status" => "error", 
                "message" => "Account flagged."
            ]; 
         }

        // CHECKING THAT USER TOKEN HAS THE RIGHT PERMISSION
        if (!$request->user()->tokenCan('view-info')) {
            return [
                "status" => "error", 
                "message" => "You do not have permission"
            ];
        }

        // MAKING SURE VERSION CODE IS ALLOWED
        if(
            strtoupper($request->app_type) == "ANDROID" && 
            (intval($request->app_version_code) < intval(config('app.androidminvc')) || $request->app_version_code > intval(config('app.androidmaxvc')))
        ){
            return [
                "status" => "error", 
                "message" => "Please update your app from the Google Play Store."
            ]; exit;
        }

        // GETTING USER
        $user = User::where('user_pottname', $user->user_pottname)->where('user_phone_number', $request->user_phone_number)->where('investor_id', $request->investor_id)->first();
        if($user == null){
            return [
                "status" => "error", 
                "message" => "Session closed. You have to login again."
            ]; exit;
        }

        // SAVING APP TYPE VERSION CODE
        if($request->app_type == "ANDROID"){
            $user->user_android_app_version_code = $request->app_version_code;
        } else if($request->app_type == "IOS"){
            $user->user_ios_app_version_code = $request->app_version_code;
        }
        // SAVING CHANGES MADE TO THE USER
        $user->save();    
        
        return $user;
    }

    /*
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    | THIS FUNCTION REGISTES THE ADMIN AND PROVIDES THEM WITH AN ACCESS TOKEN
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    |
    */
    public function registerFirstAdmin(Request $request)
    {

        $validatedData = $request->validate([
            "administrator_sys_id" => "bail|required|max:55",
            "administrator_user_pottname" => "bail|required|string|regex:/^[A-Za-z0-9_.]+$/|max:15",
            "administrator_surname" => "bail|required|max:55",
            "administrator_firstname" => "bail|required|max:55",
            "administrator_phone_number" => "bail|required|regex:/^\+\d{10,15}$/|min:10|max:15",
            "administrator_email" => "bail|email|required|max:100",
            "administrator_pin" => "bail|required|min:4|max:8",
            "password" => "bail|required|min:8|max:30",
            "administrator_scope" => "bail|required",
            "added_by_administrator_id" => "bail|required",
            "frontend_key" => "bail|required|in:2aLW4c7r9(2qf#y"
        ]);

        $validatedData["administrator_pin"] = Hash::make($request->administrator_pin);
        $validatedData["password"] = bcrypt($request->password);
        $validatedData["administrator_flagged"] = false;

        $administrator = Administrator::create($validatedData);

        $accessToken = $administrator->createToken("authToken", [$validatedData["administrator_scope"]])->accessToken;

        return response([
            "administrator" => $administrator, 
            "administrator_user_pottname" => $request->administrator_user_pottname, 
            "access_token" => $accessToken
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    | THIS FUNCTION REGISTES A USER AND PROVIDES THEM WITH AN ACCESS TOKEN
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    */
    public function loginAsAdministrator(Request $request)
    {
        // MAKING SURE THE INPUT HAS THE EXPECTED VALUES
        $validatedData = $request->validate([
            "administrator_phone_number" => "bail|required|regex:/^\+\d{10,15}$/|min:10|max:15",
            "administrator_user_pottname" => "bail|required|string|regex:/^[A-Za-z0-9_.]+$/|max:15",
            "password" => "bail|required",
            "frontend_key" => "bail|required|in:2aLW4c7r9(2qf#y"
        ]);

        $loginData["administrator_phone_number"] = $validatedData["administrator_phone_number"];
        $loginData["password"] = $validatedData["password"];


        // VALIDATING USER CREDENTIALS
        if (!auth()->guard('administrator')->attempt($loginData)) {
            return response([
                "status" => "error", 
                "message" => "Invalid Credentials"
            ]);
        }

        // CHECKING IF USER FLAGGED
        if (auth()->guard('administrator')->user()->user_flagged) {
            return response([
                "status" => "0", 
                "message" => "Account access restricted"
            ]);
        }

        //echo "administrator_flagged: " . auth()->guard('administrator')->user()->administrator_flagged;
        //echo "\n administrator_scope: " . auth()->guard('administrator')->user()->administrator_scope; exit;
        
        // GENERATING USER ACCESS TOKEN
        $accessToken = auth()->guard('administrator')->user()->createToken("authToken", [auth()->guard('administrator')->user()->administrator_scope])->accessToken;

        return response([
            "status" => "yes", 
            "message" => "",
            "access_token" => $accessToken,
            "administrator_user_pottname" => auth()->guard('administrator')->user()->administrator_user_pottname,
            "administrator_firstname" => auth()->guard('administrator')->user()->administrator_firstname,
            "administrator_surname" => auth()->guard('administrator')->user()->administrator_surname
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    | THIS FUNCTION ADDS A DRILL
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    */
    
    public function addDrill(Request $request)
    {
        /*
        |**************************************************************************
        | VALIDATION STARTS 
        |**************************************************************************
        */
        // MAKING SURE THE INPUT HAS THE EXPECTED VALUES
        $validatedData = $request->validate([
            "user_phone_number" => "bail|required|regex:/^\+\d{10,15}$/|min:10|max:15",
            "investor_id" => "bail|required",
            "user_language" => "bail|required|max:3",
            "app_type" => "bail|required|max:8",
            "app_version_code" => "bail|required|integer",
            // ADD ANY OTHER REQUIRED INPUTS FROM HERE
            "drill_question" => "min:5|max:100",
            "drill_answer_1" => "min:2|max:100",
            "drill_answer_2" => "min:2|max:100",
            "drill_answer_3" => "max:100",
            "drill_answer_4" => "max:100",
        ]);

        // MAKING SURE THE REQUEST AND USER IS VALIDATED
        $validation_response = $this->validateUserWithAuthToken($request, auth()->user());
        if(!empty($validation_response["status"]) && trim($validation_response["status"]) == "error"){
            return response($validation_response);
        } else {
            $user = $validation_response;
        }
        /*
        |**************************************************************************
        | VALIDATION ENDED 
        |**************************************************************************
        */

        //CREATING THE USER DATA TO ADD TO DB
        $drillData["drill_sys_id"] = $user->user_pottname . "-" . substr($validatedData["user_phone_number"] ,1,strlen($validatedData["user_phone_number"])) . date("Y-m-d-H-i-s") . $this->getRandomString(50);
        $drillData["drill_question"] = $validatedData["drill_question"];
        $drillData["drill_answer_1"] = $validatedData["drill_answer_1"];
        $drillData["drill_answer_2"] = $validatedData["drill_answer_2"];
        if(!empty($validatedData["drill_answer_3"])){
            $drillData["drill_answer_3"] = $validatedData["drill_answer_3"];
        }
        if(!empty($validatedData["drill_answer_4"])){
            $drillData["drill_answer_4"] = $validatedData["drill_answer_4"];
        }
        $drillData["drill_answer_implied_traits_1"] = "";
        $drillData["drill_answer_implied_traits_2"] = "";
        $drillData["drill_answer_implied_traits_3"] = "";
        $drillData["drill_answer_implied_traits_4"] = "";
        $drillData["drill_maker_investor_id"] = $user->investor_id;
        Drill::create($drillData);

        return response([
            "status" => "yes", 
            "message" => "Drill saved to your Pott. You will know when it broadcasts worldwide."
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    | THIS FUNCTION ADDS A BUSINESS' PROFILE
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    */
    
    public function addBusiness(Request $request)
    {
        /*
        |**************************************************************************
        | VALIDATION STARTS 
        |**************************************************************************
        */
        // MAKING SURE THE INPUT HAS THE EXPECTED VALUES
        $validatedData = $request->validate([
            "user_phone_number" => "bail|required|regex:/^\+\d{10,15}$/|min:10|max:15",
            "investor_id" => "bail|required",
            "user_language" => "bail|required|max:3",
            "app_type" => "bail|required|max:8",
            "app_version_code" => "bail|required|integer",
            // ADD ANY OTHER REQUIRED INPUTS FROM HERE
            "business_pottname" => "bail|required|string|regex:/^[A-Za-z0-9_.]+$/|max:15",
            "business_type" => "bail|required|string|min:5|max:100",
            "business_logo" => "bail|required|string",
            "business_full_name" => "bail|required|string|min:4|max:150",
            "business_stockmarket_shortname" => "bail|required|min:5|max:10",
            "business_descriptive_bio" => "bail|required|min:50|max:300",
            "business_address" => "bail|required|min:5|max:150",
            "business_pitch_text" => "bail|required|min:10|max:200",
            "business_pitch_video" => "bail|required",
            "business_revenue_usd" => "bail|required|integer",
            "business_loss_usd" => "bail|required|integer",
            "business_debt_usd" => "bail|required|integer",
            "business_cash_on_hand_usd" => "bail|required|integer",
            "business_net_worth_usd" => "bail|required|integer",
            "business_net_valuation_usd" => "bail|required|integer",
            "business_investments_amount_needed_usd" => "bail|required|integer",
            "business_maximum_number_of_investors_allowed" => "bail|required|integer",
            "business_descriptive_financial_bio" => "bail|required|min:5|max:150",
            "business_executive1_firstname" => "bail|required|min:2|max:100",
            "business_executive1_lastname" => "bail|required|min:2|max:100",
            "business_executive1_profile_picture" => "bail|required",
            "business_executive1_description" => "bail|required|min:5|max:150",
            //"business_executive1_facebook_url" => "bail|required",
            //"business_executive1_linkedin_url" => "bail|required",
            "business_executive2_firstname" => "bail|required|min:2|max:100",
            "business_executive2_lastname" => "bail|required|min:2|max:100",
            "business_executive2_profile_picture" => "bail|required",
            "business_executive2_description" => "bail|required|min:5|max:150",
            //"business_executive2_facebook_url" => "bail|required",
            //"business_executive2_linkedin_url" => "bail|required",
            "business_executive3_firstname" => "bail|required|min:2|max:100",
            "business_executive3_lastname" => "bail|required|min:2|max:100",
            "business_executive3_profile_picture" => "bail|required",
            "business_executive3_description" => "bail|required|min:5|max:150",
            //"business_executive3_facebook_url" => "bail|required",
            //"business_executive3_linkedin_url" => "bail|required",
            "business_executive4_firstname" => "bail|required|min:2|max:100",
            "business_executive4_lastname" => "bail|required|min:2|max:100",
            "business_executive4_profile_picture" => "bail|required",
            "business_executive4_description" => "bail|required|min:5|max:150",
            //"business_executive4_facebook_url" => "bail|required",
            //"business_executive4_linkedin_url" => "bail|required",
        ]);

        // MAKING SURE THE REQUEST AND USER IS VALIDATED
        $validation_response = $this->validateUserWithAuthToken($request, auth()->user());
        if(!empty($validation_response["status"]) && trim($validation_response["status"]) == "error"){
            return response($validation_response);
        } else {
            $user = $validation_response;
        }
        /*
        |**************************************************************************
        | VALIDATION ENDED 
        |**************************************************************************
        */

        //CREATING THE USER DATA TO ADD TO DB
        $drillData["business_sys_id"] = $user->user_pottname . "-" . substr($validatedData["user_phone_number"] ,1,strlen($validatedData["user_phone_number"])) . date("Y-m-d-H-i-s") . $this->getRandomString(50);
        $drillData["drill_question"] = $validatedData["drill_question"];
        $drillData["drill_answer_1"] = $validatedData["drill_answer_1"];
        $drillData["drill_answer_2"] = $validatedData["drill_answer_2"];
        if(!empty($validatedData["drill_answer_3"])){
            $drillData["drill_answer_3"] = $validatedData["drill_answer_3"];
        }
        if(!empty($validatedData["drill_answer_4"])){
            $drillData["drill_answer_4"] = $validatedData["drill_answer_4"];
        }
        $drillData["drill_answer_implied_traits_1"] = "";
        $drillData["drill_answer_implied_traits_2"] = "";
        $drillData["drill_answer_implied_traits_3"] = "";
        $drillData["drill_answer_implied_traits_4"] = "";
        $drillData["drill_maker_investor_id"] = $user->investor_id;
        Drill::create($drillData);

        return response([
            "status" => "yes", 
            "message" => "Drill saved to your Pott. You will know when it broadcasts worldwide."
        ]);
    }

}
