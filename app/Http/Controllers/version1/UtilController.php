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


}
