<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class CC_Education extends Model
{
    use HasFactory;

    /** Shared across competency forms S, W, WH, P (`cc_edu`). */
    protected $table = 'cc_edu';
    protected $primaryKey = 'edu_id';
    protected $fillable = [
        'login_id',
        'application_id',
        'educational_level', 
        'institute_name',
        'month_passing',
        'year_of_passing', 
        'certificate_no', 
        'upload_document',
        'status',
        'updated_at',
        'created_at',
    ];

}
