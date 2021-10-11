<?php

namespace App\Models\version1;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;
        /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'question_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'question_id', 
        'question_sys_id', 
        'question_question',
        'question_answer_1',
        'question_answer_2',
        'question_answer_3',
        'question_answer_4',
        'question_answer_implied_traits_1',
        'question_answer_implied_traits_2',
        'question_answer_implied_traits_3',
        'question_answer_implied_traits_4',
        'question_flagged',
        'question_maker_investor_id',
        'question_maker_investor_name',
        'created_at',
        'updated_at',
    ];

}
