<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\version1\SendDrillReadyNotificationCommand::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // ADDING SCHDULE
        $schedule->command('drill:sendreadynotification')->hourly(); //->hourly();
        $schedule->command('networthandposition:sendnetworthandpositionnotification')->daily(); //->daily();
        $schedule->command('business:sendreadynotification')->weekly(); //->hourly();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands/version1');

        require base_path('routes/console.php');
    }
}
