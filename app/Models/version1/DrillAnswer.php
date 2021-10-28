<?php

namespace App\Models\version1;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DrillAnswer extends Model
{
    use HasFactory;

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'drill_answer_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'drill_answer_id', 
        'drill_answer_sys_id', 
        'drill_answer_', 
        'drill_answer_used_for_pott_intelligence_calculation', 
        'drill_answer_drill_sys_id',
        'user_investor_id',
        'created_at',
        'updated_at',
    ];
}
