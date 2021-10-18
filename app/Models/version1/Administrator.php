<?php

namespace App\Models\version1;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class Administrator extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;


    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'admin_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'admin_id', 
        'admin_pottname',
        'admin_surname', 
        'admin_firstname', 
        'admin_othernames', 
        'admin_phone_number',
        'admin_email',
        'admin_pin',
        'password',
        'admin_flagged',
        'admin_scope',
        'created_at',
        'updated_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'admin_pin', 'remember_token',
        'password', 'remember_token',
    ];
}
