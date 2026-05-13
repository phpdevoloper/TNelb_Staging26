<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;

class RoleHelper {
    /**
     * Get Role ID dynamically from mst__roles table
     *
     * @param string $roleName
     * @return int|null
     */
    public static function getRole($roleName) {
        return DB::table('mst__roles')->where('id', $roleName)->value('name');
    }

    /**
     * Supervisor role key used in tnelb_workflow.forwarded_to / tnelb_workflow_a.forwarded_to.
     * This is mst_roles.r_id — the same value as mst_login_users.role_id for Supervisor logins.
     *
     * Order: mst_roles by role label → any login row joined to that role → legacy mst__staffs__tbls.roles_id → $fallbackStaff->roles_id.
     *
     * @param object|null $fallbackStaff Typically Auth user (Mst_Logins) with roles_id accessor.
     */
    public static function supervisorWorkflowRoleId($fallbackStaff = null): int
    {
        $id = (int) (DB::table('mst_roles')
            ->where(function ($q) {
                $q->whereRaw('LOWER(TRIM(COALESCE(role_name, \'\'))) = ?', ['supervisor']);
                    // ->orWhereRaw('LOWER(TRIM(COALESCE(name, \'\'))) = ?', ['supervisor']);
            })
            ->value('r_id') ?? 0);

        if ($id === 0) {
            $id = (int) (DB::table('mst_login_users as u')
                ->join('mst_roles as r', 'r.r_id', '=', 'u.role_id')
                ->where(function ($q) {
                    $q->whereRaw('LOWER(TRIM(COALESCE(r.role_name, \'\'))) = ?', ['supervisor']);
                        // ->orWhereRaw('LOWER(TRIM(COALESCE(r.name, \'\'))) = ?', ['supervisor']);
                })
                ->orderBy('u.s_id')
                ->value('u.role_id') ?? 0);
        }

        if ($id === 0) {
            $id = (int) (DB::table('mst__staffs__tbls')->where('name', 'Supervisor')->value('roles_id') ?? 0);
        }

        if ($id === 0 && $fallbackStaff !== null) {
            $id = (int) ($fallbackStaff->roles_id ?? 0);
        }

        return $id;
    }

}
