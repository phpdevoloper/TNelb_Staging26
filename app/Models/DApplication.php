<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DApplication extends Model
{
    protected $table = 'd_applications';

    protected $fillable = [
        'application_no',
        'applicant_name',
        'status',
        'parent_application_id',
        'request_context',
    ];

    public function parentApplication(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_application_id');
    }

    public function alterationApplications(): HasMany
    {
        return $this->hasMany(self::class, 'parent_application_id');
    }

    public function isAlterationApplication(): bool
    {
        return str_starts_with(strtoupper($this->application_no), 'ALT-');
    }

    public function isRenewalApplication(): bool
    {
        return str_starts_with(strtoupper($this->application_no), 'REN-');
    }

    public function isChildApplication(): bool
    {
        return $this->isAlterationApplication() || $this->isRenewalApplication();
    }

    public function educations(): HasMany
    {
        return $this->hasMany(DEducation::class, 'application_id');
    }

    public function experiences(): HasMany
    {
        return $this->hasMany(DExperience::class, 'application_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(DDocument::class, 'application_id');
    }
}
