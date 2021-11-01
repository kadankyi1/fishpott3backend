<?php

namespace App\Models\version1;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockOwnership extends Model
{
    use HasFactory;
    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'stockownership_id';


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'stockownership_id', 
        'stockownership_sys_id',
        'stockownership_stocks_quantity',
        'stockownership_total_cost_usd',
        'stockownership_flagged',
        'stockownership_flagged_reason',
        'stockownership_business_id',
        'stockownership_user_investor_id',
        'created_at',
        'updated_at',
    ];
}
