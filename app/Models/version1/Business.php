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
        // SYST
        'business_id', 
        'business_sys_id', 
        'business_business_pott_name',
        
        // BIO
        'business_type',
        'business_full_name',
        'business_short_name',
        'business_business_address',
        'business_business_country',
        'business_business_revenue_last_year',
        'business_business_revenue_last_year',
        'business_business_pitch_text',
        'business_business_pitch_video',
        // TEAM
        'business_business_executive1_firstname',
        'business_business_executive1_lastname',
        'business_business_executive1_profile_picture',
        'business_business_executive1_description',
        'business_business_executive2_firstname',
        'business_business_executive2_lastname',
        'business_business_executive2_profile_picture',
        'business_business_executive2_description',
        'business_business_executive3_firstname',
        'business_business_executive3_lastname',
        'business_business_executive3_profile_picture',
        'business_business_executive3_description',
        'business_business_executive4_firstname',
        'business_business_executive4_lastname',
        'business_business_executive4_profile_picture',
        'business_business_executive4_description',
        //s
        'business_flagged',
        'created_at',
        'updated_at',
    ];

}
