<?php

namespace App\Models\Admin;

use App\Models\Tnelb_scert;
use App\Models\WorkflowHistory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;

class Mst_Logins extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'mst_login_users';

    protected $primaryKey = 's_id';

    public $incrementing = true;

    protected $guard = 'admin';

    protected $fillable = ['role_id', 'user_name', 'user_email', 'user_passwd', 'user_status', 'updated_by'];

    protected $hidden = ['user_passwd'];

    /**
     * Laravel auth: password column is user_passwd.
     */
    public function getAuthPassword()
    {
        return $this->user_passwd;
    }

    /**
     * For compatibility with code expecting Auth::user()->id (e.g. SupervisorController).
     */
    public function getIdAttribute()
    {
        return $this->s_id;
    }

    /**
     * Dashboard and views: role name (President, Secretary, Supervisor, etc.) for route matching.
     */
    public function getNameAttribute()
    {
        return $this->role ? ($this->role->role_name ?? $this->role->name ?? $this->user_name) : $this->user_name;
    }

    /**
     * For compatibility with code expecting Auth::user()->roles_id.
     */
    public function getRolesIdAttribute()
    {
        return $this->role_id;
    }

    /**
     * First assigned form_id from user_assigned (for backward compatibility with single form_id usage).
     */
    public function getFormIdAttribute()
    {
        $row = DB::table('user_assigned')
            ->where('user_id', $this->s_id)
            ->where('is_active', 1)
            ->first();
        if (!$row || empty($row->form_id)) {
            return null;
        }
        $ids = is_string($row->form_id) ? json_decode($row->form_id, true) : $row->form_id;
        return is_array($ids) ? ($ids[0] ?? null) : null;
    }

    public function role()
    {
        return $this->belongsTo(Mst_Roles::class, 'role_id', 'r_id');
    }

    // public function role()
    // {
    //     return $this->belongsTo(MstRole::class, 'role_id');
    // }

    public function submittedApplications()
    {
        return $this->hasMany(Tnelb_scert::class, 'submitted_by');
    }

    public function workflowActions()
    {
        return $this->hasMany(WorkflowHistory::class, 'performed_by');
    }
}
