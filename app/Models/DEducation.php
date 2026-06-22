<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DEducation extends Model
{
    protected $table = 'd_educations';

    protected $fillable = [
        'application_id',
        'education_level',
        'institution_name',
        'certificate_no',
        'file_path',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(DApplication::class, 'application_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(DDocument::class, 'module_ref_id')
            ->where('module_type', 'education');
    }
}
