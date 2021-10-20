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
    | THIS FUNCTION GETS OUTPUTS NEW REFORMATED DATE GIVEN A TIME PERIOD
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    */
    public function reformatDate($date, $difference_str, $return_format)
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

    public function getDateDiff($fromDate, $toDate, $format_type)
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
    public function sendFirebaseNotification($title,$body,$target,$chid)
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

}
