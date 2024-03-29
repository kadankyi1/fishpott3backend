<?php

// I, DANKYI ANNO KWAKU is putting this project on hold.
return [

    /*
    |--------------------------------------------------------------------------
    | My Var Environment
    |--------------------------------------------------------------------------
    */
    'myenv' => env('MY_APP_ENV', 'debug'),

    // ANDROID APP MINIMUM VERSION ALLOWED
    'timedurationinhoursforsuggestions' => env('TIME_DURATION_IN_HOURS_FOR_SUGGESTIONS', 24),

    // ANDROID APP MINIMUM VERSION ALLOWED
    'timedurationinhoursforbusinesssuggestionstobeavailable' => env('TIME_DURATION_IN_HOURS_FOR_BUSINESS_SUGGESTIONS_TO_BE_AVAILABLE_BUSINESSES', 48),

    // ANDROID APP MINIMUM VERSION ALLOWED
    'androidminvc' => env('ANDROID_MIN_ALLOWED_VERSION_CODE', '7'),

    // iOS APP MINIMUM VERSION ALLOWED
    'iosminvc' => env('IOS_MIN_ALLOWED_VERSION_CODE', '7'),

    // ANDROID APP MAXIMUM VERSION ALLOWED
    'androidmaxvc' => env('ANDROID_MAX_VERSION_CODE', '24'),

    // iOS APP MAXIMUM VERSION ALLOWED
    'iosmaxvc' => env('IOS_MAX_VERSION_CODE', '20'),

    // ANDROID APP FORCE UPDATE REQUIRED
    'androidforceupdatetomaxvc' => env('ANDROID_FORCE_UPDATE_REQUIRED', false),

    // PHONE VERIFICATION REQUIRED STATUS
    'phoneverificationrequiredstatus' => env('PHONE_VERIFICATION_REQUIRED_STATUS', false),

    // ID VERIFICATION REQUIRED STATUS
    'idverificationrequiredstatus' => env('ID_VERIFICATION_REQUIRED_STATUS', false),

    // USER CAN POST PICTURES AND VIDEOS
    'canpostpicsandvids' => env('CAN_POST_PICS_AND_VIDS', 0),


    // PAYMENT CHANNEL
    'payment_channel' => env('PAYMENT_CHANNEL', 'Bank'),

    // MTN GHANA MOBILE MONEY ACCOUNT
    'mtnghanamomonum' => env('MTN_GHANA_MOMO_NUM', '0553663643'),
    'mtnghanamomoaccname' => env('MTN_GHANA_MOMO_ACC_NAME', 'Dankyi Anno Kwaku'),
    
    // VODAFONE GHANA MOBILE MONEY ACCOUNT
    'vodafoneghanamomonum' => env('VODAFONE_GHANA_MOMO_NUM', ''),
    'vodafoneghanamomoaccname' => env('VODAFONE_GHANA_MOMO_NUM', ''),
    
    // AIRTELTIGO GHANA MOBILE MONEY ACCOUNT
    'airteltigoghanamomonum' => env('AIRTELTIGO_GHANA_MOMO_NUM', ''),
    'airteltigoghanamomoaccname' => env('AIRTELTIGO_GHANA_MOMO_NUM', ''),


    // BANK ACCOUNT FOR RECEIVING WIRE TRANSFER
    'bankname' => env('BANK_NAME', 'Access Bank Ghana'),
    'bankaddress' => env('BANK_ADDRESS', 'Starlets 91 Road, Opposite Accra Sports Stadium, Osu. P. O. Box GP 353, Accra, Ghana'),
    'bankswiftiban' => env('BANK_SWIFT_OR_IBAN', 'ABNGGHAC'),
    'bankbranch' => env('BANK_BRANCH', 'Madina'),
    'bankaccountname' => env('BANK_ACCOUNT_NAME', 'FishPot Company Limited'),
    'bankaccountnumber' => env('BANK_ACCOUNT_NUMBER', '1010000002406'),


    // DEFINING VARIOUS SIZES
    'kb' => env('KILO_BYTE', 1024),
    'mb' => env('MEGA_BYTE', 1048576),
    'gb' => env('GIGA_BYTE', 1073741824),
    'tb' => env('TERA_BYTE', 1099511627776),

    // RISK INSURANCE FEE PERCENTAGES
    'zero_risk_insurance' => env('ZERO_RISK_INSURANCE', 0), // you get no payment if the shares lose it's value
    'fifty_risk_insurance' => env('FIFTY_RISK_INSURANCE', 0.05), // you will be given a payment of 50% what you paid if the shares fails to zero 
    'hundred_risk_insurance' => env('HUNDRED_RISK_INSURANCE', 0.1), // you will be given a payment of 100% what you paid if the shares fails to zero
    
    // BUY PROCESSING FEE
    'processing_fee' => env('PROCESSING_FEE', 0.01), 

    // TRANSFER PROCESSING FEE
    'transfer_processing_fee_usd' => env('TRANSFER_PROCESSING_FEE', 10), 

    // CONVERSION RATES
    'to_cedi' => env('USD_TO_CEDI', 10), 

    // SYSTEM CONTACT EMAIL
    'fishpott_email' => env('FISHPOTT_EMAIL', "fishpottcompany@gmail.com"), 
    'fishpott_email_two' => env('FISHPOTT_EMAIL_TWO', "support@fishpott.com"), 
    'fishpott_phone' => env('FISHPOTT_PHONE', "+233 (0)553 663 643"), 

    // PAYMENT GATEWAY
    'payment_gateway_name' => env('PAYMENT_GATEWAY_NAME', "PayStack"), 
    'payment_gateway_login_url' => env('PAYMENT_GATEWAY_URL', "https://dashboard.paystack.com/"), 
    'payment_gateway_secret_key' => env('PAYMENT_GATEWAY_SECRET_KEY', "sk_live_6fdfd9626e7f29080bdc0e24ee7b166eca3bbcb0"), 

    // FIREBASE 
    'firebase_notification_server_address_link' => env('FIREBASE_NOTIFICATION_SERVER_ADDRESS_LINK', "https://fcm.googleapis.com/fcm/send"), 
    'firebase_notification_account_key' => env('FIREBASE_NOTIFICATION_ACCOUNT_SERVER_KEY', "AAAAyNozJtc:APA91bHf8IpIE_vM52ZhLTP7Vi1QDS-EK3urQwX_-0cj5aSlT7TaYU3eKftPv5-d4K3aOqFKqiFN6pTWGB7nhzqV5eF6sFqOmXX9rj5qCPdYp-I-IpbcybJuE5w4S4Zp4tVIuHb4qwDf"), 

    // SMTP
    'unsubscribe_url' => env('UNSUBSCRIBE_URL', "https://app.fishpott.com/user/email/sub-or-unsub"), 

    /*
    |-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|
    | |-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-| ADMINISTRATOR SECTION ADMINISTRATOR SECTION ADMINISTRATOR SECTION ADMINISTRATOR SECTION |-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|
    | |-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-| ADMINISTRATOR SECTION ADMINISTRATOR SECTION ADMINISTRATOR SECTION ADMINISTRATOR SECTION |-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|
    | |-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-| ADMINISTRATOR SECTION ADMINISTRATOR SECTION ADMINISTRATOR SECTION ADMINISTRATOR SECTION |-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|
    |-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|
    */


    // FRONTEND KEY 
    'adminfrontendkey' => env('ADMIN_FRONTEND_KEY', 'Th3j0y'),

    /*
    |-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|
    | |-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-| Ai SECTION Ai SECTION Ai SECTION Ai SECTION Ai SECTION Ai SECTION Ai SECTION Ai SECTION |-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|
    | |-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-| Ai SECTION Ai SECTION Ai SECTION Ai SECTION Ai SECTION Ai SECTION Ai SECTION Ai SECTION |-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|
    | |-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-| Ai SECTION Ai SECTION Ai SECTION Ai SECTION Ai SECTION Ai SECTION Ai SECTION Ai SECTION |-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|
    |-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|
    */

    // FRONTEND KEY 
    'ai_data_range_min' => env('AI_DATA_RANGE_MIN', 0),
    'ai_data_range_max' => env('AI_DATA_RANGE_MAX', 100),

    // BIG FIVE ASPECTS TYPE CONSTANTS
    'openness_to_experience' => env('OPENNESS_TO_EXPERIENCE', 1),
    'conscientiousness' => env('CONSCIENTIOUSNESS', 2),
    'extraversion' => env('EXTRAVERSION', 3),
    'agreeableness' => env('AGREEABLENESS', 4),
    'neuroticism' => env('NEUROTICISM', 5),


    /*
    |--------------------------------------------------------------------------
    | Application Name
    |--------------------------------------------------------------------------
    |
    | This value is the name of your application. This value is used when the
    | framework needs to place the application's name in a notification or
    | any other location as required by the application or its packages.
    |
    */

    'name' => env('APP_NAME', 'FishPott'),

    /*
    |--------------------------------------------------------------------------
    | Application Environment
    |--------------------------------------------------------------------------
    |
    | This value determines the "environment" your application is currently
    | running in. This may determine how you prefer to configure various
    | services the application utilizes. Set this in your ".env" file.
    |
    */

    'env' => env('APP_ENV', 'debug'),

    /*
    |--------------------------------------------------------------------------
    | Application Debug Mode
    |--------------------------------------------------------------------------
    |
    | When your application is in debug mode, detailed error messages with
    | stack traces will be shown on every error that occurs within your
    | application. If disabled, a simple generic error page is shown.
    |
    */

    'debug' => (bool) env('APP_DEBUG', true),

    /*
    |--------------------------------------------------------------------------
    | Application URL
    |--------------------------------------------------------------------------
    |
    | This URL is used by the console to properly generate URLs when using
    | the Artisan command line tool. You should set this to the root of
    | your application so that it is used when running Artisan tasks.
    |
    */

    'url' => env('APP_URL', 'https://test.fishpott.com'),

    'asset_url' => env('ASSET_URL', null),

    /*
    |--------------------------------------------------------------------------
    | Application Timezone
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default timezone for your application, which
    | will be used by the PHP date and date-time functions. We have gone
    | ahead and set this to a sensible default for you out of the box.
    |
    */

    'timezone' => 'UTC',

    /*
    |--------------------------------------------------------------------------
    | Application Locale Configuration
    |--------------------------------------------------------------------------
    |
    | The application locale determines the default locale that will be used
    | by the translation service provider. You are free to set this value
    | to any of the locales which will be supported by the application.
    |
    */

    'locale' => 'en',

    /*
    |--------------------------------------------------------------------------
    | Application Fallback Locale
    |--------------------------------------------------------------------------
    |
    | The fallback locale determines the locale to use when the current one
    | is not available. You may change the value to correspond to any of
    | the language folders that are provided through your application.
    |
    */

    'fallback_locale' => 'en',

    /*
    |--------------------------------------------------------------------------
    | Faker Locale
    |--------------------------------------------------------------------------
    |
    | This locale will be used by the Faker PHP library when generating fake
    | data for your database seeds. For example, this will be used to get
    | localized telephone numbers, street address information and more.
    |
    */

    'faker_locale' => 'en_US',

    /*
    |--------------------------------------------------------------------------
    | Encryption Key
    |--------------------------------------------------------------------------
    |
    | This key is used by the Illuminate encrypter service and should be set
    | to a random, 32 character string, otherwise these encrypted strings
    | will not be safe. Please do this before deploying an application!
    |
    */

    'key' => env('APP_KEY'),

    'cipher' => 'AES-256-CBC',

    /*
    |--------------------------------------------------------------------------
    | Autoloaded Service Providers
    |--------------------------------------------------------------------------
    |
    | The service providers listed here will be automatically loaded on the
    | request to your application. Feel free to add your own services to
    | this array to grant expanded functionality to your applications.
    |
    */

    'providers' => [

        /*
         * Laravel Framework Service Providers...
         */
        Illuminate\Auth\AuthServiceProvider::class,
        Illuminate\Broadcasting\BroadcastServiceProvider::class,
        Illuminate\Bus\BusServiceProvider::class,
        Illuminate\Cache\CacheServiceProvider::class,
        Illuminate\Foundation\Providers\ConsoleSupportServiceProvider::class,
        Illuminate\Cookie\CookieServiceProvider::class,
        Illuminate\Database\DatabaseServiceProvider::class,
        Illuminate\Encryption\EncryptionServiceProvider::class,
        Illuminate\Filesystem\FilesystemServiceProvider::class,
        Illuminate\Foundation\Providers\FoundationServiceProvider::class,
        Illuminate\Hashing\HashServiceProvider::class,
        Illuminate\Mail\MailServiceProvider::class,
        Illuminate\Notifications\NotificationServiceProvider::class,
        Illuminate\Pagination\PaginationServiceProvider::class,
        Illuminate\Pipeline\PipelineServiceProvider::class,
        Illuminate\Queue\QueueServiceProvider::class,
        Illuminate\Redis\RedisServiceProvider::class,
        Illuminate\Auth\Passwords\PasswordResetServiceProvider::class,
        Illuminate\Session\SessionServiceProvider::class,
        Illuminate\Translation\TranslationServiceProvider::class,
        Illuminate\Validation\ValidationServiceProvider::class,
        Illuminate\View\ViewServiceProvider::class,

        /*
         * Package Service Providers...
         */

        /*
         * Application Service Providers...
         */
        App\Providers\AppServiceProvider::class,
        App\Providers\AuthServiceProvider::class,
        // App\Providers\BroadcastServiceProvider::class,
        App\Providers\EventServiceProvider::class,
        App\Providers\RouteServiceProvider::class,

    ],

    /*
    |--------------------------------------------------------------------------
    | Class Aliases
    |--------------------------------------------------------------------------
    |
    | This array of class aliases will be registered when this application
    | is started. However, feel free to register as many as you wish as
    | the aliases are "lazy" loaded so they don't hinder performance.
    |
    */

    'aliases' => [

        'App' => Illuminate\Support\Facades\App::class,
        //'Arr' => Illuminate\Support\Arr::class,
        'Artisan' => Illuminate\Support\Facades\Artisan::class,
        'Auth' => Illuminate\Support\Facades\Auth::class,
        'Blade' => Illuminate\Support\Facades\Blade::class,
        'Broadcast' => Illuminate\Support\Facades\Broadcast::class,
        'Bus' => Illuminate\Support\Facades\Bus::class,
        'Cache' => Illuminate\Support\Facades\Cache::class,
        'Config' => Illuminate\Support\Facades\Config::class,
        'Cookie' => Illuminate\Support\Facades\Cookie::class,
        'Crypt' => Illuminate\Support\Facades\Crypt::class,
        'Date' => Illuminate\Support\Facades\Date::class,
        'DB' => Illuminate\Support\Facades\DB::class,
        'Eloquent' => Illuminate\Database\Eloquent\Model::class,
        'Event' => Illuminate\Support\Facades\Event::class,
        'File' => Illuminate\Support\Facades\File::class,
        'Gate' => Illuminate\Support\Facades\Gate::class,
        'Hash' => Illuminate\Support\Facades\Hash::class,
        'Http' => Illuminate\Support\Facades\Http::class,
        'Lang' => Illuminate\Support\Facades\Lang::class,
        'Log' => Illuminate\Support\Facades\Log::class,
        'Mail' => Illuminate\Support\Facades\Mail::class,
        'Notification' => Illuminate\Support\Facades\Notification::class,
        'Password' => Illuminate\Support\Facades\Password::class,
        'Queue' => Illuminate\Support\Facades\Queue::class,
        'RateLimiter' => Illuminate\Support\Facades\RateLimiter::class,
        'Redirect' => Illuminate\Support\Facades\Redirect::class,
        // 'Redis' => Illuminate\Support\Facades\Redis::class,
        'Request' => Illuminate\Support\Facades\Request::class,
        'Response' => Illuminate\Support\Facades\Response::class,
        'Route' => Illuminate\Support\Facades\Route::class,
        'Schema' => Illuminate\Support\Facades\Schema::class,
        'Session' => Illuminate\Support\Facades\Session::class,
        'Storage' => Illuminate\Support\Facades\Storage::class,
        'Str' => Illuminate\Support\Str::class,
        'URL' => Illuminate\Support\Facades\URL::class,
        'Validator' => Illuminate\Support\Facades\Validator::class,
        'View' => Illuminate\Support\Facades\View::class,

    ],

];
