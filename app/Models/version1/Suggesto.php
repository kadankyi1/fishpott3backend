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
        'suggesto_item_reference_id',
        'suggesto_broadcasted',
        'suggesto_flagged',
        'suggesto_suggesto_type_id',
        'suggesto_type',
        'created_at',
        'updated_at',
    ];
}
