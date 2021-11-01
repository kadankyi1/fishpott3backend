<?php

namespace App\Models\version1;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiskInsuranceType extends Model
{

    use HasFactory;
    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'risk_type_id';


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'risk_type_id', 
        'risk_type_fullname',
        'risk_type_shortname',
        'risk_type_description',
        'created_at',
        'updated_at',
    ];}
