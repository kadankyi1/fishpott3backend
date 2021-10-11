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
        'suggesto_type',
        'suggesto_reference_id',
        'suggesto_flagged',
        'suggesto_maker_investor_id',
        'suggesto_maker_investor_name',
        'created_at',
        'updated_at',
    ];
}
