<?php

namespace App\Http\Controllers\version1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LogController extends Controller
{
    public static function save_log($user_type, $access_token_or_phone_or_email, $log_title, $log_description)
    {
        $log = new Log();
        $log->log_user_type = $user_type; 
        $log->log_user_id_or_phone_or_email = $access_token_or_phone_or_email;
        $log->log_title = $log_title; 
        $log->log_description = $log_description;
        $log->save();

    }
}
