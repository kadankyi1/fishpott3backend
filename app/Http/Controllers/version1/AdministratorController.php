<?php

namespace App\Http\Controllers\version1;

use DB;
use DateTime;
use Illuminate\Http\Request;
use App\Models\version1\User;
use App\Models\version1\Gender;
use App\Models\version1\Country;
use App\Models\version1\Language;
use App\Models\version1\ResetCode;
use App\Mail\version1\ResetCodeMail;
use App\Http\Controllers\Controller;
use App\Http\Controllers\version1\LogController;
use App\Models\version1\Administrator;
use App\Models\version1\Business;
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
    | THIS FUNCTION REGISTES THE ADMIN AND PROVIDES THEM WITH AN ACCESS TOKEN
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    |
    */
    public function registerFirstAdmin(Request $request)
    {

        $validatedData = $request->validate([
            "administrator_sys_id" => "bail|required|max:55",
            "administrator_user_pott_investor_id" => "bail|required",
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
            "password" => "bail|required",
            "administrator_user_pottname" => "bail|required|string|regex:/^[A-Za-z0-9_.]+$/|max:15",
            "administrator_pott_password" => "bail|required",
            "frontend_key" => "bail|required|in:2aLW4c7r9(2qf#y"
        ]);

        $loginData["administrator_phone_number"] = $validatedData["administrator_phone_number"];
        $loginData["password"] = $validatedData["password"];

        $pottLoginData["user_phone_number"] = $validatedData["administrator_phone_number"];
        $pottLoginData["password"] = $validatedData["administrator_pott_password"];


        // VALIDATING ADMIN CREDENTIALS
        if (!auth()->guard('administrator')->attempt($loginData)) {
            return response([
                "status" => "error", 
                "message" => "Invalid Credentials - a"
            ]);
        }

        // VALIDATING ADMIN CREDENTIALS
        if (!auth()->attempt($pottLoginData)) {
            return response([
                "status" => "error", 
                "message" => "Invalid Credentials - p"
            ]);
        }

        // CHECKING IF ADMIN FLAGGED
        if (auth()->guard('administrator')->user()->user_flagged) {
            return response([
                "status" => "0", 
                "message" => "Account access restricted"
            ]);
        }

        //echo "administrator_flagged: " . auth()->guard('administrator')->user()->administrator_flagged;
        //echo "\n administrator_scope: " . auth()->guard('administrator')->user()->administrator_scope; exit;
        
        // GENERATING ADMIN ACCESS TOKEN
        $accessToken = auth()->guard('administrator')->user()->createToken("authToken", [auth()->guard('administrator')->user()->administrator_scope])->accessToken;

        LogController::save_log("administrator", auth()->guard('administrator')->user()->administrator_sys_id, "Login Admin", "Login successful");

        return response([
            "status" => "yes", 
            "message" => "",
            "access_token" => $accessToken,
            "administrator_user_pottname" => auth()->guard('administrator')->user()->administrator_user_pottname,
            "administrator_firstname" => auth()->guard('administrator')->user()->administrator_firstname,
            "administrator_surname" => auth()->guard('administrator')->user()->administrator_surname,
            "administrator_sys_id" => auth()->guard('administrator')->user()->administrator_sys_id
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
            "administrator_phone_number" => "bail|required|regex:/^\+\d{10,15}$/|min:10|max:15",
            "administrator_sys_id" => "bail|required",
            "frontend_key" => "bail|required|in:2aLW4c7r9(2qf#y",
            // ADD ANY OTHER REQUIRED INPUTS FROM HERE
            "drill_question" => "min:5|max:100",
            "drill_answer_1" => "min:2|max:100",
            "drill_answer_2" => "min:2|max:100",
            "drill_answer_3" => "max:100",
            "drill_answer_4" => "max:100",
        ]);

        // MAKING SURE THE REQUEST AND USER IS VALIDATED
        $validation_response = UtilController::validateAdminWithAuthToken($request, auth()->guard('administrator-api')->user(), "add-drill");
        if(!empty($validation_response["status"]) && trim($validation_response["status"]) == "error"){
            return response($validation_response);
        } else {
            $admin = $validation_response;
        }
        /*
        |**************************************************************************
        | VALIDATION ENDED 
        |**************************************************************************
        */

        //CREATING THE USER DATA TO ADD TO DB
        $drillData["drill_sys_id"] = $admin->administrator_user_pottname . "-" . substr($validatedData["administrator_phone_number"] ,1,strlen($validatedData["administrator_phone_number"])) . date("Y-m-d-H-i-s") . UtilController::getRandomString(50);
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
        $drillData["drill_maker_investor_id"] = $admin->administrator_user_pott_investor_id;
        Drill::create($drillData);

        return response([
            "status" => "yes", 
            "message" => "Drill saved"
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
            "administrator_phone_number" => "bail|required|regex:/^\+\d{10,15}$/|min:10|max:15",
            "administrator_sys_id" => "bail|required",
            "frontend_key" => "bail|required|in:2aLW4c7r9(2qf#y",
            // ADD ANY OTHER REQUIRED INPUTS FROM HERE
            "business_pottname" => "nullable|string|regex:/^[A-Za-z0-9_.]+$/|max:15",
            "business_type" => "bail|required|string|min:5|max:100",
            "business_logo" => "bail|required",
            "business_full_name" => "bail|required|string|min:4|max:150",
            "business_stockmarket_shortname" => "nullable|max:10",
            "business_descriptive_bio" => "bail|required|max:150",
            "business_address" => "bail|required|min:5|max:150",
            "business_country" => "bail|required|min:5|max:150",
            "business_website" => "nullable|max:150",

            "business_pitch_text" => "bail|required|min:10|max:100",
            "business_pitch_video" => "bail|required",

            "business_lastyr_revenue_usd" => "bail|required|integer",
            "business_profit_or_loss_usd" => "bail|required|integer",
            "business_debt_usd" => "bail|required|integer",
            "business_cash_on_hand_usd" => "bail|required|integer",
            "business_net_worth_usd" => "bail|required|integer",
            "business_investments_amount_needed_usd" => "bail|required|integer",
            "business_maximum_number_of_investors_allowed" => "bail|required|integer",
            "business_descriptive_financial_bio" => "bail|required|min:5|max:150",

            "business_executive1_firstname" => "bail|required|min:2|max:100",
            "business_executive1_lastname" => "bail|required|min:2|max:100",
            "business_executive1_profile_picture" => "nullable",
            "business_executive1_position" => "bail|required|max:100",
            "business_executive1_description" => "nullable|max:150",
            "business_executive1_facebook_url" => "nullable",
            "business_executive1_linkedin_url" => "nullable",

            "business_executive2_firstname" => "bail|required|min:2|max:100",
            "business_executive2_lastname" => "bail|required|min:2|max:100",
            "business_executive2_profile_picture" => "nullable",
            "business_executive2_position" => "bail|required|max:100",
            "business_executive2_description" => "bail|required|min:5|max:150",
            "business_executive2_facebook_url" => "nullable",
            "business_executive2_linkedin_url" => "nullable",

            "business_executive3_firstname" => "nullable|min:2|max:100",
            "business_executive3_lastname" => "nullable|min:2|max:100",
            "business_executive3_profile_picture" => "nullable",
            "business_executive3_position" => "nullable|max:100",
            "business_executive3_description" => "nullable|max:150",
            "business_executive3_facebook_url" => "nullable",
            "business_executive3_linkedin_url" => "nullable",

            "business_executive4_firstname" => "nullable|min:2|max:100",
            "business_executive4_lastname" => "nullable|min:2|max:100",
            "business_executive4_profile_picture" => "nullable",
            "business_executive4_position" => "nullable|max:100",
            "business_executive4_description" => "nullable|max:150",
            "business_executive4_facebook_url" => "nullable",
            "business_executive4_linkedin_url" => "nullable",
        ]);

        // MAKING SURE THE REQUEST AND USER IS VALIDATED
        $validation_response = UtilController::validateAdminWithAuthToken($request, auth()->guard('administrator-api')->user(), "add-business");
        if(!empty($validation_response["status"]) && trim($validation_response["status"]) == "error"){
            return response($validation_response);
        } else {
            $admin = $validation_response;
        }
        /*
        |**************************************************************************
        | VALIDATION ENDED 
        |**************************************************************************
        */

        // CHECKING IF REQUEST HAS THE LOGO FILE
        if(!$request->hasFile('business_logo')) {
            return response([
                "status" => "error", 
                "message" => "Logo not found"
            ]);
        }
    
        // CHECKING IF POTT PICTURE IS UPLOADED CORRECTLY AND IS THE RIGHT FORMAT
        if(!$request->file('business_logo')->isValid() || (strtolower($request->file('business_logo')->getMimeType())  !=  "image/png" && strtolower($request->file('business_logo')->getMimeType())  !=  "image/jpg" && strtolower($request->file('business_logo')->getMimeType())  !=  "image/jpeg")) {
            return response([
                "status" => "error", 
                "message" => "Image has to be JPG or PNG"
            ]);
        }

        // CHECKING THAT IMAGE IS NOT MORE THAN 5MB
        if($request->file('business_logo')->getSize() > (2 * intval(config('app.mb')))){
            return response([
                "status" => "error", 
                "message" => "Logo cannot be more than 2 MB"
            ]);
        }

        $img_path = public_path() . '/uploads/images/';
        $img_ext = $user->investor_id . uniqid() . date("Y-m-d-H-i-s") . "." . strtolower($request->file('pott_picture')->extension());
        $img_url = config('app.url') . '/uploads/images/' . $img_ext;
    
        if(!$request->file('pott_picture')->move($img_path, $img_ext)){
            return response([
                "status" => "error", 
                "message" => "Image upload failed"
            ]);
        }
        
        // CREATING THE BUSINESS SYSTEM ID 
        $validatedData["business_sys_id"] = $admin->administrator_user_pottname . "-" . substr($validatedData["administrator_phone_number"] ,1,strlen($validatedData["administrator_phone_number"])) . date("Y-m-d-H-i-s") . UtilController::getRandomString(50);

        // CREATING THE BUSINESS
        Business::create($validatedData);

        return response([
            "status" => "yes", 
            "message" => "Business saved."
        ]);
    }

}
