<?php

namespace App\Models\version1;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'user_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'user_id', 
        'user_user_type_id',
        'user_type',
        'investor_id', 
        'user_surname', 
        'user_firstname', 
        'user_pottname', 
        'user_dob', 
        'user_phone_number',
        'user_email',
        'user_profile_picture',
        'password', 
        'user_gender_id',
        'user_gender', 
        'user_country_id',
        'user_country', 
        'user_language_id',
        'user_language', 
        'user_currency_id',
        'user_currency', 
        'user_net_worth', 
        'user_verified_tag', 
        'user_shield_date', 
        'user_referred_by', 
        'user_pott_ruler', 
        'user_fcm_token_android', 
        'user_fcm_token_web', 
        'user_fcm_token_ios',
        'user_added_to_sitemap',  
        'user_reviewed_by_admin',  
        'user_scope',
        'user_android_app_version_code',
        'user_ios_app_version_code',
        'user_phone_verified_status',
        'user_phone_verifcation_date',
        'user_phone_verification_requested',
        'user_id_verified_status',
        'user_id_verifcation_date',
        'user_id_verification_requested',
        'user_password_reset_code',
        'user_last_sms_sent_datetime',
        'user_can_post_media',
        'user_initial_signup_approved',
        'user_flagged',
        'user_flagged_reason',
        'login_at',
        'created_at',
        'updated_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
}
