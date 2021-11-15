<?php

namespace App\Models\ai;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiStockValue extends Model
{
    use HasFactory;

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'aistockvalue_id';


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'aistockvalue_id', 
        'aistockvalue_value_usd',
        'aistockvalue_value_datetime',
        'created_at',
        'updated_at',
    ];
}
