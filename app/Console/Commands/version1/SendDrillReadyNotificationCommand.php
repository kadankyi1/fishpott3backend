<?php

namespace App\Console\Commands\version1;

use Illuminate\Console\Command;
use App\Models\version1\Drill;
use App\Models\version1\Suggestion;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\version1\AlertMail;
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
        $suggestion = UtilController::getLatestSuggestion();

        // CHECKING IF A SUGGESTION EXISTS AND IS AVAILABLE TO BE NOTIFIED TO USERS
        if($suggestion !=  null && $suggestion != false && $suggestion->suggestion_suggestion_type_id == UtilController::getSuggestionType("suggestion_type_name", "Drill", 1)){
            // SENDING NOTIFICATION TO USERS
                //echo "here 1\n"; 
            if($suggestion->suggestion_notification_sent == false){
                $suggestion->suggestion_notification_sent = true;
                $suggestion->save();
                UtilController::sendNotificationToTopic(
                    config('app.firebase_notification_server_address_link'), 
                    config('app.firebase_notification_account_key'), 
                    "FISHPOT_ANDROID",
                    "normal",
                    "drill-suggestion",
                    "New Drill - FishPott",
                    "Train your FishPott and increase its intelligence with a new drill",
                    "", 
                    "", 
                    "", 
                    "", 
                    "",
                    date("F j, Y")
                );
                UtilController::sendNotificationToTopic(
                    config('app.firebase_notification_server_address_link'), 
                    config('app.firebase_notification_account_key'), 
                    "FISHPOT_IOS",
                    "normal",
                    "drill-suggestion",
                    "New Drill - FishPott",
                    "Train your FishPott and increase its intelligence with a new drill",
                    "", 
                    "", 
                    "", 
                    "", 
                    "",
                    date("F j, Y")
                );
            }
        } else {
            //echo "here 2\n";     
            $drill = Drill::where('drill_passed_as_suggestion', false)->orderBy('created_at', 'desc')->first();
            if($drill == null){
                //echo "here 3\n";     
                // NOTIFYING FISHPOTT ADMIN THAT NO DRILLS EXIST   
                //echo "here 1"; exit;     
                $email_data = array(
                    'event' => 'There is no new drill for users to answer. Set a new exciting drill and suggest it',
                    'time' => date("F j, Y, g:i a")
                );
                Mail::to(config('app.fishpott_email'))->send(new AlertMail($email_data));
            } else {
                //echo "here 4\n";     
                $suggestionData["suggestion_sys_id"] = "sug-" . $drill->drill_sys_id . date('YmdHis');
                $suggestionData["suggestion_item_reference_id"] = $drill->drill_sys_id;
                $suggestionData["suggestion_directed_at_user_investor_id"] = "";
                $suggestionData["suggestion_directed_at_user_business_find_code"] = "";
                $suggestionData["suggestion_suggestion_type_id"] = 1;            
                $suggestionData["suggestion_passed_on_by_user"] = false;
                $suggestionData["suggestion_notification_sent"] = true;
                $suggestionData["suggestion_flagged"] = false;
                Suggestion::create($suggestionData);
        
                // UPDATING THE DRILL AS SUGGESTED
                $drill->drill_passed_as_suggestion = true;
                $drill->save();

                // SENDING NOTIFICATION TO USERS
                UtilController::sendNotificationToTopic(
                    config('app.firebase_notification_server_address_link'), 
                    config('app.firebase_notification_account_key'), 
                    "FISHPOT_ANDROID",
                    "normal",
                    "drill-suggestion",
                    "New Drill - FishPott",
                    "Train your FishPott and increase its intelligence with a new drill",
                    "", 
                    "", 
                    "", 
                    "", 
                    "",
                    date("F j, Y")
                );
                UtilController::sendNotificationToTopic(
                    config('app.firebase_notification_server_address_link'), 
                    config('app.firebase_notification_account_key'), 
                    "FISHPOT_IOS",
                    "normal",
                    "drill-suggestion",
                    "New Drill - FishPott",
                    "Train your FishPott and increase its intelligence with a new drill",
                    "", 
                    "", 
                    "", 
                    "", 
                    "",
                    date("F j, Y")
                );
            }
        }

    }
}
