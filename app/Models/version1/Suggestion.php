<?php

namespace App\Models\version1;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Suggestion extends Model
{
    use HasFactory;

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'suggestion_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'suggestion_id', 
        'suggestion_sys_id', 
        'suggestion_type',
        'suggestion_item_reference_id',
        'suggestion_directed_at_user_investor_id',
        'suggestion_passed_on_by_user',
        'suggestion_flagged',
        'suggestion_suggestion_type_id',
        'created_at',
        'updated_at',
    ];
}
