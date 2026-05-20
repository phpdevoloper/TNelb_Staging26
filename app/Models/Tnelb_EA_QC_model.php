<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tnelb_EA_QC_model extends Model
{
    use HasFactory;
    protected $table = 'tnelb_ea_qc_models';

    protected $fillable = [
        'login_id',
        'application_id',
        'form_name',
        'license_name',
        'staffname',
        'category',
        'cc_number',
        'cc_validity',
        'staff_qccc_verify',
        'status',
        'flag',
        'other',
    ];
}
