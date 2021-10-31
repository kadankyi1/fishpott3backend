<?php

namespace App\Models\version1;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Withdrawal extends Model
{
    use HasFactory;
    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'withdrawal_id';


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'withdrawal_id', 
        'withdrawal_sys_id',
        'withdrawal_amt_usd',
        'withdrawal_amt_local',
        'withdrawal_local_currency_sign',
        'withdrawal_rate',
        'withdrawal_receiving_bank_or_momo_account_name',
        'withdrawal_receiving_bank_or_momo_account_number',
        'withdrawal_receiving_bank_or_momo_name',
        'withdrawal_receiving_bank_routing_number',
        'withdrawal_paid',
        'withdrawal_flagged',
        'withdrawal_flagged_reason',
        'withdrawal_user_investor_id',
        'withdrawal_user_full_name',
        'created_at',
        'updated_at',
    ];
}
