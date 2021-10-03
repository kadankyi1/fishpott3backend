<?php

namespace App\Models\version1;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResetCode extends Model
{    
    use Notifiable;

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'resetcode_id';



    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'resetcode_id', 
        'user_investor_id',
        'resetcode',
        'resetcode_use_status',
        'created_at',
        'updated_at',
    ];

}
