<?php

namespace App\Models\version1;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;  
    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'document_id';


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'document_id', 
        'user_id',
        'document_type_id',
        'document_type_fullname',
        'document_origin_country_id',
        'document_origin_country_name',
        'document_number',
        'document_added_notes',
        'document_url',
        'created_at',
        'updated_at',
    ];

}
