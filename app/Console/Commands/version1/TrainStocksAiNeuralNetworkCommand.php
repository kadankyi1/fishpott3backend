<?php

namespace App\Console\Commands\version1;

use Illuminate\Console\Command;

class TrainStocksAiNeuralNetworkCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ai:trainneuralnetwork';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Train the neural network for stocks big five aspects';

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
