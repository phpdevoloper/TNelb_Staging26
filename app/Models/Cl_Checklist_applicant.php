<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cl_Checklist_applicant extends Model
{
    use HasFactory;

    protected $table = 'cl_checklist_applicant';

    protected $fillable = [
        'login_id',
        'applicant_id',
        'cert_license_id',
        'licence_name',
        'checklist_json',
        'updated_by',
    ];
}
