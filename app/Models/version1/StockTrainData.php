<?php

namespace App\Models\version1;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockTrainData extends Model
{
    use HasFactory;
    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'stocktraindata_id';


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'stocktraindata_id', 
        'stocktraindata_value_per_stock_usd',
        'stocktraindata_value_change',
        'stocktraindata_value_volume',
        'stocktraindata_expected_output',
        'stocktraindata_admin_adder_id',
        'created_at',
        'updated_at',
    ];

}
