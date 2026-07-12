<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CC_Forms_Meta extends Model
{
    use HasFactory;

    protected $table = 'cc_form_s_meta';
    protected $primaryKey = 'app_id';
    public $incrementing = true;
    public $timestamps = false;
    protected $fillable = [
        'app_id',
        'login_id',
        'application_id',
        'applicant_name', 
        'fathers_name',
        'applicant_email',
        'applicant_address', 
        'd_o_b', 
        'age', 
        'form_name',
        'certificate_name',
        'form_id',
        'appl_type',
        'app_status',
        'payment_status',
        'payment_status',
        'processed_by',
        'certificate_no',
        'old_application',
        'previous_scc_no',
        'first_issue_date',
        'scc_from_date',
        'scc_to_date',
        'wcc_no',
        'wcc_issue_date',
        'wcc_from',
        'wcc_to',
        'qc',
        'qsc',
        'submitted_date',
        'updated_at',
        'created_at'

    ];

    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            if ($model->app_id === null || $model->app_id === '') {
                $model->app_id = (int) (static::max('app_id') ?? 0) + 1;
            }
        });
    }
}
