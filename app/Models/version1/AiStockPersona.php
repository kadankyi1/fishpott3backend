<?php

namespace App\Models\version1;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiStockPersona extends Model
{
    use HasFactory;
    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'aistockpersona_id';


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    
    protected $fillable = [
        'aistockpersona_id', 
        'aistockpersona_openness_to_experience',
        'aistockpersona_conscientiousness',
        'aistockpersona_extraversion',
        'aistockpersona_agreeableness',
        'aistockpersona_neuroticism',
        'aistockpersona_stock_business_id', 
        'created_at',
        'updated_at',
    ];

}
