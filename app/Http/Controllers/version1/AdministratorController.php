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
    | THIS FUNCTION REGISTES THE ADMIN AND PROVIDES THEM WITH AN ACCESS TOKEN
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    |
    */
    public function registerFirstAdmin(Request $request)
    {

        $validatedData = $request->validate([
            "administrator_sys_id" => "bail|required|max:55",
            "administrator_user_pottname" => "bail|required|max:55",
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

        $accessToken = $administrator->createToken("authToken", [$validatedData["admin_scope"]])->accessToken;

        return response([
            "administrator" => $administrator, 
            "administrator_user_pottname" => $request->administrator_user_pottname, 
            "access_token" => $accessToken
        ]);
    }
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
    | THIS FUNCTION REGISTES A USER AND PROVIDES THEM WITH AN ACCESS TOKEN
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    */
    public function addAdministrator(Request $request)
    {

        // MAKING SURE THE INPUT HAS THE EXPECTED VALUES
        /*
        |**************************************************************************
        | VALIDATION STARTS 
        |**************************************************************************
        */
        // MAKING SURE THE INPUT HAS THE EXPECTED VALUES
        $validatedData = $request->validate([
            "admin_pottname" => "bail|required|string|regex:/^[A-Za-z0-9_.]+$/|max:15",
            "admin_pin" => "bail|required|min:4|max:8",
            "new_admin_pottname" => "bail|required|string|regex:/^[A-Za-z0-9_.]+$/|max:15",
            "new_admin_phone_number" => "bail|required|regex:/^\+\d{10,15}$/|min:10|max:15",
            "new_admin_password" => "bail|required|min:8|max:30",
            "new_admin_pin" => "bail|required|confirmed|min:4|max:8",
            "new_admin_scope" => "bail|required",
            "frontend_key" => "bail|required|in:2aLW4c7r9(2qf#y"
        ]);

        // MAKING SURE THE REQUEST AND USER IS VALIDATED
        //$validation_response = $this->validateAdminWithAuthToken($request, auth()->user(), );
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

        // MA
        $user = User::where('user_pottname', $request->user_pottname)->where('user_phone_number', $request->user_phone_number)->where('investor_id', $request->investor_id)->first();
        if($user == null){
            return [
                "status" => "error", 
                "message" => "Session closed. You have to login again."
            ]; exit;
        }

        // CHECKING POTTNAME AVAILABILITY
        if(!$this->pottnameIsAvailable($validatedData["user_pottname"])){
            return response([
                "status" => "error", 
                "message" => "Registration failed. The pott name is already taken"
            ]);
        } 

        // PHONE NUMBER IS TAKEN
        if(!$this->phoneNumberIsAvailable($validatedData["user_phone_number"])){
            return response([
                "status" => "error", 
                "message" => "Registration failed. The phone number is already taken"
            ]);
        } 

        // EMAIL IS TAKEN
        if(!$this->emailIsAvailable($validatedData["user_email"])){
            return response([
                "status" => "error", 
                "message" => "Registration failed. The email address is already taken"
            ]);
        } 

        // CHECKING IF REFERRER USERNAME IS REAL
        if(empty($request->user_referred_by) || $this->pottnameIsAvailable($request->user_referred_by)){
            $validatedData["user_referred_by"] = "";
        } else {
            $validatedData["user_referred_by"] = $request->user_referred_by;
        }


        //GETTING COUNTRY ID
        $gender = Gender::where('gender_name', '=', $validatedData["user_gender"])->first();
        if($gender === null){
            return response([
                "status" => "error", 
                "message" => "Registration failed. Gender validation error."
            ]);
        }

        //GETTING COUNTRY ID
        $country = Country::where('country_real_name', '=', $validatedData["user_country"])->first();
        if($country === null){
            return response([
                "status" => "error", 
                "message" => "Registration failed. Country validation error."
            ]);
        }

        //GETTING LANGUAGE ID
        $language = Language::where('language_short_name', '=', $validatedData["user_language"])->first();
        if($language === null){
            return response([
                "status" => "error", 
                "message" => "Registration failed. Language validation error."
            ]);
        }

        $validatedData["admin_pin"] = Hash::make($request->admin_pin);
        $validatedData["password"] = bcrypt($request->password);
        $validatedData["admin_flagged"] = false;

        $administrator = Administrator::create($validatedData);


        //CREATING THE USER DATA TO ADD TO DB
        $userData["user_user_type_id"] = 1;
        $userData["investor_id"] = $validatedData["user_pottname"] . substr($validatedData["user_phone_number"] ,1,strlen($validatedData["user_phone_number"])) . $this->getRandomString(91);
        $userData["user_surname"] = $validatedData["user_surname"];
        $userData["user_firstname"] = $validatedData["user_firstname"];
        $userData["user_pottname"] = $validatedData["user_pottname"];
        $userData["user_dob"] = $validatedData["user_dob"];
        $userData["user_phone_number"] = $validatedData["user_phone_number"];
        $userData["user_email"] = $validatedData["user_email"];
        $userData["user_profile_picture"] = "";
        $userData["password"] = bcrypt($request->password);
        $userData["user_gender_id"] = $gender->gender_id;
        $userData["user_country_id"] = $country->country_id;
        $userData["user_language_id"] = $language->language_id;
        $userData["user_currency_id"] = 1; //USD
        $userData["user_net_worth"] = 0;
        $userData["user_verified_tag"] = 0;
        $userData["user_shield_date"] = date("Y-m-d H:i:s");
        $userData["user_referred_by"] = $validatedData["user_referred_by"];
        $userData["user_pott_ruler"] = $validatedData["user_referred_by"];
        $userData["user_fcm_token_android"] = "";
        $userData["user_fcm_token_web"] = "";
        $userData["user_fcm_token_ios"] = "";
        $userData["user_added_to_sitemap"] = false;
        $userData["user_reviewed_by_admin"] = false;
        $userData["user_initial_signup_approved"] = true;
        $userData["user_flagged"] = false;
        // SAVING APP TYPE VERSION CODE
        if($request->app_type == "ANDROID"){
            $userData["user_android_app_version_code"] = $validatedData["app_version_code"];
        } else if($request->app_type == "IOS"){
            $userData["user_ios_app_version_code"] = $validatedData["app_version_code"];
        } 
        $userData["user_scope"] = "view-info get-stock-suggestions answer-questions buy-stock-suggested trade-stocks";
        $userData["user_phone_verification_requested"] = boolval(config('app.phoneverificationrequiredstatus'));
        $userData["user_id_verification_requested"] = boolval(config('app.idverificationrequiredstatus'));

        //$userData["ssssssss"] = $validatedData["user_surname"];

        $user1 = User::create($userData);
        
        // GENERATING THE ACCESS TOKEN FOR THE REGISTERED USER
        $accessToken = $user1->createToken("authToken", ["view-info get-stock-suggestions answer-questions buy-stock-suggested trade-stocks"])->accessToken;


        return response([
            "status" => "yes", 
            "message" => "",
            "user_phone" => $user1->user_phone_number,
            "user_id" => $user1->investor_id,
            "access_token" => $accessToken,
            "user_pott_name" => $user1->user_pottname,
            "user_full_name" => $user1->user_firstname . " " . $user1->user_surname,
            "user_profile_picture" => "",
            "user_country" => $validatedData["user_country"],
            "user_verified_status" => 0,
            "user_type" => "Investor",
            "user_gender" => $validatedData["user_gender"],
            "user_date_of_birth" => $user1->user_dob,
            "user_currency" => "USD",
            "media_allowed" => intval(config('app.canpostpicsandvids')),
            "user_android_app_max_vc" => intval(config('app.androidmaxvc')),
            "user_android_app_force_update" => boolval(config('app.androidforceupdatetomaxvc')),
            "phone_verification_is_on" => boolval(config('app.phoneverificationrequiredstatus')),
            "mtn_momo_number" => config('app.mtnghanamomonum'), // MTN-GHANA MOBILE MONEY NUMBER
            "mtn_momo_acc_name" => config('app.mtnghanamomoaccname'), // MTN-GHANA ACCOUNT NAME  ON MOBILE MONEY
            "vodafone_momo_number" => config('app.vodafoneghanamomonum'), // VODAFONE-GHANA MOBILE MONEY NUMBER
            "vodafone_momo_acc_name" => config('app.vodafoneghanamomoaccname'), // VODAFONE-GHANA ACCOUNT NAME ON MOBILE MONEY
            "airteltigo_momo_number" => config('app.airteltigoghanamomonum'), // AIRTELTIGO-GHANA MOBILE MONEY NUMBER
            "airteltigo_momo_acc_name" => config('app.airteltigoghanamomoaccname') // AIRTELTIGO-GHANA ACCOUNT NAME ON MOBILE MONEY
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
            "user_phone_number" => "bail|required|regex:/^\+\d{10,15}$/|min:10|max:15",
            "password" => "bail|required",
            "user_language" => "bail|required|max:3",
            "app_type" => "bail|required|max:8",
            "app_version_code" => "bail|required|integer"
        ]);

        $loginData["user_phone_number"] = $validatedData["user_phone_number"];
        $loginData["password"] = $validatedData["password"];

        // MAKING SURE VERSION CODE IS ALLOWED
        if(
            $request->app_type == "ANDROID" && 
            ($request->app_version_code < intval(config('app.androidminvc')) || $request->app_version_code > intval(config('app.androidmaxvc')))
        ){
            return response([
                "status" => "error", 
                "message" => "Please update your app from the Google Play Store."
            ]);
        }

        // VALIDATING USER CREDENTIALS
        if (!auth()->attempt($loginData)) {
            return response([
                "status" => "error", 
                "message" => "Invalid Credentials"
            ]);
        }

        // CHECKING IF USER FLAGGED
        if (auth()->user()->user_flagged) {
            return response([
                "status" => "0", 
                "message" => "Account access restricted"
            ]);
        }
        
        // CHECKING USER
        $user = $this->getUserWithOneColumn("user_phone_number", auth()->user()->user_phone_number);
        if($user === null){
            return response([
                "status" => "error", 
                "message" => "Login failed"
            ]);
        } 

        //GETTING GENDER 
        $gender = Gender::where('gender_id', '=', $user->user_gender_id)->first();
        if($gender === null){
            return response([
                "status" => "error", 
                "message" => "Gender validation error."
            ]);
        }

        //GETTING COUNTRY 
        $country = Country::where('country_id', '=', $user->user_country_id)->first();
        if($country === null){
            return response([
                "status" => "error", 
                "message" => "Country validation error."
            ]);
        }

        //GETTING LANGUAGE 
        $language = Language::where('language_id', '=', $user->user_language_id)->first();
        if($language === null){
            return response([
                "status" => "error", 
                "message" => "Language validation error."
            ]);
        }

        // SAVING APP TYPE VERSION CODE
        if($request->app_type == "ANDROID"){
            $user->user_android_app_version_code = $validatedData["app_version_code"];
        } else if($request->app_type == "IOS"){
            $user->user_ios_app_version_code = $validatedData["app_version_code"];
        }

        // GENERATING USER ACCESS TOKEN
        $accessToken = auth()->user()->createToken("authToken", ["view-info get-stock-suggestions answer-questions buy-stock-suggested trade-stocks"])->accessToken;

        // CHECKING IF PROFILE PICTURE EXISTS
        $img_url = config('app.url') . '/uploads/images/' . $user->user_profile_picture;
        if(empty($user->user_profile_picture) || !file_exists(public_path() . '/uploads/images/' . $user->user_profile_picture)){
            $img_url = "";
        }

        // CHECKING ID VERIFICATION
        if(boolval(config('app.idverificationrequiredstatus'))){
            $user->user_id_verification_requested = $user->user_id_verification_requested;
        }

        $user->save();    

        return response([
            "status" => "yes", 
            "message" => "",
            "user_phone" => $user->user_phone_number,
            "user_id" => $user->investor_id,
            "access_token" => $accessToken,
            "user_pott_name" => $user->user_pottname,
            "user_full_name" => $user->user_firstname . " " . $user->user_surname,
            "user_profile_picture" => $img_url,
            "user_country" => $country->country_real_name,
            "user_verified_status" => 0,
            "user_type" => "Investor",
            "user_gender" => $gender->gender_name,
            "user_date_of_birth" => $user->user_dob,
            "user_currency" => "USD",
            "id_verification_is_on" => boolval($user->user_id_verification_requested),
            "media_allowed" => intval(config('app.canpostpicsandvids')),
            "user_android_app_max_vc" => intval(config('app.androidmaxvc')),
            "user_android_app_force_update" => boolval(config('app.androidforceupdatetomaxvc')),
            "phone_verification_is_on" => boolval(config('app.phoneverificationrequiredstatus')),
            "mtn_momo_number" => config('app.mtnghanamomonum'), // MTN-GHANA MOBILE MONEY NUMBER
            "mtn_momo_acc_name" => config('app.mtnghanamomoaccname'), // MTN-GHANA ACCOUNT NAME  ON MOBILE MONEY
            "vodafone_momo_number" => config('app.vodafoneghanamomonum'), // VODAFONE-GHANA MOBILE MONEY NUMBER
            "vodafone_momo_acc_name" => config('app.vodafoneghanamomoaccname'), // VODAFONE-GHANA ACCOUNT NAME ON MOBILE MONEY
            "airteltigo_momo_number" => config('app.airteltigoghanamomonum'), // AIRTELTIGO-GHANA MOBILE MONEY NUMBER
            "airteltigo_momo_acc_name" => config('app.airteltigoghanamomoaccname') // AIRTELTIGO-GHANA ACCOUNT NAME ON MOBILE MONEY
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
