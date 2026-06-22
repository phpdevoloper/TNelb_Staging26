<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Work experience rows (`tnelb_applicants_exp`).
 *
 * @property int|null $id
 * @property string $login_id
 * @property string $application_id
 * @property string|null $emp_type
 * @property string|null $emp_cate
 * @property string|null $org_name
 * @property string|null $org_address
 * @property \Illuminate\Support\Carbon|null $from_date
 * @property \Illuminate\Support\Carbon|null $to_date
 * @property int|null $total_y
 * @property int|null $total_m
 * @property int|null $total_d
 * @property string|null $designation
 * @property string|null $nature_work
 * @property string|null $voltage_level
 * @property string|null $transformer_kva
 * @property string|null $releive_document
 * @property string|null $support_document
 * @property string|null $total_exp
 */
class Mst_experience extends Model
{
    use HasFactory;

    protected $table = 'tnelb_applicants_exp';

    protected $fillable = [
        'login_id',
        'application_id',
        'emp_type',
        'emp_cate',
        'org_name',
        'org_address',
        'from_date',
        'to_date',
        'total_y',
        'total_m',
        'total_d',
        'designation',
        'nature_work',
        'voltage_level',
        'transformer_kva',
        'releive_document',
        'support_document',
        'total_exp',
        'qualified_supervisor_recognized',
        'endorsed_license_type',
        'endorsed_license_number',
        'endorsed_contractor_name',
        'endorsed_support_document',
    ];

    protected $casts = [
        'from_date' => 'date',
        'to_date' => 'date',
        'total_y' => 'integer',
        'total_m' => 'integer',
        'total_d' => 'integer',
        'transformer_kva' => 'decimal:2',
        'total_exp' => 'decimal:2',
    ];

    /* ── Legacy attribute aliases (older controllers / views) ── */

    public function getUploadDocumentAttribute(): ?string
    {
        return $this->attributes['support_document'] ?? null;
    }

    public function setUploadDocumentAttribute(?string $value): void
    {
        $this->attributes['support_document'] = $value;
    }

    public function getCompanyNameAttribute(): ?string
    {
        return $this->attributes['org_name'] ?? null;
    }

    public function setCompanyNameAttribute(?string $value): void
    {
        $this->attributes['org_name'] = $value;
    }

    /** @deprecated Use total_exp */
    public function getExperienceAttribute(): ?string
    {
        $v = $this->attributes['total_exp'] ?? null;

        return $v !== null && $v !== '' ? (string) $v : null;
    }

    /** @deprecated Use total_exp */
    public function setExperienceAttribute(string|int|float|null $value): void
    {
        $this->attributes['total_exp'] = $value !== null && $value !== '' ? $value : null;
    }
}
