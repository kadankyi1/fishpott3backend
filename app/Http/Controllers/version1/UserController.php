<?php

namespace App\Http\Controllers\version1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\version1\User;
use App\Models\version1\Gender;
use App\Models\version1\Country;
use App\Models\version1\Language;
use Illuminate\Support\Facades\File; 
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;



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
    | THIS FUNCTION GETS A USER
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    */
    public function getUserWithOneColumn($column, $keyword)
    {
        return User::where($column, '=', $keyword)->first();
    }

    /*
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    | THIS FUNCTION CHECKS IF A PHONE NUMBER IS NOT USED
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    */
    public function phoneNumberIsAvailable($keyword)
    {
        $user = User::where('user_phone_number', '=', $keyword)->first();
        if ($user !== null) {
            return false;
        } else {
            // user doesn't exist
            return true;
        }
    }

    /*
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    | THIS FUNCTION CHECKS IF A PHONE NUMBER IS NOT USED
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    */
    public function emailIsAvailable($keyword)
    {
        if(empty($keyword)){
            return false;
        }
        $user = User::where('user_email', '=', $keyword)->first();
        if ($user === null) {
            return true;
        } else {
            // user doesn't exist
            return false;
        }
    }

    /*
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    | THIS FUNCTION CHECKS IF A POTTNAME IS AVAILABLE
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    */
    public function pottnameIsAvailable($keyword)
    {
        if(empty($keyword) || strlen($keyword) < 5 || $keyword == "mylinkups"){
            return false;
        }
        $user = User::where('user_pottname', '=', $keyword)->first();
        if ($user === null) {
            return true;
        } else {
            // user doesn't exist
            return false;
        }
    }

    /*
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    | THIS FUNCTION GENERATES A RANDOM STRING
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    */
	function getRandomString($length) 
    {
		$str = "";
		$characters = array_merge(range('A','Z'), range('a','z'), range('0','9'));
		$max = count($characters) - 1;
		for ($i = 0; $i < $length; $i++) {
			$rand = mt_rand(0, $max);
			$str .= $characters[$rand];
		}
		return $str;
	}// END OF randomString

    /*
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    | THIS FUNCTION CHECKS IF A STRING HAS NO XML TAGS
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    */
    public function stringContainsNoTags($input) 
    {
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
    public function stringIsNotMoreThanMaxLength($input, $max_allowed_input_length)
    {
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
    public function inputContainsOnlyNumbers($input)
    {
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

    public function inputContainsOnlyAlphabetsWithListedSpecialCharacters($input, $include_some_special_characters, $special_characters_array)
    {
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
    | THIS FUNCTION REMOVES ALL ALPHABETS AND SYMBOLS AND LEAVES NUMBERS
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    */

    public function removeAllCharactersAndLeaveNumbers($input)
    {
        if($input != ""){
            return preg_replace('/[^0-9]/', '', $input);
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
        if(empty($validatedData["user_referred_by"]) || $this->pottnameIsAvailable($validatedData["user_referred_by"])){
            $validatedData["user_referred_by"] = "";
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
            "force_update_status" => config('app.androidforceupdatetomaxvc'),
            "media_allowed" => boolval(config('app.canpostpicsandvids')),
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
        if(empty($validatedData["user_referred_by"]) || $this->pottnameIsAvailable($validatedData["user_referred_by"])){
            $validatedData["user_referred_by"] = "";
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
        $userData["user_initial_signup_approved"] = false;
        $userData["user_flagged"] = false;
        // SAVING APP TYPE VERSION CODE
        if($request->app_type == "ANDROID"){
            $userData["user_android_app_version_code"] = $validatedData["app_version_code"];
        } else if($request->app_type == "IOS"){
            $userData["user_ios_app_version_code"] = $validatedData["app_version_code"];
        } 
        $userData["user_app_version_code"] = $validatedData["app_version_code"];
        $userData["user_scope"] = "view-info get-stock-suggestions answer-questions buy-stock-suggested trade-stocks";
        $userData["user_phone_verification_requested"] = boolval(config('app.phoneverificationrequiredstatus'));
        $userData["user_id_verification_requested"] = boolval(config('app.idverificationrequiredstatus'));

        $user1 = User::create($userData);
        
        // GENERATING THE ACCESS TOKEN FOR THE REGISTERED USER
        //$accessToken = $user1->createToken("authToken")->accessToken;
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
            "user_type" => "Business",
            "user_gender" => $validatedData["user_gender"],
            "user_date_of_birth" => $user1->user_dob,
            "user_currency" => "USD",
            "highest_version_code" => config('app.androidmaxvc'),
            "force_update_status" => config('app.androidforceupdatetomaxvc'),
            "media_allowed" => boolval(config('app.canpostpicsandvids')),
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
                "status" => "error", 
                "message" => "Account access restricted"
            ]);
        }
        
        // CHECKING POTTNAME AVAILABILITY
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
        $user->save();    

        // GENERATING USER ACCESS TOKEN
        $accessToken = auth()->user()->createToken("authToken", ["view-info get-stock-suggestions answer-questions buy-stock-suggested trade-stocks"])->accessToken;


        return response([
            "status" => "yes", 
            "message" => "",
            "user_phone" => $user->user_phone_number,
            "user_id" => $user->investor_id,
            "access_token" => $accessToken,
            "user_pott_name" => $user->user_pottname,
            "user_full_name" => $user->user_firstname . " " . $user->user_surname,
            "user_profile_picture" => config('app.url') . '/uploads/images/' . $user->user_profile_picture,
            "user_country" => $country->country_real_name,
            "user_verified_status" => 0,
            "user_type" => "Investor",
            "user_gender" => $gender->gender_name,
            "user_date_of_birth" => $user->user_dob,
            "user_currency" => "USD",
            "force_update_status" => config('app.androidforceupdatetomaxvc'),
            "media_allowed" => boolval(config('app.canpostpicsandvids')),
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
        if (!$request->user()->tokenCan('view-info')) {
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
        $img_ext = auth()->user()->user_id . uniqid() . date("Y-m-d-H-i-s") . "." . strtolower($request->file('pott_picture')->extension());
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
            "force_update_status" => config('app.androidforceupdatetomaxvc'),
            "media_allowed" => boolval(config('app.canpostpicsandvids')),
            "user_android_app_max_vc" => intval(config('app.androidmaxvc')),
            "user_android_app_force_update" => boolval(config('app.androidforceupdatetomaxvc')),
            "phone_verification_is_on" => boolval(config('app.phoneverificationrequiredstatus'))
        ]);
    }


}
