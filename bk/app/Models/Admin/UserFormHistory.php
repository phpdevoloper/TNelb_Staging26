<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserFormHistory extends Model
{
    protected $table = 'user_assigned_history';

    protected $primaryKey = 'id';

    // Since created_at & updated_at are DATE (not timestamps)
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'form_id',
        'form_type',
        'is_active',
        'disbled_at',
        'created_at',
        'created_by',
        'started_at',
        'ended_at',
        'action_status',
    ];

    protected $casts = [
        'form_id' => 'array',
        'assigned_at' => 'date',
        'created_at'  => 'date',
        'updated_at'  => 'date',
    ];

    /*
     |--------------------------------------------------------------------------
     | Optional Relationships
     |--------------------------------------------------------------------------
     */

    // Link to staff master
    // public function staff()
    // {
    //     return $this->belongsTo(Staff::class, 'staff_id');
    // }

    // // Link to forms master
    // public function form()
    // {
    //     return $this->belongsTo(Form::class, 'form_id');
    // }
}