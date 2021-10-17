<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        //'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Passport::routes();
        Passport::tokensCan([

            //ADMIN SCOPE
            'view-info' => 'View Information',
            'get-stock-suggestions' => 'Get Stock Suggestions',
            'answer-questions' => 'Answer Questions',
            'buy-stock-suggested' => 'Buy Stocks Suggested',
            'trade-stocks' => 'Trade Stocks',

        ]);
        /*
        if (!$this->app->routesAreCached()) {
            Passport::routes();
        }
        */
    }
}
