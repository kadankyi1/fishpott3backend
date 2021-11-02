<?php

namespace App\Http\Controllers\version1;

use DB;
use DateTime;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\version1\User;
use App\Models\version1\Gender;
use App\Models\version1\Country;
use App\Models\version1\Language;
use App\Models\version1\ResetCode;
use App\Mail\version1\ResetCodeMail;
use App\Mail\version1\WithdrawalMail;
use App\Models\version1\Business;
use App\Models\version1\Currency;
use App\Models\version1\Drill;
use App\Models\version1\DrillAnswer;
use App\Models\version1\StockOwnership;
use App\Models\version1\StockPurchase;
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
            "user_gender" => "bail|required|max:6",
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
        $userData["user_scope"] = "get-info-on-apps get-business-suggestions answer-drills buy-business-stocks transfer-business-stocks withdraw-funds";
        $userData["user_phone_verification_requested"] = boolval(config('app.phoneverificationrequiredstatus'));
        $userData["user_id_verification_requested"] = boolval(config('app.idverificationrequiredstatus'));

        //$userData["ssssssss"] = $validatedData["user_surname"];

        $user1 = User::create($userData);
        
        // GENERATING THE ACCESS TOKEN FOR THE REGISTERED USER
        $accessToken = $user1->createToken("authToken", ["get-info-on-apps get-business-suggestions answer-drills buy-business-stocks transfer-business-stocks withdraw-funds"])->accessToken;


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
        $userData["user_initial_signup_approved"] = false;
        $userData["user_flagged"] = false;
        // SAVING APP TYPE VERSION CODE
        if($request->app_type == "ANDROID"){
            $userData["user_android_app_version_code"] = $validatedData["app_version_code"];
        } else if($request->app_type == "IOS"){
            $userData["user_ios_app_version_code"] = $validatedData["app_version_code"];
        } 
        $userData["user_app_version_code"] = $validatedData["app_version_code"];
        $userData["user_scope"] = "get-info-on-apps get-business-suggestions answer-drills buy-business-stocks transfer-business-stocks withdraw-funds";
        $userData["user_phone_verification_requested"] = boolval(config('app.phoneverificationrequiredstatus'));
        $userData["user_id_verification_requested"] = boolval(config('app.idverificationrequiredstatus'));

        $user1 = User::create($userData);
        
        // GENERATING THE ACCESS TOKEN FOR THE REGISTERED USER
        //$accessToken = $user1->createToken("authToken")->accessToken;
        $accessToken = $user1->createToken("authToken", ["get-info-on-apps get-business-suggestions answer-drills buy-business-stocks transfer-business-stocks withdraw-funds"])->accessToken;


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
        $accessToken = auth()->user()->createToken("authToken", ["get-info-on-apps get-business-suggestions answer-drills buy-business-stocks transfer-business-stocks withdraw-funds"])->accessToken;

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

        // CHECKING IF USER HAS A SUGGESTION IS BROADCASTING THAT IS NOT MORE THAN 1 HOURS OR NOT MARKED AS PASS ON
        $suggestion = UtilController::getSuggestionMadeToUser($user->investor_id);
        //echo "suggestion->suggestion_item_reference_id: " . $suggestion->suggestion_item_reference_id; exit;
        //echo " intval(config('app.timedurationinhoursforsuggestions')): " . intval(config('app.timedurationinhoursforsuggestions')); 
        //echo "\n hours passed: " . UtilController::getDateDiff($suggestion->created_at, date('Y-m-d H:i:s'), "hours"); exit;

        if ($suggestion != null && UtilController::getDateDiff($suggestion->created_at, date('Y-m-d H:i:s'), "hours") < intval(config('app.timedurationinhoursforsuggestions'))) {
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
        //echo "getSuggestionType: " . UtilController::getSuggestionType("suggestion_type_name", "Business", 1);
        //echo "suggestion->suggestion_item_reference_id: " . $suggestion->suggestion_item_reference_id; exit;
        if($suggestion->suggestion_suggestion_type_id == UtilController::getSuggestionType("suggestion_type_name", "Drill", 1)){
            $suggestion = Drill::where('drill_sys_id', $suggestion->suggestion_item_reference_id)->first();
            $message = "drill";
            $country_real_name = "";
        }
        
        /*
        else if($suggestion->suggestion_type == SuggestionTypes::where('suggestion_type_name', 'Business')){
            $suggestion = Business::where('business_sys_id', $suggestion->suggestion_item_reference_id)->first();
            $message = "business";
            $country = Country::where('country_id', '=', $suggestion->business_country_id)->first();
            if($country === null){
                return response([
                    "status" => 3, 
                    "message" => "Country validation error."
                ]);
            }
            $suggestion->business_country = $country->country_real_name;
            $suggestion->business_logo = config('app.url') . '/uploads/logos/' . $suggestion->business_logo;
            $suggestion->business_full_financial_report_pdf_url = config('app.url') . '/uploads/financedata/' . $suggestion->business_full_financial_report_pdf_url;
        }
        */

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
        $drillAnswer = DrillAnswer::where('drill_answer_sys_id', $drillAnswerData["drill_answer_sys_id"])->first();
        if($drillAnswer == null || empty($drillAnswer->drill_answer_sys_id)){
            DrillAnswer::create($drillAnswerData);
        } else {
            $default_msg = "You already answered this drill.";
        }

        // GETTING THE ANSWERS OF FRIENDS
        $answer_1_count = UtilController::getCountDrillAnswers(["drill_answer_sys_id", "drill_answer_number"], [$drillAnswerData['drill_answer_sys_id'], 1]);
        $answer_2_count = UtilController::getCountDrillAnswers(["drill_answer_sys_id", "drill_answer_number"], [$drillAnswerData['drill_answer_sys_id'], 2]);
        $answer_3_count = UtilController::getCountDrillAnswers(["drill_answer_sys_id", "drill_answer_number"], [$drillAnswerData['drill_answer_sys_id'], 3]);
        $answer_4_count = UtilController::getCountDrillAnswers(["drill_answer_sys_id", "drill_answer_number"], [$drillAnswerData['drill_answer_sys_id'], 4]);

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

        // GETTING THE QUANTITY OF SHARES
        $item_quantity = floor($request->investment_amt_in_dollars / $business->business_price_per_stock_usd);
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
        } else {
            $overall_total_local_currency_no_currency_sign = $overall_total_usd;
            $overall_total_local_currency = "$" . $overall_total_usd;
            $rate = "$1 = " . "$1";
            $rate_no_sign = 1;
        }

        // RECORDING THE POSSIBLE ORDER
        $stockPurchaseData["stockpurchase_sys_id"] = "stockpurchase-" . $user->user_pottname . substr($user->user_phone_number ,1,strlen($user->user_phone_number)) . "-" . date("Y-m-d-H-i-s") . "-" . UtilController::getRandomString(91);
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
        $stockPurchaseData["stockpurchase_payment_gateway_status"] = "";
        $stockPurchaseData["stockpurchase_payment_gateway_info"] = "";
        StockPurchase::create($stockPurchaseData);


        $data = array(
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
            "financial_yield_info" => $yield_info
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
            "stockpurchase_sys_id" => "bail|required|string",
            "stockpurchase_payment_gateway_status" => "bail|required|string",
            "paymenstockpurchase_payment_gateway_info" => "bail|required|string",
        ]);

        // MAKING SURE THE REQUEST AND USER IS VALIDATED
        $validation_response = UtilController::validateUserWithAuthToken($request, auth()->user(), "get-info-on-apps");
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

        // GETTING THE BUSINESS
        $stockpurchase = StockPurchase::where('stockpurchase_sys_id', $request->stockpurchase_sys_id)->first();
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
        $stockpurchase->stockpurchase_payment_gateway_status = $request->stockpurchase_payment_gateway_status;
        $stockpurchase->stockpurchase_payment_gateway_info = $request->paymenstockpurchase_payment_gateway_info;
        $stockpurchase->save();

        // SAVING IT AS A TRANSACTION
        $transaction = Transaction::where('transaction_referenced_item_id', $request->stockpurchase_sys_id)->first();
        if($transaction == null){
            $transactionData["transaction_sys_id"] =  "SP-" . $user->user_pottname . "-" . date("YmdHis") . UtilController::getRandomString(4);
            $transactionData["transaction_transaction_type_id"] = 4;
            $transactionData["transaction_referenced_item_id"] = $stockpurchase->stockpurchase_sys_id;
            $transactionData["transaction_user_investor_id"] = $user->investor_id;
            $transaction = Transaction::create($transactionData);
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
        if(!empty($validation_response["status"]) && trim($validation_response["status"]) == "error"){
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
                        'info_5' => date("n M y", strtotime($withdrawal->created_at)),
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
                        $the_status = "Paid";
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
                        'info_2' => $currency->currency_symbol . $stockpurchase->stockpurchase_total_all_fees_in_currency_paid_in,
                        'info_3' => $business->business_full_name,
                        'info_4' => strval($stockpurchase->stockpurchase_stocks_quantity),
                        'info_5' => date("n M y", strtotime($stockpurchase->created_at)),
                        'info_6' => $stockpurchase->stockpurchase_sys_id
                    );
                    array_push($data, $this_item);
                }

            }
        }
        
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
        $data = array();
        $result = StockOwnership::where("stockownership_user_investor_id", $user->investor_id)->get();

        foreach($result as $stockownership){
            // STOCK PURCHASE
            //echo "here 1 \n stockownership->stockownership_business_id: " . $stockownership->stockownership_business_id;
            $business = Business::where('business_sys_id', $stockownership->stockownership_business_id)->first();
            if($business == null){
                continue;
            }
            
            $stockvalue = StockValue::where('stockvalue_business_id', $stockownership->stockownership_business_id)->first();
            if($stockvalue == null){
                $the_stockvalue = "Unknown";
                $the_stockvalue_numeric = -1;
            } else {
                $the_stockvalue = "$" . $stockvalue->stockvalue_value_per_stock_usd;
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
                'business_id' => $business->business_sys_id,
                'business_name' => $business->business_full_name . " Stock",
                'cost_per_share_usd' => "$" . $this_cost_per_share_usd,
                'value_per_share_usd' => $the_stockvalue,
                'quantity_of_stocks' => $stockownership->stockownership_stocks_quantity,
                'value_phrase' => $value_phrase,
                'ai_info' => "Pott Intelligence feedback not available"
            );
            array_push($data, $this_item);
        }
        
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
        $suggestion2 =  Suggestion::where('suggestion_directed_at_user_investor_id', '=', $user->investor_id)->where('suggestion_item_reference_id', $suggestion->business_sys_id)->where('suggestion_flagged', false)->first();

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
        ]);

        // MAKING SURE THE REQUEST AND USER IS VALIDATED
        $validation_response = UtilController::validateUserWithAuthToken($request, auth()->user(), "get-info-on-apps");
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

        $suggestion = Business::where('suggestion_directed_at_user_business_find_code', $request->business_id)->first();

        $data = array(
            "pott_intelligence" => $user->business_full_name, 
            "price_per_item" => "$" . strval($business->business_price_per_stock_usd), 
            "price_per_item" => "$" . strval($business->business_price_per_stock_usd)
        );

        return response([
            "status" => 1, 
            "message" => "success",
            "data" => $suggestion,
            "government_verification_is_on" => false,
            "media_allowed" => intval(config('app.canpostpicsandvids')),
            "user_android_app_max_vc" => intval(config('app.androidmaxvc')),
            "user_android_app_force_update" => boolval(config('app.androidforceupdatetomaxvc')),
            "phone_verification_is_on" => boolval(config('app.phoneverificationrequiredstatus'))
        ]);
    }


}
