<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tnelb_CC_Digitization extends Model
{
    use HasFactory;

    protected $table = 'tnelb_cc_digitization';
    protected $fillable = [
        'login_id',
        'application_id',
        'form_name',
        'cert_name',
        'temp_app_id',
        'ccnumber',
        'fissue',
        'from_date',
        'to_date',
        'qc',
        'qsc',
        'cl_type',
        'licence_no',
        'contractor_name',
        'qc_doc',
        'cc_doc',
        'other1',
        'other2',
        'original_name',
        'qc_original_name',
        'qc_det',
        'new_cc_no'
    ];

}
