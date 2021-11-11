<?php

namespace App\Console\Commands\version1;

use Illuminate\Console\Command;
use App\Http\Controllers\version1\UtilController;

class sendBusinessReadyNotificationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'business:sendreadynotification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Match user with business stock and notification to the user that it is available';

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
        UtilController::matchUsersToABusinesses();
    }
}
