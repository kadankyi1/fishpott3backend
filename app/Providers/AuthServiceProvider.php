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
            'get-info-on-apps' => 'View information on apps',
            'get-business-suggestions' => 'Get business suggestions',
            'answer-drills' => 'Answer drills',
            'buy-business-stocks' => 'Buy business stocks',
            'transfer-business-stocks' => 'Transfer business stocks',
            
            //ADMIN SCOPE
            'add-admins' => 'Can add administrators Stocks',
            'add-drill' => 'Can add drill',
            'add-business' => 'Can add business',

        ]);
        /*
        if (!$this->app->routesAreCached()) {
            Passport::routes();
        }
        */
    }
}
