<?php

namespace App\Models\version1;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Business extends Model
{
    use HasFactory;
        /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'business_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        // SYSTEM
        'business_id', 
        'business_sys_id', 
        'business_pott_name',
        'business_flagged',
        'created_at',
        'updated_at',
        // BIO
        'business_type',
        'business_logo',
        'business_full_name',
        'business_short_name',
        'business_descriptive_bio',
        'business_address',
        'business_country',
        // PITCH
        'business_pitch_text',
        'business_pitch_video',
        // FINANCIALS
        'business_revenue',
        'business_loss',
        // TEAM
        'business_executive1_firstname',
        'business_executive1_lastname',
        'business_executive1_profile_picture',
        'business_executive1_description',
        'business_executive2_firstname',
        'business_executive2_lastname',
        'business_executive2_profile_picture',
        'business_executive2_description',
        'business_executive3_firstname',
        'business_executive3_lastname',
        'business_executive3_profile_picture',
        'business_executive3_description',
        'business_executive4_firstname',
        'business_executive4_lastname',
        'business_executive4_profile_picture',
        'business_executive4_description',
    ];

}
