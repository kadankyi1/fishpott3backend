<?php

namespace App\Models\version1;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockTransfer extends Model
{
    use HasFactory;
    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $table = 'stocks_transfers';

    protected $primaryKey = 'stocktransfer_id';


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'stocktransfer_id', 
        'stocktransfer_sys_id',
        'stocktransfer_stocks_quantity',
        'stocktransfer_receiver_pottname',
        'stocktransfer_sender_investor_id',
        'stocktransfer_business_id',
        'stocktransfer_total_cost_usd_value_of_shares_transfer',
        'stockstransfers_processed',
        'stockstransfers_processed_reason',
        'stocktransfer_rate_cedi_to_usd',
        'stocktransfer_processing_fee_usd',
        'stocktransfer_processing_local_currency_paid_in_amt',
        'st_processingfee_curr_paid_in_id',
        'stocktransfer_flagged',
        'stocktransfer_flagged_reason',
        'stocktransfer_payment_gateway_status',
        'stocktransfer_payment_gateway_info',
        'created_at',
        'updated_at',
    ];

}
