<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DExperience extends Model
{
    protected $table = 'd_experiences';

    protected $fillable = [
        'application_id',
        'company_name',
        'designation',
        'file_path',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(DApplication::class, 'application_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(DDocument::class, 'module_ref_id')
            ->where('module_type', 'experience');
    }
}
