<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mst_checklist extends Model
{
    use HasFactory;

      protected $fillable = [
        'login_id',
        'application_id',
        'form_name',
        'cert_name',
        'appl_type',
        'status',
        'updated_by',
        'created_at',
        'checklist_name',
        'ipaddress',
    ];


}
