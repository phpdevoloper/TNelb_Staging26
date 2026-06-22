<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CScertDocument extends Model
{
    protected $table = 'c_documents';

    protected $fillable = [
        'application_id',
        'document_label',
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
