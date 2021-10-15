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
    protected $primaryKey = 'suggestion_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'suggestion_id', 
        'suggestion_sys_id', 
        'suggestion_item_reference_id',
        'suggestion_broadcasted',
        'suggestion_flagged',
        'suggestion_suggestion_type_id',
        'suggestion_type',
        'created_at',
        'updated_at',
    ];
}
