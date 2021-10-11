<?php

namespace App\Models\version1;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Suggesto extends Model
{
    use HasFactory;

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'suggesto_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'suggesto_id', 
        'suggesto_sys_id', 
        'suggesto_question',
        'suggesto_answer_1',
        'suggesto_answer_2',
        'suggesto_answer_3',
        'suggesto_answer_4',
        'suggesto_answer_implied_traits_1',
        'suggesto_answer_implied_traits_2',
        'suggesto_answer_implied_traits_3',
        'suggesto_answer_implied_traits_4',
        'suggesto_flagged',
        'suggesto_maker_investor_id',
        'suggesto_maker_investor_name',
        'created_at',
        'updated_at',
    ];
}
