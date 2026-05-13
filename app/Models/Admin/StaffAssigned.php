<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaffAssigned extends Model
{
    protected $table = 'user_assigned';

    protected $primaryKey = 'id';

    // Since created_at & updated_at are DATE (not timestamps)
    public $timestamps = true;

    protected $fillable = [
        'user_id',
        'form_id',
        'form_type',
        'is_active',
        'assigned_at',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'form_id' => 'array',
        'assigned_at' => 'date',
        'created_at'  => 'date',
        'updated_at'  => 'date',
    ];
}