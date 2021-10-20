<?php

namespace App\Http\Controllers\version1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UtilController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    | THIS FUNCTION GENERATES A RANDOM STRING
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    */
	public static function getRandomString($length) 
    {
		$str = "";
		$characters = array_merge(range('A','Z'), range('a','z'), range('0','9'));
		$max = count($characters) - 1;
		for ($i = 0; $i < $length; $i++) {
			$rand = mt_rand(0, $max);
			$str .= $characters[$rand];
		}
		return $str;
	}
    
    /*
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    | THIS FUNCTION CHECKS IF A STRING HAS NO XML TAGS
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    */
    public static function stringContainsNoTags($input) 
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
    public static function stringIsNotMoreThanMaxLength($input, $max_allowed_input_length)
    {
        $validation = false;
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
    public static function inputContainsOnlyNumbers($input)
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

    public static function inputContainsOnlyAlphabetsWithListedSpecialCharacters($input, $include_some_special_characters, $special_characters_array)
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

    public static function removeAllCharactersAndLeaveNumbers($input)
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
    | THIS FUNCTION GETS OUTPUTS NEW REFORMATED DATE GIVEN A TIME PERIOD
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    */
    public static function reformatDate($date, $difference_str, $return_format)
    {
        return date($return_format, strtotime($date. ' ' . $difference_str));
    }

    /*
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    | THIS FUNCTION GETS DATE DIFFERENCE IN ANY FORMAT
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    */

    public static function getDateDiff($fromDate, $toDate, $format_type)
    {        
        $datetime1 = strtotime($fromDate); // convert to timestamps
        $datetime2 = strtotime($toDate); // convert to timestamps
        $days = (int)(($datetime2 - $datetime1)/86400);

        if($format_type == "hours"){
            return (int)(($datetime2 - $datetime1)/(86400/24)); // CONVERTING TO GET HOURS
        } else if($format_type == "minutes"){
            return (int)(($datetime2 - $datetime1)/(86400/(24*60))); // CONVERTING TO GET MINUTES
        } else if($format_type == "seconds"){
            return intval($days) * 24 * 60 * 60; // CONVERTING TO GET SECONDS
        } else {
            return (int)(($datetime2 - $datetime1)/86400);
        }
    }

    //$this->sendFirebaseNotification("New Herald Of Glory", "Added Successfully", "/topics/ALPHA", "ALPHA");
    public static function sendFirebaseNotification($title,$body,$target,$chid)
    {
        // SETTING API ACCESS KEY
        define( 'API_ACCESS_KEY', 'AAAABb3fzMY:APA91bFeAZ6QQwlQoiiugGLWUARoh4gf3avvcdLJNIlEWv2kBljnpOL3leahkgk4FArNuzk_ejZbE74aDjuEj1vSAWLAYKAneHJEmXhzjEZFJC3SlgfZRqNW3ZOTwlHMyuPXYh6oLwok' );
        $fcmMsg = array('title' => $title,'body' => $body,'channelId' => $chid);
        $fcmFields = array(
            'to' => $target, //tokens sending for notification
            'notification' => $fcmMsg,
        );
        // SETTING HEADERS FOR CURL REQUEST
        $headers = array('Authorization: key=' . API_ACCESS_KEY,'Content-Type: application/json');
        // MAKING THE CURL REQUEST TO FIREBASE
        $ch = curl_init();
        curl_setopt( $ch,CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send' );
        curl_setopt( $ch,CURLOPT_POST, true );
        curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
        curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, true );
        curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fcmFields ) );
        $result = curl_exec($ch );
        curl_close( $ch );
        //echo $result . "\n\n";
    }

    /*
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    | THIS FUNCTION GETS A USER
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    */
    public static function getUserWithOneColumn($column, $keyword)
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
    public static function phoneNumberIsAvailable($keyword)
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
    public static function emailIsAvailable($keyword)
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
    public static function pottnameIsAvailable($keyword)
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
    | THIS FUNCTION VALIDATES A REQUEST AND THE USER MAKING IT
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    */

    public static function validateUserWithAuthToken($request, $user)
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
        if (!$request->user()->tokenCan('get-info-on-apps')) {
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

}
