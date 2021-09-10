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
        'App\Models\Model' => 'App\Policies\ModelPolicy',
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
            'add-currency' => 'Add currency',
            'view-currencies' => 'View currencies',
            'get-one-currency' => 'View one currency',

        ]);
        /*
        if (!$this->app->routesAreCached()) {
            Passport::routes();
        }
        */
    }
}
