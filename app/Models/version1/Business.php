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
        'business_pottname',
        'business_flagged', 
        'business_flagged_reason', 
        'created_at',
        'updated_at',
        // BIO
        'business_registration_number',
        'business_type',
        'business_logo', //
        'business_full_name', //
        'business_stockmarket_shortname', //
        'business_descriptive_bio', //
        'business_address', //
        'business_country', //
        'business_country_id', //
        'business_start_date', //
        'business_website', //
        // PITCH
        'business_pitch_text', //
        'business_pitch_video', //
        // FINANCIALS
        'business_lastyr_revenue_usd', //
        'business_lastyr_profit_or_loss_usd', //
        'business_debt_usd', //
        'business_cash_on_hand_usd',
        'business_net_worth_usd', //
        'business_price_per_stock_usd',
        'business_investments_amount_needed_usd',
        'business_investments_amount_received_usd',
        'business_investments_amount_left_to_receive_usd',
        'business_maximum_number_of_investors_allowed',
        'business_current_shareholders',
        'business_full_financial_report_pdf_url',
        'business_descriptive_financial_bio',
        // TEAM
        'business_executive1_firstname', //
        'business_executive1_lastname', //
        'business_executive1_profile_picture',
        'business_executive1_position', //
        'business_executive1_description',
        'business_executive1_facebook_url',
        'business_executive1_linkedin_url',
        'business_executive2_firstname', //
        'business_executive2_lastname', //
        'business_executive2_profile_picture',
        'business_executive2_position', //
        'business_executive2_description',
        'business_executive2_facebook_url',
        'business_executive2_linkedin_url',
        'business_executive3_firstname',
        'business_executive3_lastname',
        'business_executive3_profile_picture',
        'business_executive3_position', //
        'business_executive3_description',
        'business_executive3_facebook_url',
        'business_executive3_linkedin_url',
        'business_executive4_firstname',
        'business_executive4_lastname',
        'business_executive4_profile_picture',
        'business_executive4_position',//
        'business_executive4_description',
        'business_executive4_facebook_url',
        'business_executive4_linkedin_url',
    ];

}
