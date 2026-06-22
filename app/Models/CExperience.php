<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CExperience extends Model
{
    protected $table = 'c_experience';

    protected $fillable = [
        'application_id',
        'company_name',
        'years_of_experience',
        'designation',
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
