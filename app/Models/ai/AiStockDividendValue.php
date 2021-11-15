<?php

namespace App\Models\ai;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiStockDividendValue extends Model
{
    use HasFactory;

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'aistockdividendvalue_id';


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'aistockdividendvalue_id', 
        'aistockdividendvalue_value_usd',
        'aistockdividendvalue_value_datetime',
        'created_at',
        'updated_at',
    ];

}
