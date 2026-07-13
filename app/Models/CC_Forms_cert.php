<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

/** Final issued certificate for Form S (N/R/D — one row per application). */
class CC_Forms_cert extends CompetencyCertificateModel
{
    use HasFactory;

    protected $table = 'cc_forms_cert';
}
