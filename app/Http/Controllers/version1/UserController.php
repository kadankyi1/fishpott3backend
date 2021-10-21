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
use App\Models\version1\Drill;
use App\Models\version1\Suggesto;
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
        $userData["user_scope"] = "get-info-on-apps get-business-suggestions answer-drills buy-business-stocks transfer-business-stocks";
        $userData["user_phone_verification_requested"] = boolval(config('app.phoneverificationrequiredstatus'));
        $userData["user_id_verification_requested"] = boolval(config('app.idverificationrequiredstatus'));

        //$userData["ssssssss"] = $validatedData["user_surname"];

        $user1 = User::create($userData);
        
        // GENERATING THE ACCESS TOKEN FOR THE REGISTERED USER
        $accessToken = $user1->createToken("authToken", ["get-info-on-apps get-business-suggestions answer-drills buy-business-stocks transfer-business-stocks"])->accessToken;


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
        $userData["user_scope"] = "get-info-on-apps get-business-suggestions answer-drills buy-business-stocks transfer-business-stocks";
        $userData["user_phone_verification_requested"] = boolval(config('app.phoneverificationrequiredstatus'));
        $userData["user_id_verification_requested"] = boolval(config('app.idverificationrequiredstatus'));

        $user1 = User::create($userData);
        
        // GENERATING THE ACCESS TOKEN FOR THE REGISTERED USER
        //$accessToken = $user1->createToken("authToken")->accessToken;
        $accessToken = $user1->createToken("authToken", ["get-info-on-apps get-business-suggestions answer-drills buy-business-stocks transfer-business-stocks"])->accessToken;


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
        $accessToken = auth()->user()->createToken("authToken", ["get-info-on-apps get-business-suggestions answer-drills buy-business-stocks transfer-business-stocks"])->accessToken;

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
    
    public function getDrill(Request $request)
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
            //"drill_question" => "min:5|max:100",
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


        // CHECKING IF USER HAS A BUSINESS SUGGESTION IS BROADCASTING THAT IS NOT MORE THAN 72 HOURS OR NOT MARKED AS PASS ON

        // CHECKING FOR A NEW DRILL SUGGESTION IF NO BUSINESS SUGGESTION IS BROADCASTING AND IF THE OLD SUGGESTION HAS BEEN EXPIRED IF IT'S A QUESTION.

        // DD


        return response([
            "status" => "yes", 
            "message" => "Drill saved"
        ]);
    }


}
