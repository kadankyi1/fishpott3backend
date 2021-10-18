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
     * The administrator the table.
     *
     * @var string
     */

    protected $table = 'administrators';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'administrator_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'administrator_id', 
        'administrator_sys_id',
        'administrator_user_pottname',
        'administrator_surname', 
        'administrator_firstname', 
        'administrator_phone_number',
        'administrator_email',
        'administrator_pin',
        'password',
        'administrator_scope',
        'administrator_flagged',
        'added_by_administrator_id',
        'added_by_administrator_name',
        'created_at',
        'updated_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'administrator_pin', 'remember_token',
        'password', 'remember_token',
    ];
}
