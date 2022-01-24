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
use App\Mail\version1\UserAlertMail;
use App\Models\version1\Administrator;
use App\Models\version1\AiStockPersona;
use App\Models\version1\Business;
use App\Models\version1\Drill;
use App\Models\version1\DrillAnswer;
use App\Models\version1\StockPurchase;
use App\Models\version1\StockSellBack;
use App\Models\version1\StockTrainData;
use App\Models\version1\StockTransfer;
use App\Models\version1\StockValue;
use App\Models\version1\Suggestion;
use App\Models\version1\Suggesto;
use App\Models\version1\Transaction;
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
            "frontend_key" => "bail|required"
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
            "frontend_key" => "bail|required"
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
        $adminTokens = $request->user()->tokens;
        foreach($adminTokens as $token) {
            $token->revoke();   
        }
        
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
            "frontend_key" => "bail|required",
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
            "frontend_key" => "bail|required",
            // ADD ANY OTHER REQUIRED INPUTS FROM HERE
            "drill_question" => "bail|required|min:5|max:100",
            "drill_answer_1" => "bail|required|min:2|max:100",
            "drill_answer_2" => "bail|required|min:2|max:100",
            "drill_answer_3" => "bail|required|max:100",
            "drill_answer_4" => "bail|required|max:100",
            "drill_answer_1_ocean" => "bail|max:100",
            "drill_answer_2_ocean" => "bail|max:100",
            "drill_answer_3_ocean" => "bail|max:100",
            "drill_answer_4_ocean" => "bail|max:100",
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

        if(trim($request->drill_answer_1_ocean) != ""){
            if(
                count(explode("#", $request->drill_answer_1_ocean)) != 5 
                || count(explode("#", $request->drill_answer_2_ocean)) != 5
                || count(explode("#", $request->drill_answer_3_ocean)) != 5
                || count(explode("#", $request->drill_answer_4_ocean)) != 5
                ){
                return response([
                    "status" => 0, 
                    "message" => "The Ocean values for the answers have to be 5 percentage values seperated by #. Do not include the % sign"
                ]);
            }
        } else {
            $validatedData["drill_answer_1_ocean"] = "";
            $validatedData["drill_answer_2_ocean"] = "";
            $validatedData["drill_answer_3_ocean"] = "";
            $validatedData["drill_answer_4_ocean"] = "";
        }

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
        
        $drillData["drill_answer_1_ocean"] = $validatedData["drill_answer_1_ocean"];
        $drillData["drill_answer_2_ocean"] = $validatedData["drill_answer_2_ocean"];
        $drillData["drill_answer_3_ocean"] = $validatedData["drill_answer_3_ocean"];
        $drillData["drill_answer_4_ocean"] = $validatedData["drill_answer_4_ocean"];
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
            "frontend_key" => "bail|required",
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
            "business_phone1" => "bail|required|min:10|max:15",
            "business_email1" => "bail|required|min:2|max:100",

            "business_pitch_text" => "bail|required|min:10|max:100",
            "business_pitch_video" => "bail|required|mimes:mp4",

            "business_lastyr_revenue_usd" => "bail|required|integer",
            "business_lastyr_profit_or_loss_usd" => "bail|required|integer",
            "business_debt_usd" => "bail|required|integer",
            "business_cash_on_hand_usd" => "bail|required|integer",
            "business_net_worth_usd" => "bail|required|integer",
            "business_price_per_stock_usd" => "bail|required|numeric",
            "buyback_offer_usd" => "bail|required|numeric",
            "business_investments_amount_needed_usd" => "bail|required|integer",
            "business_maximum_number_of_investors_allowed" => "bail|required|integer",
            "business_current_shareholders" => "bail|required|integer",
            "business_full_financial_report_pdf_url" => "bail|required|mimes:pdf",
            "business_descriptive_financial_bio" => "bail|required|min:5|max:150",

            "business_executive1_firstname" => "bail|required|min:2|max:100",
            "business_executive1_lastname" => "bail|required|min:2|max:100",
            "business_executive1_phone" => "bail|required|min:10|max:15",
            "business_executive1_email" => "bail|required|min:2|max:100",
            "business_executive1_profile_picture" => "nullable",
            "business_executive1_position" => "bail|required|max:100",
            "business_executive1_description" => "nullable|max:150",
            "business_executive1_facebook_url" => "nullable",
            "business_executive1_linkedin_url" => "nullable",

            "business_executive2_firstname" => "bail|required|min:2|max:100",
            "business_executive2_lastname" => "bail|required|min:2|max:100",
            "business_executive2_phone" => "bail|required|min:10|max:15",
            "business_executive2_email" => "bail|required|min:2|max:100",
            "business_executive2_profile_picture" => "nullable",
            "business_executive2_position" => "bail|required|max:100",
            "business_executive2_description" => "nullable|max:150",
            "business_executive2_facebook_url" => "nullable",
            "business_executive2_linkedin_url" => "nullable",

            "business_executive3_firstname" => "nullable|min:2|max:100",
            "business_executive3_lastname" => "nullable|min:2|max:100",
            "business_executive3_phone" => "nullable|max:15",
            "business_executive3_email" => "nullable|max:100",
            "business_executive3_profile_picture" => "nullable",
            "business_executive3_position" => "nullable|max:100",
            "business_executive3_description" => "nullable|max:150",
            "business_executive3_facebook_url" => "nullable",
            "business_executive3_linkedin_url" => "nullable",

            "business_executive4_firstname" => "nullable|min:2|max:100",
            "business_executive4_lastname" => "nullable|min:2|max:100",
            "business_executive4_phone" => "nullable|max:15",
            "business_executive4_email" => "nullable|max:100",
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
    | THIS FUNCTION SEARCHES FOR A MODEL LIST
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
            "frontend_key" => "bail|required",
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
            } else if($request->model == "drill"){
                $data = Drill::select('drill_question', 'drill_sys_id')
                        ->orderBy('drill_id', 'desc')->take(100)->get();
            }
        } else {
            if($request->model == "business"){
                $data = Business::select('business_full_name', 'business_sys_id')
                        ->where('business_full_name', 'LIKE', "%{$request->keyword}%")
                        ->orderBy('business_id', 'desc')->take(100)->get();
            } else if($request->model == "drill"){
                $data = Business::select('drill_question', 'drill_sys_id')
                        ->where('drill_question', 'LIKE', "%{$request->keyword}%")
                        ->orderBy('drill_id', 'desc')->take(100)->get();
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
    | THIS FUNCTION SEARCHES FOR A MODEL LIST
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    */

    public function searchOrders(Request $request)
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
            "frontend_key" => "bail|required",
            // ADD ANY OTHER REQUIRED INPUTS FROM HERE
            "keyword" => "nullable",
            "type" => "nullable",
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
        
        // GETTING STOCK PURCHASES
        if(empty($request->keyword)){
            $data_stock_purchases = DB::table('stock_purchases')
            ->select(
                'stock_purchases.stockpurchase_sys_id', 'stock_purchases.created_at', 'users.user_surname', 'users.user_firstname', 'users.user_phone_number', 'users.user_email',  
                'businesses.business_full_name',  'businesses.business_find_code', 'countries.country_nice_name', 
                'stock_purchases.stockpurchase_price_per_stock_usd',  'stock_purchases.stockpurchase_stocks_quantity', 'risk_insurance_types.risk_type_shortname',
                'stock_purchases.stockpurchase_risk_insurance_fee_usd',  'stock_purchases.stockpurchase_processing_fee_usd', 'stock_purchases.stockpurchase_total_price_with_all_fees_usd',
                'stock_purchases.stockpurchase_rate_of_dollar_to_currency_paid_in',  'stock_purchases.stockpurchase_processed', 
                'stock_purchases.stockpurchase_processed_reason', 'stock_purchases.stockpurchase_flagged', 'stock_purchases.stockpurchase_total_all_fees_in_currency_paid_in',
                'stock_purchases.stockpurchase_flagged_reason',  'stock_purchases.stockpurchase_payment_gateway_status', 'stock_purchases.stockpurchase_payment_gateway_info', 'currencies.currency_symbol')
            ->join('users', 'users.investor_id', '=', 'stock_purchases.stockpurchase_user_investor_id')
            ->join('currencies', 'currencies.currency_id', '=', 'stock_purchases.stockpurchase_currency_paid_in_id')
            ->join('businesses', 'businesses.business_sys_id', '=', 'stock_purchases.stockpurchase_business_id')
            ->join('risk_insurance_types', 'risk_insurance_types.risk_type_id', '=', 'stock_purchases.stockpurchase_risk_insurance_type_id')
            ->join('countries', 'businesses.business_country_id', '=', 'countries.country_id')
            ->where('stockpurchase_payment_gateway_status', '!=', 0)
            ->take(100)
            ->get();
        } else {                
            $data_stock_purchases = DB::table('stock_purchases')
            ->select(
                'stock_purchases.stockpurchase_sys_id', 'stock_purchases.created_at', 'users.user_surname', 'users.user_firstname', 'users.user_phone_number', 'users.user_email',  
                'businesses.business_full_name',  'businesses.business_find_code', 'countries.country_nice_name',
                'stock_purchases.stockpurchase_price_per_stock_usd',  'stock_purchases.stockpurchase_stocks_quantity', 'risk_insurance_types.risk_type_shortname',
                'stock_purchases.stockpurchase_risk_insurance_fee_usd',  'stock_purchases.stockpurchase_processing_fee_usd', 'stock_purchases.stockpurchase_total_price_with_all_fees_usd',
                'stock_purchases.stockpurchase_rate_of_dollar_to_currency_paid_in',  'stock_purchases.stockpurchase_processed', 'stock_purchases.stockpurchase_processed_reason', 
                'stock_purchases.stockpurchase_flagged', 'stock_purchases.stockpurchase_total_all_fees_in_currency_paid_in',
                'stock_purchases.stockpurchase_flagged_reason',  'stock_purchases.stockpurchase_payment_gateway_status', 'stock_purchases.stockpurchase_payment_gateway_info', 'currencies.currency_symbol')
            ->join('users', 'users.investor_id', '=', 'stock_purchases.stockpurchase_user_investor_id')
            ->join('currencies', 'currencies.currency_id', '=', 'stock_purchases.stockpurchase_currency_paid_in_id')
            ->join('businesses', 'businesses.business_sys_id', '=', 'stock_purchases.stockpurchase_business_id')
            ->join('risk_insurance_types', 'risk_insurance_types.risk_type_id', '=', 'stock_purchases.stockpurchase_risk_insurance_type_id')
            ->join('countries', 'businesses.business_country_id', '=', 'countries.country_id')
            ->where('user_phone_number', 'LIKE', "%{$request->keyword}%")
            //->where('stockpurchase_payment_gateway_status', '!=', 0)
            ->orderBy('stockpurchase_id', 'desc')
            ->take(100)
            ->get();
        }
        

        // GETTING STOCK TRANSFERS
        if(empty($request->keyword)){
            $data_stocks_transfers = DB::table('stocks_transfers')
            ->select(
                'stocks_transfers.stocktransfer_sys_id', 'stocks_transfers.created_at', 'users.user_surname', 'users.user_firstname', 'users.user_phone_number', 'users.user_email',  
                'businesses.business_full_name',  'businesses.business_find_code', 'countries.country_nice_name',
                'stocks_transfers.stocktransfer_stocks_quantity',  'stocks_transfers.stocktransfer_receiver_pottname', 
                //'stocks_transfers.stockstransfers_processing_fee_usd', 'stock_purchases.stockpurchase_rate_of_dollar_to_currency_paid_in',  
                'stocks_transfers.stockstransfers_processed', 'stocks_transfers.stockstransfers_processed_reason', 'stocks_transfers.stocktransfer_flagged',
                'stocks_transfers.stocktransfer_flagged_reason',  'stocks_transfers.stocktransfer_payment_gateway_status', 
                'stocks_transfers.stocktransfer_payment_gateway_info')
            ->join('users', 'users.investor_id', '=', 'stocks_transfers.stocktransfer_sender_investor_id')
            ->join('businesses', 'businesses.business_sys_id', '=', 'stocks_transfers.stocktransfer_business_id')
            ->join('countries', 'businesses.business_country_id', '=', 'countries.country_id')
            ->where('stockstransfers_processed', '=', 0)
            ->where('stocktransfer_payment_gateway_status', '!=', 0)
            ->take(100)
            ->get();
        } else {                

            $data_stocks_transfers = DB::table('stocks_transfers')
            ->select(
                'stocks_transfers.stocktransfer_sys_id', 'stocks_transfers.created_at', 'users.user_surname', 'users.user_firstname', 'users.user_phone_number', 'users.user_email',  
                'businesses.business_full_name',  'businesses.business_find_code', 'countries.country_nice_name',
                'stocks_transfers.stocktransfer_stocks_quantity',  'stocks_transfers.stocktransfer_receiver_pottname', 
                //'stocks_transfers.stockstransfers_processing_fee_usd', 'stock_purchases.stockpurchase_rate_of_dollar_to_currency_paid_in',  
                'stocks_transfers.stockstransfers_processed', 'stocks_transfers.stockstransfers_processed_reason', 'stocks_transfers.stocktransfer_flagged',
                'stocks_transfers.stocktransfer_flagged_reason',  'stocks_transfers.stocktransfer_payment_gateway_status', 
                'stocks_transfers.stocktransfer_payment_gateway_info')
            ->join('users', 'users.investor_id', '=', 'stocks_transfers.stocktransfer_sender_investor_id')
            ->join('businesses', 'businesses.business_sys_id', '=', 'stocks_transfers.stocktransfer_business_id')
            ->join('countries', 'businesses.business_country_id', '=', 'countries.country_id')
            ->where('user_phone_number', 'LIKE', "%{$request->keyword}%")
            //->where('stockstransfers_processed', '=', 0)
            //->where('stocktransfer_payment_gateway_status', '!=', 0)
            ->orderBy('stocktransfer_id', 'desc')->take(100)
            ->take(100)
            ->get();
        }
        
        // GETTING STOCK SELL BACKS
        if(empty($request->keyword)){
            $data_stock_sellbacks = DB::table('stock_sell_backs')
            ->select(
                'stock_sell_backs.stocksellback_sys_id', 'stock_sell_backs.created_at', 'users.user_surname', 'users.user_firstname', 'users.user_phone_number', 'users.user_email',  
                'businesses.business_full_name',  'businesses.business_find_code', 'countries.country_nice_name',
                'stock_sell_backs.stocksellback_stocks_quantity',  'stock_sell_backs.stocksellback_buyback_offer_per_stock_usd', 
                'stock_sell_backs.stocksellback_buyback_offer_per_stock_usd',  'stock_sell_backs.stocksellback_payout_amt_local_currency_paid_in', 
                'stock_sell_backs.stocksellback_receiving_bank_or_momo_account_name','stock_sell_backs.stocksellback_receiving_bank_or_momo_account_name', 
                'stock_sell_backs.stocksellback_receiving_bank_or_momo_account_number', 'stock_sell_backs.stocksellback_receiving_bank_or_momo_name', 
                'stock_sell_backs.stocksellback_receiving_bank_routing_number', 'stock_sell_backs.stocksellback_rate_dollar_to_local_with_no_signs', 
                'stock_sell_backs.stocksellback_processing_fee_usd', 'stock_sell_backs.stocksellback_flagged', 'stock_sell_backs.stocksellback_flagged_reason',
                'stock_sell_backs.stocksellback_processed', 'stock_sell_backs.stocksellback_processed_reason', 'currencies.currency_symbol')
            ->join('users', 'users.investor_id', '=', 'stock_sell_backs.stocksellback_seller_investor_id')
            ->join('currencies', 'currencies.currency_id', '=', 'stock_sell_backs.stocksellback_local_currency_paid_in_id')
            ->join('businesses', 'businesses.business_sys_id', '=', 'stock_sell_backs.stocksellback_business_id')
            ->join('countries', 'businesses.business_country_id', '=', 'countries.country_id')
            ->where('stocksellback_processed', '=', 0)
            ->take(100)
            ->get();
        } else {              
            $data_stock_sellbacks = DB::table('stock_sell_backs')
            ->select(
                'stock_sell_backs.stocksellback_sys_id', 'stock_sell_backs.created_at', 'users.user_surname', 'users.user_firstname', 'users.user_phone_number', 'users.user_email',  
                'businesses.business_full_name',  'businesses.business_find_code', 'countries.country_nice_name',
                'stock_sell_backs.stocksellback_stocks_quantity',  'stock_sell_backs.stocksellback_buyback_offer_per_stock_usd', 
                'stock_sell_backs.stocksellback_buyback_offer_per_stock_usd',  'stock_sell_backs.stocksellback_payout_amt_local_currency_paid_in', 
                'stock_sell_backs.stocksellback_receiving_bank_or_momo_account_name','stock_sell_backs.stocksellback_receiving_bank_or_momo_account_name', 
                'stock_sell_backs.stocksellback_receiving_bank_or_momo_account_number', 'stock_sell_backs.stocksellback_receiving_bank_or_momo_name', 
                'stock_sell_backs.stocksellback_receiving_bank_routing_number', 'stock_sell_backs.stocksellback_rate_dollar_to_local_with_no_signs', 
                'stock_sell_backs.stocksellback_processing_fee_usd', 'stock_sell_backs.stocksellback_flagged', 'stock_sell_backs.stocksellback_flagged_reason',
                'stock_sell_backs.stocksellback_processed', 'stock_sell_backs.stocksellback_processed_reason', 'currencies.currency_symbol')
            ->join('currencies', 'currencies.currency_id', '=', 'stock_sell_backs.stocksellback_local_currency_paid_in_id')
            ->join('users', 'users.investor_id', '=', 'stock_sell_backs.stocksellback_seller_investor_id')
            ->join('businesses', 'businesses.business_sys_id', '=', 'stock_sell_backs.stocksellback_business_id')
            ->join('countries', 'businesses.business_country_id', '=', 'countries.country_id')
            ->where('user_phone_number', 'LIKE', "%{$request->keyword}%")
            //->where('stocksellback_processing_fee_usd', '=', 0)
            ->orderBy('stocksellback_id', 'desc')
            ->take(100)
            ->take(100)
            ->get();
        }

        $all_data = array();

        // FORMATTING TRANSACTION
        foreach($data_stock_purchases as $stockpurchase){
            $this_transaction = Transaction::where('transaction_referenced_item_id', $stockpurchase->stockpurchase_sys_id)->first();
            if($this_transaction == null){
                continue;
            } 
            $this_output = [
                "transaction_type" => "BUY",
                "transaction_id" => $this_transaction->transaction_id,
                "transaction_sys_id" => $this_transaction->transaction_sys_id,
                "transaction_ref_id" => $stockpurchase->stockpurchase_sys_id,
                "user_fullname" => $stockpurchase->user_surname . " " . $stockpurchase->user_firstname,
                "user_phone" => $stockpurchase->user_phone_number,
                "user_email" => $stockpurchase->user_email,
                "stock_name" => $stockpurchase->business_full_name,
                "stock_business_fincode" => $stockpurchase->business_find_code,
                "stock_price_usd_or_receiver_pottname_or_buyback_offer" => "$" . $stockpurchase->stockpurchase_price_per_stock_usd,
                "stocks_quantity" => $stockpurchase->stockpurchase_stocks_quantity,
                "risk_insurance" => $stockpurchase->risk_type_shortname,
                "risk_insurance_fee" => "$" . $stockpurchase->stockpurchase_risk_insurance_fee_usd,
                "total_fees_usd" => $stockpurchase->stockpurchase_total_price_with_all_fees_usd,
                "rate_usd_to_local" => $stockpurchase->stockpurchase_rate_of_dollar_to_currency_paid_in,
                "processing_status" => $stockpurchase->stockpurchase_processed,
                "flagged_status" => $stockpurchase->stockpurchase_flagged,
                "payment_status" => $stockpurchase->stockpurchase_payment_gateway_status,
                "payment_status_text" => $stockpurchase->stockpurchase_payment_gateway_info,
                "networkname" => "NA",
                "routing_no" => "NA",
                "account_name" => "NA",
                "account_no" => "NA",
                "total_fee_local_or_total_payout_local" => $stockpurchase->currency_symbol . $stockpurchase->stockpurchase_total_all_fees_in_currency_paid_in,
                "created_at" => $stockpurchase->created_at
            ];
            array_push($all_data, $this_output);
        }

        foreach($data_stocks_transfers as $stocktransfer){
            $this_transaction = Transaction::where('transaction_referenced_item_id', $stocktransfer->stocktransfer_sys_id)->first();
            if($this_transaction == null){
                continue;
            } 
            $this_output = [
                "transaction_type" => "TRANSFER",
                "transaction_id" => $this_transaction->transaction_id,
                "transaction_sys_id" => $this_transaction->transaction_sys_id,
                "transaction_ref_id" => $stocktransfer->stocktransfer_sys_id,
                "user_fullname" => $stocktransfer->user_surname . " " . $stocktransfer->user_firstname,
                "user_phone" => $stocktransfer->user_phone_number,
                "user_email" => $stocktransfer->user_email,
                "stock_name" => $stocktransfer->business_full_name,
                "stock_business_fincode" => $stocktransfer->business_find_code,
                "stock_price_usd_or_receiver_pottname_or_buyback_offer" => $stocktransfer->stocktransfer_receiver_pottname,
                "stocks_quantity" => $stocktransfer->stocktransfer_stocks_quantity,
                "risk_insurance" => "NA",
                "risk_insurance_fee" => "NA",
                "total_fees_usd" => "$" . config('app.transfer_processing_fee_usd'),
                "rate_usd_to_local" => config('app.to_cedi'),
                "processing_status" => $stocktransfer->stockstransfers_processed,
                "flagged_status" => $stocktransfer->stocktransfer_flagged,
                "payment_status" => $stocktransfer->stocktransfer_payment_gateway_status,
                "payment_status_text" => $stocktransfer->stocktransfer_payment_gateway_info,
                "networkname" => "NA",
                "routing_no" => "NA",
                "account_name" => "NA",
                "account_no" => "NA",
                "total_fee_local_or_total_payout_local" => "NA",
                "created_at" => $stocktransfer->created_at
            ];
            array_push($all_data, $this_output);
        }
        

        foreach($data_stock_sellbacks as $stocksellback){
            $this_transaction = Transaction::where('transaction_referenced_item_id', $stocksellback->stocksellback_sys_id)->first();
            if($this_transaction == null){
                continue;
            } 
            $this_output = [
                "transaction_type" => "SELLBACK",
                "transaction_id" => $this_transaction->transaction_id,
                "transaction_sys_id" => $this_transaction->transaction_sys_id,
                "transaction_ref_id" => $stocksellback->stocksellback_sys_id,
                "user_fullname" => $stocksellback->user_surname . " " . $stocksellback->user_firstname,
                "user_phone" => $stocksellback->user_phone_number,
                "user_email" => $stocksellback->user_email,
                "stock_name" => $stocksellback->business_full_name,
                "stock_business_fincode" => $stocksellback->business_find_code,
                "stock_price_usd_or_receiver_pottname_or_buyback_offer" => "$" . $stocksellback->stocksellback_buyback_offer_per_stock_usd,
                "stocks_quantity" => $stocksellback->stocksellback_stocks_quantity,
                "risk_insurance" => "$" . $stocksellback->stocksellback_buyback_offer_per_stock_usd,
                "risk_insurance_fee" => "NA",
                "total_fees_usd" => "$" . config('app.transfer_processing_fee_usd'),
                "rate_usd_to_local" => $stocksellback->stocksellback_rate_dollar_to_local_with_no_signs,
                "processing_status" => $stocksellback->stocksellback_processed,
                "flagged_status" => $stocksellback->stocksellback_flagged,
                "payment_status" => "NA",
                "payment_status_text" => "NA",
                "networkname" => $stocksellback->stocksellback_receiving_bank_or_momo_name,
                "routing_no" => $stocksellback->stocksellback_receiving_bank_routing_number,
                "account_name" => $stocksellback->stocksellback_receiving_bank_or_momo_account_name,
                "account_no" => $stocksellback->stocksellback_receiving_bank_or_momo_account_number,
                "total_fee_local_or_total_payout_local" => $stocksellback->currency_symbol . $stocksellback->stocksellback_payout_amt_local_currency_paid_in,
                "created_at" => $stocksellback->created_at
            ];
            array_push($all_data, $this_output);
        }

        usort($all_data, function($a, $b) {
            return $a['transaction_id'] <=> $b['transaction_id'];
        });

        return response([
            "status" => 1, 
            "message" => "success",
            "data" => $all_data
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
            "frontend_key" => "bail|required",
            "administrator_pin" => "bail|required",
            // ADD ANY OTHER REQUIRED INPUTS FROM HERE
            "item_id" => "bail|required",
            "item_type" => "bail|required|integer",
            "user_pottname" => "nullable|string",
            "suggestion_reason" => "nullable|string",
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
        
        if($request->item_type == 1){
            // CHECKING IF THE DRILL EXISTS
            $drill = Drill::where('drill_sys_id', $request->item_id)->first();
            if($drill == null){
                return response([
                    "status" => 0, 
                    "message" => "Drill not found"
                ]);
            }
            // CREATING THE SUGGESTION VALUE DATA FOR DRILL
            $suggestionData["suggestion_sys_id"] = "sug-" . $drill->drill_sys_id . date('YmdHis');
            $suggestionData["suggestion_item_reference_id"] = $drill->drill_sys_id;
            $suggestionData["suggestion_directed_at_user_investor_id"] = "";
            $suggestionData["suggestion_directed_at_user_business_find_code"] = "";
            $suggestionData["suggestion_suggestion_type_id"] = $request->item_type;
            $message = "Suggestion saved.";

            // SENDING NOTIFICATION TO USERS
            UtilController::sendNotificationToTopic(
                config('app.firebase_notification_server_address_link'), 
                config('app.firebase_notification_account_key'), 
                "FISHPOT_TIPS",
                "normal",
                "drill-suggestion",
                "New Drill - FishPott",
                "Train your FishPott and increase its intelligence with a new drill",
                "", 
                "", 
                "", 
                "", 
                "",
                date("F j, Y")
            );
        } else if($request->item_type == 2){
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
            $suggestionData["suggestion_sys_id"] = "sug-" . $business->business_sys_id . date('YmdHis');
            $suggestionData["suggestion_item_reference_id"] = $business->business_sys_id;
            $suggestionData["suggestion_directed_at_user_investor_id"] = $pott_user->investor_id;
            $suggestionData["suggestion_directed_at_user_business_find_code"] = $pott_user->user_pottname . date('YmdHis');
            $suggestionData["suggestion_suggestion_type_id"] = $request->item_type;
            $suggestionData["suggestion_reason"] = $request->suggestion_reason;
            $message = "Suggestion saved. Find code is : " . $suggestionData["suggestion_directed_at_user_business_find_code"];

            // SENDING NOTIFICATION TO THE USER
            UtilController::sendNotificationToUser(
                config('app.firebase_notification_server_address_link'), 
                config('app.firebase_notification_account_key'), 
                array($pott_user->user_fcm_token_android, $pott_user->user_fcm_token_web, $pott_user->user_fcm_token_ios),
                "normal",
                "business-suggestion",
                "Stock Suggestion - FishPott",
                "You have a new business you can invest in",
                "", 
                "", 
                "", 
                "", 
                "",
                date("F j, Y")
            );
        } else {
            return response([
                "status" => 0, 
                "message" => "Item type not found"
            ]);
        }


        // CREATING THE SUGGESTION VALUE DATA FOR BUSINESS
        $suggestionData["suggestion_passed_on_by_user"] = false;
        $suggestionData["suggestion_notification_sent"] = true;
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
            "frontend_key" => "bail|required",
            "administrator_pin" => "bail|required",
            // ADD ANY OTHER REQUIRED INPUTS FROM HERE
            "item_id" => "bail|required",
            "new_value" => "bail|required|numeric",
            "new_change" => "bail|required|numeric",
            "new_volume" => "bail|required|numeric",
        ]);

        // MAKING SURE THE REQUEST AND USER IS VALIDATED
        $validation_response = UtilController::validateAdminWithAuthToken($request, auth()->guard('administrator-api')->user(), "work-ai");
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
        $business = Business::where('business_sys_id', $request->item_id)->first();
        if($business == null){
            return response([
                "status" => "error", 
                "message" => "Business not found"
            ]);
        }

        //CREATING THE STOCK VALUE DATA
        $stockValueData["stockvalue_business_id"] = $request->item_id;
        $stockValueData["stockvalue_value_per_stock_usd"] = floatval($request->new_value);
        $stockValueData["stockvalue_value_change"] = floatval($request->new_change);
        $stockValueData["stockvalue_volume"] = intval($request->new_volume);
        $stockValueData["stockvalue_admin_adder_id"] = $admin->administrator_sys_id;
        StockValue::create($stockValueData);

        // CALCULATING STOCK PERSONA WITH NEW VALUE
        $stocktraindata = StockValue::select('stockvalue_value_change')
        ->where('stockvalue_business_id', "=" , $request->item_id)
        ->take(7)
        ->orderBy('stockvalue_id', 'desc')->get();

        $raw_train_input_data = "";
        foreach($stocktraindata as $key => $thisstocktraindata){
            if($key == 0){
                $add_input =  "" ;
            } else {
                $add_input = " # " ;
            }
            $raw_train_input_data = $raw_train_input_data   . $add_input .  $thisstocktraindata->stockvalue_value_change;
        }
        //echo "\n\nraw_train_input_data - " . $raw_train_input_data; 
        //exit;


        $openness_to_experience = UtilController::testNeuralNetworkToGetStockOpennessToExperience($raw_train_input_data, true, config("app.openness_to_experience"));
        $conscientiousness = UtilController::testNeuralNetworkToGetStockOpennessToExperience($raw_train_input_data, true, config("app.conscientiousness"));
        $extraversion = UtilController::testNeuralNetworkToGetStockOpennessToExperience($raw_train_input_data, true, config("app.extraversion"));
        $agreeableness = UtilController::testNeuralNetworkToGetStockOpennessToExperience($raw_train_input_data, true, config("app.agreeableness"));
        $neuroticism = UtilController::testNeuralNetworkToGetStockOpennessToExperience($raw_train_input_data, true, config("app.neuroticism"));

        /*
        echo "\n\openness_to_experience - " . $openness_to_experience; 
        echo "\n\conscientiousness - " . $conscientiousness; 
        echo "\n\extraversion - " . $extraversion; 
        echo "\n\agreeableness - " . $agreeableness; 
        echo "\n\neuroticism - " . $neuroticism; 
        exit;
        */

        // CREATING THE STOCK PERSONA
        if($openness_to_experience != null){
            $AiStockPersonaData["aistockpersona_openness_to_experience"] = $openness_to_experience;
            $AiStockPersonaData["aistockpersona_conscientiousness"] = floatval($conscientiousness);
            $AiStockPersonaData["aistockpersona_extraversion"] = floatval($extraversion);
            $AiStockPersonaData["aistockpersona_agreeableness"] = floatval($agreeableness);
            $AiStockPersonaData["aistockpersona_neuroticism"] = floatval($neuroticism);
            $AiStockPersonaData["aistockpersona_stock_business_id"] = $request->item_id;
            AiStockPersona::create($AiStockPersonaData);
        }
        
        $new_persona = "Openness_to_experience - " . round($openness_to_experience, 2)
        . "<br>conscientiousness - " . round($conscientiousness, 2)
        . "<br>extraversion - " . round($extraversion, 2)
        . "<br>agreeableness - " . round($agreeableness, 2)
        . "<br>neuroticism - " . round($neuroticism, 2); 

        return response([
            "status" => 1, 
            "message" => "New stock value saved. $new_persona"
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    | THIS FUNCTION ADDS A DRILL
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    */
    
    public function addTrainDataStockValuesAndOutput(Request $request)
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
            "frontend_key" => "bail|required",
            "administrator_pin" => "bail|required",
            // ADD ANY OTHER REQUIRED INPUTS FROM HERE
            "value_per_stock_usd_seven_inputs" => "bail|required",
            "value_change_seven_inputs" => "bail|required",
            "volume_seven_inputs" => "bail|required",
            "expected_output_o" => "bail|required|numeric",
            "expected_output_c" => "bail|required|numeric",
            "expected_output_e" => "bail|required|numeric",
            "expected_output_a" => "bail|required|numeric",
            "expected_output_n" => "bail|required|numeric",
        ]);

        // MAKING SURE THE REQUEST AND USER IS VALIDATED
        $validation_response = UtilController::validateAdminWithAuthToken($request, auth()->guard('administrator-api')->user(), "work-ai");
        if(!empty($validation_response["status"])){
            return response($validation_response);
        } else {
            $admin = $validation_response;
        }
        /*
        |**************************************************************************
        | VALIDATION ENDED 
        |**************************************************************************
        */

        if(count(explode("#", $request->value_per_stock_usd_seven_inputs)) != 7){
            return response([
                "status" => 0, 
                "message" => "The stock values must be a string of 7 values separates by #"
            ]);
        }

        if(count(explode("#", $request->value_change_seven_inputs)) != 7){
            return response([
                "status" => 0, 
                "message" => "The stock CHANGE values must be a string of 7 values separates by #"
            ]);
        }

        if(count(explode("#", $request->volume_seven_inputs)) != 7){
            return response([
                "status" => 0, 
                "message" => "The stock VOLUME values must be a string of 7 values separates by #"
            ]);
        }

        //CREATING THE STOCK VALUE DATA
        $StockTrainData["stocktraindata_value_per_stock_usd_seven_inputs"] = $request->value_per_stock_usd_seven_inputs;
        $StockTrainData["stocktraindata_value_change_seven_inputs"] = $request->value_change_seven_inputs;
        $StockTrainData["stocktraindata_volume_seven_inputs"] = $request->volume_seven_inputs;
        $StockTrainData["stocktraindata_expected_output_o"] = $request->expected_output_o;
        $StockTrainData["stocktraindata_expected_output_c"] = $request->expected_output_c;
        $StockTrainData["stocktraindata_expected_output_e"] = $request->expected_output_e;
        $StockTrainData["stocktraindata_expected_output_a"] = $request->expected_output_a;
        $StockTrainData["stocktraindata_expected_output_n"] = $request->expected_output_n;
        $StockTrainData["stocktraindata_admin_adder_id"] = $admin->administrator_sys_id;
        StockTrainData::create($StockTrainData);

        //|--------------------------------------------------------------------------
        //| TRAINING NEURAL NETWORK FOR -- O ---
        //|--------------------------------------------------------------------------
    
        // GETTING THE DATA
        $stocktraindata = StockTrainData::select('stocktraindata_value_change_seven_inputs', 'stocktraindata_expected_output_o')
        ->orderBy('stocktraindata_id', 'desc')->get();

        $raw_train_input_data = "";
        $raw_train_output_data = "";

        foreach($stocktraindata as $key => $thisstocktraindata){
            if($key == 0){
                $add_input =  "" ;
                $add_output = "" ;
            } else {
                $add_input =  " | " ;
                $add_output = " # " ;
            }
            $raw_train_input_data = $raw_train_input_data   . $add_input .  $thisstocktraindata->stocktraindata_value_change_seven_inputs;
            $raw_train_output_data = $raw_train_output_data . $add_output . $thisstocktraindata->stocktraindata_expected_output_o;
        }
        echo "\n\nraw_train_input_data - " . $raw_train_input_data;
        echo "\n\nraw_train_output_data - " . $raw_train_output_data;
        
        // TRAINING
        UtilController::trainNeuralNetwork($raw_train_input_data, $raw_train_output_data, config('app.openness_to_experience'));
        
        //|--------------------------------------------------------------------------
        //| TRAINING NEURAL NETWORK FOR -- C ---
        //|--------------------------------------------------------------------------
    
        // GETTING THE DATA
        $stocktraindata = StockTrainData::select('stocktraindata_value_change_seven_inputs', 'stocktraindata_expected_output_o')
        ->orderBy('stocktraindata_id', 'desc')->get();

        $raw_train_input_data = "";
        $raw_train_output_data = "";

        foreach($stocktraindata as $key => $thisstocktraindata){
            if($key == 0){
                $add_input =  "" ;
                $add_output = "" ;
            } else {
                $add_input =  " | " ;
                $add_output = " # " ;
            }
            $raw_train_input_data = $raw_train_input_data   . $add_input .  $thisstocktraindata->stocktraindata_value_change_seven_inputs;
            $raw_train_output_data = $raw_train_output_data . $add_output . $thisstocktraindata->stocktraindata_expected_output_o;
        }
        echo "\n\nraw_train_input_data - " . $raw_train_input_data;
        echo "\n\nraw_train_output_data - " . $raw_train_output_data;
        
        // TRAINING
        UtilController::trainNeuralNetwork($raw_train_input_data, $raw_train_output_data, config('app.conscientiousness'));

        //|--------------------------------------------------------------------------
        //| TRAINING NEURAL NETWORK FOR -- E ---
        //|--------------------------------------------------------------------------
    
        // GETTING THE DATA
        $stocktraindata = StockTrainData::select('stocktraindata_value_change_seven_inputs', 'stocktraindata_expected_output_o')
        ->orderBy('stocktraindata_id', 'desc')->get();

        $raw_train_input_data = "";
        $raw_train_output_data = "";

        foreach($stocktraindata as $key => $thisstocktraindata){
            if($key == 0){
                $add_input =  "" ;
                $add_output = "" ;
            } else {
                $add_input =  " | " ;
                $add_output = " # " ;
            }
            $raw_train_input_data = $raw_train_input_data   . $add_input .  $thisstocktraindata->stocktraindata_value_change_seven_inputs;
            $raw_train_output_data = $raw_train_output_data . $add_output . $thisstocktraindata->stocktraindata_expected_output_o;
        }
        echo "\n\nraw_train_input_data - " . $raw_train_input_data;
        echo "\n\nraw_train_output_data - " . $raw_train_output_data;
        
        // TRAINING
        UtilController::trainNeuralNetwork($raw_train_input_data, $raw_train_output_data, config('app.extraversion'));

        //|--------------------------------------------------------------------------
        //| TRAINING NEURAL NETWORK FOR -- A ---
        //|--------------------------------------------------------------------------
    
        // GETTING THE DATA
        $stocktraindata = StockTrainData::select('stocktraindata_value_change_seven_inputs', 'stocktraindata_expected_output_o')
        ->orderBy('stocktraindata_id', 'desc')->get();

        $raw_train_input_data = "";
        $raw_train_output_data = "";

        foreach($stocktraindata as $key => $thisstocktraindata){
            if($key == 0){
                $add_input =  "" ;
                $add_output = "" ;
            } else {
                $add_input =  " | " ;
                $add_output = " # " ;
            }
            $raw_train_input_data = $raw_train_input_data   . $add_input .  $thisstocktraindata->stocktraindata_value_change_seven_inputs;
            $raw_train_output_data = $raw_train_output_data . $add_output . $thisstocktraindata->stocktraindata_expected_output_o;
        }
        echo "\n\nraw_train_input_data - " . $raw_train_input_data;
        echo "\n\nraw_train_output_data - " . $raw_train_output_data;
        
        // TRAINING
        UtilController::trainNeuralNetwork($raw_train_input_data, $raw_train_output_data, config('app.agreeableness'));

        //|--------------------------------------------------------------------------
        //| TRAINING NEURAL NETWORK FOR -- N ---
        //|--------------------------------------------------------------------------
    
        // GETTING THE DATA
        $stocktraindata = StockTrainData::select('stocktraindata_value_change_seven_inputs', 'stocktraindata_expected_output_o')
        ->orderBy('stocktraindata_id', 'desc')->get();

        $raw_train_input_data = "";
        $raw_train_output_data = "";

        foreach($stocktraindata as $key => $thisstocktraindata){
            if($key == 0){
                $add_input =  "" ;
                $add_output = "" ;
            } else {
                $add_input =  " | " ;
                $add_output = " # " ;
            }
            $raw_train_input_data = $raw_train_input_data   . $add_input .  $thisstocktraindata->stocktraindata_value_change_seven_inputs;
            $raw_train_output_data = $raw_train_output_data . $add_output . $thisstocktraindata->stocktraindata_expected_output_o;
        }
        echo "\n\nraw_train_input_data - " . $raw_train_input_data;
        echo "\n\nraw_train_output_data - " . $raw_train_output_data;
        
        // TRAINING
        UtilController::trainNeuralNetwork($raw_train_input_data, $raw_train_output_data, config('app.neuroticism'));

        return response([
            "status" => 1, 
            "message" => "Train data added and neural network trained"
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    | THIS FUNCTION ADDS A DRILL
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    */

    public function updateOrderProcessedOrFlaggedStatus(Request $request)
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
            "frontend_key" => "bail|required",
            // ADD ANY OTHER REQUIRED INPUTS FROM HERE
            "action_type" => "bail|required|integer",
            "order_id" => "bail|required|string",
            "action_info" => "nullable",
        ]);

        // MAKING SURE THE REQUEST AND USER IS VALIDATED
        $validation_response = UtilController::validateAdminWithAuthToken($request, auth()->guard('administrator-api')->user(), "add-admins");
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

        // GETTING THE TRANSACTION
        $this_transaction = Transaction::where('transaction_referenced_item_id', $request->order_id)->first();
        if($this_transaction == null || empty($this_transaction->transaction_referenced_item_id)){
            return response([
                "status" => 0, 
                "message" => "Transaction not found"
            ]);
        }

        if($this_transaction->transaction_transaction_type_id == 4){ // STOCK PURCHASE
            // GETTING THE ORDER
            $stockpurchase = StockPurchase::where('stockpurchase_sys_id', $request->order_id)->first();
            if($stockpurchase == null || empty($stockpurchase->stockpurchase_sys_id)){
                return response([
                    "status" => 0, 
                    "message" => "Stock purchase not found"
                ]);
            }

            if($request->action_type == "1"){ // PROCESSING
                if($stockpurchase->stockpurchase_payment_gateway_status != 1){
                    return response([
                        "status" => 0, 
                        "message" => "You cannot process an unpaid order"
                    ]);
                }
                if($stockpurchase->stockpurchase_flagged == 1){
                    return response([
                        "status" => 0, 
                        "message" => "You cannot process a flagged order"
                    ]);
                }
                if($stockpurchase->stockpurchase_processed != 0){
                    return response([
                        "status" => 0, 
                        "message" => "You cannot re-process an order"
                    ]);
                }
                $stockpurchase->stockpurchase_processed = 1;
                $stockpurchase->stockpurchase_processed_reason = $request->action_info;
            } else if($request->action_type == "2"){
                $stockpurchase->stockpurchase_flagged = 1;
                $stockpurchase->stockpurchase_flagged_reason = $request->action_info;
            } else if($request->action_type == "3"){
                if($stockpurchase->stockpurchase_payment_gateway_status != 1){
                    return response([
                        "status" => 0, 
                        "message" => "You cannot process an unpaid order"
                    ]);
                }
                if($stockpurchase->stockpurchase_flagged == 1){
                    return response([
                        "status" => 0, 
                        "message" => "You cannot process a flagged order"
                    ]);
                }
                if($stockpurchase->stockpurchase_processed != 0){
                    return response([
                        "status" => 0, 
                        "message" => "You cannot re-process an order"
                    ]);
                }
                $stockpurchase->stockpurchase_processed = 2;
                $stockpurchase->stockpurchase_processed_reason = $request->action_info;
            }
            // SAVING UPDATE
            $stockpurchase->save();

        } else if($this_transaction->transaction_transaction_type_id == 5){ // STOCK TRANSFER
            // GETTING THE ORDER
            //echo "\n this_transaction->transaction_referenced_item_id: " . $this_transaction->transaction_referenced_item_id;
            //$stockpurchase = StockPurchase::where('stockpurchase_sys_id', $request->order_id)->first();

            $stocktransfer = StockTransfer::where('stocktransfer_sys_id', $request->order_id)->first();
            //echo "\n stocktransfer->stocktransfer_sys_id: " . $stocktransfer->stocktransfer_sys_id;
            //var_dump($stocktransfer);
            if($stocktransfer == null || empty($stocktransfer->stocktransfer_sys_id)){
                return response([
                    "status" => 0, 
                    "message" => "Stock transfer not found"
                ]);
            }

            if($request->action_type == "1"){ // PROCESSING
                if($stocktransfer->stocktransfer_payment_gateway_status != 1){
                    return response([
                        "status" => 0, 
                        "message" => "You cannot process an unpaid order"
                    ]);
                }
                if($stocktransfer->stocktransfer_flagged == 1){
                    return response([
                        "status" => 0, 
                        "message" => "You cannot process a flagged order"
                    ]);
                }
                if($stocktransfer->stockstransfers_processed != 0){
                    return response([
                        "status" => 0, 
                        "message" => "You cannot re-process an order"
                    ]);
                }
                $stocktransfer->stockstransfers_processed = 1;
                $stocktransfer->stockstransfers_processed_reason = $request->action_info;
            } else if($request->action_type == "2"){
                $stocktransfer->stocktransfer_flagged = 1;
                $stocktransfer->stocktransfer_flagged_reason = $request->action_info;
            } else if($request->action_type == "3"){ 
                if($stocktransfer->stocktransfer_payment_gateway_status != 1){
                    return response([
                        "status" => 0, 
                        "message" => "You cannot process an unpaid order"
                    ]);
                }
                if($stocktransfer->stocktransfer_flagged == 1){
                    return response([
                        "status" => 0, 
                        "message" => "You cannot process a flagged order"
                    ]);
                }
                if($stocktransfer->stockstransfers_processed != 0){
                    return response([
                        "status" => 0, 
                        "message" => "You cannot re-process an order"
                    ]);
                }
                $stocktransfer->stockstransfers_processed = 2;
                $stocktransfer->stockstransfers_processed_reason = $request->action_info;
            }
            // SAVING UPDATE
            $stocktransfer->save();
        } else if($this_transaction->transaction_transaction_type_id == 6){ // STOCK SELLBACK

            // GETTING THE ORDER
            $stocksellback = StockSellBack::where('stocksellback_sys_id', $request->order_id)->first();
            if($stocksellback == null || empty($stocksellback->stocksellback_sys_id)){
                return response([
                    "status" => 0, 
                    "message" => "Stock sell back not found"
                ]);
            }

            if($request->action_type == "1"){ // PROCESSING
                if($stocksellback->stocksellback_flagged == 1){
                    return response([
                        "status" => 0, 
                        "message" => "You cannot process a flagged order"
                    ]);
                }
                if($stocksellback->stocksellback_processed != 0){
                    return response([
                        "status" => 0, 
                        "message" => "You cannot re-process an order"
                    ]);
                }
                $stocksellback->stocksellback_processed = 1;
                $stocksellback->stocksellback_processed_reason = $request->action_info;
            } else if($request->action_type == "2"){
                $stocksellback->stocksellback_flagged = 1;
                $stocksellback->stocksellback_flagged_reason = $request->action_info;
            } else if($request->action_type == "3"){
                if($stocksellback->stocksellback_flagged == 1){
                    return response([
                        "status" => 0, 
                        "message" => "You cannot process a flagged order"
                    ]);
                }
                if($stocksellback->stocksellback_processed != 0){
                    return response([
                        "status" => 0, 
                        "message" => "You cannot re-process an order"
                    ]);
                }
                $stocksellback->stocksellback_processed = 2;
                $stocksellback->stocksellback_processed_reason = $request->action_info;
            }
            // SAVING UPDATE
            $stocksellback->save();
        } else {
            return response([
                "status" => 0, 
                "message" => "Transaction type not found"
            ]);
        }



        return response([
            "status" => 1, 
            "message" => "Order updated"
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    | THIS FUNCTION SEARCHES FOR A USER LIST
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    */

    public function searchUsers(Request $request)
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
            "frontend_key" => "bail|required",
            // ADD ANY OTHER REQUIRED INPUTS FROM HERE
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
        // 
        //
        if(empty($request->keyword)){
            $data = DB::table('users')
            ->select(
                'users.user_id', 'users.user_surname', 'users.user_firstname', 'users.user_phone_number', 'users.user_email',  
                'users.user_net_worth_usd', 'users.user_pott_intelligence', 'users.user_pott_position', 'users.user_dob', 'users.user_pottname', 
                'countries.country_nice_name', 'users.last_online', 'users.user_phone_number', 'users.user_email',  'genders.gender_name',  
                'users.user_surname', 'users.user_firstname', 'users.user_phone_number', 'users.user_profile_picture', 'users.user_flagged'
            )
            ->join('countries', 'users.user_country_id', '=', 'countries.country_id')
            ->join('genders', 'users.user_gender_id', '=', 'genders.gender_id')
            ->take(100)
            ->get();
        } else {    
            $data = DB::table('users')
            ->select(
                'users.user_id', 'users.user_surname', 'users.user_firstname', 'users.user_phone_number', 'users.user_email',  
                'users.user_net_worth_usd', 'users.user_pott_intelligence', 'users.user_pott_position', 'users.user_dob',   'users.user_pottname',
                'countries.country_nice_name', 'users.last_online', 'users.user_phone_number', 'users.user_email', 'genders.gender_name', 
                'users.user_surname', 'users.user_firstname', 'users.user_phone_number', 'users.user_profile_picture', 'users.user_flagged'
            )
            ->join('countries', 'users.user_country_id', '=', 'countries.country_id')
            ->join('genders', 'users.user_gender_id', '=', 'genders.gender_id')
            ->where('user_phone_number', 'LIKE', "%{$request->keyword}%")
            ->take(100)
            ->get();
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
    | THIS FUNCTION UPDATES A USER
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    */
    public function updateUserFlaggedStatus(Request $request)
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
            "frontend_key" => "bail|required",
            // ADD ANY OTHER REQUIRED INPUTS FROM HERE
            "action_type" => "bail|required|integer",
            "user_id" => "bail|required|integer",
            "action_info" => "nullable",
        ]);

        // MAKING SURE THE REQUEST AND USER IS VALIDATED
        $validation_response = UtilController::validateAdminWithAuthToken($request, auth()->guard('administrator-api')->user(), "add-admins");
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

        // GETTING THE ORDER
        $user = User::where('user_id', $request->user_id)->first();
        if($user == null || empty($user->investor_id)){
            return response([
                "status" => 0, 
                "message" => "User not found"
            ]);
        }

        if($request->action_type == "1"){
            $user->user_flagged = 1;
            $user->user_flagged_reason = $request->action_info;
        } else if($request->action_type == "2"){
            $user->user_flagged = 0;
            $user->user_flagged_reason = "";
        }
        // SAVING UPDATE
        $user->save();

        return response([
            "status" => 1, 
            "message" => "User updated"
        ]);
    }

    public function trainingAi(Request $request)
    {
        $validatedData = $request->validate([
            "input" => "bail|required|string",
            "output" => "bail|required|string",
            "train_type" => "bail|required|string",
        ]);

        $response = UtilController::trainNeuralNetwork($request->input, $request->output, intval($request->train_type));

        return response([
            "status" => 1, 
            "message" => "Train done"
        ]);
        /*
        return response([
            "response" => $response
        ]);
        */
    }
    public function testingAi(Request $request)
    {
        $validatedData = $request->validate([
            "input" => "bail|required|string",
            "test_type" => "bail|required|string",
        ]);

        $response = UtilController::testNeuralNetworkToGetStockOpennessToExperience($request->input, true, intval($request->test_type));
        
        return response([
            "response" => $response . "%"
        ]);
        
    }

    public function getData(Request $request)
    {
        $validatedData = $request->validate([
            "input" => "bail|required|string",
            "test_type" => "bail|required|string",
        ]);

        $response = UtilController::matchUsersToABusinesses();
        
        //return response([
        //    "response" => $response
        //]);
        
    }
    /*
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    | THIS FUNCTION SENDS NOTIFICATIONS
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    */
    public function sendNotificationToUsers(Request $request)
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
            "frontend_key" => "bail|required",
            // ADD ANY OTHER REQUIRED INPUTS FROM HERE
            "notification_type" => "bail|required|integer",
            "title" => "bail|required|string",
            "full_message" => "bail|required|string",
            "user_pottname" => "nullable|string",
        ]);

        // MAKING SURE THE REQUEST AND USER IS VALIDATED
        $validation_response = UtilController::validateAdminWithAuthToken($request, auth()->guard('administrator-api')->user(), "add-admins");
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
        if($request->notification_type == "1"){ // ALL USERS
            // SENDING NOTIFICATION TO USERS
            UtilController::sendNotificationToTopic(
                config('app.firebase_notification_server_address_link'), 
                config('app.firebase_notification_account_key'), 
                "FISHPOT_TIPS",
                "normal",
                "information",
                "FishPott - Info",
                $request->title,
                $request->full_message,
                "", 
                "", 
                "", 
                "",
                date("F j, Y")
            );

        } else if($request->notification_type == "2"){ // SINGLE USER
            $user = User::where('user_pottname', $request->user_pottname)->first();
            if($user == null || empty($user->investor_id)){
                return response([
                    "status" => 0, 
                    "message" => "User not found"
                ]);
            }
            UtilController::sendNotificationToUser(
                config('app.firebase_notification_server_address_link'), 
                config('app.firebase_notification_account_key'), 
                array($user->user_fcm_token_android, $user->user_fcm_token_web, $user->user_fcm_token_ios),
                "normal",
                "information",
                "FishPott - Info",
                $request->title,
                $request->full_message,
                "", 
                "", 
                "", 
                "",
                date("F j, Y")
            );

            $title = $request->title;
            $message =  $request->full_message;
            $email_data = array(
                'title' => $title,
                'message' => $message,
                'time' => date("F j, Y, g:i a")
            );
            Mail::to($validatedData["user_email"])->send(new UserAlertMail($email_data));
        }

        return response([
            "status" => 1, 
            "message" => "Notification sent"
        ]);
    }

}
