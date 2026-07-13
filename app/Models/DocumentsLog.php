<?php

namespace App\Models;

use App\Enums\DocumentApplicationType;
use App\Enums\DocumentRequestType;
use App\Enums\DocumentStorageType;
use App\Enums\DocumentVersionStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use App\Models\CC_Forms_Meta;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Production document audit log (table: documents_log).
 * application_id / parent_application_id = tnelb_application_tbl.id
 */
class DocumentsLog extends Model
{
    protected $table = 'documents_log';

    protected $fillable = [
        'application_id',
        'parent_application_id',
        'module_type',
        'module_ref_id',
        'document_type',
        'file_name',
        'file_path',
        'old_file_path',
        'storage_type',
        'request_type',
        'application_type',
        'version_no',
        'status',
        'is_active',
        'remarks',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'version_no' => 'integer',
        'module_ref_id' => 'integer',
        'parent_application_id' => 'integer',
        'approved_by' => 'integer',
        'approved_at' => 'datetime',
        'status' => DocumentVersionStatus::class,
    ];

    protected function storageType(): Attribute
    {
        return Attribute::make(
            get: function (?string $value) {
                if ($value !== null && ($resolved = DocumentStorageType::tryFrom($value))) {
                    return $resolved;
                }

                if (($this->attributes['status'] ?? null) === DocumentVersionStatus::APPROVED->value) {
                    return DocumentStorageType::PERMANENT;
                }

                return DocumentStorageType::TEMP;
            },
            set: fn ($value) => $value instanceof DocumentStorageType ? $value->value : $value,
        );
    }

    protected function requestType(): Attribute
    {
        return Attribute::make(
            get: function (?string $value) {
                if ($value !== null && ($resolved = DocumentRequestType::tryFrom($value))) {
                    if (
                        $resolved === DocumentRequestType::ALTERATION
                        && ($this->attributes['application_type'] ?? null) === DocumentApplicationType::RENEWAL->value
                    ) {
                        return DocumentRequestType::RENEWAL;
                    }

                    return $resolved;
                }

                if (((int) ($this->attributes['version_no'] ?? 1)) <= 1) {
                    return DocumentRequestType::INITIAL;
                }

                return match ($this->attributes['application_type'] ?? null) {
                    DocumentApplicationType::RENEWAL->value => DocumentRequestType::RENEWAL,
                    DocumentApplicationType::ALTERATION->value => DocumentRequestType::ALTERATION,
                    default => DocumentRequestType::ALTERATION,
                };
            },
            set: fn ($value) => $value instanceof DocumentRequestType ? $value->value : $value,
        );
    }

    protected function applicationType(): Attribute
    {
        return Attribute::make(
            get: function (?string $value) {
                if ($value !== null && ($resolved = DocumentApplicationType::tryFrom($value))) {
                    return $resolved;
                }

                return DocumentApplicationType::NEW;
            },
            set: fn ($value) => $value instanceof DocumentApplicationType ? $value->value : $value,
        );
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(CC_Forms_Meta::class, 'application_id', 'app_id');
    }

    public function parentApplication(): BelongsTo
    {
        return $this->belongsTo(CC_Forms_Meta::class, 'parent_application_id', 'app_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->whereIn('status', [
            DocumentVersionStatus::PENDING,
            DocumentVersionStatus::PENDING_L2,
            DocumentVersionStatus::PENDING_L3,
        ]);
    }

    public function scopeForGroup(
        Builder $query,
        int $applicationId,
        string $moduleType,
        ?int $moduleRefId,
        string $documentType
    ): Builder {
        $query->where('application_id', $applicationId)
            ->where('module_type', $moduleType)
            ->where('document_type', $documentType);

        if ($moduleRefId === null) {
            return $query->whereNull('module_ref_id');
        }

        return $query->where('module_ref_id', $moduleRefId);
    }

    public function scopeForApplication(Builder $query, int $applicationId): Builder
    {
        return $query->where('application_id', $applicationId);
    }

    public function isPending(): bool
    {
        return $this->status->isPending();
    }
}
