<?php

namespace App\Http\Controllers\version1;

use DB;
use DateTime;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\version1\User;
use App\Models\version1\Gender;
use App\Models\version1\Country;
use App\Models\version1\Drill;
use App\Models\version1\Business;
use App\Models\version1\Suggesto;
use App\Models\version1\Language;
use App\Models\version1\ResetCode;
use App\Mail\version1\ResetCodeMail;
use Illuminate\Support\Facades\File; 
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Models\version1\StockValue;
use App\Models\version1\StockOwnership;
use App\Models\version1\Administrator;
use App\Models\version1\SuggestionTypes;
use App\Models\version1\Suggestion;
use App\Http\Controllers\version1\LogController;
use App\Models\version1\DrillAnswer;

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

    /*
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
    */

    /*
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    | THIS FUNCTION SENDS A NOTIFICATION TO A USER
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    */
	public static function sendNotificationToUser($path_fcm, $server_key, $receiver_keys_array, $priority, $type, $title, $message, $text, $info1, $info2, $image, $video, $date){
        // REMOVING EMPTY KEYS
        $receiver_keys_array = array_filter($receiver_keys_array, fn($value) => !is_null($value) && $value !== '');

        if(count($receiver_keys_array) > 0){
			$headers = array('Authorization:key=' . $server_key, 'Content-Type:application/json');
			$fields = array(
			  'registration_ids' => $receiver_keys_array,
			  'priority' => $priority,
			  'data' => array(
			    'not_type' => $type,
			    'not_title' => $title,
			    'not_message' => $message,
			    'not_message_text' => $text, 
			    'not_message_info1' => $info1, 
			    'not_message_info2' => $info2, 
			    'not_message_image' => $image, 
			    'not_message_video' => $video,
			    'not_time' => $date  
			    )
			  );
			$payload = json_encode($fields);
			$curl_session = curl_init();
			curl_setopt($curl_session, CURLOPT_URL, $path_fcm);
			curl_setopt($curl_session, CURLOPT_POST, true);
			curl_setopt($curl_session, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($curl_session, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl_session, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($curl_session, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
			curl_setopt($curl_session, CURLOPT_POSTFIELDS, $payload);
			$curl_result = curl_exec($curl_session);
			
			/*
            echo "\n\n\n";
			var_dump($receiver_keys_array);
			echo "\n\n\n";
			var_dump($curl_result);
            */
			
			return true;
		} else {
			return false;
		}


	} 

    /*
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    | THIS FUNCTION SENDS A NOTIFICATION TO A TOPIC
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    */
    public static function sendNotificationToTopic($path_fcm, $server_key, $topic, $priority, $type, $title, $message, $text, $info1, $info2, $image, $video, $date){

        if(!empty($topic)){
			$headers = array('Authorization:key=' . $server_key, 'Content-Type:application/json');
			$fields = array(
                "to" => '/topics/'. $topic,
			  'priority' => $priority,
			  'data' => array(
			    'not_type' => $type,
			    'not_title' => $title,
			    'not_message' => $message,
			    'not_message_text' => $text, 
			    'not_message_info1' => $info1, 
			    'not_message_info2' => $info2, 
			    'not_message_image' => $image, 
			    'not_message_video' => $video,
			    'not_time' => $date  
			    )
			  );
			$payload = json_encode($fields);
			$curl_session = curl_init();
			curl_setopt($curl_session, CURLOPT_URL, $path_fcm);
			curl_setopt($curl_session, CURLOPT_POST, true);
			curl_setopt($curl_session, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($curl_session, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl_session, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($curl_session, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
			curl_setopt($curl_session, CURLOPT_POSTFIELDS, $payload);
			$curl_result = curl_exec($curl_session);

			/*
            echo "\n\n\n";
			var_dump($curl_result);
            */

			return true;
		} else {
			return false;
		}


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
    | THIS FUNCTION VALIDATES A REQUEST FROM A USER
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    */

    public static function validateUserWithAuthToken($request, $user, $actions)
    {
        // CHECKING IF USER FLAGGED
        if ($user->user_flagged) {
            $request->user()->token()->revoke();
            return [
                "status" => 4, 
                "message" => "Account flagged.",
                "government_verification_is_on" => false,
                "media_allowed" => intval(config('app.canpostpicsandvids')),
                "user_android_app_max_vc" => intval(config('app.androidmaxvc')),
                "user_android_app_force_update" => boolval(config('app.androidforceupdatetomaxvc')),
                "phone_verification_is_on" => boolval(config('app.phoneverificationrequiredstatus'))
            ]; 
         }

        // CHECKING THAT USER TOKEN HAS THE RIGHT PERMISSION
        if (!$request->user()->tokenCan($actions)) {
            return [
                "status" => 4, 
                "message" => "You do not have permission",
                "government_verification_is_on" => false,
                "media_allowed" => intval(config('app.canpostpicsandvids')),
                "user_android_app_max_vc" => intval(config('app.androidmaxvc')),
                "user_android_app_force_update" => boolval(config('app.androidforceupdatetomaxvc')),
                "phone_verification_is_on" => boolval(config('app.phoneverificationrequiredstatus'))
            ];
        }

        // MAKING SURE VERSION CODE IS ALLOWED
        if(
            strtoupper($request->app_type) == "ANDROID" && 
            (intval($request->app_version_code) < intval(config('app.androidminvc')) || $request->app_version_code > intval(config('app.androidmaxvc')))
        ){
            return [
                "status" => 2, 
                "message" => "Please update your app from the Google Play Store.",
                "government_verification_is_on" => false,
                "media_allowed" => intval(config('app.canpostpicsandvids')),
                "user_android_app_max_vc" => intval(config('app.androidmaxvc')),
                "user_android_app_force_update" => boolval(config('app.androidforceupdatetomaxvc')),
                "phone_verification_is_on" => boolval(config('app.phoneverificationrequiredstatus'))
            ]; 
        }

        // GETTING USER
        $user = User::where('user_pottname', $user->user_pottname)->where('user_phone_number', $request->user_phone_number)->where('investor_id', $request->investor_id)->first();
        if($user == null){
            return [
                "status" => 5, 
                "message" => "Session closed. You have to login again...",
                "government_verification_is_on" => false,
                "media_allowed" => intval(config('app.canpostpicsandvids')),
                "user_android_app_max_vc" => intval(config('app.androidmaxvc')),
                "user_android_app_force_update" => boolval(config('app.androidforceupdatetomaxvc')),
                "phone_verification_is_on" => boolval(config('app.phoneverificationrequiredstatus'))
            ]; 
        }

        // SAVING APP TYPE VERSION CODE
        if($request->app_type == "ANDROID"){
            $user->user_android_app_version_code = $request->app_version_code;
        } else if($request->app_type == "IOS"){
            $user->user_ios_app_version_code = $request->app_version_code;
        }
        // SAVING CHANGES MADE TO THE USER
        if($actions != "get-info-in-background"){
            $user->last_online = date("Y-m-d H:i:s");
        }
        $user->save();    
        
        return $user;
    }

    /*
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    | THIS FUNCTION VALIDATES A REQUEST FROM AN ADMIN
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    */
    public static function validateAdminWithAuthToken($request, $admin, $actions)
    {
        // CHECKING IF ADMIN FLAGGED
        if ($admin->administrator_flagged) {
            //$request->auth()->guard('administrator-api')->user()->token()->revoke();
            return [
                "status" => "error", 
                "message" => "Account flagged."
            ]; 
         }

        // CHECKING THAT ADMIN TOKEN HAS THE RIGHT PERMISSION
        if (!$admin->tokenCan($actions)) {
            return [
                "status" => "error", 
                "message" => "You do not have permission"
            ];
        }

        // CHECKING PIN
        if ($actions != "get-info" && !Hash::check($request->administrator_pin, $admin->administrator_pin)) {
            return [
                "status" => "error", 
                "message" => "Incorrect pin"
            ];
        }

        // MAKING SURE FRONTEND HAS THE RIGHT KEY
        if(strtoupper($request->frontend_key) == config('app.adminfrontendkey')){
            return [
                "status" => "error", 
                "message" => "Device not recognized."
            ];
        }

        // GETTING ADMIN
        $administrator = Administrator::where('administrator_phone_number', $admin->administrator_phone_number)->where('administrator_sys_id', $request->administrator_sys_id)->first();
        if($administrator == null){
            return [
                "status" => "error", 
                "message" => "Session closed. You have to login again."
            ]; 
        }   
        LogController::save_log("administrator", $admin->administrator_sys_id, "Validation Admin", "Validation successful");

        return $administrator;
    }

    /*
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    | THIS FUNCTION GETS A SUGGESTION DIRECTED AT A SPECIFIC USER
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    */
    public static function getSuggestionMadeToUser($user_investor_id)
    {
        if(empty($user_investor_id)){
            return false;
        }

        // GETTING THE RECENT TAYLORED SUGGESTION
        return Suggestion::where('suggestion_directed_at_user_investor_id', '=', $user_investor_id)->where('suggestion_flagged', false)->first();

    }

    public static function getLatestSuggestion()
    {
        // GETTING THE RECENT
        $suggestion = Suggestion::where('suggestion_flagged', false)->orderby('created_at', 'desc')->first();
        //echo "hours passed: " . UtilController::getDateDiff($suggestion->created_at, date('Y-m-d H:i:s'), "hours");
        if ($suggestion != null && UtilController::getDateDiff($suggestion->created_at, date('Y-m-d H:i:s'), "hours") >= intval(config('app.timedurationinhoursforsuggestions'))) {
            return false;
        } 

        //echo "suggestion->suggestion_item_reference_id: " . $suggestion->suggestion_item_reference_id; exit;
        return $suggestion;
    }

    public static function getSuggestionType($column, $value, $fetch_type)
    {
        $suggestiontype = SuggestionTypes::where($column, $value)->first();

        if($fetch_type == 1){
            return $suggestiontype->suggestion_type_id;
        } else if($fetch_type == 2){
            return $suggestiontype->suggestion_type_name;
        }
    }

    public static function getCountDrillAnswers($column, $value)
    {
        $drillAnswer = DrillAnswer::where($column[0], $value[0])->where($column[1], $value[1])->get();
        return $drillAnswer->count();
    }

    public static function formatNumberShort( $n, $precision = 1 ) {
		if ($n < 900) {
			// 0 - 900
			$n_format = number_format($n, $precision);
			$suffix = '';
		} else if ($n < 900000) {
			// 0.9k-850k
			$n_format = number_format($n / 1000, $precision);
			$suffix = 'K';
		} else if ($n < 900000000) {
			// 0.9m-850m
			$n_format = number_format($n / 1000000, $precision);
			$suffix = 'M';
		} else if ($n < 900000000000) {
			// 0.9b-850b
			$n_format = number_format($n / 1000000000, $precision);
			$suffix = 'B';
		} else {
			// 0.9t+
			$n_format = number_format($n / 1000000000000, $precision);
			$suffix = 'T';
		}

	  // Remove unecessary zeroes after decimal. "1.0" -> "1"; "1.00" -> "1"
	  // Intentionally does not affect partials, eg "1.50" -> "1.50"
		if ( $precision > 0 ) {
			$dotzero = '.' . str_repeat( '0', $precision );
			$n_format = str_replace( $dotzero, '', $n_format );
		}

		return $n_format . $suffix;
	}

    public static function formatNumberWithPositionAffix($input_number)
    {
        if($input_number == 0){
            return "Last";
        }
        $number = (string) $input_number;
        $last_digit = substr($number, -1);
        $second_last_digit = substr($number, -2, 1);
        $suffix = 'th';
        if ($second_last_digit != '1')
        {
            switch ($last_digit)
            {
            case '1':
                $suffix = 'st';
                break;
            case '2':
                $suffix = 'nd';
                break;
            case '3':
                $suffix = 'rd';
                break;
            default:
                break;
            }
        }
        if ((string) $number === '1') $suffix = 'st';
            return $number.$suffix;
    }

    /*
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    | THIS FUNCTION CALCULATES USERS NETWORTH AND POSITION
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    */
    public static function calculateUsersNetworthAndSetPosition()
    {
        // GETTING ALL USERS
        $users = User::orderBy('user_net_worth_usd', 'DESC')->get();
        $user_position = 1;
        foreach($users as $user){
            $this_user_net_worth_usd = 0;
            // SUMMING UP THE SHARES OWNED
            $ownedstocks = StockOwnership::where("stockownership_user_investor_id", $user->investor_id)->get();

            foreach($ownedstocks as $ownedstock){
                //echo "stockbusiness_id: " . $ownedstock->stockownership_business_id . " -- ownedstock quantity: " . $ownedstock->stockownership_stocks_quantity;
                //echo "\n here 1";
                $stockvalue = StockValue::where("stockvalue_business_id", $ownedstock->stockownership_business_id)->orderby('created_at', 'desc')->first();
                echo "\n StockValue: $" . $stockvalue->stockvalue_value_per_stock_usd;
                if($stockvalue == null){
                //echo "\n here 2";
                    $this_user_net_worth_usd = $this_user_net_worth_usd + $ownedstock->stockownership_total_cost_usd;
                } else {
                //echo "\n here 3";
                    $this_user_net_worth_usd = $this_user_net_worth_usd + ($ownedstock->stockownership_stocks_quantity * $stockvalue->stockvalue_value_per_stock_usd);
                }
            }

            // NOTIFYING USER OF NEW NET WORTH
            if($user->user_net_worth_usd < $this_user_net_worth_usd){
                // SENDING NOTIFICATION TO THE USER
                UtilController::sendNotificationToUser(
                    config('app.firebase_notification_server_address_link'), 
                    config('app.firebase_notification_account_key'), 
                    array($user->user_fcm_token_android, $user->user_fcm_token_web, $user->user_fcm_token_ios),
                    "normal",
                    "networth-info",
                    "Net Worth Up - FishPott",
                    "Net worth has climbed up. Stocks are doing well.",
                    "", 
                    "", 
                    "", 
                    "", 
                    "",
                    date("F j, Y")
                );
            } else if($user->user_net_worth_usd > $this_user_net_worth_usd){
                // SENDING NOTIFICATION TO THE USER
                UtilController::sendNotificationToUser(
                    config('app.firebase_notification_server_address_link'), 
                    config('app.firebase_notification_account_key'), 
                    array($user->user_fcm_token_android, $user->user_fcm_token_web, $user->user_fcm_token_ios),
                    "normal",
                    "networth-info",
                    "Net Worth Reduced - FishPott",
                    "Net worth has fallen. Stocks are not doing well.",
                    "", 
                    "", 
                    "", 
                    "", 
                    "",
                    date("F j, Y")
                );
            }

            //echo "\n user_pottname: " . $user->user_pottname . " -- user position: " . $user_position . " -- user_net_worth_usd: " . $user->user_net_worth_usd;
            $user->user_net_worth_usd = $this_user_net_worth_usd + $user->user_wallet_usd;
            $user->user_pott_position = $user_position;

            $user->save();
            $user_position++;
        }
    }



    public static function matchUsersToABusinesses()
    {
        
    }
}
