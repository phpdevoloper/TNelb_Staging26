<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProprietorformA extends Model
{
    use HasFactory;
    protected $table = 'cl_ownership_table';

    // Mass assignable attributes
    protected $fillable = [
        'login_id',
        'application_id',
        'proprietor_name',
        'proprietor_address',
        'dob',
        'age',
        'qualification',
        'qualification_text',
        'fathers_name',
        'present_business',
        'competency_certificate_holding',
        'competency_certificate_number',
        'competency_certificate_validity',
        'competency_certificate_first_issue',
        'competency_certificate_validity_from',
        'competency_certificate_validity_to',
        'educ_qual_proof',
        'educational_proof',
        'proprietor_flag',
        'ownership_type',
        'ownership_count',
        'age_proof',
        'managing_director'
    ];
}
