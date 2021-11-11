<?php

namespace App\Console\Commands\version1;

use Illuminate\Console\Command;
use App\Http\Controllers\version1\UtilController;

class sendNetworthAndPositionNotificationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'networthandposition:sendnetworthandpositionnotification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send notification to inform users of their pott net worth and position';

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
        UtilController::calculateUsersNetworthAndSetPosition();
    }
}
