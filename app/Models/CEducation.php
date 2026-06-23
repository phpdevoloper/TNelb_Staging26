<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CEducation extends Model
{
    protected $table = 'c_education';

    protected $fillable = [
        'application_id',
        'education_level',
        'institution_name',
        'year_of_passing',
        'grade',
        'file_name',
        'file_path',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(ScertApp::class, 'application_id');
    }
}
