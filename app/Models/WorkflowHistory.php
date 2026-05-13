<?php

namespace App\Models;

use App\Models\Admin\Mst_Logins;
use App\Models\Admin\Mst_Roles;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkflowHistory extends Model
{
     use HasFactory;

    protected $table = 'tnelb_workflow_history';

    protected $fillable = [
        'application_id',
        'from_role_id',
        'to_role_id',
        'performed_by',
        'action_type',
        'status_before',
        'status_after',
        'query_type',
        'query_message',
        'query_reply',
        'query_status',
        'parent_query_id',
        'remarks',
        'created_ip'
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    /* ========================
       Relationships
    ======================== */

    public function application()
    {
        return $this->belongsTo(Tnelb_scert::class, 'application_id');
    }

    public function fromRole()
    {
        return $this->belongsTo(Mst_Roles::class, 'from_role_id');
    }

    public function toRole()
    {
        return $this->belongsTo(Mst_Roles::class, 'to_role_id');
    }

    public function performedBy()
    {
        return $this->belongsTo(Mst_Logins::class, 'performed_by');
    }

    public function parentQuery()
    {
        return $this->belongsTo(WorkflowHistory::class, 'parent_query_id');
    }

    public function replies()
    {
        return $this->hasMany(WorkflowHistory::class, 'parent_query_id');
    }
}
