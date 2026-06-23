<?php

namespace App\Models;

use App\Enums\ScertAppStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ScertApp extends Model
{
    protected $table = 'scert_app';

    protected $fillable = [
        'application_code',
        'applicant_name',
        'status',
        'submitted_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'status' => ScertAppStatus::class,
    ];

    public function educations(): HasMany
    {
        return $this->hasMany(CEducation::class, 'application_id');
    }

    public function experiences(): HasMany
    {
        return $this->hasMany(CExperience::class, 'application_id');
    }

    public function photo(): HasOne
    {
        return $this->hasOne(CPhoto::class, 'application_id');
    }

    public function signature(): HasOne
    {
        return $this->hasOne(CSignature::class, 'application_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(CScertDocument::class, 'application_id');
    }

    public function alterationRequests(): HasMany
    {
        return $this->hasMany(CAlterationRequest::class, 'application_id');
    }

    public function canRequestAlteration(): bool
    {
        return in_array($this->status, [
            ScertAppStatus::SUBMITTED,
            ScertAppStatus::DIGITIZATION,
            ScertAppStatus::ALTERATION,
        ], true);
    }
}
