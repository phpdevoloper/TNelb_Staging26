<?php

namespace App\Models;

use App\Models\Admin\Mst_Logins;
use App\Models\Admin\Mst_Roles;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tnelb_scert extends Model
{
    use HasFactory;

    protected $table = 'tnelb_applications';

    protected $fillable = [
        'login_id',
        'application_id',
        'form_id',
        'form_name',
        'applicant_name',
        'fathers_name',
        'applicant_address',
        'dob',
        'age',
        'aadhaar',
        'pancard',
        'aadhaar_doc',
        'pan_doc',
        'license_name',
        'license_number',
        'certificate_no',
        'certificate_date',
        'cert_verify',
        'license_verify',
        'previously_number',
        'previously_date',
        'wireman_details',
        'status',
        'current_status',
        'current_role_id',
        'payment_status',
        'appl_type',
        'submitted_by',
        'processed_by',
        'submitted_date',
    ];

    protected $casts = [
        'dob' => 'date',
        'certificate_date' => 'date',
        'submitted_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /* ========================
       Relationships
    ======================== */

    public function form()
    {
        return $this->belongsTo(MstLicence::class, 'form_id');
    }

    public function currentRole()
    {
        return $this->belongsTo(Mst_Roles::class, 'current_role_id');
    }

    public function submittedUser()
    {
        return $this->belongsTo(Mst_Logins::class, 'submitted_by');
    }

    public function processedUser()
    {
        return $this->belongsTo(Mst_Logins::class, 'processed_by');
    }

    public function workflowHistory()
    {
        return $this->hasMany(WorkflowHistory::class, 'application_id');
    }
}
