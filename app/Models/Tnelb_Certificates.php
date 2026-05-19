<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tnelb_Certificates extends Model
{
    use HasFactory;

    protected $table = 'tnelb_license';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'application_id',
        'license_number',
        'issued_by',
        'issued_at',
        'issued_from',
        'expires_at',
        'created_at',
        'updated_at',
        'license_status',
        'license_pdf_en',
        'license_pdf_ta',
        'license_pdf_bilingual',

    ];
}