<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CC_Proof_doc extends Model
{
    use HasFactory;

    protected $table = 'cc_proof_doc';

    protected $primaryKey = 'p_id';

    public $timestamps = false;

    protected $fillable = [
        'application_id',
        'app_type',
        'proof_name',
        'proof_type',
        'proof_no',
        'proof_doc',
        'status',
        'updated_at',
        'created_at',
    ];
}
