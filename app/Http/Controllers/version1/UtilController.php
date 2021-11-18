<?php

namespace App\Http\Controllers\version1;
//require_once ("../ai/NeuralNetwork.php");

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
use App\Models\version1\DrillAnswer;
use App\Http\Controllers\ai\NeuralNetworkController;
use App\Http\Controllers\version1\LogController;

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
                //echo "\n StockValue: $" . $stockvalue->stockvalue_value_per_stock_usd;
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
                    "Your net worth has climbed up as your stocks are doing well.",
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
                    "Your net worth has fallen as your stocks are not doing well.",
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


    /*
    |--------------------------------------------------------------------------------------
    |--------------------------------------------------------------------------------------
    | THIS FUNCTION NORMALIZES ANY DATA TO RANGE FROM 0 - 100 TO BE FED TO THE AI SUB-SYSTEM
    |--------------------------------------------------------------------------------------
    |--------------------------------------------------------------------------------------
    */

    public static function normalizeDataSet($dataset_array, $zero_division_infers_true)
    {
        $normalized_dataset_array = array();
        $new_dataset_array_formatted_percentage = array();
        
        // GETTING MAXIMUM VALUE FROM DATASET
        $dataset_max_value = max($dataset_array);

        // GETTING MINIMUM VALUE FROM DATASET
        $dataset_min_value = min($dataset_array);

        // GETTING CONSTANT MAXIMUM RANGE VALUE
        $neural_network_range_max_value = config('app.ai_data_range_max');

        // GETTING CONSTANT MINIMUM RANGE VALUE
        $neural_network_range_min_value = config('app.ai_data_range_min');
        
        foreach ($dataset_array as $key => $data) {
            
            if($dataset_max_value-$dataset_min_value == 0 && $zero_division_infers_true){
                array_push($normalized_dataset_array, 1);
                continue;
            } else if($dataset_max_value-$dataset_min_value == 0 && !$zero_division_infers_true){
                array_push($normalized_dataset_array, 0);
                continue;
            }
            
            $neuron_or_node = $neural_network_range_min_value + ($neural_network_range_max_value - $neural_network_range_min_value) * ($data-$dataset_min_value)/($dataset_max_value-$dataset_min_value);
            array_push($normalized_dataset_array, $neuron_or_node);
            //array_push($new_dataset_array_formatted_percentage, strval($neuron_or_node) . "%");
        }

        return $normalized_dataset_array;
    }


    /*
    |--------------------------------------------------
    |--------------------------------------------------
    | THIS FUNCTION TRAINS FOR THE NEURAL NETWORK
    |--------------------------------------------------
    |--------------------------------------------------
    */
    public static function trainNeuralNetwork($raw_input, $raw_ouput, $training_type)
    {
        // PREPPING THE DATA
        $raw_input_array = explode("|", $raw_input);
        $raw_output_array = explode("#", $raw_ouput);

        $normalized_input_data_array = array(); 
        foreach ($raw_input_array as $key => $data) {
            array_push($normalized_input_data_array, UtilController::normalizeDataSet(explode("#", $data), false));
        }

        $formatted_output_data_array = array(); 
        foreach ($raw_output_array as $key => $data) {
            $this_output = array(0 => intval($data));
            array_push($formatted_output_data_array, $this_output);
        }

        // Create a new neural network with 3 input neurons, 4 hidden neurons, and 1 output neuron
        $n = new NeuralNetworkController(7, 8, 1);
        $n->setVerbose(false);

        // ADDING TEST DATA
        foreach ($normalized_input_data_array as $key => $value) {
            $n->addTestData($value, $formatted_output_data_array[$key]);
        }
        
        // we try training the network for at most $max times
        $max = 3;
        $i = 0;
        echo "<h1>Learning the XOR function</h1>";
        // train the network in max 1000 epochs, with a max squared error of 0.01
        while (!($success = $n->train(1000, 0.01)) && ++$i<$max) {
            echo "Round $i: No success...<br />";
        }
        // print a message if the network was succesfully trained
        if ($success) {
            $epochs = $n->getEpoch();
            echo "Type $training_type : Success in $epochs training rounds!<br />";
        } else {
            echo "Type $training_type : Failed in training rounds!<br />";
        }

        if(!empty($epochs)){
            if($training_type == config('app.openness_to_experience')){ // openness to experience - O 
                // Feed in weekly (day 1 - 7) values of stock value changes 
                // where those with high changes are 1 and 0 for vice versa
                // here even the higher changes (0.1+) should record as 1
                $n->save(public_path() . "/uploads/ai/nn-o.ini");
            } else if($training_type == config('app.conscientiousness')){ // conscientiousness - C 
                // Feed in weekly (day 1 - 7) values of the standard deviation of the stock value changes to the overall change of all hosted stocks 
                // where those with high changes are 0 and 1 for vice versa
                // here changes less than 20% should record as 0
                $n->save(public_path() . "/uploads/ai/nn-c.ini");
            } else if($training_type == config('app.extraversion')){ // extraversion - E 
                // Feed in weekly (day 1 - 7) values of the standard deviation of the stock value changes to the overall change of all hosted stocks 
                // where those with high changes are 0 and 1 for vice versa
                // here changes less than 25% should record as 1
                $n->save(public_path() . "/uploads/ai/nn-e.ini");
            } else if($training_type == config('app.agreeableness')){ // agreeableness - A 
                // Feed in weekly (day 1 - 7) values of the standard deviation of the stock value changes to the overall change of all hosted stocks 
                // where those with high changes are 0 and 1 for vice versa
                // here changes less than 10% should record as 1
                $n->save(public_path() . "/uploads/ai/nn-a.ini");
            } else if($training_type == config('app.neuroticism')){ // neuroticism - N 
                // Feed in weekly (day 1 - 7) values of stock value changes 
                // where those with high changes are 0 and 1 for vice versa
                // here even the tiniest changes (0.01+) should record as 1
                $n->save(public_path() . "/uploads/ai/nn-n.ini");
            }
        }
        
        /*
        echo "<h2>Result</h2>";
        echo "<div class='result'>";
        // in any case, we print the output of the neural network
        for ($i = 0; $i < count($n->trainInputs); $i ++) {
            $output = $n->calculate($n->trainInputs[$i]);
            echo "<div>Testset $i; ";
            echo "expected output = (".implode(", ", $n->trainOutput[$i]).") ";
            echo "output from neural network = (".implode(", ", $output).")\n</div>";   
        }
        echo "</div>";
        echo "<h2>Internal network state</h2>";
        //$n->showWeights($force=true);
        
        // Now, play around with some of the network's parameters a bit, to see how it
        // influences the result
        $learningRates = array(0.1, 0.25, 0.5, 0.75, 1);
        $momentum = array(0.2, 0.4, 0.6, 0.8, 1);
        $rounds = array(100, 500, 1000, 2000);
        $errors = array(0.1, 0.05, 0.01, 0.001);
        
        echo "<h1>Playing around...</h1>";
        echo "<p>The following is to show how changing the momentum & learning rate,
        in combination with the number of rounds and the maximum allowable error, can
        lead to wildly differing results. To obtain the best results for your
        situation, play around with these numbers until you find the one that works
        best for you.</p>";
        echo "<p>The values displayed here are chosen randomly, so you can reload
        the page to see another set of values...</p>";
        
        for ($j=0; $j<10; $j++) {
            // no time-outs
            set_time_limit(0);
                
            $lr = $learningRates[array_rand($learningRates)];
            $m = $momentum[array_rand($momentum)];
            $r = $rounds[array_rand($rounds)];
            $e = $errors[array_rand($errors)];
            echo "<h2>Learning rate $lr, momentum $m @ ($r rounds, max sq. error $e)</h2>";
            $n->clear();
            $n->setLearningRate($lr);
            $n->setMomentum($m);
            $i = 0;
            while (!($success = $n->train($r, $e)) && ++$i<$max) {
                echo "Round $i: No success...<br />";
                flush();
            }
        
            // print a message if the network was succesfully trained
            if ($success) {
                $epochs = $n->getEpoch();
                echo "Success in $epochs training rounds!<br />";
        
                echo "<div class='result'>";
                for ($i = 0; $i < count($n->trainInputs); $i ++) {
                    $output = $n->calculate($n->trainInputs[$i]);
                    echo "<div>Testset $i; ";
                    echo "expected output = (".implode(", ", $n->trainOutput[$i]).") ";
                    echo "output from neural network = (".implode(", ", $output).")\n</div>";
                }
        
                //$output = $n->calculate(array(0.001, 0.0015, 0.019, 0.031, 0.011, 0.012, 0.014));
                //echo "<div>Testset input : 0.21, 0.15, 0.19, 0.31, 0.11, 0.12, 0.14";
                //echo " -- expected output = (-1) ";
                //echo "expected output = (".implode(", ", $n->trainOutput[$i]).") ";
                //echo "output from neural network = (".implode(", ", $output).")\n</div>";
            }
        }
        */
    }

    /*
    |---------------------------------------------------------------------------------------------------
    |---------------------------------------------------------------------------------------------------
    | THIS FUNCTION OUTPUTS OPENESS-TO-EXPERIENCE
    |---------------------------------------------------------------------------------------------------
    |---------------------------------------------------------------------------------------------------
    */
    public static function testNeuralNetworkToGetStockOpennessToExperience($raw_input, $show_as_percentage, $test_type)
    {

        // LOADING THE NEURAL NETWORK
        // Create a new neural network with 3 input neurons, 4 hidden neurons, and 1 output neuron
        $n = new NeuralNetworkController(7, 8, 1);
        $n->setVerbose(false);

        if($test_type == config('app.openness_to_experience')){ // openness to experience - O // Feed in a week's (day 1 - 7) values of stock value changes
            $n->load(public_path() . "/uploads/ai/nn-o.ini");
        } else if($test_type == config('app.conscientiousness')){ // conscientiousness - C // Feed in a week's (day 1 - 7) values of stock value changes
            $n->load(public_path() . "/uploads/ai/nn-c.ini");
        } else if($test_type == config('app.extraversion')){ // extraversion - E // Feed in a week's (day 1 - 7) values of stock value changes
            $n->load(public_path() . "/uploads/ai/nn-e.ini");
        } else if($test_type == config('app.agreeableness')){ // agreeableness - A // Feed in a week's (day 1 - 7) values of stock value changes
            $n->load(public_path() . "/uploads/ai/nn-a.ini");
        } else if($test_type == config('app.neuroticism')){ // neuroticism - N // Feed in a week's (day 1 - 7)) values of stock value changes
            $n->load(public_path() . "/uploads/ai/nn-n.ini");
        } else {
            return null;
        }

        // NORMALIZING THE DATA
        $raw_input_array = explode("#", $raw_input);
        $normalized_input_data_array = UtilController::normalizeDataSet($raw_input_array, false); 

        // CALCULATING THE CRITERIA
        $output = $n->calculate($normalized_input_data_array);
        /*
        echo "<div>Testset : " . join(',', $raw_input_array) . "<br>Testset Normalized : " . join(',', $normalized_input_data_array);
        echo "<br>expected output = (-1) ";
        echo "output from neural network = (".implode(", ", $output).")\n</div>";
        */
        if(is_numeric($output[0])){
            if($show_as_percentage){
                $output = floatval($output[0]) * 100;
            }
        } else {
            $output = null;
        }

        return $output;

    }


    /*
    |---------------------------------------------------------------------------------------------------
    |---------------------------------------------------------------------------------------------------
    | THIS FUNCTION GETS TEST DATA
    |---------------------------------------------------------------------------------------------------
    |---------------------------------------------------------------------------------------------------
    */
    public static function getTestDataForNeuralNetworkAi($stock_business_id, $big_five_type, $randomize)
    {
        if($big_five_type == config('app.openness_to_experience')){ // openness to experience - O // Feed in a week's (day 1 - 7) values of stock value changes
            // Feed in weekly (day 1 - 7) values of stock value changes 
            // where those with high changes are 1 and 0 for vice versa
            // here even the higher changes (0.1+) should record as 1
            
            // GETTING THE 7 VALUES OF THE STOCK
            $businesses = StockValue::select('stockvalue_value_change')
            ->where('stockvalue_business_id', '=', $stock_business_id)
            ->orderBy('stockvalue_id', 'desc')->take(7)->get();

            $output_data_array = array();
            foreach($businesses as $business){
                array_push($output_data_array, $business->stockvalue_value_change);
            }
            return $output_data_array;

        } else if($big_five_type == config('app.conscientiousness')){ // conscientiousness - C // Feed in a week's (day 1 - 7) values of stock value changes

        } else if($big_five_type == config('app.extraversion')){ // extraversion - E // Feed in a week's (day 1 - 7) values of stock value changes

        } else if($big_five_type == config('app.agreeableness')){ // agreeableness - A // Feed in a week's (day 1 - 7) values of stock value changes

        } else if($big_five_type == config('app.neuroticism')){ // neuroticism - N // Feed in a week's (day 1 - 7)) values of stock value changes

        } else {
            return null;
        }
    }
    /*
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    | THIS FUNCTION CALCULATES USERS NETWORTH AND POSITION
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    */
    public static function getUserBusinessSuggestion($user)
    {

        // GETTING DRILL ANSWERS
        $answers = DrillAnswer::select('drill_answer_drill_sys_id', 'drill_answer_number')
        ->where('drill_answer_user_investor_id', '=', $user->investor_id)
        ->orderBy('drill_answer_id', 'desc')->take(30)->get();

        if(count($answers) < 7){
            return false;
        }

        // INITIALIZING ARRAY
        $output_data_array = array('o' => 0,'c' => 0,'e' => 0,'a' => 0,'n' => 0);

        $count_answers = 0;
        foreach($answers as $answer){
            $this_drill = Drill::where('drill_sys_id', '=', $answer->drill_answer_drill_sys_id)->first();
            if($answer->drill_answer_number == 1){
                echo "\n\ndrill answer 1: " . $this_drill->drill_answer_1;
                $this_raw_ocean_array = explode("#", $this_drill->drill_answer_1_ocean);
            } else if($answer->drill_answer_number == 2){
                echo "\n\ndrill answer 2: " . $this_drill->drill_answer_2;
                $this_raw_ocean_array = explode("#", $this_drill->drill_answer_2_ocean);
            } else if($answer->drill_answer_number == 3){
                echo "\n\ndrill answer 3: " . $this_drill->drill_answer_3;
                $this_raw_ocean_array = explode("#", $this_drill->drill_answer_3_ocean);
            } else if($answer->drill_answer_number == 4){
                echo "\n\ndrill answer 4: " . $this_drill->drill_answer_4;
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
        var_dump($output_data_array);

    }


    /*
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    | THIS FUNCTION CALCULATES USERS NETWORTH AND POSITION
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    */
    public static function matchUsersToABusinesses()
    {
        // GETTING ALL USERS
        $users = User::orderBy('last_online', 'DESC')->get();
        foreach($users as $user){
            $suggestion_given = false;

            // suggestion_given
            // user->user_net_worth_usd
            // user's list of persona traits from drill answers

            $suggestion_given = UtilController::getUserBusinessSuggestion($user);

            // SAVING SUGGESTION
            //$user->user_net_worth_usd = $this_user_net_worth_usd + $user->user_wallet_usd;
            //$user->user_pott_position = $user_position;
            //$user->save();
            
            // NOTIFYING USER OF NEW NET WORTH
            if($suggestion_given){
                // SENDING NOTIFICATION TO THE USER
                echo "here 1";
                /*
                UtilController::sendNotificationToUser(
                    config('app.firebase_notification_server_address_link'), 
                    config('app.firebase_notification_account_key'), 
                    array($user->user_fcm_token_android, $user->user_fcm_token_web, $user->user_fcm_token_ios),
                    "normal",
                    "suggestion-info",
                    "Business Suggestion - FishPott",
                    "You have a business you can invest in. Check it out.",
                    "", 
                    "", 
                    "", 
                    "", 
                    "",
                    date("F j, Y")
                );
                */
            } else {
                // SENDING NOTIFICATION TO THE USER
                echo "here 2";
                /*
                UtilController::sendNotificationToUser(
                    config('app.firebase_notification_server_address_link'), 
                    config('app.firebase_notification_account_key'), 
                    array($user->user_fcm_token_android, $user->user_fcm_token_web, $user->user_fcm_token_ios),
                    "normal",
                    "suggestion-info",
                    "Pott Failed - FishPott",
                    "No business suggestion. Your pott is not smart enough.",
                    "", 
                    "", 
                    "", 
                    "", 
                    "",
                    date("F j, Y")
                );
                */
            }
        }
    }

}
