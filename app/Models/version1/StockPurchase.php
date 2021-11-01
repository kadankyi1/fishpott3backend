<?php

namespace App\Models\version1;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockPurchase extends Model
{
    use HasFactory;
    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'stockpurchase_id';


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'stockpurchase_id', 
        'stockpurchase_sys_id',
        'stockpurchase_business_id',
        'stockpurchase_price_per_stock_usd',
        'stockpurchase_quantity',
        'stockpurchase_total_price_no_fees_usd',
        'stockpurchase_risk_insurance_fee_usd',
        'stockpurchase_processing_fee_usd',
        'stockpurchase_total_price_with_all_fees_usd',
        'stockpurchase_currency_paid_in_id',
        'stockpurchase_rate_of_dollar_to_currency_paid_in',
        'stockpurchase_total_all_fees_in_currency_paid_in',
        'stockpurchase_currency_paid_in_fullname',
        'stockpurchase_risk_insurance_type_id',
        'stockpurchase_risk_insurance_type_full_name',
        'stockpurchase_user_investor_id',
        'stockpurchase_processed',
        'stockpurchase_processed_reason',
        'stockpurchase_flagged',
        'stockpurchase_flagged_reason',
        'stockpurchase_payment_gateway_status',
        'stockpurchase_payment_gateway_info',
        'created_at',
        'updated_at',
    ];
}
