<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CSignature extends Model
{
    protected $table = 'c_signature';

    protected $fillable = [
        'application_id',
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
