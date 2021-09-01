<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

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
        'user_surname', 
        'user_firstname', 
        'user_pottname', 
        'user_dob', 
        'user_phone_number',
        'user_email',
        'user_profile_picture', 
        'password',
        'user_gender', 
        'user_country', 
        'user_language', 
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
        'user_flagged',
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
