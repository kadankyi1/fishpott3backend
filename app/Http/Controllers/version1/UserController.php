<?php

namespace App\Http\Controllers\version1;

use DB;
use DateTime;
use Yabacon\Paystack;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\version1\User;
use App\Models\version1\Gender;
use App\Models\version1\Country;
use App\Models\version1\Language;
use App\Models\version1\ResetCode;
use App\Mail\version1\ResetCodeMail;
use App\Mail\version1\UserAlertMail;
use App\Mail\version1\WithdrawalMail;
use App\Models\version1\Business;
use App\Models\version1\Currency;
use App\Models\version1\Drill;
use App\Models\version1\DrillAnswer;
use App\Models\version1\StockOwnership;
use App\Models\version1\StockPurchase;
use App\Models\version1\StockSellBack;
use App\Models\version1\StockTransfer;
use App\Models\version1\StockValue;
use App\Models\version1\Suggestion;
use App\Models\version1\SuggestionTypes;
use App\Models\version1\Suggesto;
use App\Models\version1\Withdrawal;
use App\Models\version1\Transaction;
use Illuminate\Support\Facades\File; 
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\version1\AlertMail;

ini_set('memory_limit','1024M');
ini_set("upload_max_filesize","100M");
ini_set("max_execution_time",60000); //--- 10 minutes
ini_set("post_max_size","135M");
ini_set("file_uploads","On");

