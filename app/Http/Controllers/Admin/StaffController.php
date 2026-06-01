<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\Mst_Logins;
use App\Models\Admin\Mst_Roles;
use App\Models\Admin\Mst_Staffs_Tbl;
use App\Models\Admin\StaffAssigned;
use App\Models\Admin\StaffProfile;
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
        ->leftJoin('mst_staff_profiles as p', 'p.s_id', '=', 'u.s_id')
        ->where(function ($q) {
            $q->whereNull('u.user_status')
              ->orWhere('u.user_status', '!=', '3'); // exclude soft-deleted
        })
        ->select(
            'u.s_id',
            'u.user_name',
            'u.user_email',
            'r.role_name',
            'u.role_id',
            'u.user_status',
            'p.employee_code',
            'p.full_name',
            'p.mobile',
            'p.designation',
            'p.profile_photo',
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

    /**
     * Generate the next sequential employee code in the format TNELB-EMP-0001.
     * Pads to 4 digits; grows automatically (TNELB-EMP-10000 etc.).
     */
    protected function generateEmployeeCode(): string
    {
        $prefix = 'TNELB-EMP-';

        $last = StaffProfile::where('employee_code', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->value('employee_code');

        $nextNum = 1;
        if ($last && preg_match('/(\d+)$/', $last, $m)) {
            $nextNum = ((int) $m[1]) + 1;
        }

        return $prefix . str_pad($nextNum, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Common validator for staff profile fields shared between insert and update.
     * Photo is validated only when present.
     */
    protected function profileFieldRules(?int $ignoreSId = null): array
    {
        return [
            'full_name'     => 'required|string|max:150',
            'mobile'        => 'required|string|min:10|max:15|regex:/^[0-9+\-\s]{10,15}$/',
            'designation'   => 'required|string|max:120',
            'joining_date'  => 'required|date',
            'date_of_birth' => 'nullable|date|before_or_equal:today',
            'gender'        => 'nullable|in:M,F,O',
            'alt_phone'     => 'nullable|string|max:20|regex:/^[0-9+\-\s]{0,20}$/',
            'profile_photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ];
    }

    /**
     * Save the uploaded profile photo to public/staff_photos/ and return its relative path.
     */
    protected function storeProfilePhoto($file, int $sId): string
    {
        $dir = public_path('staff_photos');
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        $ext = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        $filename = "staff_{$sId}_" . time() . ".{$ext}";
        $file->move($dir, $filename);
        return "staff_photos/{$filename}";
    }

    /**
     * Create a new portal user (staff) in mst_login_users + mst_staff_profiles.
     * Uniqueness on user_name / user_email ignores soft-deleted rows (user_status = '3').
     */
    public function insertStaff(Request $request)
    {
        $loginRules = [
            'staff_name'        => [
                'required', 'string', 'max:120',
                function ($attribute, $value, $fail) {
                    $exists = Mst_Logins::where('user_name', $value)
                        ->where(function ($q) {
                            $q->whereNull('user_status')->orWhere('user_status', '!=', '3');
                        })
                        ->exists();
                    if ($exists) {
                        $fail('This user name is already taken.');
                    }
                },
            ],
            'role_id'           => 'required|integer|exists:mst_roles,r_id',
            'staff_email'       => [
                'required', 'email', 'max:150',
                function ($attribute, $value, $fail) {
                    $exists = Mst_Logins::where('user_email', $value)
                        ->where(function ($q) {
                            $q->whereNull('user_status')->orWhere('user_status', '!=', '3');
                        })
                        ->exists();
                    if ($exists) {
                        $fail('This email is already registered.');
                    }
                },
            ],
            'user_random_pass'  => [
                'required', 'string', 'min:8',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
                'regex:/[\W_]/',
                'confirmed',
            ],
        ];

        $validator = Validator::make(
            $request->all(),
            array_merge($loginRules, $this->profileFieldRules()),
            [
                'user_random_pass.regex'     => 'Password must contain uppercase, number and special character.',
                'user_random_pass.confirmed' => 'Password and confirm password do not match.',
                'role_id.exists'             => 'Invalid role selected.',
                'mobile.regex'               => 'Mobile number must contain digits only (10\u201315 chars).',
                'alt_phone.regex'            => 'Alternate phone must contain digits only.',
                'profile_photo.image'        => 'Profile photo must be an image.',
                'profile_photo.max'          => 'Profile photo must be under 2 MB.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $result = DB::transaction(function () use ($request) {
                $user = Mst_Logins::create([
                    'user_name'   => trim($request->staff_name),
                    'user_email'  => strtolower(trim($request->staff_email)),
                    'user_passwd' => Hash::make($request->user_random_pass),
                    'role_id'     => (int) $request->role_id,
                    'user_status' => '1',
                    'created_by'  => Auth::id(),
                    'updated_by'  => Auth::id(),
                ]);

                $photoPath = null;
                if ($request->hasFile('profile_photo')) {
                    $photoPath = $this->storeProfilePhoto($request->file('profile_photo'), $user->s_id);
                }

                $profile = StaffProfile::create([
                    's_id'          => $user->s_id,
                    'employee_code' => $this->generateEmployeeCode(),
                    'full_name'     => trim($request->full_name),
                    'mobile'        => preg_replace('/\s+/', '', $request->mobile),
                    'designation'   => trim($request->designation),
                    'joining_date'  => $request->joining_date,
                    'date_of_birth' => $request->date_of_birth ?: null,
                    'gender'        => $request->gender ?: null,
                    'alt_phone'     => $request->alt_phone ? preg_replace('/\s+/', '', $request->alt_phone) : null,
                    'profile_photo' => $photoPath,
                    'created_by'    => Auth::id(),
                    'updated_by'    => Auth::id(),
                ]);

                return compact('user', 'profile');
            });

            return response()->json([
                'status'  => true,
                'message' => 'Staff added successfully.',
                'user'    => [
                    's_id'          => $result['user']->s_id,
                    'user_name'     => $result['user']->user_name,
                    'user_email'    => $result['user']->user_email,
                    'employee_code' => $result['profile']->employee_code,
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Unable to add staff.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Fetch a single staff record (login + profile) for the Edit modal.
     */
    public function getStaff($id)
    {
        $user = DB::table('mst_login_users as u')
            ->leftJoin('mst_roles as r', 'r.r_id', '=', 'u.role_id')
            ->leftJoin('mst_staff_profiles as p', 'p.s_id', '=', 'u.s_id')
            ->where('u.s_id', $id)
            ->where(function ($q) {
                $q->whereNull('u.user_status')->orWhere('u.user_status', '!=', '3');
            })
            ->select(
                'u.s_id', 'u.user_name', 'u.user_email', 'u.role_id', 'u.user_status', 'r.role_name',
                'p.employee_code', 'p.full_name', 'p.mobile', 'p.designation', 'p.joining_date',
                'p.date_of_birth', 'p.gender', 'p.alt_phone', 'p.profile_photo'
            )
            ->first();

        if (!$user) {
            return response()->json([
                'status'  => false,
                'message' => 'Staff not found.',
            ], 404);
        }

        // Normalize dates for date inputs (YYYY-MM-DD)
        if (!empty($user->joining_date)) {
            $user->joining_date = date('Y-m-d', strtotime($user->joining_date));
        }
        if (!empty($user->date_of_birth)) {
            $user->date_of_birth = date('Y-m-d', strtotime($user->date_of_birth));
        }
        if (!empty($user->profile_photo)) {
            $user->profile_photo_url = asset($user->profile_photo);
        } else {
            $user->profile_photo_url = null;
        }

        return response()->json([
            'status' => true,
            'user'   => $user,
        ]);
    }

    /**
     * Update staff login details (name / email / role) AND profile fields.
     * Profile row is upserted, so it also fixes legacy users that didn't have one yet.
     */
    public function updateStaffDetails(Request $request)
    {
        $userId = (int) $request->input('user_id');

        $loginRules = [
            'user_id'     => 'required|integer|exists:mst_login_users,s_id',
            'staff_name'  => [
                'required', 'string', 'max:120',
                function ($attribute, $value, $fail) use ($userId) {
                    $exists = Mst_Logins::where('user_name', $value)
                        ->where('s_id', '!=', $userId)
                        ->where(function ($q) {
                            $q->whereNull('user_status')->orWhere('user_status', '!=', '3');
                        })
                        ->exists();
                    if ($exists) {
                        $fail('This user name is already taken.');
                    }
                },
            ],
            'staff_email' => [
                'required', 'email', 'max:150',
                function ($attribute, $value, $fail) use ($userId) {
                    $exists = Mst_Logins::where('user_email', $value)
                        ->where('s_id', '!=', $userId)
                        ->where(function ($q) {
                            $q->whereNull('user_status')->orWhere('user_status', '!=', '3');
                        })
                        ->exists();
                    if ($exists) {
                        $fail('This email is already registered.');
                    }
                },
            ],
            'role_id'     => 'required|integer|exists:mst_roles,r_id',
            'remove_photo' => 'nullable|in:0,1',
        ];

        $validator = Validator::make(
            $request->all(),
            array_merge($loginRules, $this->profileFieldRules($userId)),
            [
                'role_id.exists' => 'Invalid role selected.',
                'mobile.regex'   => 'Mobile number must contain digits only (10\u201315 chars).',
                'alt_phone.regex' => 'Alternate phone must contain digits only.',
                'profile_photo.image' => 'Profile photo must be an image.',
                'profile_photo.max'   => 'Profile photo must be under 2 MB.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            DB::transaction(function () use ($request, $userId) {
                Mst_Logins::where('s_id', $userId)->update([
                    'user_name'  => trim($request->staff_name),
                    'user_email' => strtolower(trim($request->staff_email)),
                    'role_id'    => (int) $request->role_id,
                    'updated_by' => Auth::id(),
                    'updated_at' => now(),
                ]);

                $profile = StaffProfile::where('s_id', $userId)->first();

                $photoPath = $profile?->profile_photo;

                if ($request->input('remove_photo') == 1 && $photoPath) {
                    $abs = public_path($photoPath);
                    if (is_file($abs)) @unlink($abs);
                    $photoPath = null;
                }

                if ($request->hasFile('profile_photo')) {
                    if ($photoPath) {
                        $abs = public_path($photoPath);
                        if (is_file($abs)) @unlink($abs);
                    }
                    $photoPath = $this->storeProfilePhoto($request->file('profile_photo'), $userId);
                }

                $data = [
                    'full_name'     => trim($request->full_name),
                    'mobile'        => preg_replace('/\s+/', '', $request->mobile),
                    'designation'   => trim($request->designation),
                    'joining_date'  => $request->joining_date,
                    'date_of_birth' => $request->date_of_birth ?: null,
                    'gender'        => $request->gender ?: null,
                    'alt_phone'     => $request->alt_phone ? preg_replace('/\s+/', '', $request->alt_phone) : null,
                    'profile_photo' => $photoPath,
                    'updated_by'    => Auth::id(),
                ];

                if ($profile) {
                    $profile->update($data);
                } else {
                    // Legacy user without a profile row yet — create it with a new employee_code.
                    StaffProfile::create(array_merge($data, [
                        's_id'          => $userId,
                        'employee_code' => $this->generateEmployeeCode(),
                        'created_by'    => Auth::id(),
                    ]));
                }
            });

            return response()->json([
                'status'  => true,
                'message' => 'Staff details updated successfully.',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Unable to update staff details.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Soft-delete a staff user (user_status = '3').
     * If user has active form assignments, requires explicit force confirmation,
     * mirroring the changeStatus() flow.
     */
    public function deleteStaff(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id'        => 'required|exists:mst_login_users,s_id',
            'force_delete'   => 'nullable|in:0,1,true,false,on,off',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Invalid request',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $forceDelete = in_array($request->force_delete, [1, '1', true, 'true', 'on'], true);

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
            ->map(fn ($t) => $t === 'N' ? 'New' : ($t === 'R' ? 'Renewal' : $t))
            ->values()
            ->toArray();

        if (!empty($assignedTypes) && !$forceDelete) {
            return response()->json([
                'status'             => false,
                'needs_confirmation' => true,
                'message'            => 'Forms are assigned for ' . implode(' and ', $assignedTypes) . '. Do you want to delete this user and clear all assigned forms?',
                'assigned_types'     => $assignedTypes,
            ], 422);
        }

        $formTypesToStop = StaffAssigned::where('user_id', $request->user_id)
            ->where('is_active', 1)
            ->whereIn('form_type', ['N', 'R'])
            ->pluck('form_type')
            ->filter()
            ->unique()
            ->values();

        try {
            DB::transaction(function () use ($request, $formTypesToStop) {
                Mst_Logins::where('s_id', $request->user_id)
                    ->update([
                        'user_status' => '3',
                        'updated_by'  => Auth::id(),
                        'updated_at'  => now(),
                    ]);

                $formIdValue = DB::getDriverName() === 'pgsql'
                    ? DB::raw("'[]'::jsonb")
                    : json_encode([]);

                StaffAssigned::where('user_id', $request->user_id)
                    ->update([
                        'form_id'     => $formIdValue,
                        'is_active'   => 0,
                        'updated_by'  => Auth::id(),
                        'assigned_at' => now(),
                    ]);

                UserFormHistory::where('user_id', $request->user_id)
                    ->whereNull('ended_at')
                    ->update([
                        'ended_at' => now(),
                    ]);

                foreach ($formTypesToStop as $formType) {
                    UserFormHistory::create([
                        'user_id'       => $request->user_id,
                        'form_id'       => [],
                        'form_type'     => $formType,
                        'is_active'     => 0,
                        'action_status' => 'STOP',
                        'started_at'    => now(),
                        'ended_at'      => now(),
                        'created_by'    => Auth::id(),
                    ]);
                }
            });

            return response()->json([
                'status'  => true,
                'message' => 'Staff deleted successfully.',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Unable to delete staff.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Fetch form-assignment history for a given user.
     * Powers the User Form History modal table.
     */
    public function getUserHistory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer|exists:mst_login_users,s_id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $rows = DB::table('user_assigned_history as h')
            ->where('h.user_id', $request->user_id)
            ->orderBy('h.started_at', 'desc')
            ->orderBy('h.id', 'desc')
            ->select(
                'h.id',
                'h.form_id',
                'h.form_type',
                'h.is_active',
                'h.action_status',
                'h.started_at',
                'h.ended_at'
            )
            ->get();

        $allFormIds = collect();
        foreach ($rows as $row) {
            $ids = $row->form_id;
            if (is_string($ids)) {
                $decoded = json_decode($ids, true);
                $ids = is_array($decoded) ? $decoded : [];
            } elseif (!is_array($ids)) {
                $ids = [];
            }
            $allFormIds = $allFormIds->merge($ids);
        }
        $allFormIds = $allFormIds->map(fn ($v) => (int) $v)->filter()->unique()->values();

        $formNames = $allFormIds->isEmpty()
            ? collect()
            : MstLicence::whereIn('id', $allFormIds)->pluck('form_name', 'id');

        $data = $rows->map(function ($row) use ($formNames) {
            $ids = $row->form_id;
            if (is_string($ids)) {
                $decoded = json_decode($ids, true);
                $ids = is_array($decoded) ? $decoded : [];
            } elseif (!is_array($ids)) {
                $ids = [];
            }
            $names = collect($ids)
                ->map(fn ($id) => $formNames[(int) $id] ?? null)
                ->filter()
                ->values()
                ->all();

            return [
                'form_type_label' => $row->form_type === 'N' ? 'New' : ($row->form_type === 'R' ? 'Renewal' : $row->form_type),
                'form_names'      => !empty($names) ? implode(', ', $names) : '-',
                'status_label'    => $row->is_active == 1 ? 'Active' : 'Inactive',
                'action_status'   => $row->action_status ?? '-',
                'started_at'      => $row->started_at ? date('d-m-Y H:i', strtotime($row->started_at)) : '-',
                'ended_at'        => $row->ended_at ? date('d-m-Y H:i', strtotime($row->ended_at)) : '-',
            ];
        });

        return response()->json([
            'status' => true,
            'data'   => $data,
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
