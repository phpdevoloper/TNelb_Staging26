<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mapping_Digi_CLS extends Model
{
    use HasFactory;

     protected $table = 'mapping_digi_cls'; 

    protected $fillable = [
        'login_id',
        'application_id',
        'licence_name',
        'cert_name',
        'form_code',
        'temp_app_id',
        'clnumber',
        'fissue',
        'from_date',
        'to_date',
        'cl_doc',      
    ];
}