class UserController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    | THIS FUNCTION REGISTES A USER AND PROVIDES THEM WITH AN ACCESS TOKEN
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    */
    public function registerPersonalAccount(Request $request)
    {

        // MAKING SURE THE INPUT HAS THE EXPECTED VALUES
        $validatedData = $request->validate([
            "user_firstname" => "bail|required|string|regex:/^[A-Za-z0-9_.]+$/|max:15",
            "user_surname" => "bail|required|string|regex:/^[A-Za-z0-9_.]+$/|max:15",
            "user_email" => "bail|required|email|min:4|max:50",
            "user_pottname" => "bail|required|string|regex:/^[A-Za-z0-9_.]+$/|max:15",
            "user_gender" => "bail|required|max:10",
            "user_language" => "bail|required|max:3",
            "user_country" => "bail|required|max:55",
            "user_dob" => "bail|required|date|before:-13 years",
            "user_phone_number" => "bail|required|regex:/^\+\d{10,15}$/|min:10|max:15",
            "password" => "bail|required|max:20",
            "app_type" => "bail|required|max:8",
            "app_version_code" => "bail|required|integer"
        ]);

        // MAKING SURE VERSION CODE IS ALLOWED
        if($request->app_type == "ANDROID" && 
        ($request->app_version_code < intval(config('app.androidminvc')) || $request->app_version_code > intval(config('app.androidmaxvc')))
        ){
            return response([
                "status" => "error", 
                "message" => "Please update your app from the Google Play Store."
            ]);
        }


        if($request->app_type == "IOS" && 
        ($request->app_version_code < intval(config('app.iosminvc')) || $request->app_version_code > intval(config('app.iosmaxvc')))
        ){
            return response([
            "status" => "error", 
            "message" => "Please update your app from the Apple App Store."
            ]);
        }

        // CHECKING POTTNAME AVAILABILITY
        if(!UtilController::pottnameIsAvailable($validatedData["user_pottname"])){
            return response([
                "status" => "error", 
                "message" => "Registration failed. The pott name is already taken"
            ]);
        } 

        // PHONE NUMBER IS TAKEN
        if(!UtilController::phoneNumberIsAvailable($validatedData["user_phone_number"])){
            return response([
                "status" => "error", 
                "message" => "Registration failed. The phone number is already taken"
            ]);
        } 

        // EMAIL IS TAKEN
        if(!UtilController::emailIsAvailable($validatedData["user_email"])){
            return response([
                "status" => "error", 
                "message" => "Registration failed. The email address is already taken"
            ]);
        } 

        // CHECKING IF REFERRER USERNAME IS REAL
        if(empty($request->user_referred_by) || UtilController::pottnameIsAvailable($request->user_referred_by)){
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

        //CREATING THE USER DATA TO ADD TO DB
        $userData["user_user_type_id"] = 1;
        $userData["investor_id"] = $validatedData["user_pottname"] . substr($validatedData["user_phone_number"] ,1,strlen($validatedData["user_phone_number"])) . UtilController::getRandomString(91);
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
        $userData["user_net_worth_usd"] = 0;
        $userData["user_pott_intelligence"] = 0;
        $userData["user_pott_position"] = 0;
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
        $userData["user_email_alerts_subscribed"] = true;
        $userData["user_flagged"] = false;
        // SAVING APP TYPE VERSION CODE
        if($request->app_type == "ANDROID"){
            $userData["user_android_app_version_code"] = $validatedData["app_version_code"];
        } else if($request->app_type == "IOS"){
            $userData["user_ios_app_version_code"] = $validatedData["app_version_code"];
        } 
        $userData["user_scope"] = "get-info-on-apps get-info-in-background get-business-suggestions answer-drills buy-business-stocks transfer-business-stocks withdraw-funds";
        $userData["user_phone_verification_requested"] = boolval(config('app.phoneverificationrequiredstatus'));
        $userData["user_id_verification_requested"] = boolval(config('app.idverificationrequiredstatus'));
        $userData["last_online"] = date("Y-m-d H:i:s");

        //$userData["ssssssss"] = $validatedData["user_surname"];

        $user1 = User::create($userData);
        
        // GENERATING THE ACCESS TOKEN FOR THE REGISTERED USER
        $accessToken = $user1->createToken("authToken", ["get-info-on-apps get-info-in-background get-business-suggestions answer-drills buy-business-stocks transfer-business-stocks withdraw-funds"])->accessToken;

        
        $title = "Welcome " . $validatedData["user_firstname"];
        $message =    "You have joined the FishPott - Private Investing Network and we are happy to have you. "
                    . "\nTo enjoy using our service, train your FishPott by answering the Drill questions so that you can be suggested business you'd like to invest in." 
                    . "\nYou can also signup to a monthly subscription to have our team and Ai work together to send you monthly business suggestions."
                    . "\nIf you need any assistance, please do not hesitate to contact us via " . config('app.fishpott_email_two') . " or "  . config('app.fishpott_phone');
        $email_data = array(
            'title' => $title,
            'message' => $message,
            'time' => date("F j, Y, g:i a")
        );
        
        if(config('app.myenv') == "live"){
            Mail::to($validatedData["user_email"])->send(new UserAlertMail($email_data));
        }

        return response([
            "status" => "yes", 
            "message" => "",
            "user_phone" => $user1->user_phone_number,
            "user_email" => $user1->user_email,
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
    public function registerBusinessAccount(Request $request)
    {
        // MAKING SURE THE INPUT HAS THE EXPECTED VALUES
        $validatedData = $request->validate([
            "user_firstname" => "bail|required|string|max:50",
            "user_surname" => "bail|string|min:0|max:50",
            "user_email" => "bail|required|email|min:4|max:50",
            "user_pottname" => "bail|required|string|regex:/^[A-Za-z0-9_.]+$/|max:15",
            "user_gender" => "bail|required|min:8|max:8",
            "user_language" => "bail|required|max:3",
            "user_country" => "bail|required|max:55",
            "user_dob" => "bail|required|date|before:today",
            "user_phone_number" => "bail|required|regex:/^\+\d{10,15}$/|min:10|max:15",
            "password" => "bail|required|max:20",
            "user_referred_by" => "bail|max:15",
            "app_type" => "bail|required|max:8",
            "app_version_code" => "bail|required|integer"
        ]);

        // MAKING SURE VERSION CODE IS ALLOWED
        if($request->app_type == "ANDROID" && 
            ($request->app_version_code < intval(config('app.androidminvc')) || $request->app_version_code > intval(config('app.androidmaxvc')))
        ){
            return response([
                "status" => "error", 
                "message" => "Please update your app from the Google Play Store."
            ]);
        }

        // CHECKING POTTNAME AVAILABILITY
        if(!UtilController::pottnameIsAvailable($validatedData["user_pottname"])){
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
        if(!UtilController::emailIsAvailable($validatedData["user_email"])){
            return response([
                "status" => "error", 
                "message" => "Registration failed. The email address is already taken"
            ]);
        } 

        // CHECKING IF REFERRER USERNAME IS REAL
        if(empty($request->user_referred_by) || UtilController::pottnameIsAvailable($request->user_referred_by)){
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

        //CREATING THE USER DATA TO ADD TO DB
        $userData["user_user_type_id"] = 2;
        $userData["investor_id"] = $validatedData["user_pottname"] . substr($validatedData["user_phone_number"] ,1,strlen($validatedData["user_phone_number"])) . UtilController::getRandomString(91);
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
        $userData["user_net_worth_usd"] = 0;
        $userData["user_pott_intelligence"] = 0;
        $userData["user_pott_position"] = 0;
        $userData["user_verified_tag"] = 0;
        $userData["user_shield_date"] = date("Y-m-d H:i:s");
        $userData["user_referred_by"] = $validatedData["user_referred_by"];
        $userData["user_pott_ruler"] = $validatedData["user_referred_by"];
        $userData["user_fcm_token_android"] = "";
        $userData["user_fcm_token_web"] = "";
        $userData["user_fcm_token_ios"] = "";
        $userData["user_added_to_sitemap"] = false;
        $userData["user_reviewed_by_admin"] = false;
        $userData["user_initial_signup_approved"] = false;
        $userData["user_flagged"] = false;
        // SAVING APP TYPE VERSION CODE
        if($request->app_type == "ANDROID"){
            $userData["user_android_app_version_code"] = $validatedData["app_version_code"];
        } else if($request->app_type == "IOS"){
            $userData["user_ios_app_version_code"] = $validatedData["app_version_code"];
        } 
        $userData["user_app_version_code"] = $validatedData["app_version_code"];
        $userData["user_scope"] = "get-info-on-apps get-info-in-background get-business-suggestions answer-drills buy-business-stocks transfer-business-stocks withdraw-funds";
        $userData["user_phone_verification_requested"] = boolval(config('app.phoneverificationrequiredstatus'));
        $userData["user_id_verification_requested"] = boolval(config('app.idverificationrequiredstatus'));
        $userData["last_online"] = date("Y-m-d H:i:s");

        $user1 = User::create($userData);
        
        // GENERATING THE ACCESS TOKEN FOR THE REGISTERED USER
        //$accessToken = $user1->createToken("authToken")->accessToken;
        $accessToken = $user1->createToken("authToken", ["get-info-on-apps get-info-in-background get-business-suggestions answer-drills buy-business-stocks transfer-business-stocks withdraw-funds"])->accessToken;


        return response([
            "status" => "yes", 
            "message" => "",
            "user_phone" => $user1->user_phone_number,
            "user_email" => $user1->user_email,
            "user_id" => $user1->investor_id,
            "access_token" => $accessToken,
            "user_pott_name" => $user1->user_pottname,
            "user_full_name" => $user1->user_firstname . " " . $user1->user_surname,
            "user_profile_picture" => "",
            "user_country" => $validatedData["user_country"],
            "user_verified_status" => 0,
            "user_type" => "Business",
            "user_gender" => $validatedData["user_gender"],
            "user_date_of_birth" => $user1->user_dob,
            "user_currency" => "USD",
            "highest_version_code" => config('app.androidmaxvc'),
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
    public function login(Request $request)
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
            strtolower($request->app_type) == "android" && 
            $request->app_version_code < intval(config('app.androidminvc'))
        ){
            return response([
                "status" => "error", 
                "message" => "Please update your app from the Google Play Store."
            ]);
        }
        if(
            strtolower($request->app_type) == "ios" && 
            $request->app_version_code < intval(config('app.iosminvc'))
        ){
            return response([
                "status" => "error", 
                "message" => "Please update your app from the App Store."
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
        $user = UtilController::getUserWithOneColumn("user_phone_number", auth()->user()->user_phone_number);
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
        $accessToken = auth()->user()->createToken("authToken", [$user->user_scope])->accessToken;

        // CHECKING IF PROFILE PICTURE EXISTS
        $img_url = config('app.url') . '/uploads/images/' . $user->user_profile_picture;
        if(empty($user->user_profile_picture) || !file_exists(public_path() . '/uploads/images/' . $user->user_profile_picture)){
            $img_url = "";
        }

        // CHECKING ID VERIFICATION
        if(boolval(config('app.idverificationrequiredstatus'))){
            $user->user_id_verification_requested = $user->user_id_verification_requested;
        }

        $user->last_online = date("Y-m-d H:i:s");
        $user->save();    

        return response([
            "status" => "yes", 
            "message" => "",
            "user_phone" => $user->user_phone_number,
            "user_email" => $user->user_email,
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
            "user_min_allowed_version" => intval(config('app.iosminvc')),
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
    | THIS FUNCTION PROVIDES A REGISTERED USER WITH A RESET PASSWORD TOKEN
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    |
    */
        
    public function sendPasswordResetCode(Request $request)
    {
        $resetcode_controller = new ResetCodeController();

        $request->validate([
            "user_phone_number" => "bail|required|regex:/^\+\d{1,3}[0-9]{9}/|min:10|max:15",            
            "user_email" => "bail|required|email|min:4|max:50",
            "user_pottname" => "bail|required|string|regex:/^[A-Za-z0-9_.]+$/|max:15",
            "user_language" => "bail|required|max:3",
            "app_type" => "bail|required|max:8",
            "app_version_code" => "bail|required|integer"
        ]);

        // MAKING SURE VERSION CODE IS ALLOWED
        if($request->app_type == "ANDROID" && 
            ($request->app_version_code < intval(config('app.androidminvc')) || $request->app_version_code > intval(config('app.androidmaxvc')))
        ){
            return response([
                "status" => "error", 
                "message" => "Please update your app from the Google Play Store."
            ]);
        }


        // CHECKING USER
        $user = User::where('user_pottname', $request->user_pottname)->where('user_phone_number', $request->user_phone_number)->where('user_email', $request->user_email)->first();
        if($user === null || $user->user_flagged){
            return response([
                "status" => "yes", 
                "message" => "If you have an account with us, check your inbox/spam for a reset code to reset your password"
            ]);
        } 

        $resetcode = $resetcode_controller->generate_resetcode();

        $email_data = array(
            'reset_code' => $resetcode,
            'time' => date("F j, Y, g:i a")
        );

        $resetcode_controller->saveResetCode($user->investor_id, strval($resetcode));

        Mail::to($user->user_email)->send(new ResetcodeMail($email_data));

        return response([
            "status" => "yes", 
            "message" => "If you have an account with us, check your inbox/spam for a reset code to reset your password"
        ]);
    }


/*
|--------------------------------------------------------------------------
|--------------------------------------------------------------------------
| THIS FUNCTION VERIFIES THE PASSCODE ENTERED AND UPDATES PASSWORDS
|--------------------------------------------------------------------------
|--------------------------------------------------------------------------
*/

public function changePasswordWithResetCode(Request $request)
{
    $resetcode_controller = new ResetCodeController();

    $request->validate([
        "user_phone_number" => "bail|required|regex:/^\+\d{1,3}[0-9]{9}/|min:10|max:15",            
        "user_new_password" => "bail|required|max:20",
        "user_password_reset_code" => "bail|required|max:20",
        "user_language" => "bail|required|max:3",
        "app_type" => "bail|required|max:8",
        "app_version_code" => "bail|required|integer"
    ]);
    
    // MAKING SURE VERSION CODE IS ALLOWED
    if($request->app_type == "ANDROID" && 
        ($request->app_version_code < intval(config('app.androidminvc')) || $request->app_version_code > intval(config('app.androidmaxvc')))
    ){
        return response([
            "status" => "error", 
            "message" => "Please update your app from the Google Play Store."
        ]);
    }

    // CHECKING USER IS FOUND
    $user = User::where('user_phone_number', $request->user_phone_number)->first();
    if($user === null || $user->user_flagged){
        return response([
            "status" => "0", 
            "message" => "User not found"
        ]);
    } 

    //  GETTING THE RESET CODE FROM BASED ON THE USAGE STATUS AND USER INVESTOR ID
    $resetcode = ResetCode::where([
        'user_investor_id' => $user->investor_id,
        'resetcode_used_status' => false,
        'resetcode' => $request->user_password_reset_code
    ])
    ->orderBy('resetcode', 'desc')->first();


    if($resetcode === null || UtilController::getDateDiff($resetcode->created_at, date('Y-m-d H:i:s'), "minutes") > 15){
        return response([
            "status" => "error", 
            "message" => "Reset code not found. Please get a new code"
        ]);
    } 

    if (!empty($resetcode->resetcode) && $resetcode->resetcode == $request->user_password_reset_code) {
        
        // UPDATING RESET CODE USAGE STATUS 
        $resetcode->resetcode_used_status = true;
        $resetcode->save();

        // UPDATING THE NEW PASSWORD
        $user->password = bcrypt($request->user_new_password);
        $user->save();

        return response([
            "status" => "yes", 
            "message" => "Password changed successfully"
        ]);
    } else {
        return response([
            "status" => "error", 
            "message" => "Reset code not valid. Please get a new code"
        ]);
    }
}


    /*
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    | THIS FUNCTION ALLOWS USERS TO UPLOAD A PROFILE PICTURE
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    */
    public function uploadProfilePicture(Request $request)
    {
        // CHECKING THAT THE REQUEST FROM THE USER HAS A VALID TOKEN
        if (!Auth::guard('api')->check()) {
            return response([
                "status" => "error", 
                "message" => "Session closed. You have to login again"
            ]);
        }
    
        // CHECKING THAT USER TOKEN HAS THE RIGHT PERMISSION
        if (!$request->user()->tokenCan('get-info-on-apps')) {
            return response([
                "status" => "error", 
                "message" => "You do not have permission"
            ]);
        }
    
        // CHECKING IF USER FLAGGED
        if (auth()->user()->user_flagged) {
            $request->user()->token()->revoke();
            return response([
                "status" => "error", 
                "message" => "Account flagged."
            ]);
         }
    
        // MAKING SURE THE INPUT HAS THE EXPECTED VALUES
        $validatedData = $request->validate([
            "user_phone_number" => "bail|required|regex:/^\+\d{10,15}$/|min:10|max:15",
            "investor_id" => "bail|required",
            "pott_picture" => "bail|required",
            "user_language" => "bail|required|max:3",
            "app_type" => "bail|required|max:8",
            "app_version_code" => "bail|required|integer"
        ]);

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
        
        // GETTING USER
        $user = User::where('user_pottname', auth()->user()->user_pottname)->where('user_phone_number', $request->user_phone_number)->where('investor_id', $request->investor_id)->first();
        if($user == null){
            return response([
                "status" => "error", 
                "message" => "Session closed. You have to login again."
            ]);
        }
        
        // SAVING APP TYPE VERSION CODE
        if($request->app_type == "ANDROID"){
            $user->user_android_app_version_code = $validatedData["app_version_code"];
        } else if($request->app_type == "IOS"){
            $user->user_ios_app_version_code = $validatedData["app_version_code"];
        }

        // CHECKING IF REQUEST HAS THE IMAGE FILE
        if(!$request->hasFile('pott_picture')) {
            return response([
                "status" => "error", 
                "message" => "Image not found"
            ]);
        }
    
        // CHECKING IF POTT PICTURE IS UPLOADED CORRECTLY AND IS THE RIGHT FORMAT
        if(!$request->file('pott_picture')->isValid() || (strtolower($request->file('pott_picture')->getMimeType())  !=  "image/png" && strtolower($request->file('pott_picture')->getMimeType())  !=  "image/jpg" && strtolower($request->file('pott_picture')->getMimeType())  !=  "image/jpeg")) {
            return response([
                "status" => "error", 
                "message" => "Image has to be JPG or PNG"
            ]);
        }

        // CHECKING THAT IMAGE IS NOT MORE THAN 5MB
        if($request->file('pott_picture')->getSize() > (5 * intval(config('app.mb')))){
            return response([
                "status" => "error", 
                "message" => "Image cannot be more than 5 MB"
            ]);
        }

        //DELETING THE OLD PROFILE PHOTO
        if(auth()->user()->user_profile_picture != ""){
            File::delete(public_path() . '/uploads/images/' . auth()->user()->user_profile_picture);
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
    
        // SAVING CHANGES MADE TO THE USER
        $user->user_profile_picture = $img_ext;    
        $user->save();    

        return response([
            "status" => "yes", 
            "message" => "Upload complete",
            "pott_pic_path" => $img_url, 
            "government_verification_is_on" => false,
            "media_allowed" => intval(config('app.canpostpicsandvids')),
            "user_android_app_max_vc" => intval(config('app.androidmaxvc')),
            "user_android_app_force_update" => boolval(config('app.androidforceupdatetomaxvc')),
            "phone_verification_is_on" => boolval(config('app.phoneverificationrequiredstatus'))
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    | THIS FUNCTION ADDS A DRILL
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    */
    
    public function getMySuggestion(Request $request)
    {
        /*
        |**************************************************************************
        | VALIDATION STARTS 
        |**************************************************************************
        */
        // MAKING SURE THE INPUT HAS THE EXPECTED VALUES
        $validatedData = $request->validate([
            "user_phone_number" => "bail|required|regex:/^\+\d{10,15}$/|min:10|max:15",
            "user_pottname" => "bail|required|string|regex:/^[A-Za-z0-9_.]+$/|max:15",
            "investor_id" => "bail|required",
            "user_language" => "bail|required|max:3",
            "app_type" => "bail|required|max:8",
            "app_version_code" => "bail|required|integer",
            // ADD ANY OTHER REQUIRED INPUTS FROM HERE
            //"drill_question" => "min:5|max:100",
        ]);

        // MAKING SURE THE REQUEST AND USER IS VALIDATED
        $validation_response = UtilController::validateUserWithAuthToken($request, auth()->user(), "get-info-on-apps");
        if(!empty($validation_response["status"])){
            return response($validation_response);
        } else {
            $user = $validation_response;
        }
        /*
        |**************************************************************************
        | VALIDATION ENDED 
        |**************************************************************************
        */

        // CHECKING IF USER HAS A SUGGESTION IS BROADCASTING THAT IS NOT MORE THAN 1 HOURS OR NOT MARKED AS PASS ON
        $suggestion = UtilController::getSuggestionMadeToUser($user->investor_id);
        //echo "suggestion->suggestion_item_reference_id: " . $suggestion->suggestion_item_reference_id; exit;
        //echo " intval(config('app.timedurationinhoursforsuggestions')): " . intval(config('app.timedurationinhoursforsuggestions')); 
        //echo "\n hours passed: " . UtilController::getDateDiff($suggestion->created_at, date('Y-m-d H:i:s'), "hours"); exit;

        if ($suggestion != null && UtilController::getDateDiff($suggestion->created_at, date('Y-m-d H:i:s'), "hours") < intval(config('app.timedurationinhoursforsuggestions'))) {
            //echo "\n\n<br><br>here 1";
            $suggestion = Business::where('business_sys_id', $suggestion->suggestion_item_reference_id)->first();
            $message = "business";
            $country = Country::where('country_id', '=', $suggestion->business_country_id)->first();
            if($country === null){
                return response([
                    "status" => 3, 
                    "message" => "Country validation error."
                ]);
            }

            // REFORMATTING NEEDED VALUES
            $suggestion->business_country = $country->country_real_name;
            $suggestion->business_logo = config('app.url') . '/uploads/logos/' . $suggestion->business_logo;
            $suggestion->business_pitch_video = config('app.url') . '/uploads/pitchvideos/' . $suggestion->business_pitch_video;
            $suggestion->business_full_financial_report_pdf_url = config('app.url') . '/uploads/financedata/' . $suggestion->business_full_financial_report_pdf_url;
            $suggestion->business_net_worth_usd = "$" . UtilController::formatNumberShort($suggestion->business_net_worth_usd);
            $suggestion->business_lastyr_revenue_usd = "$" . UtilController::formatNumberShort($suggestion->business_lastyr_revenue_usd);
            $suggestion->business_lastyr_profit_or_loss_usd = "$" . UtilController::formatNumberShort($suggestion->business_lastyr_profit_or_loss_usd);
            $suggestion->business_debt_usd = "$" . UtilController::formatNumberShort($suggestion->business_debt_usd);
            $suggestion->business_cash_on_hand_usd = "$" . UtilController::formatNumberShort($suggestion->business_cash_on_hand_usd);
            $suggestion->business_investments_amount_needed_usd = "$" . UtilController::formatNumberShort($suggestion->business_investments_amount_needed_usd);

            // SENDING RESPONSE TO FRONTEND
            return response([
                "status" => 1, 
                "message" => $message,
                "data" => $suggestion,
                "government_verification_is_on" => false,
                "media_allowed" => intval(config('app.canpostpicsandvids')),
                "user_android_app_max_vc" => intval(config('app.androidmaxvc')),
                "user_android_app_force_update" => boolval(config('app.androidforceupdatetomaxvc')),
                "phone_verification_is_on" => boolval(config('app.phoneverificationrequiredstatus'))
            ]);
        }

        // CHECKING FOR A NEW DRILL SUGGESTION IF NO BUSINESS SUGGESTION IS BROADCASTING AND IF THE OLD SUGGESTION HAS BEEN EXPIRED IF IT'S A QUESTION.
        $suggestion = UtilController::getLatestSuggestion();

        if($suggestion ==  null || $suggestion == false){
            return response([
                "status" => 3, 
                "message" => "Oops.. No new suggestions. It happens.",
                "government_verification_is_on" => false,
                "media_allowed" => intval(config('app.canpostpicsandvids')),
                "user_android_app_max_vc" => intval(config('app.androidmaxvc')),
                "user_android_app_force_update" => boolval(config('app.androidforceupdatetomaxvc')),
                "phone_verification_is_on" => boolval(config('app.phoneverificationrequiredstatus'))
            ]);
        }

        // CHECKING SUGGESTION TYPE TO GET IT'S INFO
        //echo "\n\n<br><br>getSuggestionType: " . UtilController::getSuggestionType("suggestion_type_name", "Business", 1);
        //echo "\n\n<br><br>suggestion->suggestion_suggestion_type_id: " . $suggestion->suggestion_suggestion_type_id;
        //echo "\n\n<br><br>suggestion->suggestion_item_reference_id: " . $suggestion->suggestion_item_reference_id;
        if($suggestion->suggestion_suggestion_type_id == UtilController::getSuggestionType("suggestion_type_name", "Drill", 1)){
            //echo "\n\n<br><br>here 2";
            $suggestion = Drill::where('drill_sys_id', $suggestion->suggestion_item_reference_id)->first();
            $message = "drill";
            $country_real_name = "";
        } else if($suggestion->suggestion_suggestion_type_id == UtilController::getSuggestionType("suggestion_type_name", "Business", 1)){
            //echo "\n\n<br><br>here 3";
            $suggestion = Business::where('business_sys_id', $suggestion->suggestion_item_reference_id)->first();
            $message = "business";
            $country = Country::where('country_id', '=', $suggestion->business_country_id)->first();
            if($country === null){
                return response([
                    "status" => 3, 
                    "message" => "Country validation error."
                ]);
            }

            // REFORMATTING NEEDED VALUES
            $suggestion->business_country = $country->country_real_name;
            $suggestion->business_logo = config('app.url') . '/uploads/logos/' . $suggestion->business_logo;
            $suggestion->business_pitch_video = config('app.url') . '/uploads/pitchvideos/' . $suggestion->business_pitch_video;
            $suggestion->business_full_financial_report_pdf_url = config('app.url') . '/uploads/financedata/' . $suggestion->business_full_financial_report_pdf_url;
            $suggestion->business_net_worth_usd = "$" . UtilController::formatNumberShort($suggestion->business_net_worth_usd);
            $suggestion->business_lastyr_revenue_usd = "$" . UtilController::formatNumberShort($suggestion->business_lastyr_revenue_usd);
            $suggestion->business_lastyr_profit_or_loss_usd = "$" . UtilController::formatNumberShort($suggestion->business_lastyr_profit_or_loss_usd);
            $suggestion->business_debt_usd = "$" . UtilController::formatNumberShort($suggestion->business_debt_usd);
            $suggestion->business_cash_on_hand_usd = "$" . UtilController::formatNumberShort($suggestion->business_cash_on_hand_usd);
            $suggestion->business_investments_amount_needed_usd = "$" . UtilController::formatNumberShort($suggestion->business_investments_amount_needed_usd);
        }

        return response([
            "status" => 1, 
            "message" => $message,
            "data" => $suggestion,
            "government_verification_is_on" => false,
            "media_allowed" => intval(config('app.canpostpicsandvids')),
            "user_android_app_max_vc" => intval(config('app.androidmaxvc')),
            "user_android_app_force_update" => boolval(config('app.androidforceupdatetomaxvc')),
            "phone_verification_is_on" => boolval(config('app.phoneverificationrequiredstatus'))
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    | THIS FUNCTION RECORDS A DRILL ANSWER AND RETURNS WHAT OTHERS SAID
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    */
    
    public function saveDrillAnswerAndReturnWhatOthersSaid(Request $request)
    {
        /*
        |**************************************************************************
        | VALIDATION STARTS 
        |**************************************************************************
        */
        // MAKING SURE THE INPUT HAS THE EXPECTED VALUES
        $validatedData = $request->validate([
            "user_phone_number" => "bail|required|regex:/^\+\d{10,15}$/|min:10|max:15",
            "user_pottname" => "bail|required|string|regex:/^[A-Za-z0-9_.]+$/|max:15",
            "investor_id" => "bail|required",
            "user_language" => "bail|required|max:3",
            "app_type" => "bail|required|max:8",
            "app_version_code" => "bail|required|integer",
            // ADD ANY OTHER REQUIRED INPUTS FROM HERE
            "drill_id" => "bail|required|string",
            "drill_answer" => "bail|required|integer|min:1|max:4",
        ]);

        // MAKING SURE THE REQUEST AND USER IS VALIDATED
        $validation_response = UtilController::validateUserWithAuthToken($request, auth()->user(), "answer-drills");
        if(!empty($validation_response["status"])){
            return response($validation_response);
        } else {
            $user = $validation_response;
        }
        /*
        |**************************************************************************
        | VALIDATION ENDED 
        |**************************************************************************
        */

        // GETTING THE DRILL THAT WAS ANSWERED
        $default_msg = "See answers from the world";
        $drill = Drill::where('drill_sys_id', $request->drill_id)->first();
        if($drill == null || empty($drill->drill_sys_id)){
            return response([
                "status" => 3, 
                "message" => "Drill not found",
                "government_verification_is_on" => false,
                "media_allowed" => intval(config('app.canpostpicsandvids')),
                "user_android_app_max_vc" => intval(config('app.androidmaxvc')),
                "user_android_app_force_update" => boolval(config('app.androidforceupdatetomaxvc')),
                "phone_verification_is_on" => boolval(config('app.phoneverificationrequiredstatus'))
            ]);
        }



        // SETTING THE INFO NEEDED FOR DRILL ANSWER CREATION
        $drillAnswerData["drill_answer_sys_id"] =  "drill-answer-" . $user->user_pottname . substr($user->user_phone_number ,1,strlen($user->user_phone_number)) . $drill->drill_sys_id;
        $drillAnswerData["drill_answer_number"] = intval($request->drill_answer);
        $drillAnswerData["drill_answer_used_for_pott_intelligence_calculation"] = false;
        $drillAnswerData["drill_answer_drill_sys_id"] = $request->drill_id;
        $drillAnswerData["drill_answer_user_investor_id"] = $user->investor_id;

        // CHECKING IF AN ANSWER IS RECORDED AND CREATING THE DRILL ANSWER
        $drillAnswer = DrillAnswer::where('drill_answer_sys_id', $drillAnswerData["drill_answer_sys_id"])->where('drill_answer_user_investor_id', $user->investor_id)->first();
        if($drillAnswer == null || empty($drillAnswer->drill_answer_sys_id)){
            DrillAnswer::create($drillAnswerData);
        } else {
            $default_msg = "You already answered this drill.";
        }

        // CALCULATING POTT-INTELLIGENCE/OR HOW WELL YOUR POTT KNOWS YOU
        $six_months_ago = date('Y-m-d',strtotime("-180 days")); 
        $answers_six_months_ago = DrillAnswer::where('drill_answer_user_investor_id', $user->investor_id)->whereDate('created_at', ">=" , $six_months_ago)->count(); 
        $pott_user_sync = (intval($answers_six_months_ago)/180) * 100;
        $user->user_pott_intelligence = $pott_user_sync;
        $user->save();
        /*
        echo "\n six_months_ago: " .  $six_months_ago;
        echo "\n answers_six_months_ago: " .  $answers_six_months_ago;
        exit;
        */

        // CALCULATING OCEAN VALUES FOR USER

        // GETTING DRILL ANSWERS
        $answers = DrillAnswer::select('drill_answer_drill_sys_id', 'drill_answer_number')
        ->where('drill_answer_user_investor_id', '=', $user->investor_id)
        ->orderBy('drill_answer_id', 'desc')->take(30)->get();

        /*
        if(count($answers) < 7){
            return 1;
        }
        */

        // INITIALIZING ARRAY
        $output_data_array = array('o' => 0,'c' => 0,'e' => 0,'a' => 0,'n' => 0);

        $count_answers = 0;
        foreach($answers as $answer){
            $this_drill = Drill::where('drill_sys_id', '=', $answer->drill_answer_drill_sys_id)->first();
            if($answer->drill_answer_number == 1){
                //echo "\n\ndrill answer 1: " . $this_drill->drill_answer_1_ocean;
                $this_raw_ocean_array = explode("#", $this_drill->drill_answer_1_ocean);
            } else if($answer->drill_answer_number == 2){
                //echo "\n\ndrill answer 2: " . $this_drill->drill_answer_2_ocean;
                $this_raw_ocean_array = explode("#", $this_drill->drill_answer_2_ocean);
            } else if($answer->drill_answer_number == 3){
                //echo "\n\ndrill answer 3: " . $this_drill->drill_answer_3_ocean;
                $this_raw_ocean_array = explode("#", $this_drill->drill_answer_3_ocean);
            } else if($answer->drill_answer_number == 4){
                //echo "\n\ndrill answer 4: " . $this_drill->drill_answer_4_ocean;
                $this_raw_ocean_array = explode("#", $this_drill->drill_answer_4_ocean);
            } else {
                continue;
            }

            if(count($this_raw_ocean_array) != 5){
                continue;
            }
            
            $output_data_array["o"] = $output_data_array["o"] + $this_raw_ocean_array[0];
            $output_data_array["c"] = $output_data_array["c"] + $this_raw_ocean_array[1];
            $output_data_array["e"] = $output_data_array["e"] + $this_raw_ocean_array[2];
            $output_data_array["a"] = $output_data_array["a"] + $this_raw_ocean_array[3];
            $output_data_array["n"] = $output_data_array["n"] + $this_raw_ocean_array[4];
            $count_answers++;
        }
        

        $o = $output_data_array["o"]/$count_answers;
        $c = $output_data_array["c"]/$count_answers;
        $e = $output_data_array["e"]/$count_answers;
        $a = $output_data_array["a"]/$count_answers;
        $n = $output_data_array["n"]/$count_answers;

        /*
        echo "\n\n o : " . $o . "%\n\n"; 
        echo "\n\n c : " . $c . "%\n\n"; 
        echo "\n\n e : " . $e . "%\n\n"; 
        echo "\n\n a : " . $a . "%\n\n"; 
        echo "\n\n n : " . $n . "%\n\n";
        //exit;
        */

        $user->ocean_openness_to_experience = $o;
        $user->ocean_conscientiousness = $c;
        $user->ocean_extraversion = $e;
        $user->ocean_agreeableness = $a;
        $user->ocean_neuroticism = $n;

        $user->save();

        // GETTING THE ANSWERS OF FRIENDS
        $answer_1_count = UtilController::getCountDrillAnswers(["drill_answer_drill_sys_id", "drill_answer_number"], [$drill->drill_sys_id, 1]);
        $answer_2_count = UtilController::getCountDrillAnswers(["drill_answer_drill_sys_id", "drill_answer_number"], [$drill->drill_sys_id, 2]);
        $answer_3_count = UtilController::getCountDrillAnswers(["drill_answer_drill_sys_id", "drill_answer_number"], [$drill->drill_sys_id, 3]);
        $answer_4_count = UtilController::getCountDrillAnswers(["drill_answer_drill_sys_id", "drill_answer_number"], [$drill->drill_sys_id, 4]);

        $data = array(
            "drill_next_one_time" => "Your next suggestion will be in " . strval(config('app.timedurationinhoursforsuggestions')) . " hr",
            "answer_1" => $drill->drill_answer_1, 
            "answer_1_count" => $answer_1_count, 
            "answer_2" => $drill->drill_answer_2, 
            "answer_2_count" => $answer_2_count, 
            "answer_3" => $drill->drill_answer_3, 
            "answer_3_count" => $answer_3_count, 
            "answer_4" => $drill->drill_answer_4, 
            "answer_4_count" => $answer_4_count, 
        );

        return response([
            "status" => 1, 
            "message" => $default_msg,
            "data" => $data,
            "government_verification_is_on" => false,
            "media_allowed" => intval(config('app.canpostpicsandvids')),
            "user_android_app_max_vc" => intval(config('app.androidmaxvc')),
            "user_android_app_force_update" => boolval(config('app.androidforceupdatetomaxvc')),
            "phone_verification_is_on" => boolval(config('app.phoneverificationrequiredstatus'))
        ]);
    }

    public function getFinalPriceSummary(Request $request)
    {
        /*
        |**************************************************************************
        | VALIDATION STARTS 
        |**************************************************************************
        */
        // MAKING SURE THE INPUT HAS THE EXPECTED VALUES
        $validatedData = $request->validate([
            "user_phone_number" => "bail|required|regex:/^\+\d{10,15}$/|min:10|max:15",
            "user_pottname" => "bail|required|string|regex:/^[A-Za-z0-9_.]+$/|max:15",
            "investor_id" => "bail|required",
            "user_language" => "bail|required|max:3",
            "app_type" => "bail|required|max:8",
            "app_version_code" => "bail|required|integer",
            // ADD ANY OTHER REQUIRED INPUTS FROM HERE
            "business_id" => "bail|required|string",
            "investment_amt_in_dollars" => "bail|required|integer",
            "investment_risk_protection" => "bail|required|integer|min:0|max:3",
        ]);

        // MAKING SURE THE REQUEST AND USER IS VALIDATED
        $validation_response = UtilController::validateUserWithAuthToken($request, auth()->user(), "get-info-on-apps");
        if(!empty($validation_response["status"])){
            return response($validation_response);
        } else {
            $user = $validation_response;
        }

        if($request->investment_risk_protection != 1 && $request->investment_risk_protection != 2 && $request->investment_risk_protection != 3){
            return response([
                "status" => 3, 
                "message" => "Risk determinance failure",
                "government_verification_is_on" => false,
                "media_allowed" => intval(config('app.canpostpicsandvids')),
                "user_android_app_max_vc" => intval(config('app.androidmaxvc')),
                "user_android_app_force_update" => boolval(config('app.androidforceupdatetomaxvc')),
                "phone_verification_is_on" => boolval(config('app.phoneverificationrequiredstatus'))
            ]);
        }
        /*
        |**************************************************************************
        | VALIDATION ENDED 
        |**************************************************************************
        */

        // GETTING THE BUSINESS
        $business = Business::where('business_sys_id', $request->business_id)->first();
        if($business == null || empty($business->business_registration_number)){
            return response([
                "status" => 3, 
                "message" => "Business not found",
                "government_verification_is_on" => false,
                "media_allowed" => intval(config('app.canpostpicsandvids')),
                "user_android_app_max_vc" => intval(config('app.androidmaxvc')),
                "user_android_app_force_update" => boolval(config('app.androidforceupdatetomaxvc')),
                "phone_verification_is_on" => boolval(config('app.phoneverificationrequiredstatus'))
            ]);
        }

        // CHECKING THE INVESTMENT AMOUNT IF IT WILL BE ACCEPTED
        $business->business_investments_amount_left_to_receive_usd = $business->business_investments_amount_needed_usd - $business->business_investments_amount_received_usd;
        if(($business->business_investments_amount_needed_usd - $business->business_investments_amount_received_usd) < $request->investment_amt_in_dollars ){
            if($business->business_investments_amount_left_to_receive_usd <= 0){
                return response([
                    "status" => 3, 
                    "message" => "This business is no longer receiving investments",
                    "government_verification_is_on" => false,
                    "media_allowed" => intval(config('app.canpostpicsandvids')),
                    "user_android_app_max_vc" => intval(config('app.androidmaxvc')),
                    "user_android_app_force_update" => boolval(config('app.androidforceupdatetomaxvc')),
                    "phone_verification_is_on" => boolval(config('app.phoneverificationrequiredstatus'))
                ]);
            } else {
                return response([
                    "status" => 3, 
                    "message" => "You can only invest " . $business->business_investments_amount_left_to_receive_usd,
                    "government_verification_is_on" => false,
                    "media_allowed" => intval(config('app.canpostpicsandvids')),
                    "user_android_app_max_vc" => intval(config('app.androidmaxvc')),
                    "user_android_app_force_update" => boolval(config('app.androidforceupdatetomaxvc')),
                    "phone_verification_is_on" => boolval(config('app.phoneverificationrequiredstatus'))
                ]);
            }
        }

        if(floatval($request->investment_amt_in_dollars) < floatval($business->business_price_per_stock_usd)){
            return response([
                "status" => 3, 
                "message" => "The least amount you can invest is $" . $business->business_price_per_stock_usd,
                "government_verification_is_on" => false,
                "media_allowed" => intval(config('app.canpostpicsandvids')),
                "user_android_app_max_vc" => intval(config('app.androidmaxvc')),
                "user_android_app_force_update" => boolval(config('app.androidforceupdatetomaxvc')),
                "phone_verification_is_on" => boolval(config('app.phoneverificationrequiredstatus'))
            ]);
        }

        // GETTING THE QUANTITY OF SHARES
        $item_quantity = floor($request->investment_amt_in_dollars / $business->business_price_per_stock_usd);

        if($item_quantity < 1){
            return response([
                "status" => 3, 
                "message" => "Invalid stock quantity. Try investing $" . $business->business_price_per_stock_usd,
                "government_verification_is_on" => false,
                "media_allowed" => intval(config('app.canpostpicsandvids')),
                "user_android_app_max_vc" => intval(config('app.androidmaxvc')),
                "user_android_app_force_update" => boolval(config('app.androidforceupdatetomaxvc')),
                "phone_verification_is_on" => boolval(config('app.phoneverificationrequiredstatus'))
            ]);
        }

        $total_item_quantity_cost = $item_quantity * $business->business_price_per_stock_usd;

        // CALCULATING RISK INSURANCE FEE
        if($request->investment_risk_protection == 3){
            $risk_type_id = 3;
            $risk_statement = "No risk insurance";
            $risk_fee = "0";
            $yield_info = $business->business_descriptive_financial_bio . ". Choosing no risk insurance means if the business fails, FishPott will not pay any amount back to you to cushion you.";
        } else if($request->investment_risk_protection == 2){
            $risk_type_id = 2;
            $risk_statement = "50% Risk Insurance.";
            $risk_fee = $total_item_quantity_cost * floatval(config('app.fifty_risk_insurance'));
            $yield_info = $business->business_descriptive_financial_bio . ". Choosing 50% risk insurance means if the business fails, FishPott reimburse 50% what you paid for the shares.";
        } else if($request->investment_risk_protection == 1){
            $risk_type_id = 1;
            $risk_statement = "100% Risk Insurance.";
            $risk_fee = $total_item_quantity_cost * floatval(config('app.hundred_risk_insurance'));
            $yield_info = $business->business_descriptive_financial_bio . ". Choosing 50% risk insurance means if the business fails, FishPott reimburse 100% what you paid for the shares.";
        }

        // CALCULATING PROCESSING FEE
        $processing_fee = $total_item_quantity_cost * floatval(config('app.processing_fee'));

        // CALCULATING TOTAL OVERAL COST
        $overall_total_usd = $total_item_quantity_cost + $risk_fee + $processing_fee;

        // CONVERTING TO USER'S LOCAL CURRENCY
        //echo var_dump($user); exit;
        $currency_local = Currency::where("currency_country_id", '=', $user->user_country_id)->first();

        if($user->user_country_id == 81){ // GHANA
            $overall_total_local_currency_no_currency_sign = ($overall_total_usd * floatval(config('app.to_cedi')));
            $overall_total_local_currency = "Gh¢" . ($overall_total_usd * floatval(config('app.to_cedi')));
            $rate = "$1 = " . "Gh¢" . floatval(config('app.to_cedi'));
            $rate_no_sign = floatval(config('app.to_cedi'));
            $payment_gateway_amount_cents_or_pesewas = $overall_total_local_currency_no_currency_sign * 100;
        } else {
            $overall_total_local_currency_no_currency_sign = $overall_total_usd;
            $overall_total_local_currency = "$" . $overall_total_usd;
            $rate = "$1 = " . "$1";
            $rate_no_sign = 1;
            $payment_gateway_amount_cents_or_pesewas = $overall_total_local_currency_no_currency_sign * 100;
        }

        // RECORDING THE POSSIBLE ORDER
        $stockPurchaseData["stockpurchase_sys_id"] = "sp" . substr($user->user_phone_number ,1,strlen($user->user_phone_number)) . date("YmdHis");
        $stockPurchaseData["stockpurchase_business_id"] = $business->business_sys_id;
        $stockPurchaseData["stockpurchase_price_per_stock_usd"] = $business->business_price_per_stock_usd;
        $stockPurchaseData["stockpurchase_stocks_quantity"] = $item_quantity;
        $stockPurchaseData["stockpurchase_total_price_no_fees_usd"] = $total_item_quantity_cost;
        $stockPurchaseData["stockpurchase_risk_insurance_fee_usd"] = floatval($risk_fee);
        $stockPurchaseData["stockpurchase_processing_fee_usd"] = floatval($processing_fee);
        $stockPurchaseData["stockpurchase_total_price_with_all_fees_usd"] = floatval($overall_total_usd);
        $stockPurchaseData["stockpurchase_currency_paid_in_id"] = $currency_local->currency_id;
        $stockPurchaseData["stockpurchase_rate_of_dollar_to_currency_paid_in"] = $rate_no_sign;
        $stockPurchaseData["stockpurchase_total_all_fees_in_currency_paid_in"] = $overall_total_local_currency_no_currency_sign;
        $stockPurchaseData["stockpurchase_risk_insurance_type_id"] = $risk_type_id;
        $stockPurchaseData["stockpurchase_user_investor_id"] = $user->investor_id;
        $stockPurchaseData["stockpurchase_processed"] = false;
        $stockPurchaseData["stockpurchase_processed_reason"] = "";
        $stockPurchaseData["stockpurchase_flagged"] = false;
        $stockPurchaseData["stockpurchase_flagged_reason"] = "";
        $stockPurchaseData["stockpurchase_payment_gateway_status"] = 0;
        $stockPurchaseData["stockpurchase_payment_gateway_info"] = "";
        $new_stockpurchase = StockPurchase::create($stockPurchaseData);


        // TESTING
        /*
        $data = array(
            "order_id" => $new_stockpurchase->stockpurchase_sys_id, 
            "item" => $business->business_full_name, 
            "price_per_item" => "$0.1", 
            "quantity" => 1, 
            "rate" => $rate, 
            "risk" => $request->investment_risk_protection,  
            "risk_statement" => $risk_statement,   
            "risk_insurance_fee" => "$0.03", 
            "processing_fee" => "$0.03", 
            "overall_total_usd" => "$0.16", 
            "overall_total_local_currency" => "GHS 1",
            "overall_total_local_currency_floatval" => 100,
            "payment_gateway_amount_in_pesewas_or_cents_intval" => 100,
            "payment_gateway_currency" => "GHS",
            "financial_yield_info" => "This is a test",
            "mobile_money_number" => config('app.mtnghanamomonum'),
            "mobile_money_name" => config('app.mtnghanamomoaccname')
        );
        */

        if(strval(config('app.payment_channel')) == "Momo"){
            $payment_details = array(
                'mobile_money_number' => strval(config('app.mtnghanamomonum')), 
                'mobile_money_name' => strval(config('app.mtnghanamomoaccname')), 
            );
        } else {
            $payment_details = array(
                'bankname' => strval(config('app.bankname')), 
                'bankaddress' => strval(config('app.bankaddress')), 
                'bankswiftiban' => strval(config('app.bankswiftiban')), 
                'bankbranch' => strval(config('app.bankbranch')), 
                'bankaccountname' => strval(config('app.bankaccountname')), 
                'bankaccountnumber' => strval(config('app.bankaccountnumber')), 
            );
        }

        // LIVE
        $data = array(
            "order_id" => $new_stockpurchase->stockpurchase_sys_id, 
            "item" => $business->business_full_name, 
            "price_per_item" => "$" . strval($business->business_price_per_stock_usd), 
            "quantity" => $item_quantity, 
            "rate" => $rate, 
            "risk" => $request->investment_risk_protection,  
            "risk_statement" => $risk_statement,   
            "risk_insurance_fee" => "$" . strval($risk_fee), 
            "processing_fee" => "$" . strval($processing_fee), 
            "overall_total_usd" => "$" . strval($overall_total_usd), 
            "overall_total_local_currency" => $overall_total_local_currency,
            "overall_total_local_currency_floatval" => $overall_total_local_currency_no_currency_sign,
            "payment_gateway_amount_in_pesewas_or_cents_intval" => $payment_gateway_amount_cents_or_pesewas,
            "payment_gateway_currency" => $currency_local->currency_short_name,
            "financial_yield_info" => $yield_info,
            "payment_channel" => strval(config('app.payment_channel')),
            "payment_details" => $payment_details
        );
        


        return response([
            "status" => 1, 
            "message" => "success",
            "data" => $data,
            "government_verification_is_on" => false,
            "media_allowed" => intval(config('app.canpostpicsandvids')),
            "user_android_app_max_vc" => intval(config('app.androidmaxvc')),
            "user_android_app_force_update" => boolval(config('app.androidforceupdatetomaxvc')),
            "phone_verification_is_on" => boolval(config('app.phoneverificationrequiredstatus'))
        ]);
    }


    public function updateBuyOrderPaymentInfo(Request $request)
    {
        /*
        |**************************************************************************
        | VALIDATION STARTS 
        |**************************************************************************
        */
        // MAKING SURE THE INPUT HAS THE EXPECTED VALUES
        $validatedData = $request->validate([
            "user_phone_number" => "bail|required|regex:/^\+\d{10,15}$/|min:10|max:15",
            "user_pottname" => "bail|required|string|regex:/^[A-Za-z0-9_.]+$/|max:15",
            "investor_id" => "bail|required",
            "user_language" => "bail|required|max:3",
            "app_type" => "bail|required|max:8",
            "app_version_code" => "bail|required|integer",
            // ADD ANY OTHER REQUIRED INPUTS FROM HERE
            "item_type" => "bail|required|string",
            "item_id" => "bail|required|string",
            "payment_gateway_status" => "bail|required|string",
            "payment_gateway_info" => "bail|required|string",
        ]);

        // MAKING SURE THE REQUEST AND USER IS VALIDATED
        $validation_response = UtilController::validateUserWithAuthToken($request, auth()->user(), "get-info-on-apps");
        if(!empty($validation_response["status"])){
            return response($validation_response);
        } else {
            $user = $validation_response;
        }

        /*
        |**************************************************************************
        | VALIDATION ENDED 
        |**************************************************************************
        */
        
        // GETTING THE ORDER
        if($request->item_type == "stockpurchase"){
            $stockpurchase = StockPurchase::where('stockpurchase_sys_id', $request->item_id)->first();
            if($stockpurchase == null || empty($stockpurchase->stockpurchase_sys_id)){
                return response([
                    "status" => 3, 
                    "message" => "Order not found",
                    "government_verification_is_on" => false,
                    "media_allowed" => intval(config('app.canpostpicsandvids')),
                    "user_android_app_max_vc" => intval(config('app.androidmaxvc')),
                    "user_android_app_force_update" => boolval(config('app.androidforceupdatetomaxvc')),
                    "phone_verification_is_on" => boolval(config('app.phoneverificationrequiredstatus'))
                ]);
            }
            // UPDATING THE ORDER
            $stockpurchase->stockpurchase_payment_gateway_status = $request->payment_gateway_status;
            $stockpurchase->stockpurchase_payment_gateway_info = $request->payment_gateway_info;
            $stockpurchase->save();
            // SAVING IT AS A TRANSACTION
            $transaction = Transaction::where('transaction_referenced_item_id', $request->item_id)->first();
            if($transaction == null){
                $transactionData["transaction_sys_id"] =  "SP-" . $user->user_pottname . "-" . date("YmdHis") . UtilController::getRandomString(4);
                $transactionData["transaction_transaction_type_id"] = 4;
                $transactionData["transaction_referenced_item_id"] = $request->item_id;
                $transactionData["transaction_user_investor_id"] = $user->investor_id;
                $transaction = Transaction::create($transactionData);
            } 
        } else if($request->item_type == "stocktransfer"){
            $stocktransfer = StockTransfer::where('stocktransfer_sys_id', $request->item_id)->first();
            if($stocktransfer == null || empty($stocktransfer->stocktransfer_sys_id)){
                return response([
                    "status" => 3, 
                    "message" => "Order not found",
                    "government_verification_is_on" => false,
                    "media_allowed" => intval(config('app.canpostpicsandvids')),
                    "user_android_app_max_vc" => intval(config('app.androidmaxvc')),
                    "user_android_app_force_update" => boolval(config('app.androidforceupdatetomaxvc')),
                    "phone_verification_is_on" => boolval(config('app.phoneverificationrequiredstatus'))
                ]);
            }
            // UPDATING THE ORDER
            $stocktransfer->stocktransfer_payment_gateway_status = $request->payment_gateway_status;
            $stocktransfer->stocktransfer_payment_gateway_info = $request->payment_gateway_info;
            $stocktransfer->stocktransfer_flagged = false;
            $stocktransfer->save();

            // TAKING AWAY STOCK
            $stockownership = StockOwnership::where("stockownership_user_investor_id", $user->investor_id)->where('stockownership_business_id', $stocktransfer->stocktransfer_business_id)->first();
            if($stockownership == null || empty($stockownership->stockownership_business_id)){
                return response([
                    "status" => 3, 
                    "message" => "Stock ownership not verified",
                    "government_verification_is_on" => false,
                    "media_allowed" => intval(config('app.canpostpicsandvids')),
                    "user_android_app_max_vc" => intval(config('app.androidmaxvc')),
                    "user_android_app_force_update" => boolval(config('app.androidforceupdatetomaxvc')),
                    "phone_verification_is_on" => boolval(config('app.phoneverificationrequiredstatus'))
                ]);
            }
    
            $stockownership->stockownership_total_cost_usd = ($stockownership->stockownership_total_cost_usd/$stockownership->stockownership_stocks_quantity) * ($stockownership->stockownership_stocks_quantity - intval($stocktransfer->stocktransfer_stocks_quantity));
            $stockownership->stockownership_stocks_quantity = $stockownership->stockownership_stocks_quantity - intval($stocktransfer->stocktransfer_stocks_quantity);
            $stockownership->save();

            // SAVING IT AS A TRANSACTION
            $transaction = Transaction::where('transaction_referenced_item_id', $request->item_id)->first();
            if($transaction == null){
                $transactionData["transaction_sys_id"] =  "ST-" . $user->user_pottname . "-" . date("YmdHis") . UtilController::getRandomString(4);
                $transactionData["transaction_transaction_type_id"] = 5;
                $transactionData["transaction_referenced_item_id"] = $request->item_id;
                $transactionData["transaction_user_investor_id"] = $user->investor_id;
                $transaction = Transaction::create($transactionData);
            } 
        } else {
            return response([
                "status" => 3, 
                "message" => "Order type not found",
                "government_verification_is_on" => false,
                "media_allowed" => intval(config('app.canpostpicsandvids')),
                "user_android_app_max_vc" => intval(config('app.androidmaxvc')),
                "user_android_app_force_update" => boolval(config('app.androidforceupdatetomaxvc')),
                "phone_verification_is_on" => boolval(config('app.phoneverificationrequiredstatus'))
            ]);
        }

        $data = array(
            "order_id" => $transaction->transaction_sys_id
        );

        return response([
            "status" => 1, 
            "message" => "success",
            "data" => $data,
            "government_verification_is_on" => false,
            "media_allowed" => intval(config('app.canpostpicsandvids')),
            "user_android_app_max_vc" => intval(config('app.androidmaxvc')),
            "user_android_app_force_update" => boolval(config('app.androidforceupdatetomaxvc')),
            "phone_verification_is_on" => boolval(config('app.phoneverificationrequiredstatus'))
        ]);
    }

    public function updateBuyOrderPaymentInfoForPaymentGateWay(Request $request)
    {
        /*
        |**************************************************************************
        | VALIDATION STARTS 
        |**************************************************************************
        */
        // MAKING SURE THE INPUT HAS THE EXPECTED VALUES
        $validatedData = $request->validate([
            "user_phone_number" => "bail|required|regex:/^\+\d{10,15}$/|min:10|max:15",
            "user_pottname" => "bail|required|string|regex:/^[A-Za-z0-9_.]+$/|max:15",
            "investor_id" => "bail|required",
            "user_language" => "bail|required|max:3",
            "app_type" => "bail|required|max:8",
            "app_version_code" => "bail|required|integer",
            // ADD ANY OTHER REQUIRED INPUTS FROM HERE
            "item_type" => "bail|required|string",
            "item_id" => "bail|required|string",
            "payment_gateway_status" => "bail|required|string",
            "payment_gateway_info" => "bail|required|string",
        ]);

        // MAKING SURE THE REQUEST AND USER IS VALIDATED
        $validation_response = UtilController::validateUserWithAuthToken($request, auth()->user(), "get-info-on-apps");
        if(!empty($validation_response["status"])){
            return response($validation_response);
        } else {
            $user = $validation_response;
        }

        /*
        |**************************************************************************
        | VALIDATION ENDED 
        |**************************************************************************
        */

        // VERIFYING THE TRANSACTION
        $reference = $request->item_id;
        if(!$reference){
          die('No order reference supplied');
        }
    
        
        // initiate the Library's Paystack Object
        $paystack = new Paystack(config('app.payment_gateway_secret_key'));
        try
        {
          // verify using the library
          $tranx = $paystack->transaction->verify([
            'reference'=>$reference, // unique to transactions
          ]);
        } catch(Paystack\Exception\ApiException $e){
          //print_r($e->getResponseObject());
          //die($e->getMessage());
          $this_msg = $e->getMessage() . ". If think this is an error, email us the issue with your order ID: " . $request->item_id;
          return response([
              "status" => 3, 
              "message" => $this_msg,
              "government_verification_is_on" => false,
              "media_allowed" => intval(config('app.canpostpicsandvids')),
              "user_android_app_max_vc" => intval(config('app.androidmaxvc')),
              "user_android_app_force_update" => boolval(config('app.androidforceupdatetomaxvc')),
              "phone_verification_is_on" => boolval(config('app.phoneverificationrequiredstatus'))
          ]);
        }
        if ('success' === $tranx->data->status) {
          // transaction was successful...
          // please check other things like whether you already gave value for this ref
          // if the email matches the customer who owns the product etc
          // Give value
        }
        

        // GETTING THE ORDER
        if($request->item_type == "stockpurchase"){
            $stockpurchase = StockPurchase::where('stockpurchase_sys_id', $request->item_id)->first();
            if($stockpurchase == null || empty($stockpurchase->stockpurchase_sys_id)){
                return response([
                    "status" => 3, 
                    "message" => "Order not found",
                    "government_verification_is_on" => false,
                    "media_allowed" => intval(config('app.canpostpicsandvids')),
                    "user_android_app_max_vc" => intval(config('app.androidmaxvc')),
                    "user_android_app_force_update" => boolval(config('app.androidforceupdatetomaxvc')),
                    "phone_verification_is_on" => boolval(config('app.phoneverificationrequiredstatus'))
                ]);
            }
            // UPDATING THE ORDER
            $stockpurchase->stockpurchase_payment_gateway_status = $request->payment_gateway_status;
            $stockpurchase->stockpurchase_payment_gateway_info = $request->payment_gateway_info;
            $stockpurchase->save();
            // SAVING IT AS A TRANSACTION
            $transaction = Transaction::where('transaction_referenced_item_id', $request->item_id)->first();
            if($transaction == null){
                $transactionData["transaction_sys_id"] =  "SP-" . $user->user_pottname . "-" . date("YmdHis") . UtilController::getRandomString(4);
                $transactionData["transaction_transaction_type_id"] = 4;
                $transactionData["transaction_referenced_item_id"] = $request->item_id;
                $transactionData["transaction_user_investor_id"] = $user->investor_id;
                $transaction = Transaction::create($transactionData);
            } 
        } else if($request->item_type == "stocktransfer"){
            $stocktransfer = StockTransfer::where('stocktransfer_sys_id', $request->item_id)->first();
            if($stocktransfer == null || empty($stocktransfer->stocktransfer_sys_id)){
                return response([
                    "status" => 3, 
                    "message" => "Order not found",
                    "government_verification_is_on" => false,
                    "media_allowed" => intval(config('app.canpostpicsandvids')),
                    "user_android_app_max_vc" => intval(config('app.androidmaxvc')),
                    "user_android_app_force_update" => boolval(config('app.androidforceupdatetomaxvc')),
                    "phone_verification_is_on" => boolval(config('app.phoneverificationrequiredstatus'))
                ]);
            }
            // UPDATING THE ORDER
            $stocktransfer->stocktransfer_payment_gateway_status = $request->payment_gateway_status;
            $stocktransfer->stocktransfer_payment_gateway_info = $request->payment_gateway_info;
            $stocktransfer->stocktransfer_flagged = false;
            $stocktransfer->save();

            // TAKING AWAY STOCK
            $stockownership = StockOwnership::where("stockownership_user_investor_id", $user->investor_id)->where('stockownership_business_id', $stocktransfer->stocktransfer_business_id)->first();
            if($stockownership == null || empty($stockownership->stockownership_business_id)){
                return response([
                    "status" => 3, 
                    "message" => "Stock ownership not verified",
                    "government_verification_is_on" => false,
                    "media_allowed" => intval(config('app.canpostpicsandvids')),
                    "user_android_app_max_vc" => intval(config('app.androidmaxvc')),
                    "user_android_app_force_update" => boolval(config('app.androidforceupdatetomaxvc')),
                    "phone_verification_is_on" => boolval(config('app.phoneverificationrequiredstatus'))
                ]);
            }
    
            $stockownership->stockownership_total_cost_usd = ($stockownership->stockownership_total_cost_usd/$stockownership->stockownership_stocks_quantity) * ($stockownership->stockownership_stocks_quantity - intval($stocktransfer->stocktransfer_stocks_quantity));
            $stockownership->stockownership_stocks_quantity = $stockownership->stockownership_stocks_quantity - intval($stocktransfer->stocktransfer_stocks_quantity);
            $stockownership->save();

            // SAVING IT AS A TRANSACTION
            $transaction = Transaction::where('transaction_referenced_item_id', $request->item_id)->first();
            if($transaction == null){
                $transactionData["transaction_sys_id"] =  "ST-" . $user->user_pottname . "-" . date("YmdHis") . UtilController::getRandomString(4);
                $transactionData["transaction_transaction_type_id"] = 5;
                $transactionData["transaction_referenced_item_id"] = $request->item_id;
                $transactionData["transaction_user_investor_id"] = $user->investor_id;
                $transaction = Transaction::create($transactionData);
            } 
        } else {
            return response([
                "status" => 3, 
                "message" => "Order type not found",
                "government_verification_is_on" => false,
                "media_allowed" => intval(config('app.canpostpicsandvids')),
                "user_android_app_max_vc" => intval(config('app.androidmaxvc')),
                "user_android_app_force_update" => boolval(config('app.androidforceupdatetomaxvc')),
                "phone_verification_is_on" => boolval(config('app.phoneverificationrequiredstatus'))
            ]);
        }

        $data = array(
            "order_id" => $transaction->transaction_sys_id
        );

        return response([
            "status" => 1, 
            "message" => "success",
            "data" => $data,
            "government_verification_is_on" => false,
            "media_allowed" => intval(config('app.canpostpicsandvids')),
            "user_android_app_max_vc" => intval(config('app.androidmaxvc')),
            "user_android_app_force_update" => boolval(config('app.androidforceupdatetomaxvc')),
            "phone_verification_is_on" => boolval(config('app.phoneverificationrequiredstatus'))
        ]);
    }

    public function sendStockTransfer(Request $request)
    {
        /*
        |**************************************************************************
        | VALIDATION STARTS 
        |**************************************************************************
        */
        // MAKING SURE THE INPUT HAS THE EXPECTED VALUES
        $validatedData = $request->validate([
            "user_phone_number" => "bail|required|regex:/^\+\d{10,15}$/|min:10|max:15",
            "user_pottname" => "bail|required|string|regex:/^[A-Za-z0-9_.]+$/|max:15",
            "investor_id" => "bail|required",
            "user_language" => "bail|required|max:3",
            "app_type" => "bail|required|max:8",
            "app_version_code" => "bail|required|integer",
            // ADD ANY OTHER REQUIRED INPUTS FROM HERE
            "user_password" => "bail|required|string",
            "stockownership_id" => "bail|required|string",
            "transfer_quantity" => "bail|required|integer",
            "receiver_pottname" => "bail|required|string|regex:/^[A-Za-z0-9_.]+$/|max:15",
        ]);

        // MAKING SURE THE REQUEST AND USER IS VALIDATED
        $validation_response = UtilController::validateUserWithAuthToken($request, auth()->user(), "get-info-on-apps");
        if(!empty($validation_response["status"])){
            return response($validation_response);
        } else {
            $user = $validation_response;
        }

        if (!Hash::check($request->user_password, $user->password)) {
            return response([
                "status" => 3, 
                "message" => "Invalid Password"
            ]);
        }

        
        /*
        |**************************************************************************
        | VALIDATION ENDED 
        |**************************************************************************
        */

        // GETTING THE STOCK OWNERSHIP
        $stockownership = StockOwnership::where("stockownership_user_investor_id", $user->investor_id)->where('stockownership_sys_id', $request->stockownership_id)->first();
        if($stockownership == null || empty($stockownership->stockownership_business_id)){
            return response([
                "status" => 3, 
                "message" => "Ownership not verified",
                "government_verification_is_on" => false,
                "media_allowed" => intval(config('app.canpostpicsandvids')),
                "user_android_app_max_vc" => intval(config('app.androidmaxvc')),
                "user_android_app_force_update" => boolval(config('app.androidforceupdatetomaxvc')),
                "phone_verification_is_on" => boolval(config('app.phoneverificationrequiredstatus'))
            ]);
        }

        $business = Business::where('business_sys_id', $stockownership->stockownership_business_id)->first();
        if($business == null || empty($business->business_registration_number)){
            return response([
                "status" => 3, 
                "message" => "Business not found",
                "government_verification_is_on" => false,
                "media_allowed" => intval(config('app.canpostpicsandvids')),
                "user_android_app_max_vc" => intval(config('app.androidmaxvc')),
                "user_android_app_force_update" => boolval(config('app.androidforceupdatetomaxvc')),
                "phone_verification_is_on" => boolval(config('app.phoneverificationrequiredstatus'))
            ]);
        }

        if($stockownership->stockownership_flagged){
            return response([
                "status" => 3, 
                "message" => "Your stock is flagged.",
                "government_verification_is_on" => false,
                "media_allowed" => intval(config('app.canpostpicsandvids')),
                "user_android_app_max_vc" => intval(config('app.androidmaxvc')),
                "user_android_app_force_update" => boolval(config('app.androidforceupdatetomaxvc')),
                "phone_verification_is_on" => boolval(config('app.phoneverificationrequiredstatus'))
            ]);
        }

        // CHECKING IF THE QUANTITY OF STOCKS TO BE SENT EXISTS
        if($stockownership->stockownership_stocks_quantity < intval($request->transfer_quantity) || $request->transfer_quantity < 1){
            return response([
                "status" => 3, 
                "message" => "Insufficient stocks quantity",
                "government_verification_is_on" => false,
                "media_allowed" => intval(config('app.canpostpicsandvids')),
                "user_android_app_max_vc" => intval(config('app.androidmaxvc')),
                "user_android_app_force_update" => boolval(config('app.androidforceupdatetomaxvc')),
                "phone_verification_is_on" => boolval(config('app.phoneverificationrequiredstatus'))
            ]);
        }
        

        // CHECKING THE RECEIVER EXISTS
        if(empty($request->receiver_pottname) || !UtilController::pottnameIsTaken($request->receiver_pottname)){
            return response([
                "status" => 3, 
                "message" => "Receiver not found",
                "government_verification_is_on" => false,
                "media_allowed" => intval(config('app.canpostpicsandvids')),
                "user_android_app_max_vc" => intval(config('app.androidmaxvc')),
                "user_android_app_force_update" => boolval(config('app.androidforceupdatetomaxvc')),
                "phone_verification_is_on" => boolval(config('app.phoneverificationrequiredstatus'))
            ]);
        }
        

        $info_1 = "You are transfering " . $request->transfer_quantity . " stocks of " . $business->business_full_name;
        
        // CALCULATING PROCESSING FEE
        $processing_fee_usd = floatval(config('app.transfer_processing_fee_usd'));
        $currency_local = Currency::where("currency_country_id", '=', $user->user_country_id)->first();

        if($user->user_country_id == 81){ // GHANA
            $processing_fee_local = ($processing_fee_usd * floatval(config('app.to_cedi')));
            $processing_fee_local_with_currency_sign = "Gh¢" . ($processing_fee_usd * floatval(config('app.to_cedi')));
            $rate = "$1 = " . "Gh¢" . floatval(config('app.to_cedi'));
            $rate_no_sign = floatval(config('app.to_cedi'));
            $payment_gateway_amount_cents_or_pesewas = $processing_fee_local * 100;
        } else {
            $processing_fee_local = $processing_fee_usd;
            $processing_fee_local_with_currency_sign = "$" . $processing_fee_usd;
            $rate = "$1 = " . "$1";
            $rate_no_sign = 1.00;
            $payment_gateway_amount_cents_or_pesewas = $processing_fee_local * 100;
        }

        // RECORDING THE TRANSFER
        $stockTransferData["stocktransfer_sys_id"] = "stS" . $user->user_pottname . "R" . $request->receiver_pottname . date("YmdHis");
        $stockTransferData["stocktransfer_stocks_quantity"] = intval($request->transfer_quantity);
        $stockTransferData["stocktransfer_receiver_pottname"] = $request->receiver_pottname;
        $stockTransferData["stocktransfer_total_cost_usd_value_of_shares_transfer"] = ($stockownership->stockownership_total_cost_usd/$stockownership->stockownership_stocks_quantity) * intval($request->transfer_quantity);
        $stockTransferData["stocktransfer_sender_investor_id"] = $user->investor_id;
        $stockTransferData["stocktransfer_business_id"] = $stockownership->stockownership_business_id;
        $stockTransferData["stocktransfer_rate_cedi_to_usd"] = $rate_no_sign;
        $stockTransferData["stocktransfer_processing_fee_usd"] = $processing_fee_usd;
        $stockTransferData["stocktransfer_processing_local_currency_paid_in_amt"] = $processing_fee_local;
        $stockTransferData["st_processingfee_curr_paid_in_id"] = $currency_local->currency_id;
        $stockTransferData["stocktransfer_flagged"] = true;
        $stockTransferData["stocktransfer_flagged_reason"] = "unpaid";
        $stockTransferData["stocktransfer_payment_gateway_status"] = false;
        $stockTransferData["stocktransfer_payment_gateway_info"] = "unpaid";
        $stockTransferData["stockstransfers_processed_reason"] = "";
        StockTransfer::create($stockTransferData);

        // TESTING
        /*
        $data = array(
            "info_1" => $info_1,
            "transanction_id" => $stockTransferData['stocktransfer_sys_id'],
            "share_name" => $business->business_full_name,
            "share_quantity" => $request->transfer_quantity,
            "transfer_fee_cedis_no_sign" => $processing_fee_local,
            "transfer_fee_cedis_with_sign" => $processing_fee_local_with_currency_sign,
            "rate" => $rate,
            "rate_no_sign" => $rate,
            "overall_total_usd" => $processing_fee_usd,
            "overall_total_local_currency" => $processing_fee_local_with_currency_sign,
            "overall_total_local_currency_floatval" => $processing_fee_local,
            "payment_gateway_amount_in_pesewas_or_cents_intval" => 100,
            "payment_gateway_currency" => "GHS",
            "mobile_money_number" => config('app.mtnghanamomonum'),
            "mobile_money_name" => config('app.mtnghanamomoaccname')
        );
        */
        
        $data = array(
            "info_1" => $info_1,
            "transanction_id" => $stockTransferData['stocktransfer_sys_id'],
            "share_name" => $business->business_full_name,
            "share_quantity" => $request->transfer_quantity,
            "transfer_fee_cedis_no_sign" => $processing_fee_local,
            "transfer_fee_cedis_with_sign" => $processing_fee_local_with_currency_sign,
            "rate" => $rate,
            "rate_no_sign" => $rate,
            "overall_total_usd" => $processing_fee_usd,
            "overall_total_local_currency" => $processing_fee_local_with_currency_sign,
            "overall_total_local_currency_floatval" => $processing_fee_local,
            "payment_gateway_amount_in_pesewas_or_cents_intval" => $payment_gateway_amount_cents_or_pesewas,
            "payment_gateway_currency" => $currency_local->currency_short_name,
            "mobile_money_number" => config('app.mtnghanamomonum'),
            "mobile_money_name" => config('app.mtnghanamomoaccname')
        );


        return response([
            "status" => 1, 
            "message" => "success",
            "data" => $data,
            "government_verification_is_on" => false,
            "media_allowed" => intval(config('app.canpostpicsandvids')),
            "user_android_app_max_vc" => intval(config('app.androidmaxvc')),
            "user_android_app_force_update" => boolval(config('app.androidforceupdatetomaxvc')),
            "phone_verification_is_on" => boolval(config('app.phoneverificationrequiredstatus'))
        ]);
    }


    public function sellBackStocks(Request $request)
    {
        /*
        |**************************************************************************
        | VALIDATION STARTS 
        |**************************************************************************
        */
        // MAKING SURE THE INPUT HAS THE EXPECTED VALUES
        $validatedData = $request->validate([
            "user_phone_number" => "bail|required|regex:/^\+\d{10,15}$/|min:10|max:15",
            "user_pottname" => "bail|required|string|regex:/^[A-Za-z0-9_.]+$/|max:15",
            "investor_id" => "bail|required",
            "user_language" => "bail|required|max:3",
            "app_type" => "bail|required|max:8",
            "app_version_code" => "bail|required|integer",
            // ADD ANY OTHER REQUIRED INPUTS FROM HERE
            "user_password" => "bail|required|string",
            "stockownership_id" => "bail|required|string",
            "transfer_quantity" => "bail|required|integer",
            "bank_or_network_name" => "bail|required|string",
            "acc_name" => "bail|required|string",
            "acc_number" => "bail|required|string",
            "routing_number" => "string",
        ]);

        // MAKING SURE THE REQUEST AND USER IS VALIDATED
        $validation_response = UtilController::validateUserWithAuthToken($request, auth()->user(), "get-info-on-apps");
        if(!empty($validation_response["status"])){
            return response($validation_response);
        } else {
            $user = $validation_response;
        }

        if (!Hash::check($request->user_password, $user->password)) {
            return response([
                "status" => 3, 
                "message" => "Invalid Password"
            ]);
        }
        /*
        |**************************************************************************
        | VALIDATION ENDED 
        |**************************************************************************
        */


        // CALCULATING PROCESSING FEE
        $processing_fee_usd = floatval(config('app.transfer_processing_fee_usd'));
        $currency_local = Currency::where("currency_country_id", '=', $user->user_country_id)->first();

        if($user->user_country_id == 81){ // GHANA
            $processing_fee_local = ($processing_fee_usd * floatval(config('app.to_cedi')));
            $processing_fee_local_with_currency_sign = "Gh¢" . ($processing_fee_usd * floatval(config('app.to_cedi')));
            $rate = "$1 = " . "Gh¢" . floatval(config('app.to_cedi'));
            $rate_no_sign = floatval(config('app.to_cedi'));
            $local_currency = "Gh¢";
        } else {
            $processing_fee_local = $processing_fee_usd;
            $processing_fee_local_with_currency_sign = "$" . $processing_fee_usd;
            $rate = "$1 = " . "$1";
            $rate_no_sign = 1;
            $local_currency = "$";
        }
        
        // GETTING THE STOCK OWNERSHIP
        $stockownership = StockOwnership::where("stockownership_user_investor_id", $user->investor_id)->where('stockownership_sys_id', $request->stockownership_id)->first();
        if($stockownership == null || empty($stockownership->stockownership_business_id)){
            return response([
                "status" => 3, 
                "message" => "Ownership not verified",
                "government_verification_is_on" => false,
                "media_allowed" => intval(config('app.canpostpicsandvids')),
                "user_android_app_max_vc" => intval(config('app.androidmaxvc')),
                "user_android_app_force_update" => boolval(config('app.androidforceupdatetomaxvc')),
                "phone_verification_is_on" => boolval(config('app.phoneverificationrequiredstatus'))
            ]);
        }

        $business = Business::where('business_sys_id', $stockownership->stockownership_business_id)->first();
        if($business == null || empty($business->business_registration_number)){
            return response([
                "status" => 3, 
                "message" => "Business not found",
                "government_verification_is_on" => false,
                "media_allowed" => intval(config('app.canpostpicsandvids')),
                "user_android_app_max_vc" => intval(config('app.androidmaxvc')),
                "user_android_app_force_update" => boolval(config('app.androidforceupdatetomaxvc')),
                "phone_verification_is_on" => boolval(config('app.phoneverificationrequiredstatus'))
            ]);
        }

        if($stockownership->stockownership_flagged){
            return response([
                "status" => 3, 
                "message" => "Your stock is flagged.",
                "government_verification_is_on" => false,
                "media_allowed" => intval(config('app.canpostpicsandvids')),
                "user_android_app_max_vc" => intval(config('app.androidmaxvc')),
                "user_android_app_force_update" => boolval(config('app.androidforceupdatetomaxvc')),
                "phone_verification_is_on" => boolval(config('app.phoneverificationrequiredstatus'))
            ]);
        }

        // CHECKING IF THE QUANTITY OF STOCKS TO BE SENT EXISTS
        if($stockownership->stockownership_stocks_quantity < intval($request->transfer_quantity) || $request->transfer_quantity < 1){
            return response([
                "status" => 3, 
                "message" => "Insufficient stocks quantity",
                "government_verification_is_on" => false,
                "media_allowed" => intval(config('app.canpostpicsandvids')),
                "user_android_app_max_vc" => intval(config('app.androidmaxvc')),
                "user_android_app_force_update" => boolval(config('app.androidforceupdatetomaxvc')),
                "phone_verification_is_on" => boolval(config('app.phoneverificationrequiredstatus'))
            ]);
        }
        
        // REMOVING SHARES
        $cost_per_stock = $stockownership->stockownership_total_cost_usd/$stockownership->stockownership_stocks_quantity;

        $stockownership->stockownership_total_cost_usd = $cost_per_stock * ($stockownership->stockownership_stocks_quantity - intval($request->transfer_quantity));
        $stockownership->stockownership_stocks_quantity = $stockownership->stockownership_stocks_quantity - intval($request->transfer_quantity);
        $stockownership->save();
        
        $sellback_payout = $business->buyback_offer_usd * $rate_no_sign * intval($request->transfer_quantity);

        // RECORDING THE TRANSFER
        $stockSellbackData["stocksellback_sys_id"] = "sbS" . $user->user_pottname . date("YmdHis");
        $stockSellbackData["stocksellback_stocks_quantity"] = intval($request->transfer_quantity);
        $stockSellbackData["stocksellback_buyback_offer_per_stock_usd"] = $business->buyback_offer_usd;
        $stockSellbackData["stocksellback_rate_dollar_to_local_with_no_signs"] = $rate_no_sign;
        $stockSellbackData["stocksellback_payout_amt_local_currency_paid_in"] = $sellback_payout;
        $stockSellbackData["stocksellback_processing_fee_usd"] = $processing_fee_usd;
        $stockSellbackData["stocksellback_local_currency_paid_in_id"] = $currency_local->currency_id;
        $stockSellbackData["stocksellback_receiving_bank_or_momo_name"] = $request->bank_or_network_name;
        $stockSellbackData["stocksellback_receiving_bank_routing_number"] = $request->routing_number;
        $stockSellbackData["stocksellback_receiving_bank_or_momo_account_name"] = $request->acc_name;
        $stockSellbackData["stocksellback_receiving_bank_or_momo_account_number"] = $request->acc_number;
        $stockSellbackData["stocksellback_seller_investor_id"] = $user->investor_id;
        $stockSellbackData["stocksellback_business_id"] = $stockownership->stockownership_business_id;
        $stockSellbackData["stocksellback_flagged"] = false;
        $stockSellbackData["stocktransfer_flagged_reason"] = "";
        $stockSellbackData["stocksellback_processed"] = false;
        $stockSellbackData["stocksellback_processed_reason"] = "unpaid";
        StockSellBack::create($stockSellbackData);
        

        // SAVING IT AS A TRANSACTION
        $transactionData["transaction_sys_id"] =  "SSB-" . $stockSellbackData["stocksellback_sys_id"];
        $transactionData["transaction_transaction_type_id"] = 6;
        $transactionData["transaction_referenced_item_id"] = $stockSellbackData["stocksellback_sys_id"];
        $transactionData["transaction_user_investor_id"] = $user->investor_id;
        Transaction::create($transactionData);

        return response([
            "status" => 1, 
            "message" => "Sellback order placed and under review. Order ID: " .  $stockSellbackData['stocksellback_sys_id'],
            "transaction_id" => $stockSellbackData['stocksellback_sys_id'],
            "government_verification_is_on" => false,
            "media_allowed" => intval(config('app.canpostpicsandvids')),
            "user_android_app_max_vc" => intval(config('app.androidmaxvc')),
            "user_android_app_force_update" => boolval(config('app.androidforceupdatetomaxvc')),
            "phone_verification_is_on" => boolval(config('app.phoneverificationrequiredstatus'))
        ]);
    }

    public function sendWithdrawalRequest(Request $request)
    {
        /*
        |**************************************************************************
        | VALIDATION STARTS 
        |**************************************************************************
        */
        // MAKING SURE THE INPUT HAS THE EXPECTED VALUES
        $validatedData = $request->validate([
            "user_phone_number" => "bail|required|regex:/^\+\d{10,15}$/|min:10|max:15",
            "user_pottname" => "bail|required|string|regex:/^[A-Za-z0-9_.]+$/|max:15",
            "investor_id" => "bail|required",
            "user_language" => "bail|required|max:3",
            "app_type" => "bail|required|max:8",
            "app_version_code" => "bail|required|integer",
            // ADD ANY OTHER REQUIRED INPUTS FROM HERE
            "withdrawal_amt" => "bail|required|integer",
            "withdrawal_receiving_bank_or_momo_account_name" => "bail|required|string",
            "withdrawal_receiving_bank_or_momo_account_number" => "bail|required|string",
            "withdrawal_receiving_bank_or_momo_name" => "bail|required|string",
            "withdrawal_receiving_bank_routing_number" => "nullable|string",
            "password" => "bail|required|string",
        ]);

        // MAKING SURE THE REQUEST AND USER IS VALIDATED
        $validation_response = UtilController::validateUserWithAuthToken($request, auth()->user(), "withdraw-funds");
        if(!empty($validation_response["status"])){
            return response($validation_response);
        } else {
            $user = $validation_response;
        }

        $loginData["user_phone_number"] = $validatedData["user_phone_number"];
        $loginData["password"] = $validatedData["password"];
        
        // VALIDATING USER CREDENTIALS
        if (!Auth::guard('web')->attempt($loginData)) {
            return response([
                "status" => 3, 
                "message" => "Invalid Credentials",
                "government_verification_is_on" => false,
                "media_allowed" => intval(config('app.canpostpicsandvids')),
                "user_android_app_max_vc" => intval(config('app.androidmaxvc')),
                "user_android_app_force_update" => boolval(config('app.androidforceupdatetomaxvc')),
                "phone_verification_is_on" => boolval(config('app.phoneverificationrequiredstatus'))
            ]);
        }

        /*
        |**************************************************************************
        | VALIDATION ENDED 
        |**************************************************************************
        */

        if($user->user_wallet_usd < $request->withdrawal_amt){
            return response([
                "status" => 3, 
                "message" => "Insufficient funds",
                "government_verification_is_on" => false,
                "media_allowed" => intval(config('app.canpostpicsandvids')),
                "user_android_app_max_vc" => intval(config('app.androidmaxvc')),
                "user_android_app_force_update" => boolval(config('app.androidforceupdatetomaxvc')),
                "phone_verification_is_on" => boolval(config('app.phoneverificationrequiredstatus'))
            ]);
        }

        // UPDATING THE USER'S WALLET BALANCE
        $user->user_wallet_usd =  $user->user_wallet_usd - intval($request->withdrawal_amt);
        $user->save();

        // CREATING THE WITHDRAWALs
        $withdrawalData["withdrawal_sys_id"] =  "withdrawal-" . $user->user_pottname . substr($user->user_phone_number ,1,strlen($user->user_phone_number)) . UtilController::getRandomString(91);
        $withdrawalData["withdrawal_amt_usd"] = $request->withdrawal_amt;
        // CONVERTING TO USER'S LOCAL CURRENCY
        if($user->user_country_id == 81){ // GHANA
            $withdrawalData["withdrawal_amt_local"] = floatval($request->withdrawal_amt) * floatval(config('app.to_cedi'));
            $withdrawalData["withdrawal_rate"] = floatval(config('app.to_cedi'));
            $withdrawalData["withdrawal_local_currency_sign"] = "Gh¢";
        } else {
            $withdrawalData["withdrawal_amt_local"] = floatval($$request->withdrawal_amt);
            $withdrawalData["withdrawal_rate"] = 1.00;
            $withdrawalData["withdrawal_local_currency_sign"] = "$";
        }

        $withdrawalData["withdrawal_receiving_bank_or_momo_account_name"] = $request->withdrawal_receiving_bank_or_momo_account_name;
        $withdrawalData["withdrawal_receiving_bank_or_momo_account_number"] = $request->withdrawal_receiving_bank_or_momo_account_number;
        $withdrawalData["withdrawal_receiving_bank_or_momo_name"] = $request->withdrawal_receiving_bank_or_momo_name;
        $withdrawalData["withdrawal_receiving_bank_routing_number"] = $request->withdrawal_receiving_bank_routing_number;
        $withdrawalData["withdrawal_flagged"] = false;
        $withdrawalData["withdrawal_user_investor_id"] = $user->investor_id;
        $withdrawal = Withdrawal::create($withdrawalData);

        // SAVING IT AS A TRANSACTION
        $transactionData["transaction_sys_id"] =  "transaction-" . $withdrawalData["withdrawal_sys_id"];
        $transactionData["transaction_transaction_type_id"] = 1;
        $transactionData["transaction_referenced_item_id"] = $withdrawalData["withdrawal_sys_id"];
        $transactionData["transaction_user_investor_id"] = $user->investor_id;
        Transaction::create($transactionData);



        // SENDING MAIL TO FISHPOTT
        $the_amt = "$" . $withdrawal->withdrawal_amt_usd  . " | " . $withdrawalData["withdrawal_local_currency_sign"] . " " . $withdrawal->withdrawal_amt_local;
        $email_data = array(
            'user_pottname' => $user->user_pottname,
            'withdrawal_id' => $withdrawal->withdrawal_sys_id,
            'amount' => $the_amt,
            'withdrawal_receiving_bank_or_momo_account_name' => $request->withdrawal_receiving_bank_or_momo_account_name,
            'withdrawal_receiving_bank_or_momo_account_number' => $request->withdrawal_receiving_bank_or_momo_account_number,
            'withdrawal_receiving_bank_or_momo_name' => $request->withdrawal_receiving_bank_or_momo_name,
            'withdrawal_receiving_bank_routing_number' => $request->withdrawal_receiving_bank_routing_number,
            'amount' => $the_amt,
            'time' => date("F j, Y, g:i a")
        );

        Mail::to(config('app.fishpott_email'))->send(new WithdrawalMail($email_data));

        $data = array(
            "new_wallet_bal" => $user->user_wallet_usd
        );

        return response([
            "status" => 1, 
            "message" => "Withdrawal request sent. You will be notified when it's paid. Your new balance is $" . $user->user_wallet_usd,
            "data" => $data,
            "government_verification_is_on" => false,
            "media_allowed" => intval(config('app.canpostpicsandvids')),
            "user_android_app_max_vc" => intval(config('app.androidmaxvc')),
            "user_android_app_force_update" => boolval(config('app.androidforceupdatetomaxvc')),
            "phone_verification_is_on" => boolval(config('app.phoneverificationrequiredstatus'))
        ]);
    }

    
    /*
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    | THIS FUNCTION GETS A USER'S TRANSACTIONS
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    */
    
    public function getMyTransactions(Request $request)
    {
        /*
        |**************************************************************************
        | VALIDATION STARTS 
        |**************************************************************************
        */
        // MAKING SURE THE INPUT HAS THE EXPECTED VALUES
        $validatedData = $request->validate([
            "user_phone_number" => "bail|required|regex:/^\+\d{10,15}$/|min:10|max:15",
            "user_pottname" => "bail|required|string|regex:/^[A-Za-z0-9_.]+$/|max:15",
            "investor_id" => "bail|required",
            "user_language" => "bail|required|max:3",
            "app_type" => "bail|required|max:8",
            "app_version_code" => "bail|required|integer",
            // ADD ANY OTHER REQUIRED INPUTS FROM HERE
            
        ]);

        // MAKING SURE THE REQUEST AND USER IS VALIDATED
        $validation_response = UtilController::validateUserWithAuthToken($request, auth()->user(), "get-info-on-apps");
        if(!empty($validation_response["status"])){
            return response($validation_response);
        } else {
            $user = $validation_response;
        }
        /*
        |**************************************************************************
        | VALIDATION ENDED 
        |**************************************************************************
        */
        $data = array();
        $result = Transaction::where("transaction_user_investor_id", $user->investor_id)->get();

        foreach($result as $transaction){

            // WITHDRAWAL
            if($transaction->transaction_transaction_type_id == 1){
                $withdrawal = Withdrawal::where('withdrawal_sys_id', $transaction->transaction_referenced_item_id)->first();
                if($withdrawal != null){
                    if($withdrawal->withdrawal_paid == 0){
                        $the_status = "Pending";
                    } else if($withdrawal->withdrawal_paid == 1){
                        $the_status = "Paid";
                    } else if($withdrawal->withdrawal_paid == 2){
                        $the_status = "Cancelled";
                    } else {
                        $the_status = "Error";
                    }
                    $this_item = array(
                        'type' => "WITHDRAWAL",
                        'info_1' => $the_status,
                        'info_2' => $withdrawal->withdrawal_local_currency_sign . $withdrawal->withdrawal_amt_local,
                        'info_3' => $withdrawal->withdrawal_receiving_bank_or_momo_name,
                        'info_4' => $withdrawal->withdrawal_receiving_bank_or_momo_account_number,
                        'info_5' => date("j M y", strtotime($withdrawal->created_at)),
                        'info_6' => $withdrawal->withdrawal_sys_id,
                        'info_6' => $withdrawal->withdrawal_sys_id
                    );
                    array_push($data, $this_item);
                }
            } 

            // CREDIT
            if($transaction->transaction_transaction_type_id == 2){

            }

            // DIVIDEND
            if($transaction->transaction_transaction_type_id == 3){

            }

            // STOCK PURCHASE
            if($transaction->transaction_transaction_type_id == 4){
                $stockpurchase = StockPurchase::where('stockpurchase_sys_id', $transaction->transaction_referenced_item_id)->first();
                if($stockpurchase != null){
                    if($stockpurchase->stockpurchase_processed == 0){
                        $the_status = "Pending";
                    } else if($stockpurchase->stockpurchase_processed == 1){
                        $the_status = "Completed";
                    } else if($stockpurchase->stockpurchase_processed == 2){
                        $the_status = "Cancelled";
                    } else {
                        $the_status = "Error";
                    }

                    $currency = Currency::where('currency_id', $stockpurchase->stockpurchase_currency_paid_in_id)->first();
                    $business = Business::where('business_sys_id', $stockpurchase->stockpurchase_business_id)->first();

                    $this_item = array(
                        'type' => "SHARES PURCHASE",
                        'info_1' => $the_status,
                        'info_2' => $currency->currency_symbol . number_format($stockpurchase->stockpurchase_total_all_fees_in_currency_paid_in),
                        'info_3' => $business->business_full_name,
                        'info_4' => strval(number_format($stockpurchase->stockpurchase_stocks_quantity)),
                        'info_5' => date("j M y", strtotime($stockpurchase->created_at)),
                        'info_6' => $stockpurchase->stockpurchase_sys_id
                    );
                    array_push($data, $this_item);
                }
            }

            // STOCK TRANSFER
            if($transaction->transaction_transaction_type_id == 5){
                $stocktransfer = StockTransfer::where('stocktransfer_sys_id', $transaction->transaction_referenced_item_id)->first();
                if($stocktransfer != null){
                    if($stocktransfer->stockstransfers_processed == 0){
                        $the_status = "Pending";
                    } else if($stocktransfer->stockstransfers_processed == 1){
                        $the_status = "Completed";
                    } else if($stocktransfer->stockstransfers_processed == 2){
                        $the_status = "Cancelled";
                    } else {
                        $the_status = "Error";
                    }

                    $currency = Currency::where('currency_id', $stocktransfer->st_processingfee_curr_paid_in_id)->first();
                    $business = Business::where('business_sys_id', $stocktransfer->stocktransfer_business_id)->first();

                    $this_item = array(
                        'type' => "SHARES TRANSFER",
                        'info_1' => $the_status,
                        'info_2' => strval(number_format($stocktransfer->stocktransfer_stocks_quantity)),
                        'info_3' => $business->business_full_name,
                        'info_4' => $stocktransfer->stocktransfer_receiver_pottname,
                        'info_5' => date("j M y", strtotime($stocktransfer->created_at)),
                        'info_6' => $stocktransfer->stocktransfer_sys_id
                    );
                    array_push($data, $this_item);
                }

            }
        

            // STOCK SELLBACK
            if($transaction->transaction_transaction_type_id == 6){
                $stocksellBack = StockSellBack::where('stocksellback_sys_id', $transaction->transaction_referenced_item_id)->first();
                if($stocksellBack != null){
                    if($stocksellBack->stocksellback_processed == 0){
                        $the_status = "Pending";
                    } else if($stocksellBack->stocksellback_processed == 1){
                        $the_status = "Completed";
                    } else if($stocksellBack->stocksellback_processed == 2){
                        $the_status = "Cancelled";
                    } else {
                        $the_status = "Error";
                    }

                    $currency = Currency::where('currency_id', $stocksellBack->stocksellback_local_currency_paid_in_id)->first();
                    $business = Business::where('business_sys_id', $stocksellBack->stocksellback_business_id)->first();

                    $this_item = array(
                        'type' => "SHARES SELLBACK",
                        'info_1' => $the_status,
                        'info_2' => $currency->currency_symbol . number_format($stocksellBack->stocksellback_payout_amt_local_currency_paid_in),
                        'info_3' => $business->business_full_name,
                        'info_4' => strval(number_format($stocksellBack->stocksellback_stocks_quantity)),
                        'info_5' => date("j M y", strtotime($stocksellBack->created_at)),
                        'info_6' => $stocksellBack->stocksellback_sys_id
                    );
                    array_push($data, $this_item);
                }

            }
        }

        $data = array_reverse($data);

        return response([
            "status" => 1, 
            "message" => "success",
            "data" => $data,
            "government_verification_is_on" => false,
            "media_allowed" => intval(config('app.canpostpicsandvids')),
            "user_android_app_max_vc" => intval(config('app.androidmaxvc')),
            "user_android_app_force_update" => boolval(config('app.androidforceupdatetomaxvc')),
            "phone_verification_is_on" => boolval(config('app.phoneverificationrequiredstatus'))
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    | THIS FUNCTION GETS A USER'S INVESTMENTS
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    */
    
    public function getMyInvestments(Request $request)
    {
        /*
        |**************************************************************************
        | VALIDATION STARTS 
        |**************************************************************************
        */
        // MAKING SURE THE INPUT HAS THE EXPECTED VALUES
        $validatedData = $request->validate([
            "user_phone_number" => "bail|required|regex:/^\+\d{10,15}$/|min:10|max:15",
            "user_pottname" => "bail|required|string|regex:/^[A-Za-z0-9_.]+$/|max:15",
            "investor_id" => "bail|required",
            "user_language" => "bail|required|max:3",
            "app_type" => "bail|required|max:8",
            "app_version_code" => "bail|required|integer",
            // ADD ANY OTHER REQUIRED INPUTS FROM HERE
        ]);

        // MAKING SURE THE REQUEST AND USER IS VALIDATED
        $validation_response = UtilController::validateUserWithAuthToken($request, auth()->user(), "get-info-on-apps");
        if(!empty($validation_response["status"])){
            return response($validation_response);
        } else {
            $user = $validation_response;
        }
        /*
        |**************************************************************************
        | VALIDATION ENDED 
        |**************************************************************************
        */

        // CALCULATING PROCESSING FEE
        $processing_fee_usd = floatval(config('app.transfer_processing_fee_usd'));
        $currency_local = Currency::where("currency_country_id", '=', $user->user_country_id)->first();

        if($user->user_country_id == 81){ // GHANA
            $processing_fee_local = ($processing_fee_usd * floatval(config('app.to_cedi')));
            $processing_fee_local_with_currency_sign = "Gh¢" . ($processing_fee_usd * floatval(config('app.to_cedi')));
            $rate = "$1 = " . "Gh¢" . floatval(config('app.to_cedi'));
            $rate_no_sign = floatval(config('app.to_cedi'));
            $local_currency = "Gh¢";
        } else {
            $processing_fee_local = $processing_fee_usd;
            $processing_fee_local_with_currency_sign = "$" . $processing_fee_usd;
            $rate = "$1 = " . "$1";
            $rate_no_sign = 1;
            $local_currency = "$";
        }

        $data = array();
        $result = StockOwnership::where("stockownership_user_investor_id", $user->investor_id)->get();

        foreach($result as $stockownership){
            // STOCK PURCHASE
            if($stockownership->stockownership_flagged != 0){
                continue;
            }
            //echo "here 1 \n stockownership->stockownership_business_id: " . $stockownership->stockownership_business_id;
            $business = Business::where('business_sys_id', $stockownership->stockownership_business_id)->first();
            if($business == null){
                continue;
            }
            
            $stockvalue = StockValue::where('stockvalue_business_id', $stockownership->stockownership_business_id)->first();
            if($stockvalue == null){
                $the_stockvalue = "$" . number_format($business->business_price_per_stock_usd);
                $the_stockvalue_numeric = floatval($business->business_price_per_stock_usd);
            } else {
                $the_stockvalue = "$" . number_format($stockvalue->stockvalue_value_per_stock_usd);
                $the_stockvalue_numeric = floatval($stockvalue->stockvalue_value_per_stock_usd);
            }

            // GETTING THE COST-PER-SHARE
            $this_cost_per_share_usd = $stockownership->stockownership_total_cost_usd/$stockownership->stockownership_stocks_quantity;

            // GETTING VALUE PHRASE
            if($the_stockvalue_numeric == -1){
                $value_phrase = "Value Unchanged";
            } else if($this_cost_per_share_usd > $the_stockvalue_numeric){
                $value_phrase = "Value Loss";
            } else if($this_cost_per_share_usd < $the_stockvalue_numeric){
                $value_phrase = "Value Profit";
            } else if($this_cost_per_share_usd == $the_stockvalue_numeric){
                $value_phrase = "Value Unchanged";
            } else {
                $value_phrase = "Value Unchanged";
            }

            $this_item = array(
                'stock_id' => $stockownership->stockownership_sys_id,
                'business_id' => $business->business_sys_id,
                'business_name' => $business->business_full_name . " Stock",
                'buyback_usd' => number_format($business->buyback_offer_usd),
                'buyback_local' => $business->buyback_offer_usd * $rate_no_sign,
                'cost_per_share_usd' => "$" . number_format($this_cost_per_share_usd),
                'value_per_share_usd' => $the_stockvalue,
                'quantity_of_stocks' => number_format($stockownership->stockownership_stocks_quantity),
                'value_phrase' => $value_phrase,
                'ai_info' => "Pott Intelligence feedback not available"
            );
            array_push($data, $this_item);
        }
        
        $data = array_reverse($data);

        return response([
            "status" => 1, 
            "message" => "success",
            "transfer_fee_local_with_sign" => $processing_fee_local_with_currency_sign,
            "transfer_fee_local" => $processing_fee_local,
            "rate" => $rate,
            "rate_no_sign" => $rate_no_sign,
            "local_currency" => $local_currency,
            "data" => $data,
            "government_verification_is_on" => false,
            "media_allowed" => intval(config('app.canpostpicsandvids')),
            "user_android_app_max_vc" => intval(config('app.androidmaxvc')),
            "user_android_app_force_update" => boolval(config('app.androidforceupdatetomaxvc')),
            "phone_verification_is_on" => boolval(config('app.phoneverificationrequiredstatus'))
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    | THIS FUNCTION GETS FINDS A BUSINESS USING A FIND CODE
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    */
    
    public function findBusiness(Request $request)
    {
        /*
        |**************************************************************************
        | VALIDATION STARTS 
        |**************************************************************************
        */
        // MAKING SURE THE INPUT HAS THE EXPECTED VALUES
        $validatedData = $request->validate([
            "user_phone_number" => "bail|required|regex:/^\+\d{10,15}$/|min:10|max:15",
            "user_pottname" => "bail|required|string|regex:/^[A-Za-z0-9_.]+$/|max:15",
            "investor_id" => "bail|required",
            "user_language" => "bail|required|max:3",
            "app_type" => "bail|required|max:8",
            "app_version_code" => "bail|required|integer",
            // ADD ANY OTHER REQUIRED INPUTS FROM HERE
            "business_id" => "bail|required",
        ]);

        // MAKING SURE THE REQUEST AND USER IS VALIDATED
        $validation_response = UtilController::validateUserWithAuthToken($request, auth()->user(), "get-info-on-apps");
        if(!empty($validation_response["status"])){
            return response($validation_response);
        } else {
            $user = $validation_response;
        }
        /*
        |**************************************************************************
        | VALIDATION ENDED 
        |**************************************************************************
        */

        $suggestion = Suggestion::where('suggestion_directed_at_user_business_find_code', $request->business_id)->first();
        if($suggestion == null){
            $suggestion = Business::where('business_sys_id', $request->business_id)->orWhere('business_find_code', $request->business_id)->orWhere('business_pottname', $request->business_id)->first();
        } else {
            $suggestion = Business::where('business_sys_id', $suggestion->suggestion_item_reference_id)->first();
        }

        if($suggestion === null){
            return response([
                "status" => 3, 
                "message" => "Business not found",
                "government_verification_is_on" => false,
                "media_allowed" => intval(config('app.canpostpicsandvids')),
                "user_android_app_max_vc" => intval(config('app.androidmaxvc')),
                "user_android_app_force_update" => boolval(config('app.androidforceupdatetomaxvc')),
                "phone_verification_is_on" => boolval(config('app.phoneverificationrequiredstatus'))
            ]);
        }
        $message = "business";
        $country = Country::where('country_id', '=', $suggestion->business_country_id)->first();
        if($country === null){
            return response([
                "status" => 3, 
                "message" => "Country validation error.",
                "government_verification_is_on" => false,
                "media_allowed" => intval(config('app.canpostpicsandvids')),
                "user_android_app_max_vc" => intval(config('app.androidmaxvc')),
                "user_android_app_force_update" => boolval(config('app.androidforceupdatetomaxvc')),
                "phone_verification_is_on" => boolval(config('app.phoneverificationrequiredstatus'))
            ]);
        }

        // REFORMATTING NEEDED VALUES
        $suggestion->business_country = $country->country_real_name;
        $suggestion->business_logo = config('app.url') . '/uploads/logos/' . $suggestion->business_logo;
        $suggestion->business_pitch_video = config('app.url') . '/uploads/pitchvideos/' . $suggestion->business_pitch_video;
        $suggestion->business_full_financial_report_pdf_url = config('app.url') . '/uploads/financedata/' . $suggestion->business_full_financial_report_pdf_url;
        $suggestion->business_net_worth_usd = "$" . UtilController::formatNumberShort($suggestion->business_net_worth_usd);
        $suggestion->business_lastyr_revenue_usd = "$" . UtilController::formatNumberShort($suggestion->business_lastyr_revenue_usd);
        $suggestion->business_lastyr_profit_or_loss_usd = "$" . UtilController::formatNumberShort($suggestion->business_lastyr_profit_or_loss_usd);
        $suggestion->business_debt_usd = "$" . UtilController::formatNumberShort($suggestion->business_debt_usd);
        $suggestion->business_cash_on_hand_usd = "$" . UtilController::formatNumberShort($suggestion->business_cash_on_hand_usd);
        $suggestion->business_investments_amount_needed_usd = "$" . UtilController::formatNumberShort($suggestion->business_investments_amount_needed_usd);

        // CHECKING IF USER CAN BUY SHARES OR NOT FROM BUSINESS
        $suggestion2 =  Suggestion::where('suggestion_directed_at_user_investor_id', '=', $user->investor_id)->where('suggestion_item_reference_id', $suggestion->business_sys_id)->where('suggestion_flagged', false)->orderBy('created_at', 'desc')->first();

        if ($suggestion2 != null && UtilController::getDateDiff($suggestion2->created_at, date('Y-m-d H:i:s'), "hours") < intval(config('app.timedurationinhoursforbusinesssuggestionstobeavailable'))) {
            $can_buy = "yes";
            $invest_message = "Awesome. You found a business that you can invest in. Simply click the 'Buy Shares' button to proceed";
        } else {
            $can_buy = "no";
            $invest_message = "Nice. You found a business. Unfortunately, you have not been invited to buy shares in the business. For exceptions, please contact us on " . config('app.fishpott_email');
        }
        return response([
            "status" => 1, 
            "message" => $message,
            "invest_message" => $invest_message,
            "can_buy" => $can_buy,
            "data" => $suggestion,
            "government_verification_is_on" => false,
            "media_allowed" => intval(config('app.canpostpicsandvids')),
            "user_android_app_max_vc" => intval(config('app.androidmaxvc')),
            "user_android_app_force_update" => boolval(config('app.androidforceupdatetomaxvc')),
            "phone_verification_is_on" => boolval(config('app.phoneverificationrequiredstatus'))
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    | THIS FUNCTION SENDS THE RECENT USER INFO TO FRONTEND
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    */
    
    public function updateAndGetRecentUserInfo(Request $request)
    {
        /*
        |**************************************************************************
        | VALIDATION STARTS 
        |**************************************************************************
        */
        // MAKING SURE THE INPUT HAS THE EXPECTED VALUES
        $validatedData = $request->validate([
            "user_phone_number" => "bail|required|regex:/^\+\d{10,15}$/|min:10|max:15",
            "user_pottname" => "bail|required|string|regex:/^[A-Za-z0-9_.]+$/|max:15",
            "investor_id" => "bail|required",
            "user_language" => "bail|required|max:3",
            "app_type" => "bail|required|max:8",
            "app_version_code" => "bail|required|integer",
            // ADD ANY OTHER REQUIRED INPUTS FROM HERE
            "fcm_token" => "nullable|string",
        ]);

        // MAKING SURE THE REQUEST AND USER IS VALIDATED
        $validation_response = UtilController::validateUserWithAuthToken($request, auth()->user(), "get-info-in-background");
        if(!empty($validation_response["status"])){
            return response($validation_response);
        } else {
            $user = $validation_response;
        }
        
        if(strtoupper($request->app_type) == "ANDROID" && !empty($request->fcm_token)){
            $user->user_fcm_token_android = $request->fcm_token;
            $user->save();
        }

        if($request->app_type == "ios" && !empty($request->fcm_token)){
            $user->user_fcm_token_ios = $request->fcm_token;
            $user->save();
        }
        /*
        |**************************************************************************
        | VALIDATION ENDED 
        |**************************************************************************
        */

        // SAVING FCM TOKEN FROM USER

        // GETTING ALL USERS
        if($request->app_type == "ios"){
            $all_users = User::count() + 135;   
            $user_net_worth_usd = "$" . strval(UtilController::formatNumberShort($user->user_net_worth_usd));
            $user_pott_intelligence = $user->user_pott_intelligence . "%";
            $user_pott_position = UtilController::formatNumberWithPositionAffix($user->user_pott_position);
        } else {
            $all_users = User::count() + 135;   
            $user_net_worth_usd = "$" . strval(UtilController::formatNumberShort($user->user_net_worth_usd)) . " -- Pott Net Worth";
            $user_pott_intelligence = $user->user_pott_intelligence . "% -- Pott Intelligence";
            $user_pott_position = UtilController::formatNumberWithPositionAffix($user->user_pott_position) . " - Your FishPott ranks at this position currently out of " . strval($all_users) . " FishPotts";
        }

        if($user->user_pott_intelligence < 5){
            $ai_info = "Your Pott-Intelligence is currently too low to show an accurate view of your personality. Keep answering drills to train your FishPott.";
        } else {
            $ai_info = "This is your personality based on your drill answers. People who are in tune with wealth generation have a personality of at least 70% openness, 70% conscientiousness, 70% Extraversion, 70% Agreeableness, 70% Neuroticism.";
        }
        
        $data = array(
            "pott_networth" => $user_net_worth_usd, 
            "pott_intelligence" => $user_pott_intelligence, 
            "pott_position" => $user_pott_position, 
            "all_potts" => strval($all_users),
            "ai_info" => $ai_info, 
            "o" => $user->ocean_openness_to_experience . "%", 
            "c" => $user->ocean_conscientiousness . "%", 
            "e" => $user->ocean_extraversion . "%", 
            "a" => $user->ocean_agreeableness . "%", 
            "n" => $user->ocean_neuroticism . "%"
        );

        return response([
            "status" => 1, 
            "message" => "success",
            "data" => $data,
            "government_verification_is_on" => false,
            "media_allowed" => intval(config('app.canpostpicsandvids')),
            "user_android_app_max_vc" => intval(config('app.androidmaxvc')),
            "user_android_app_force_update" => boolval(config('app.androidforceupdatetomaxvc')),
            "phone_verification_is_on" => boolval(config('app.phoneverificationrequiredstatus'))
        ]);
    }

    
    /*
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    | THIS FUNCTION REGISTES A USER AND PROVIDES THEM WITH AN ACCESS TOKEN
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    */
    public function updateEmailAlertSettings(Request $request)
    {

        // MAKING SURE THE INPUT HAS THE EXPECTED VALUES
        $validatedData = $request->validate([
            "subscriber_user_email" => "bail|required|email|max:100",
            "subscriber_user_phone" => "bail|required|max:15",
            "subscriber_user_pottname" => "bail|required|max:15",
            "subscribe_or_not" => "bail|required|max:3"
        ]);

        $user = User::where('user_pottname', $request->subscriber_user_pottname)->where('user_phone_number', $request->subscriber_user_phone)->where('user_email', $request->subscriber_user_email)->first();
        if($user != null && !empty($user->user_pottname)){
            $user->user_email_alerts_subscribed = boolval($request->subscribe_or_not);
            $user->save();    
        } 


        return response([
            "status" => 1, 
            "message" => "If your credentials are correct, your email alert settings have been updated"
        ]);
    }


    /*
    public function testFunc(Request $request)
    {

        $user = User::where('investor_id', 'testraylight233553663643kjhXiZCqyL42ugllVFRRb1AkXucRBjBycQsUgcT15LHT8QfN63bt2h5TVV3i7132C1Tuqzyz309l21l98SAtEArtN1h')->first();

        // GETTING DRILL ANSWERS
        $answers = DrillAnswer::select('drill_answer_drill_sys_id', 'drill_answer_number')
        ->where('drill_answer_user_investor_id', '=', "$user->investor_id")
        ->orderBy('drill_answer_id', 'desc')->take(30)->get();

        if(count($answers) < 7){
            return 1;
        }

        // INITIALIZING ARRAY
        $output_data_array = array('o' => 0,'c' => 0,'e' => 0,'a' => 0,'n' => 0);

        $count_answers = 0;
        foreach($answers as $answer){
            $this_drill = Drill::where('drill_sys_id', '=', $answer->drill_answer_drill_sys_id)->first();
            if($answer->drill_answer_number == 1){
                //echo "\n\ndrill answer 1: " . $this_drill->drill_answer_1_ocean;
                $this_raw_ocean_array = explode("#", $this_drill->drill_answer_1_ocean);
            } else if($answer->drill_answer_number == 2){
                //echo "\n\ndrill answer 2: " . $this_drill->drill_answer_2_ocean;
                $this_raw_ocean_array = explode("#", $this_drill->drill_answer_2_ocean);
            } else if($answer->drill_answer_number == 3){
                //echo "\n\ndrill answer 3: " . $this_drill->drill_answer_3_ocean;
                $this_raw_ocean_array = explode("#", $this_drill->drill_answer_3_ocean);
            } else if($answer->drill_answer_number == 4){
                //echo "\n\ndrill answer 4: " . $this_drill->drill_answer_4_ocean;
                $this_raw_ocean_array = explode("#", $this_drill->drill_answer_4_ocean);
            } else {
                continue;
            }

            if(count($this_raw_ocean_array) != 5){
                continue;
            }
            
            $output_data_array["o"] = $output_data_array["o"] + $this_raw_ocean_array[0];
            $output_data_array["c"] = $output_data_array["c"] + $this_raw_ocean_array[1];
            $output_data_array["e"] = $output_data_array["e"] + $this_raw_ocean_array[2];
            $output_data_array["a"] = $output_data_array["a"] + $this_raw_ocean_array[3];
            $output_data_array["n"] = $output_data_array["n"] + $this_raw_ocean_array[4];
            $count_answers++;
        }

        //echo "\n\n count_answers : " . $count_answers . "\n\n"; 
        //var_dump($output_data_array);

        if($count_answers >= 7){
            $o = $output_data_array["o"]/$count_answers;
            $c = $output_data_array["c"]/$count_answers;
            $e = $output_data_array["e"]/$count_answers;
            $a = $output_data_array["a"]/$count_answers;
            $n = $output_data_array["n"]/$count_answers;

            
            //echo "\n\n o : " . $o . "%\n\n"; 
            //echo "\n\n c : " . $c . "%\n\n"; 
            //echo "\n\n e : " . $e . "%\n\n"; 
            //echo "\n\n a : " . $a . "%\n\n"; 
            //echo "\n\n n : " . $n . "%\n\n";
            
            
            

            // FINDING A BUSINESS THAT IS 5% CLOSER IN PERSONALITY

            $business_id = DB::table('ai_stock_personas')
            ->select('ai_stock_personas.aistockpersona_stock_business_id')
            ->join('businesses', 'ai_stock_personas.aistockpersona_stock_business_id', '=', 'businesses.business_sys_id')
            ->whereBetween('aistockpersona_openness_to_experience', [$o-5, $o+5])
            ->orWhereBetween('aistockpersona_conscientiousness', [$c-5, $c+5])
            ->orWhereBetween('aistockpersona_extraversion', [$e-5, $e+5])
            ->orWhereBetween('aistockpersona_agreeableness', [$a-5, $a+5])
            ->orWhereBetween('aistockpersona_neuroticism', [$n-5, $n+5])
            ->orderBy('ai_stock_personas.created_at', 'desc')
            ->take(1)
            ->get();
            //echo "\n\n business_id : " . $business_id[0]->aistockpersona_stock_business_id . "\n\n"; 

            // CHECKING IF THE BUSINESS EXISTS
            if(empty($business_id[0])){
                return 1;
            }
            $business = Business::where('business_sys_id', $business_id[0]->aistockpersona_stock_business_id)->first();
            if($business == null){
                return 1;
            }

            // CHECKING IF USER EXISTS
            $pott_user = User::where('user_pottname', $user->user_pottname)->first();
            if($pott_user == null){
                return 1;
            }

            // CREATING THE SUGGESTION VALUE DATA FOR BUSINESS
            $suggestionData["suggestion_sys_id"] = "sug-" . $business->business_sys_id . date('YmdHis');
            $suggestionData["suggestion_item_reference_id"] = $business->business_sys_id;
            $suggestionData["suggestion_directed_at_user_investor_id"] = $user->investor_id;
            $suggestionData["suggestion_directed_at_user_business_find_code"] = $user->user_pottname . date('YmdHis');
            $suggestionData["suggestion_reason"] = "Your personality and how the stock of this business behaves align";
            $suggestionData["suggestion_suggestion_type_id"] = 2;
            $suggestionData["suggestion_passed_on_by_user"] = false;
            $suggestionData["suggestion_notification_sent"] = true;
            $suggestionData["suggestion_flagged"] = false;
            Suggestion::create($suggestionData);

            if(true){
            //if($send_notification){
                    UtilController::sendNotificationToUser(
                    config('app.firebase_notification_server_address_link'), 
                    config('app.firebase_notification_account_key'), 
                    array($user->user_fcm_token_android, $user->user_fcm_token_web, $user->user_fcm_token_ios),
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
            }
            
            //echo "\n\n business_id : " . $business_id[0]->aistockpersona_stock_business_id . "\n\n"; 

            return $suggestionData;
        } else {
            return 1;
        }

    }
    */

}
