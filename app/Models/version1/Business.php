<?php

namespace App\Models\version1;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Business extends Model
{
    use HasFactory;
        /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'business_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'business_id', 
        'business_sys_id', 
        'business_type',
        'business_full_name',
        'business_short_name',
        'business_business_ceo_name',
        'business_business_pott_name',
        'business_business_address',
        'business_business_country',
        'business_business_revenue_last_year',
        'business_business_revenue_last_year',
        'business_business_pitch_text',
        'business_business_pitch_video',
        'business_flagged',
        'created_at',
        'updated_at',
    ];

}
