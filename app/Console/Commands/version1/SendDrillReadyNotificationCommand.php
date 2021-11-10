<?php

namespace App\Console\Commands\version1;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\version1\ResetCodeMail;
use App\Http\Controllers\version1\UtilController;

class SendDrillReadyNotificationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'drill:sendreadynotification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send notification that a drill is available';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        //Log::info("Cron is working fine!");
        // CREATING THE USER OBJECT AND CALLING THE SEND FCM METHOD
        //$user_controller = new UserController();
        //$user_controller->sendFirebaseNotification("New Herald Of Glory", "Added Successfully", "/topics/ALPHA", "ALPHA");
        //Log::info('drill:sendreadynotification Command Run successfully!');

        $suggestion = UtilController::getLatestSuggestion();

        // CHECKING IF A SUGGESTION EXISTS
        if($suggestion !=  null && $suggestion != false ){
            // CHECKING IF A SUGGESTION IS AVAILABLE TO BE NOTIFIED TO USERS
            if($suggestion->suggestion_notification_sent == false && $suggestion->suggestion_suggestion_type_id == UtilController::getSuggestionType("suggestion_type_name", "Drill", 1)){
                //
            } else {
                //
            }
        } else {
            // NOTIFYING FISHPOTT ADMIN THAT NO DRILLS EXIST
        }

        // CHECKING SUGGESTION TYPE TO GET IT'S INFO
        //echo "getSuggestionType: " . UtilController::getSuggestionType("suggestion_type_name", "Business", 1);
        //echo "suggestion->suggestion_item_reference_id: " . $suggestion->suggestion_item_reference_id; exit;
        if($suggestion->suggestion_suggestion_type_id == UtilController::getSuggestionType("suggestion_type_name", "Drill", 1)){
            $suggestion = Drill::where('drill_sys_id', $suggestion->suggestion_item_reference_id)->first();
            $message = "drill";
            $country_real_name = "";
        }

        $email_data = array(
            'reset_code' => date('Y-m-d H:i:s'),
            'time' => date("F j, Y, g:i a")
        );

        Mail::to(config('app.fishpott_email'))->send(new ResetcodeMail($email_data));
        $this->info('Successfully sent drill notification.');


    }
}
