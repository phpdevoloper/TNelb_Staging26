<?php

namespace App\Models;

use App\Enums\ScertAlterationStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CAlterationRequest extends Model
{
    protected $table = 'c_alteration_requests';

    protected $fillable = [
        'application_id',
        'target_table',
        'target_row_id',
        'upload_type',
        'old_file_name',
        'old_file_path',
        'new_file_name',
        'new_file_path',
        'reason',
        'status',
        'review_remarks',
        'reviewed_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
        'status' => ScertAlterationStatus::class,
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(ScertApp::class, 'application_id');
    }

    public function isPending(): bool
    {
        return $this->status === ScertAlterationStatus::PENDING;
    }
}
