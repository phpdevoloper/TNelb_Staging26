<?php

namespace App\Models\Competency;

use Illuminate\Database\Eloquent\Model;

/**
 * Admin workflow history row for competency certificate applications.
 *
 * @property int $w_id
 * @property string|null $login_id
 * @property string $application_id
 * @property int|null $processed_by
 * @property int|null $forwarded_to
 * @property int $role_id
 * @property string $appl_status
 * @property string|null $remarks
 * @property string|null $query_status
 * @property string $is_verified
 * @property array|null $queries
 * @property string|null $raised_by
 * @property string|null $chklist_status
 * @property string|null $reject_reason
 */
abstract class CC_CompetencyWorkflow extends Model
{
    protected $primaryKey = 'w_id';

    public $timestamps = true;

    const CREATED_AT = 'created_at';

    const UPDATED_AT = 'updated_at';

    protected $fillable = [
        'login_id',
        'application_id',
        'processed_by',
        'forwarded_to',
        'role_id',
        'appl_status',
        'remarks',
        'query_status',
        'is_verified',
        'queries',
        'raised_by',
        'chklist_status',
        'reject_reason',
    ];

    protected $casts = [
        'processed_by' => 'integer',
        'forwarded_to' => 'integer',
        'role_id' => 'integer',
        'queries' => 'array',
    ];
}
