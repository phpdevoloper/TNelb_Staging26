<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TnelbApplicantsSign extends Model
{
    use HasFactory;

    protected $table = 'tnelb_applicants_sign';

    protected $primaryKey = 'id';

    public $timestamps = true;

    protected $fillable = [
        'login_id',
        'application_id',
        'uploaded_doc',
        'created_at',
        'updated_at'
    ];
}