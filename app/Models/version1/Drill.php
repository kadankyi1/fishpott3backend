<?php

namespace App\Models\version1;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Drill extends Model
{
    use HasFactory;
        /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'drill_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'drill_id', 
        'drill_sys_id', 
        'drill_question',
        'drill_answer_1',
        'drill_answer_2',
        'drill_answer_3',
        'drill_answer_4',
        'drill_answer_1_ocean',
        'drill_answer_2_ocean',
        'drill_answer_3_ocean',
        'drill_answer_4_ocean',
        'drill_passed_as_suggestion',
        'drill_flagged',
        'drill_maker_investor_id',
        'drill_maker_investor_name',
        'created_at',
        'updated_at',
    ];

}
