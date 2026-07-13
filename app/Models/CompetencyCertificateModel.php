<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Base for per-form issued certificate tables (N/R/D in one row per application).
 */
abstract class CompetencyCertificateModel extends Model
{
    public $timestamps = false;

    protected $primaryKey = 'cc_id';

    protected $fillable = [
        'application_id',
        'certificate_no',
        'dateof_issue',
        'valid_from',
        'valid_to',
        'cert_status',
        'cert_pdf',
        'issued_by',
        'created_at',
        'updated_at',
    ];
}
