<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\Mst_Logins;
use App\Models\Admin\Mst_Roles;
use App\Models\Admin\Mst_Staffs_Tbl;
use App\Models\Admin\StaffAssigned;
use App\Models\Admin\TnelbForms;
use App\Models\Admin\UserFormHistory;
use App\Models\MstLicence;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class StaffController extends Controller
{

     protected $updatedBy;

    public function __construct()
    {
        // Ensure user is authenticated before accessing
        $this->middleware(function ($request, $next) {
            $this->updatedBy = Auth::user()->name ?? 'System';
            return $next($request);
        });
    }
    public function index(){
        
        $newFormsSub = DB::table('user_assigned as ua')
        ->join(
            DB::raw("jsonb_array_elements(ua.form_id) jf(form_id)"),
            DB::raw('true'),
            DB::raw('true')
        )
        ->join('mst_licences as f', 'f.id', '=', DB::raw('jf.form_id::int'))
        ->where('ua.form_type', 'N')
        ->select(
            'ua.user_id',
            DB::raw("string_agg(f.form_name, ', ') as new_forms"),
            DB::raw("json_agg(jf.form_id::int) as new_form_ids")
        )
        ->groupBy('ua.user_id');


        $renewalFormsSub = DB::table('user_assigned as ua')
        ->join(
            DB::raw("jsonb_array_elements(ua.form_id) jf(form_id)"),
            DB::raw('true'),
            DB::raw('true')
        )
        ->join('mst_licences as f', 'f.id', '=', DB::raw('jf.form_id::int'))
        ->where('ua.form_type', 'R')
        ->select(
            'ua.user_id',
            DB::raw("string_agg(f.form_name, ', ') as renewal_forms"),
            DB::raw("json_agg(jf.form_id::int) as renewal_form_ids")
        )
        ->groupBy('ua.user_id');

    
        $users = DB::table('mst_login_users as u')
        ->leftJoinSub($newFormsSub, 'nf', function ($join) {
            $join->on('nf.user_id', '=', 'u.s_id');
        })
        ->leftJoinSub($renewalFormsSub, 'rf', function ($join) {
            $join->on('rf.user_id', '=', 'u.s_id');
        })
        ->leftJoin('mst_roles as r', 'r.r_id', '=', 'u.role_id')
        ->select(
            'u.s_id',
            'u.user_name',
            'r.role_name',
            'u.user_status',
            'nf.new_form_ids',
            'rf.renewal_form_ids',
            DB::raw("COALESCE(nf.new_forms, '') as new_forms"),
            DB::raw("COALESCE(rf.renewal_forms, '') as renewal_forms")
        )
        ->orderBy('r.r_id', 'asc')
        ->orderBy('u.user_name', 'asc')
        ->get();



       
        $userRoles = Mst_Roles::all();

        $formlist = MstLicence::leftJoin('mst_licence_category as mlc', 'mst_licences.category_id', '=', 'mlc.id')
            ->where('mst_licences.status', 1)
            ->select('mst_licences.*', 'mlc.category_name')
            ->orderBy('mst_licences.id', 'asc')
            ->get();



        return view('admincms.staffdetails.index', compact('users','userRoles', 'formlist'));
    }

    public function getAssignedForms(Request $request)
    {
        $formIds = DB::table('staff_assigned')
            ->where('staff_id', $request->staff_id)
            ->where('is_active', 1)
            ->pluck('form_id')
            ->toArray();

        return response()->json([
            'status' => true,
            'form_ids' => $formIds
        ]);
    }

    public function insertStaff(Request $request)
    {
        $request->validate([
            'staff_name'    => 'required|string',
            'name'          => 'required|string',
            'name'         => 'required|string|unique:mst__staffs__tbls,name', 
            'email'        => 'required|email|unique:mst__staffs__tbls,email',
            'handle_forms'  => 'required|array',
            'status'        => 'required|in:0,1,2',
            // 'created_by'    => 'required|string',
            // 'updated_by'    => 'required|string',
        ]);
    
        // Step 1: Create staff record
        $staff = Mst_Staffs_Tbl::create([
            'staff_name'    => $request->staff_name,
            'name'   => $request->name,
            'email'         => $request->email,
            'handle_forms'  => json_encode($request->handle_forms),
            'status'        => $request->status,
            // 'created_by'    => $request->created_by,
            'updated_by'    => $this->updatedBy,
        ]);
    
        // Step 2: Update forms with this staff_id
        TnelbForms::whereIn('id', $request->handle_forms)->update([
            'staff_id' => $staff->id,
            'Assigned' => 'A'
        ]);
    
        return response()->json([
            'status' => 'success',
            'message' => 'Staff Added Successfully.',
            'staff' => $staff,
            'form_names' => TnelbForms::whereIn('id', $request->handle_forms)->pluck('form_name')->toArray()
        ]);
    }


    // ------------------------update staff details-----------------------

    public function updateStaff(Request $request)
    {
        $request->validate([
            'user_id'           => 'required|integer',
            'assigned_forms'    => 'array',
            'assigned_forms.*'  => 'integer',
            'form_type'         => 'required|in:N,R',
        ], [
            'user_id.required'          => 'Please select a user.',
            'user_id.integer'           => 'Invalid user selected.',
            'assigned_forms.array'      => 'Assigned forms must be a list.',
            'assigned_forms.*.integer'  => 'Invalid form selected.',
            'form_type.required'        => 'Please select a form type.',
            'form_type.in'              => 'Invalid form type selected.',
        ]);

        // 🔑 OPTION 1 FIX: force integers (VERY IMPORTANT)
        $assignedForms = array_map('intval', $request->input('assigned_forms', []));

        try {
            DB::transaction(function () use ($request, $assignedForms) {

                $roleId = Mst_Logins::where('s_id', $request->user_id)->value('role_id');

                if (!$roleId) {
                    throw new \RuntimeException('User role not found.');
                }

                $roleUserIds = Mst_Logins::where('role_id', $roleId)
                    ->pluck('s_id')
                    ->toArray();

                $activeRoleUserIds = Mst_Logins::where('role_id', $roleId)
                    ->where('user_status', 1)
                    ->pluck('s_id')
                    ->toArray();

                $currentStatus = StaffAssigned::where('user_id', $request->user_id)
                    ->where('form_type', $request->form_type)
                    ->value('is_active');

                // If any forms are assigned now, this assignment should be active.
                $userStatus = !empty($assignedForms)
                    ? 1
                    : ($request->user_status !== null
                        ? (int) $request->user_status
                        : (int) ($currentStatus ?? 1));

                if ($userStatus === 1 && empty($assignedForms)) {
                    throw new \RuntimeException(
                        'No form is assigned to this user. Please assign at least one form or deactivate the user.'
                    );
                }

                // 🔑 JSONB-safe conflict check
                if ($userStatus === 1 && !empty($assignedForms)) {
                    $baseQuery = StaffAssigned::where('form_type', $request->form_type)
                        ->whereIn('user_id', $activeRoleUserIds)
                        ->where('user_id', '!=', $request->user_id)
                        ->where('is_active', 1);

                    if (DB::getDriverName() === 'pgsql') {
                        $conflictRows = $baseQuery->where(function ($q) use ($assignedForms) {
                            foreach ($assignedForms as $formId) {
                                $q->orWhereRaw('form_id @> ?::jsonb', [json_encode([$formId])]);
                            }
                        })->get(['user_id', 'form_id']);
                    } else {
                        $conflictRows = $baseQuery->where(function ($q) use ($assignedForms) {
                            foreach ($assignedForms as $formId) {
                                $q->orWhereJsonContains('form_id', $formId);
                            }
                        })->get(['user_id', 'form_id']);
                    }

                    if ($conflictRows->isNotEmpty()) {
                        $formTypeLabel = $request->form_type === 'N' ? 'New' : 'Renewal';
                        $formsByUser = [];

                        foreach ($conflictRows as $conflictRow) {
                            $rawFormIds = $conflictRow->form_id;

                            if (is_string($rawFormIds)) {
                                $decoded = json_decode($rawFormIds, true);
                                $rowFormIds = is_array($decoded) ? $decoded : [];
                            } elseif (is_array($rawFormIds)) {
                                $rowFormIds = $rawFormIds;
                            } else {
                                $rowFormIds = [];
                            }

                            $rowFormIds = array_map('intval', $rowFormIds);
                            $matchedIds = array_values(array_intersect($rowFormIds, $assignedForms));

                            if (empty($matchedIds)) {
                                continue;
                            }

                            if (!isset($formsByUser[$conflictRow->user_id])) {
                                $formsByUser[$conflictRow->user_id] = [];
                            }

                            $formsByUser[$conflictRow->user_id] = array_values(array_unique(array_merge(
                                $formsByUser[$conflictRow->user_id],
                                $matchedIds
                            )));
                        }

                        if (!empty($formsByUser)) {
                            $userNames = Mst_Logins::whereIn('s_id', array_keys($formsByUser))
                                ->pluck('user_name', 's_id')
                                ->toArray();

                            $allConflictFormIds = [];
                            foreach ($formsByUser as $ids) {
                                $allConflictFormIds = array_merge($allConflictFormIds, $ids);
                            }
                            $allConflictFormIds = array_values(array_unique($allConflictFormIds));

                            $formNames = MstLicence::whereIn('id', $allConflictFormIds)
                                ->pluck('form_name', 'id')
                                ->toArray();

                            $details = [];
                            foreach ($formsByUser as $userId => $formIds) {
                                $name = $userNames[$userId] ?? "User ID {$userId}";
                                $conflictFormNames = array_values(array_filter(array_map(function ($id) use ($formNames) {
                                    return $formNames[$id] ?? null;
                                }, $formIds)));

                                $formText = !empty($conflictFormNames)
                                    ? implode(', ', $conflictFormNames)
                                    : implode(', ', $formIds);

                                $details[] = "{$name} ({$formText})";
                            }

                            throw new \RuntimeException(
                                "Conflict in {$formTypeLabel} form assignment. Already assigned to: " . implode('; ', $details) . '.'
                            );
                        }

                        throw new \RuntimeException(
                            'This form is already assigned to another user with the same role.'
                        );
                    }
                }

                 $existingAssignment = StaffAssigned::where('user_id', $request->user_id)
                ->where('form_type', $request->form_type)
                ->first();

                $action = 'START';

                if ($existingAssignment) {
                    if ($existingAssignment->is_active == 1 && $userStatus == 0) {
                        $action = 'STOP';
                    } elseif ($existingAssignment->is_active == 0 && $userStatus == 1) {
                        $action = 'START';
                    } else {
                        $action = 'CHANGE';
                    }
                }
                


                // ✅ update or create
                $staffAssigned = StaffAssigned::updateOrCreate(
                    [
                        'user_id'   => $request->user_id,
                        'form_type' => $request->form_type,
                    ],
                    [
                        'form_id'     => $assignedForms,   // ✅ numeric JSON
                        'is_active'  => $userStatus,
                        'assigned_at' => now(),
                    ]
                );

                if ($staffAssigned->wasRecentlyCreated) {
                    $staffAssigned->created_by = Auth::id();
                } else {
                    $staffAssigned->updated_by = Auth::id();
                }
                $staffAssigned->save();

                // Keep login status in sync with assigned forms across both New/Renewal.
                $activeAssignmentsQuery = StaffAssigned::where('user_id', $request->user_id)
                    ->where('is_active', 1);

                if (DB::getDriverName() === 'pgsql') {
                    $activeAssignmentsQuery->whereRaw("jsonb_array_length(COALESCE(form_id, '[]'::jsonb)) > 0");
                } else {
                    $activeAssignmentsQuery->whereRaw("JSON_LENGTH(form_id) > 0");
                }

                $hasAnyActiveAssignments = $activeAssignmentsQuery->exists();

                Mst_Logins::where('s_id', $request->user_id)
                    ->update([
                        'user_status' => $hasAnyActiveAssignments ? 1 : 2,
                        'updated_by' => Auth::id(),
                        'updated_at' => now(),
                    ]);

                UserFormHistory::where('form_type', $request->form_type)
                ->whereIn('user_id', $roleUserIds)
                ->whereNull('ended_at')
                ->update([
                    'ended_at' => now(),
                ]);

                // ✅ history (always)
                UserFormHistory::create([
                    'user_id'    => $request->user_id,
                    'form_id'    => $assignedForms,
                    'form_type'  => $request->form_type,
                    'is_active'  => $userStatus,
                    'action_status'  => $action,        
                    'started_at' => now(),
                    'enabled_at' => $action === 'STOP' ? now() : null,
                    'created_by' => Auth::id(),
                ]);
            });

            return response()->json([
                'status'  => true,
                'message' => 'Forms Assigned successfully.',
            ]);

        } catch (\Throwable $e) {

            if (
                str_starts_with($e->getMessage(), 'Conflict in ')
                || $e->getMessage() === 'This form is already assigned to another user with the same role.'
                || $e->getMessage() === 'No form is assigned to this user. Please assign at least one form or deactivate the user.'
            ) {
                return response()->json([
                    'status' => false,
                    'message' => $e->getMessage()
                ], 422);
            }

            return response()->json([
                'status'  => false,
                'message' => 'Update failed',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }


     public function changeStatus(Request $request)
    {
        // 1️⃣ Validate request
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:mst_login_users,s_id',
            'status'  => 'required|in:1,2', // 1 = Active, 2 = Inactive
            'force_deactivate' => 'nullable|in:0,1,true,false,on,off'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Invalid request',
                'errors'  => $validator->errors()
            ], 422);
        }

        $forceDeactivate = in_array($request->force_deactivate, [1, '1', true, 'true', 'on'], true);

        if ((int) $request->status === 2) {
            $assignedFormsQuery = StaffAssigned::where('user_id', $request->user_id)
                ->where('is_active', 1)
                ->whereIn('form_type', ['N', 'R']);

            if (DB::getDriverName() === 'pgsql') {
                $assignedFormsQuery->whereRaw("jsonb_array_length(COALESCE(form_id, '[]'::jsonb)) > 0");
            } else {
                $assignedFormsQuery->whereRaw("JSON_LENGTH(form_id) > 0");
            }

            $assignedTypes = (clone $assignedFormsQuery)
                ->pluck('form_type')
                ->filter()
                ->unique()
                ->map(function ($type) {
                    return $type === 'N' ? 'New' : ($type === 'R' ? 'Renewal' : $type);
                })
                ->values()
                ->toArray();

            if (!empty($assignedTypes) && !$forceDeactivate) {
                return response()->json([
                    'status' => false,
                    'needs_confirmation' => true,
                    'message' => 'Forms are assigned for ' . implode(' and ', $assignedTypes) . '. Do you want to deactivate and clear all assigned forms?',
                    'assigned_types' => $assignedTypes
                ], 422);
            }

            $formTypesToStop = StaffAssigned::where('user_id', $request->user_id)
                ->where('is_active', 1)
                ->whereIn('form_type', ['N', 'R'])
                ->pluck('form_type')
                ->filter()
                ->unique()
                ->values();

            DB::transaction(function () use ($request, $formTypesToStop) {
                Mst_Logins::where('s_id', $request->user_id)
                    ->update([
                        'user_status' => 2,
                        'updated_by' => Auth::id(),
                        'updated_at'  => now()
                    ]);

                $formIdValue = DB::getDriverName() === 'pgsql'
                    ? DB::raw("'[]'::jsonb")
                    : json_encode([]);

                StaffAssigned::where('user_id', $request->user_id)
                    ->update([
                        'form_id' => $formIdValue,
                        'is_active' => 0,
                        'updated_by' => Auth::id(),
                        'assigned_at' => now(),
                    ]);

                UserFormHistory::where('user_id', $request->user_id)
                    ->whereNull('ended_at')
                    ->update([
                        'ended_at' => now(),
                    ]);

                foreach ($formTypesToStop as $formType) {
                    UserFormHistory::create([
                        'user_id' => $request->user_id,
                        'form_id' => [],
                        'form_type' => $formType,
                        'is_active' => 0,
                        'action_status' => 'STOP',
                        'started_at' => now(),
                        'enabled_at' => now(),
                        'created_by' => Auth::id(),
                    ]);
                }
            });

            return response()->json([
                'status'  => true,
                'message' => 'User deactivated and assigned forms cleared successfully'
            ]);
        }

        Mst_Logins::where('s_id', $request->user_id)
            ->update([
                'user_status' => 1,
                'updated_by' => Auth::id(),
                'updated_at'  => now()
            ]);

        return response()->json([
            'status'  => true,
            'message' => 'User activated successfully'
        ]);
    }

     // ------------------------ Reset password -----------------------
    public function resetPassword(Request $request)
    {
        // var_dump($request->all());
        // exit;
        // ✅ Backend validation (never skip this)
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:mst_login_users,s_id',
            'password' => [
                'required',
                'min:8',
                'regex:/[A-Z]/',        // One uppercase
                'regex:/[0-9]/',        // One number
                'regex:/[\W_]/',        // One special character
                'confirmed'             // password_confirmation
            ],
        ], [
            'password.regex' => 'Password must contain uppercase, number and special character'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // ✅ Update password
        Mst_Logins::where('s_id', $request->user_id)
            ->update([
                'user_passwd' => Hash::make($request->password),
                'updated_by' => Auth::id(),
                'updated_at' => now()
            ]);

        return response()->json([
            'status' => true,
            'message' => 'Password reset successfully'
        ]);
    }

    

}
