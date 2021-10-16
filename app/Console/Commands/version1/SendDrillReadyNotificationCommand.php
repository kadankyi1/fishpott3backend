<?php

namespace App\Console\Commands\version1;

use Illuminate\Console\Command;

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
        // SENDING DRILL READY NOTIFICATION
        
    }
}
