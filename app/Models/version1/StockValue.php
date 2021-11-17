<?php

namespace App\Models\version1;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockValue extends Model
{
    use HasFactory;
    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'stockvalue_id';


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'stockvalue_id', 
        'stockvalue_value_per_stock_usd',
        'stockvalue_value_change',
        'stockvalue_value_volume',
        'stockvalue_business_id',
        'stockvalue_admin_adder_id',
        'created_at',
        'updated_at',
    ];

}
