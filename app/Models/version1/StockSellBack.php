<?php

namespace App\Models\version1;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockSellBack extends Model
{
    use HasFactory;
    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'stocksellback_id';


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'stocksellback_id', 
        'stocksellback_sys_id',
        'stocksellback_stocks_quantity',
        'stocksellback_buyback_offer_per_stock_usd',
        'stocksellback_payout_amt_local_currency_paid_in',
        'stocksellback_rate_dollar_to_local_with_no_signs',
        'stocksellback_processing_fee_usd',
        'stocksellback_local_currency_paid_in_id',
        'stocksellback_receiving_bank_or_momo_account_name',
        'stocksellback_receiving_bank_or_momo_account_number',
        'stocksellback_receiving_bank_or_momo_name',
        'stocksellback_receiving_bank_routing_number',
        'stocksellback_currency_paid_in_fullname',
        'stocksellback_seller_investor_id',
        'stocksellback_processed',
        'stocksellback_processed_reason',
        'stocksellback_flagged',
        'stocksellback_flagged_reason',
        'stocksellback_business_id',
        'created_at',
        'updated_at',
    ];
}
