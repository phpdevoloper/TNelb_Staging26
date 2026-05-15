<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mst_experience extends Model
{
    use HasFactory;

    protected $table = 'tnelb_applicants_exp';

    protected $fillable = [
        'emp_type',
        'emp_cate',
        'intimation_date',
        'from_date',
        'to_date',
        'total_y',
        'total_m',
        'total_d',
        'designation',
        'upload_document',
        'login_id',
        'application_id',
        'exp_serial',
        // keep legacy keys temporarily to avoid mass-assignment breakage
        'company_name',
        'experience',
        'document',
    ];

    protected $casts = [
        'intimation_date' => 'date',
        'from_date' => 'date',
        'to_date' => 'date',
    ];
}
