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
use App\Models\version1\DrillAnswer;
use App\Models\version1\StockPurchase;
use App\Models\version1\StockValue;
use App\Models\version1\Suggestion;
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
                "status" => 0, 
                "message" => "Invalid Credentials - a"
            ]);
        }

        // VALIDATING ADMIN CREDENTIALS
        if (!auth()->attempt($pottLoginData)) {
            return response([
                "status" => 0, 
                "message" => "Invalid Credentials - p"
            ]);
        }

        // CHECKING IF ADMIN FLAGGED
        if (auth()->guard('administrator')->user()->user_flagged) {
            return response([
                "status" => 0, 
                "message" => "Account access restricted"
            ]);
        }

        //echo "administrator_flagged: " . auth()->guard('administrator')->user()->administrator_flagged;
        //echo "\n administrator_scope: " . auth()->guard('administrator')->user()->administrator_scope; exit;
        
        // GENERATING ADMIN ACCESS TOKEN
        $accessToken = auth()->guard('administrator')->user()->createToken("authToken", [auth()->guard('administrator')->user()->administrator_scope])->accessToken;

        LogController::save_log("administrator", auth()->guard('administrator')->user()->administrator_sys_id, "Login Admin", "Login successful");

        return response([
            "status" => 1, 
            "message" => "",
            "access_token" => $accessToken,
            "administrator_phone_number" => auth()->guard('administrator')->user()->administrator_phone_number,
            "administrator_user_pottname" => auth()->guard('administrator')->user()->administrator_user_pottname,
            "administrator_firstname" => auth()->guard('administrator')->user()->administrator_firstname,
            "administrator_surname" => auth()->guard('administrator')->user()->administrator_surname,
            "administrator_sys_id" => auth()->guard('administrator')->user()->administrator_sys_id,
            "frontend_key" => $request->frontend_key
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    | THIS FUNCTION LOGS OUT AN ADMIN
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    */
    public function logoutAsAdministrator(Request $request)
    {

        $request->user()->token()->revoke();
        return response([
            "status" => 1, 
            "message" => "Logged out"
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    | THIS FUNCTION RETURNS ADMIN DASHBOARD DATA
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    */
    public function getDashboardData(Request $request)
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
        ]);

        // MAKING SURE THE REQUEST AND USER IS VALIDATED
        $validation_response = UtilController::validateAdminWithAuthToken($request, auth()->guard('administrator-api')->user(), "get-info");
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
        
        // GETTING TIME FRAMES
        $one_hour_ago = date('Y-m-d H:i:s',strtotime("-1 hours")); 
        $one_day_ago = date('Y-m-d',strtotime("-1 days")); 
        $thirty_days_ago = date('Y-m-d',strtotime("-31 days")); 
        $one_yr_days_ago = date('Y-m-d',strtotime("-365 days")); 

        // GETTING USERS DATA

        // GETTING USERS DATA
        $users_all = User::count();   
        $users_today_count = User::where('last_online', ">=" , $one_day_ago)->count(); 
        $users_thirtydays_count = User::where('last_online', ">=" , $thirty_days_ago)->count(); 

        // GETTING SUGGESTIONS DATA
        $suggestions_active = Suggestion::where('created_at', ">=" , $one_hour_ago)->count(); 
        $suggestions_active_drill = Suggestion::where('suggestion_suggestion_type_id', "=" , 1)->where('created_at', ">=" , $one_hour_ago)->count(); 
        $suggestions_active_business = Suggestion::where('suggestion_suggestion_type_id', "=" , 2)->where('created_at', ">=" , $one_hour_ago)->count(); 
        
        // GETTING BUSINESS DATA
        $businesses_all = Business::count();   
        $businesses_listed = Business::where('business_stockmarket_shortname', "!=" , "")->count(); 
        $businesses_not_listed = Business::where('business_stockmarket_shortname', "=" , "")->count(); 
        
        // GETTING ORDERS DATA
        $orders_paid_pending = StockPurchase::where('stockpurchase_payment_gateway_status', "=" , 1)->where('stockpurchase_processed', "=" , 0)->count(); 
        $orders_paid_thirty_days = StockPurchase::where('created_at', ">=" , $thirty_days_ago)->where('stockpurchase_payment_gateway_status', "=" , 1)->count(); 
        $orders_unpaid_thirty_days = StockPurchase::where('created_at', ">=" , $thirty_days_ago)->where('stockpurchase_payment_gateway_status', "!=" , 1)->count(); 
        
        // GETTING USERS DATA
        $answers_today_count = DrillAnswer::where('created_at', ">=" , $one_day_ago)->count(); 
        $answers_thirtydays_count = DrillAnswer::where('created_at', ">=" , $thirty_days_ago)->count(); 
        $answers_oneyear_count = DrillAnswer::where('created_at', ">=" , $one_yr_days_ago)->count(); 

        /*
        echo "\nnow : " . date('Y-m-d H:i:s');
        echo "\none_hour_ago : " . $one_hour_ago;
        */
        
        $data = array(
            "users_total_count" => $users_all, 
            "users_today_count" => $users_today_count, 
            "users_thirtydays_count" => $users_thirtydays_count, 
            "suggestions_active" => $suggestions_active, 
            "suggestions_active_drill" => $suggestions_active_drill, 
            "suggestions_active_business" => $suggestions_active_business, 
            "businesses_all" => $businesses_all, 
            "businesses_listed" => $businesses_listed, 
            "businesses_not_listed" => $businesses_not_listed,
            "orders_paid_pending" => $orders_paid_pending, 
            "orders_paid_thirty_days" => $orders_paid_thirty_days, 
            "orders_unpaid_thirty_days" => $orders_unpaid_thirty_days, 
            "answers_today_count" => $answers_today_count, 
            "answers_thirtydays_count" => $answers_thirtydays_count, 
            "answers_oneyear_count" => $answers_oneyear_count, 
            "contact_email" => config('app.fishpott_email'), 
            "payment_gateway_name" => config('app.payment_gateway_name'), 
            "payment_gateway_url" => config('app.payment_gateway_login_url')
        );

        return response([
            "status" => 1, 
            "message" => "success",
            "data" => $data
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
            "administrator_pin" => "bail|required",
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
        $drillData["drill_sys_id"] = "drill-" . $admin->administrator_user_pottname . "-" . substr($validatedData["administrator_phone_number"] ,1,strlen($validatedData["administrator_phone_number"])) . date("Y-m-d-H-i-s") . UtilController::getRandomString(50);
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
            "status" => 1, 
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
            "administrator_pin" => "bail|required",
            "frontend_key" => "bail|required|in:2aLW4c7r9(2qf#y",
            // ADD ANY OTHER REQUIRED INPUTS FROM HERE
            "business_pottname" => "nullable|string|regex:/^[A-Za-z0-9_.]+$/|max:15",
            "business_registration_number" => "bail|required|string|min:5|max:100",
            "business_type" => "bail|required|string|min:5|max:100",
            "business_logo_file" => "bail|required",
            //"business_find_code" => "bail|required|max:10",
            "business_full_name" => "bail|required|string|min:4|max:150",
            "business_stockmarket_shortname" => "nullable|max:10",
            "business_descriptive_bio" => "bail|required|max:150",
            "business_address" => "bail|required|min:5|max:150",
            "business_country" => "bail|required|integer",
            "business_start_date" => "bail|required|date|before:-1 years",
            "business_website" => "nullable|max:150",

            "business_pitch_text" => "bail|required|min:10|max:100",
            "business_pitch_video" => "bail|required|mimes:mp4",

            "business_lastyr_revenue_usd" => "bail|required|integer",
            "business_lastyr_profit_or_loss_usd" => "bail|required|integer",
            "business_debt_usd" => "bail|required|integer",
            "business_cash_on_hand_usd" => "bail|required|integer",
            "business_net_worth_usd" => "bail|required|integer",
            "business_price_per_stock_usd" => "bail|required|numeric",
            "business_investments_amount_needed_usd" => "bail|required|integer",
            "business_maximum_number_of_investors_allowed" => "bail|required|integer",
            "business_current_shareholders" => "bail|required|integer",
            "business_full_financial_report_pdf_url" => "bail|required|mimes:pdf",
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
            "business_executive2_description" => "nullable|max:150",
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

        // GETTING COUNTRY ID
        $country = Country::where('country_id', '=', $validatedData["business_country"])->first();
        if($country === null){
            return response([
                "status" => "error", 
                "message" => "Country validation error."
            ]);
        }

        // CHECKING IF REQUEST HAS THE LOGO FILE
        if(!$request->hasFile('business_logo_file')) {
            return response([
                "status" => "error", 
                "message" => "Logo not found"
            ]);
        }
    
        // CHECKING IF POTT PICTURE IS UPLOADED CORRECTLY AND IS THE RIGHT FORMAT
        if(!$request->file('business_logo_file')->isValid() || (strtolower($request->file('business_logo_file')->getMimeType())  !=  "image/png" && strtolower($request->file('business_logo_file')->getMimeType())  !=  "image/jpg" && strtolower($request->file('business_logo_file')->getMimeType())  !=  "image/jpeg")) {
            return response([
                "status" => "error", 
                "message" => "Image has to be JPG or PNG"
            ]);
        }

        // CHECKING THAT IMAGE IS NOT MORE THAN 2MB
        if($request->file('business_logo_file')->getSize() > (2 * intval(config('app.mb')))){
            return response([
                "status" => "error", 
                "message" => "Logo cannot be more than 2 MB"
            ]);
        }

        $img_path = public_path() . '/uploads/logos/';
        $img_ext = $validatedData["business_registration_number"] . "." . strtolower($request->file('business_logo_file')->extension());
    
        if(!$request->file('business_logo_file')->move($img_path, $img_ext)){
            return response([
                "status" => "error", 
                "message" => "Logo upload failed"
            ]);
        }
        
        // CHECKING IF REQUEST HAS THE BUSINESS FINANCIAL INFO
        if(!$request->hasFile('business_full_financial_report_pdf_url')) {
            return response([
                "status" => "error", 
                "message" => "Financial info PDF not found"
            ]);
        }
    
        // CHECKING IF THE BUSINESS FINANCIAL INFO IS UPLOADED CORRECTLY AND IS THE RIGHT FORMAT
        if(!$request->file('business_full_financial_report_pdf_url')->isValid()) {
            return response([
                "status" => "error", 
                "message" => "Financial info has to be a valid PDF"
            ]);
        }

        // CHECKING THAT THE BUSINESS FINANCIAL INFO PDF IS NOT MORE THAN 10MB
        if($request->file('business_full_financial_report_pdf_url')->getSize() > (10 * intval(config('app.mb')))){
            return response([
                "status" => "error", 
                "message" => "Financial info cannot be more than 10 MB"
            ]);
        }
        
        $pdf_path = public_path() . '/uploads/financedata/';
        $pdf_ext = $validatedData["business_registration_number"] . "." . strtolower($request->file('business_full_financial_report_pdf_url')->extension());

        // UPLOADING FILE
        if(!$request->file('business_full_financial_report_pdf_url')->move($pdf_path, $pdf_ext)){
            return response([
                "status" => "error", 
                "message" => "Financial info PDF upload failed"
            ]);
        }

        // CHECKING IF REQUEST HAS THE BUSINESS PITCH VIDEO
        if(!$request->hasFile('business_pitch_video')) {
            return response([
                "status" => "error", 
                "message" => "Pitch video not found"
            ]);
        }
    
        // CHECKING IF THE BUSINESS PITCH VIDEO IS UPLOADED CORRECTLY AND IS THE RIGHT FORMAT
        if(!$request->file('business_pitch_video')->isValid()) {
            return response([
                "status" => "error", 
                "message" => "Pitch video has to be valid MP4"
            ]);
        }

        // CHECKING THAT THE BUSINESS FINANCIAL INFO PDF IS NOT MORE THAN 10MB
        if($request->file('business_pitch_video')->getSize() > (25 * intval(config('app.mb')))){
            return response([
                "status" => "error", 
                "message" => "Pitch video cannot be more than 25 MB"
            ]);
        }
        
        $business_pitch_video_path = public_path() . '/uploads/pitchvideos/';
        $business_pitch_video_ext = $validatedData["business_registration_number"] . "." . strtolower($request->file('business_pitch_video')->extension());

        // UPLOADING FILE
        if(!$request->file('business_pitch_video')->move($business_pitch_video_path, $business_pitch_video_ext)){
            return response([
                "status" => "error", 
                "message" => "Pitch video upload failed"
            ]);
        }

        // CREATING THE BUSINESS SYSTEM ID 
        $validatedData["business_sys_id"] = "business-" . $admin->administrator_user_pottname . "-" . substr($validatedData["administrator_phone_number"] ,1,strlen($validatedData["administrator_phone_number"])) . date("Y-m-d-H-i-s") . UtilController::getRandomString(50);
        
        // ADDING BUSINESS COUNTRY ID
        $validatedData["business_country_id"] = $country->country_id;

        // ADDING LOGO PATH, PDF AND VIDEO PATHS AND DEFAULT FLAGGED REASON
        $validatedData["business_logo"] = $img_ext;
        $validatedData["business_full_financial_report_pdf_url"] = $pdf_ext;
        $validatedData["business_pitch_video"] = $business_pitch_video_ext;
        $validatedData["business_flagged_reason"] = "";
        $validatedData["business_investments_amount_received_usd"] = 0;
        $validatedData["business_find_code"] = date('Ymd-His');
        if(empty($request->business_executive1_description)){
            $validatedData["business_executive1_description"] = "";
        }
        if(empty($request->business_executive2_description)){
            $validatedData["business_executive2_description"] = "";
        }
        if(empty($request->business_executive3_description)){
            $validatedData["business_executive3_description"] = "";
        }
        if(empty($request->business_executive4_description)){
            $validatedData["business_executive4_description"] = "";
        }

        // REMOVING UN-NEEDED INFO
        unset($validatedData["business_country"]);
        unset($validatedData["business_logo_file"]);
        
        // CREATING THE BUSINESS
        Business::create($validatedData);

        return response([
            "status" => 1, 
            "message" => "Business saved."
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    | THIS FUNCTION SEARCHES FOR A BUSINESS
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    */

    public function searchModel(Request $request)
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
            "model" => "bail|required",
            "keyword" => "nullable",
        ]);

        // MAKING SURE THE REQUEST AND USER IS VALIDATED
        $validation_response = UtilController::validateAdminWithAuthToken($request, auth()->guard('administrator-api')->user(), "get-info");
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
        if(empty($request->keyword)){
            if($request->model == "business"){
                $data = Business::select('business_full_name', 'business_sys_id')
                        ->orderBy('business_id', 'desc')->take(100)->get();
            }
        } else {
            if($request->model == "business"){
                $data = Business::select('business_full_name', 'business_sys_id')
                        ->where('business_full_name', 'LIKE', "%{$request->keyword}%")
                        ->orderBy('business_id', 'desc')->take(100)->get();
            }
        }

        return response([
            "status" => 1, 
            "message" => "success",
            "data" => $data
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    | THIS FUNCTION ADDS A SUGGESTION
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    */
    
    public function addSuggestion(Request $request)
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
            "administrator_pin" => "bail|required",
            // ADD ANY OTHER REQUIRED INPUTS FROM HERE
            "item_id" => "bail|required",
            "item_type" => "bail|required|integer",
            "user_pottname" => "bail|required|string",
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

        // INITIALIZING SUGGESTIONS DATA
        $suggestionData = array();
        
        if($request->item_id == 1){
            // CHECKING IF THE BUSINESS EXISTS
            $drill = Drill::where('drill_sys_id', $request->item_id)->first();
            if($drill == null){
                return response([
                    "status" => 0, 
                    "message" => "Drill not found"
                ]);
            }
            // CREATING THE SUGGESTION VALUE DATA FOR BUSINESS
            $suggestionData["suggestion_sys_id"] = "sug-" . $drill->drill_sys_id;
            $suggestionData["suggestion_item_reference_id"] = $drill->drill_sys_id;
            $suggestionData["suggestion_directed_at_user_investor_id"] = "";
            $suggestionData["suggestion_directed_at_user_business_find_code"] = "";
            $suggestionData["suggestion_suggestion_type_id"] = $request->item_type;
            $message = "Suggestion saved.";
        } else if($request->item_id == 2){
            // CHECKING IF THE BUSINESS EXISTS
            $business = Business::where('business_sys_id', $request->item_id)->first();
            if($business == null){
                return response([
                    "status" => 0, 
                    "message" => "Business not found"
                ]);
            }

            // CHECKING IF USER EXISTS
            $pott_user = User::where('user_pottname', $request->user_pottname)->first();
            if($pott_user == null){
                return response([
                    "status" => 0, 
                    "message" => "User not found"
                ]);
            }

            // CREATING THE SUGGESTION VALUE DATA FOR BUSINESS
            $suggestionData["suggestion_sys_id"] = "sug-" . $business->business_sys_id;
            $suggestionData["suggestion_item_reference_id"] = $business->business_sys_id;
            $suggestionData["suggestion_directed_at_user_investor_id"] = $pott_user->investor_id;
            $suggestionData["suggestion_directed_at_user_business_find_code"] = $pott_user->user_pottname . date('YmdHis');
            $suggestionData["suggestion_suggestion_type_id"] = $request->item_type;
            $message = "Suggestion saved. Find code is : " . $suggestionData["suggestion_directed_at_user_business_find_code"];
        } else {
            return response([
                "status" => 0, 
                "message" => "Item type not found"
            ]);
        }


        // CREATING THE SUGGESTION VALUE DATA FOR BUSINESS
        $suggestionData["suggestion_passed_on_by_user"] = false;
        $suggestionData["suggestion_flagged"] = false;
        Suggestion::create($suggestionData);

        return response([
            "status" => 1, 
            "message" => $message
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    | THIS FUNCTION ADDS A DRILL
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    */
    
    public function addNewShareValue(Request $request)
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
            "business_id" => "bail|required",
            "new_value" => "bail|required|numeric",
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

        // CHECKING IF THE BUSINESS EXISTS
        $business = Business::where('business_sys_id', $request->business_id)->first();
        if($business == null){
            return response([
                "status" => "error", 
                "message" => "Business not found"
            ]);
        }

        //CREATING THE STOCK VALUE DATA
        $stockValueData["stockvalue_business_id"] = $request->business_id;
        $stockValueData["stockvalue_value_per_stock_usd"] = floatval($request->new_value);
        $stockValueData["stockvalue_admin_adder_id"] = $admin->administrator_sys_id;
        StockValue::create($stockValueData);

        return response([
            "status" => 1, 
            "message" => "New stock value saved"
        ]);
    }

}
