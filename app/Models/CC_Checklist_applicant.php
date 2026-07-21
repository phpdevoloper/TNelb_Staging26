<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CC_Checklist_applicant extends Model
{
    use HasFactory;

    protected $table = 'cc_checklist_applicant';

    protected $fillable = [
        'login_id',
        'applicant_id',
        'cert_license_id',
        'certificate_name',
        'checklist_json',
        'updated_by',
    ];
}
