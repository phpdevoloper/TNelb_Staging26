<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Models\Admin\ApplicationModel;

use App\Models\Admin\FormaModel;
use App\Models\Admin\FormbModel;
use App\Models\Admin\FormsaModel;
use App\Models\Admin\FormsbModel;
use App\Models\Admin\UserModel;
use App\Models\TnelbApplicantPhoto;

use App\Models\Admin\Mst_equipment_tbl;

use App\Models\Admin\TnelbMenu;

use App\Models\Admin\Portaladmin_menu;
use App\Models\Admin\Tnelb_homeslider_tbl;
use App\Models\Admin\Tnelb_Mst_Media;
use App\Models\Admin\Tnelb_submenus;
use App\Models\Equipment_storetmp_A;
use App\Models\MstLicence;
use App\Models\Tnelb_banksolvency_a;
use Carbon\Carbon;
use App\Models\Admin\FeesValidity;
use App\Models\Admin\Mst_Logins;
use Illuminate\Support\Facades\Storage;

class LoginController extends Controller
{
    public function index()
    {

        return view('admin.index');
    }

     public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'password' => 'required|string',
            'captcha' => ['required', function ($attribute, $value, $fail) {
                if (session('custom_captcha') !== $value) {
                    $fail('The captcha is incorrect.');
                }
            }],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 422);
        }

        $check_user = Mst_Logins::where('user_name', $request->username)
            ->orWhere('user_email', $request->username)
            ->first();

        if ($check_user) {
            // Only active staff can log in (user_status: 1 = active)
            if ((int) $check_user->user_status !== 1) {
                return response()->json(['message' => 'Your account is inactive. Please contact the administrator.'], 403);
            }

            $password = $request->password ?? $request->input('password');
            if (\Illuminate\Support\Facades\Hash::check($password, $check_user->user_passwd)) {
                Auth::guard('admin')->login($check_user);

                if (Auth::guard('admin')->check()) {
                    return response()->json(['message' => 'Login successful'], 200);
                }

                return response()->json(['message' => 'Login failed during session handling.'], 401);
            }
        }

        return response()->json(['message' => 'Invalid login credentials. Please try again.'], 401);
    }
    // public function dashboard()
    // {
    //     $staff = Auth::user();

    //     if (!$staff || !$staff->name) {
    //         return abort(403, 'Unauthorized');
    //     }
    //     // var_dump($staff);die;

    //     $assignedFormID = $staff->form_id;
    //     $processed_by   = $this->getProcessedByRole($staff->name);


    //     // Fetch data using model methods
    //     $pendings                   = ApplicationModel::getPendingCount($assignedFormID);
    //     $completed                  = ApplicationModel::getCompletedCount($assignedFormID, $processed_by);
    //     $auditor_pendings           = ApplicationModel::getAuditorPendingCounts();
    //     $auditorForma_pendings      = FormaModel::getAuditorFormAPendingCounts();
    //     $secForma_counts            = FormaModel::getSecFormACounts();
    //     $secretary                  = ApplicationModel::getSecretaryPendingCounts();
    //     $secretaryCompleted         = ApplicationModel::getSecretaryComplCounts();
    //     $form_a_pending             = FormaModel::getPendingCountForma();
    //     $form_a_completed           = FormaModel::getcompleteCountForma();


    //     $president = DB::table('tnelb_forms as f')
    //         ->leftJoin('tnelb_application_tbl as ta', 'ta.form_id', '=', 'f.id')
    //         ->select(
    //             'f.id',
    //             'f.form_name',
    //             'f.license_name',
    //             DB::raw("COUNT(CASE WHEN ta.status = 'F' AND ta.processed_by = 'SE' THEN 1 END) as pending_count"),
    //             DB::raw("COUNT(CASE WHEN ta.status = 'A' AND ta.processed_by = 'PR' THEN 1 END) as completed_count")
    //         )
    //         ->groupBy('f.id', 'f.form_name', 'f.license_name')
    //         ->get();


    //     $presidentFormA = DB::table('tnelb_ea_applications as ta')
    //         ->select(
    //             DB::raw("COUNT(CASE WHEN ta.application_status IN ('F','RF') AND ta.processed_by IN ('SE','S') THEN 1 END) as pending_count"),
    //             DB::raw("COUNT(CASE WHEN ta.application_status = 'A' AND ta.processed_by = 'PR' THEN 1 END) as completed_count")
    //         )
    //         ->first();

    //     $formColors = [
    //         'C'  => 'bg-yellow',
    //         'B'  => 'bg-red',
    //         'WH' => 'bg-H',
    //         'PG' => 'bg-green',
    //     ];

    //     // Determine the view based on role
    //     $view = match ($staff->name) {
    //         'President'   => 'admin.dashboard.president',
    //         'Secretary'   => 'admin.dashboard.index',
    //         'Supervisor'  => 'admin.dashboard.loginpage.supervisor_index',
    //         'Accountant'     => 'admin.dashboard.loginpage.auditor_index',
    //         'Supervisor2' => 'admin.dashboard.loginpage.supervisor_w_index',
    //         default       => abort(403, 'Unauthorized'),
    //     };

    //     $applications = DB::table('mst_workflows as mw')
    //         ->join('tnelb_application_tbl as ta', 'mw.application_id', '=', 'ta.application_id') // Join condition
    //         ->select('mw.*', 'ta.applicant_name') // Select fields
    //         ->where('ta.status', ['P', 'F']) // Select fields
    //         ->get();

    //    $query1 = DB::table('tnelb_application_tbl')
    //         ->select('*')
    //         ->where('status', 'P')
    //         ->get();

    //     $query2 = DB::table('tnelb_ea_applications')
    //         ->select('id', '*')
    //         ->where('application_status', 'P')
    //         ->get();

    //     $recieved_apps = $query1->merge($query2);


    //     $query3 = DB::table('tnelb_application_tbl')
    //         ->select('*')
    //         ->where('status', 'F')
    //         ->get();

    //     $query3 = DB::table('tnelb_ea_applications')
    //         ->select('id', '*')
    //         ->where('application_status', 'F')
    //         ->get();

    //     $inprogress = $query1->merge($query2);


    //     $form_wh_counts['pendings'] = DB::table('tnelb_application_tbl')
    //     ->where('status', 'P')
    //     ->where('form_name', 'WH')
    //     ->count();



    //     return view($view, compact(
    //         'applications',
    //         'completed',
    //         'pendings',
    //         'auditor_pendings',
    //         'formColors',
    //         'secretary',
    //         'president',
    //         'secretaryCompleted',
    //         'form_a_pending',
    //         'form_a_completed',
    //         'auditorForma_pendings',
    //         'secForma_counts',
    //         'presidentFormA',
    //         'recieved_apps',
    //         'inprogress','form_wh_counts'
    //     ));

    // }


    /**
     * NEW dashboard:
     * - Superadmin → CMS dashboard (admincms.dashboard.index)
     * - Other active users → common dashboard driven by assigned forms in user_assigned.
     */
    public function dashboard()
    {
        $staff = Auth::user();


        if (!$staff) {
            return abort(403, 'Unauthorized');
        }

   
        $roleCode     = $staff->role_code ?? optional($staff->role)->role_code ?? null;

        $isSuperadmin = $roleCode === 'SUPADMIN';

        if ($isSuperadmin) {

            

            $menus = TnelbMenu::whereNotIn('id', [1, 2, 3])
                ->orderBy('order_id')
                ->get();
                
            $submenus = Tnelb_submenus::all();

            return view('admincms.dashboard.index', compact('staff', 'menus', 'submenus', 'isSuperadmin'));
        }

        $assignedFormsQuery = \App\Models\Admin\StaffAssigned::where('user_id', $staff->id)
            ->where('is_active', 1)
            ->whereIn('form_type', ['N', 'R']);



        if (DB::getDriverName() === 'pgsql') {
            $assignedFormsQuery->whereRaw("jsonb_array_length(COALESCE(form_id, '[]'::jsonb)) > 0");
        } else {
            $assignedFormsQuery->whereRaw('JSON_LENGTH(form_id) > 0');
        }

        $assignedRows = $assignedFormsQuery->get(['form_id', 'form_type']);


        // Flatten all unique form IDs across N + R
        $assignedFormIDs = $assignedRows
            ->pluck('form_id')   
            ->flatten()
            ->map(function ($id) {
                return (int) $id;
            })
            ->unique()
            ->values()
            ->all();



        // Look up licence / form details for those IDs
        $licences = DB::table('mst_licences as f')
            ->whereIn('f.id', $assignedFormIDs)
            ->select(
                'f.id',
                'f.form_name',
                'f.licence_name',
                'f.cert_licence_code as color_code',
                'f.category_id'
            )
            ->get()
            ->keyBy('id');



        $roleLevel = (int) (optional($staff->role)->role_level ?? 0);
        $isSupervisorRole = $roleLevel === 1;

        // Pending application counts:
        // - Supervisor/Supervisor2: new/renewal apps not yet in any workflow table
        // - Other roles: apps currently forwarded to the logged-in role (latest workflow row)
        $pendingCountsMap = [];
        if (!empty($assignedFormIDs)) {
            if ($isSupervisorRole) {
                $pendingCounts = DB::table('tnelb_application_tbl as ta')
                    ->whereIn('ta.form_id', $assignedFormIDs)
                    ->whereIn('ta.status', ['P', 'RE'])
                    ->whereIn('ta.payment_status', ['payment', 'paid'])
                    ->whereNotExists(function ($q) {
                        $q->select(DB::raw(1))
                            ->from('tnelb_workflow as tw')
                            ->whereRaw('tw.application_id = ta.application_id');
                    })
                    ->whereNotExists(function ($q) {
                        $q->select(DB::raw(1))
                            ->from('tnelb_workflow_a as twa')
                            ->whereRaw('twa.application_id = ta.application_id');
                    })
                    ->selectRaw('ta.form_id, ta.appl_type, COUNT(*) as cnt')
                    ->groupBy('ta.form_id', 'ta.appl_type')
                    ->get();
            } else {
                $roleId = (int) ($staff->roles_id ?? 0);

                $twLast = DB::table('tnelb_workflow')
                    ->select('application_id', DB::raw('MAX(id) as max_id'))
                    ->groupBy('application_id');

                $twaLast = DB::table('tnelb_workflow_a')
                    ->select('application_id', DB::raw('MAX(id) as max_id'))
                    ->groupBy('application_id');

                $currentFromTw = DB::table('tnelb_workflow as tw')
                    ->joinSub($twLast, 'tw_last', function ($join) {
                        $join->on('tw.application_id', '=', 'tw_last.application_id')
                            ->on('tw.id', '=', 'tw_last.max_id');
                    })
                    ->where('tw.forwarded_to', $roleId)
                    ->whereIn('tw.appl_status', ['F', 'RF'])
                    ->select('tw.application_id');

                $currentFromTwa = DB::table('tnelb_workflow_a as tw')
                    ->joinSub($twaLast, 'tw_last', function ($join) {
                        $join->on('tw.application_id', '=', 'tw_last.application_id')
                            ->on('tw.id', '=', 'tw_last.max_id');
                    })
                    ->where('tw.forwarded_to', $roleId)
                    ->whereIn('tw.appl_status', ['F', 'RF'])
                    ->select('tw.application_id');

                // Fallback: if an application has no workflow rows yet (manual edits / legacy data),
                // infer who should handle it based on the hierarchy:
                // Supervisor -> Accountant -> Secretary -> President
                $previousProcessedBy = match ($roleLevel) {
                    2 => ['S', 'S2'], // Accountant handles after Supervisor/Supervisor2
                    3 => ['A'],       // Secretary handles after Accountant
                    4 => ['SE'],      // President handles after Secretary
                    default => [],
                };

                $fallbackAppIds = DB::table('tnelb_application_tbl as ta')
                    ->whereIn('ta.status', ['F', 'RF'])
                    ->whereIn('ta.payment_status', ['payment', 'paid'])
                    ->when(!empty($previousProcessedBy), function ($q) use ($previousProcessedBy) {
                        return $q->whereIn('ta.processed_by', $previousProcessedBy);
                    })
                    ->whereNotExists(function ($q) {
                        $q->select(DB::raw(1))
                            ->from('tnelb_workflow as tw')
                            ->whereRaw('tw.application_id = ta.application_id');
                    })
                    ->whereNotExists(function ($q) {
                        $q->select(DB::raw(1))
                            ->from('tnelb_workflow_a as twa')
                            ->whereRaw('twa.application_id = ta.application_id');
                    })
                    ->select('ta.application_id');

                $currentAppIds = $currentFromTw->union($currentFromTwa)->union($fallbackAppIds);

                $pendingCounts = DB::query()
                    ->fromSub($currentAppIds, 'cur')
                    ->join('tnelb_application_tbl as ta', 'ta.application_id', '=', 'cur.application_id')
                    ->whereIn('ta.form_id', $assignedFormIDs)
                    ->whereIn('ta.payment_status', ['payment', 'paid'])
                    ->selectRaw('ta.form_id, ta.appl_type, COUNT(*) as cnt')
                    ->groupBy('ta.form_id', 'ta.appl_type')
                    ->get();

            }

            foreach ($pendingCounts as $row) {
                $fid = (int) $row->form_id;
                $type = strtoupper((string) $row->appl_type) === 'R' ? 'R' : 'N';
                if (!isset($pendingCountsMap[$fid])) {
                    $pendingCountsMap[$fid] = ['N' => 0, 'R' => 0];
                }
                $pendingCountsMap[$fid][$type] = (int) $row->cnt;
            }

            // Form P uses tnelb_form_p; add its pending counts if Form P is assigned
            $formPId = (int) DB::table('mst_licences')->where('cert_licence_code', 'P')->value('id');
            if ($formPId > 0 && in_array($formPId, $assignedFormIDs, true)) {
                if ($isSupervisorRole) {
                    $formPCounts = DB::table('tnelb_form_p as ta')
                        ->whereIn('ta.payment_status', ['payment', 'paid'])
                        ->whereIn('ta.app_status', ['P', 'RE'])
                        ->whereNotExists(function ($q) {
                            $q->select(DB::raw(1))
                                ->from('tnelb_workflow as tw')
                                ->whereRaw('tw.application_id = ta.application_id');
                        })
                        ->selectRaw('ta.appl_type, COUNT(*) as cnt')
                        ->groupBy('ta.appl_type')
                        ->get();
                } else {
                    $roleId = (int) ($staff->roles_id ?? 0);
                    $twLast = DB::table('tnelb_workflow')
                        ->select('application_id', DB::raw('MAX(id) as max_id'))
                        ->groupBy('application_id');
                    $formPCounts = DB::table('tnelb_workflow as tw')
                        ->joinSub($twLast, 'tw_last', function ($join) {
                            $join->on('tw.application_id', '=', 'tw_last.application_id')
                                ->on('tw.id', '=', 'tw_last.max_id');
                        })
                        ->where('tw.forwarded_to', $roleId)
                        ->whereIn('tw.appl_status', ['F', 'RF'])
                        ->join('tnelb_form_p as ta', 'ta.application_id', '=', 'tw.application_id')
                        ->whereIn('ta.payment_status', ['payment', 'paid'])
                        ->selectRaw('ta.appl_type, COUNT(*) as cnt')
                        ->groupBy('ta.appl_type')
                        ->get();
                }
                if (!isset($pendingCountsMap[$formPId])) {
                    $pendingCountsMap[$formPId] = ['N' => 0, 'R' => 0];
                }
                foreach ($formPCounts as $row) {
                    $type = strtoupper((string) ($row->appl_type ?? '')) === 'R' ? 'R' : 'N';
                    $pendingCountsMap[$formPId][$type] = (int) ($row->cnt ?? 0);
                }
            }
        }



        // Build a detailed flat list combining ID + type + licence details
        $assignedForms = $assignedRows
            ->flatMap(function ($row) use ($licences) {
                $type = $row->form_type;
                $typeLabel = $type === 'N' ? 'New' : ($type === 'R' ? 'Renewal' : $type);

                return collect($row->form_id)->map(function ($id) use ($type, $typeLabel, $licences) {
                    $id = (int) $id;
                    $lic = $licences->get($id);

                    return [
                        'id'             => $id,
                        'form_type'      => $type,
                        'form_type_label'=> $typeLabel,
                        'form_name'      => $lic->form_name ?? null,
                        'licence_name'   => $lic->licence_name ?? null,
                        'color_code'     => $lic->color_code ?? null,
                        'category_id'    => $lic->category_id ?? null,
                    ];
                });
            })
            ->values();

        // Group by licence/form; New/Renewal counts from tnelb_application_tbl (pending applications)
        $assignedFormSummary = $assignedForms
            ->groupBy('id')
            ->map(function ($items) use ($pendingCountsMap) {
                $first = $items->first();
                $fid = $first['id'];
                $counts = $pendingCountsMap[$fid] ?? ['N' => 0, 'R' => 0];
                return [
                    'id'            => $fid,
                    'form_name'     => $first['form_name'],
                    'licence_name'  => $first['licence_name'],
                    'color_code'    => $first['color_code'],
                    'category_id'   => $first['category_id'],
                    'new_count'     => $counts['N'],
                    'renewal_count' => $counts['R'],
                ];
            })
            ->values()
            ->all();


        $formColors = [
            'C' => 'bg-yellow',
            'B' => 'bg-red',
            'H' => 'bg-H',
            'P' => 'bg-green',
            'EA' => 'bg-thickgreen',
            'SA' => 'bg-thickgreen',
        ];

        // Classify: Contractor (by name) → Amendments (by LicenceCategory) → Competency (rest)
        $summaryCollection = collect($assignedFormSummary);

        $contractorCardsCollection = $summaryCollection->filter(function ($item) {
            $name = mb_strtolower($item['licence_name'] ?? '');
            return strpos($name, 'contractor') !== false;
        })->values();

        $contractorIds = $contractorCardsCollection->pluck('id')->all();

        // Amendments: use LicenceCategory so forms like "Certificate H to B" are included
        $amendmentCategoryIds = \App\Models\Admin\LicenceCategory::whereRaw("LOWER(category_name) LIKE ?", ['%amend%'])
            ->pluck('id')
            ->map(function ($id) {
                return (int) $id;
            })
            ->all();

        $amendmentCardsCollection = $summaryCollection
            ->reject(function ($item) use ($contractorIds) {
                return in_array($item['id'], $contractorIds, true);
            })
            ->filter(function ($item) use ($amendmentCategoryIds) {
                if (empty($amendmentCategoryIds)) {
                    $name = mb_strtolower($item['licence_name'] ?? '');
                    return strpos($name, 'amend') !== false;
                }
                return in_array((int) ($item['category_id'] ?? 0), $amendmentCategoryIds, true);
            })
            ->values();

        $amendmentIds = $amendmentCardsCollection->pluck('id')->all();
        $contractorOrAmendmentIds = array_merge($contractorIds, $amendmentIds);

        $competencyCardsCollection = $summaryCollection->reject(function ($item) use ($contractorOrAmendmentIds) {
            return in_array($item['id'], $contractorOrAmendmentIds, true);
        })->values();

        $competencyCards = $competencyCardsCollection->all();
        $contractorCards = $contractorCardsCollection->all();
        $amendmentCards = $amendmentCardsCollection->all();

        return view('admin.dashboard.staff_dashboard', compact(
            'staff',
            'assignedFormIDs',
            'assignedForms',
            'assignedFormSummary',
            'competencyCards',
            'contractorCards',
            'amendmentCards',
            'formColors'
        ));
    }


    public function getForms($form_id)
    {
        return DB::table('tnelb_forms')
            ->where('id', $form_id) // Filter by Form S
            ->select('*')
            ->first();
    }

    public function logout()
    {
        Auth::logout();
        return redirect()->route('admin.index')->with('a', 'Logged out successfully');
    }



    public function getApplicantDetails(Request $request)
    {
        $applicationId = $request->application_id;

        // Debugging: Log the received application ID
        Log::info('Fetching details for Application ID: ' . $applicationId);

        // Fetch single applicant data
        $applicant = DB::table('tnelb_application_tbl')
            ->where('application_id', $applicationId)
            ->first(); // Fetch only one record

        if (!$applicant) {
            return response()->json(['error' => 'No data found'], 404);
        }

        return response()->json([
            'applicant_id' => $applicant->application_id,
            'applicant_name' => $applicant->applicant_name,
            'fathers_name' => $applicant->fathers_name,
            'applicants_address' => $applicant->applicants_address,
            'd_o_b' => $applicant->d_o_b,
            'age' => $applicant->age,
            // 'checklist' => $applicant->checklist,  
            // 'process' => $applicant->process       
        ]);
    }



    public function showApplicantDetails($applicant_id)
    {

        // $roles = DB::table('tnelb_registers')
        //     ->select('*')
        //         ->get();


        $returnForwardUser = null;
        // Fetch applicant details
        $applicant = DB::table('tnelb_application_tbl')
            ->join('payments', 'tnelb_application_tbl.application_id', '=', 'payments.application_id')
            ->where('tnelb_application_tbl.application_id', $applicant_id)
            ->select('tnelb_application_tbl.*', 'payments.*')
            ->first();




        if (!$applicant) {
            return abort(403, 'Applicant not found');
        }


        // var_dump($applicant->form_id);die;

        if ($applicant->appl_type == "R") {

            // $ids = [$applicant->old_application, $applicant_id];

            // Fetch educational qualifications
            $educationalQualifications = DB::table('tnelb_applicants_edu')
                ->where('application_id', $applicant_id)
                ->get();

            // Fetch work experience
            $workExperience = DB::table('tnelb_applicants_exp')
                ->where('application_id', $applicant_id)
                ->get();

            // Fetch documents
            $documents = DB::table('tnelb_applicants_doc')
                ->where('application_id', $applicant_id)
                ->get();

            // Get the last uploaded photo (if available)
            $uploadedPhoto = TnelbApplicantPhoto::where('application_id', $applicant_id)
                ->whereNotNull('upload_path')
                ->orderByDesc('id')
                ->first();

            // var_dump($workExperience);die;

        } else {

            // Fetch educational qualifications
            $educationalQualifications = DB::table('tnelb_applicants_edu')
                ->where('application_id', $applicant_id)
                ->orderBy('year_of_passing', 'desc')
                ->get();

            // Fetch work experience
            $workExperience = DB::table('tnelb_applicants_exp')
                ->where('application_id', $applicant_id)
                ->get();

            // Fetch documents
            $documents = DB::table('tnelb_applicants_doc')
                ->where('application_id', $applicant_id)
                ->get();

            // Get the last uploaded photo (if available)
            $uploadedPhoto = TnelbApplicantPhoto::where('application_id', $applicant_id)
                ->whereNotNull('upload_path')
                ->orderByDesc('id')
                ->first();
        }




        // Get the current user's role ID
        $staff = Auth::user();



        if (!$staff || !$staff->roles_id) {
            return abort(403, 'Unauthorized');
        }

        // Fetch next role dynamically from the roles table
        if (in_array($staff->name, ["Supervisor", "Supervisor2"])) {

            if ($applicant->status == 'RE') {

                // $processed_by = match ($applicant->processed_by) {
                //     'PR'  => 'President',
                //     'SE'  => 'Secretary',
                //     'S'  => 'Supervisor',
                //     'A'  => 'Accountant'
                // };

                // $nextForwardUser = DB::table('mst__staffs__tbls')
                //     ->where('name', $processed_by)
                //     ->select('name', 'roles_id')
                //     ->first(); 

                $nextForwardUser = DB::table('mst__staffs__tbls')
                    ->where('name', 'Secretary')
                    ->select('name', 'roles_id')
                    ->first();
            } else {
                $nextForwardUser = DB::table('mst__staffs__tbls')
                    ->where('name', 'Accountant')
                    ->select('name', 'roles_id')
                    ->first();
            }
        }



        // if ($staff->name === "Supervisor2") {
        //     $nextForwardUser = DB::table('mst__staffs__tbls')
        //         ->where('name', 'Accountant')
        //         ->select('name', 'roles_id')
        //         ->first();
        // }


        if ($staff->name === "Accountant") {
            $nextForwardUser = DB::table('mst__staffs__tbls')
                ->where('name', 'Secretary')
                ->select('name', 'roles_id')
                ->first();
        }
        if ($staff->name === "Secretary") {

            if ($applicant->form_id == 1) {

                $nextForwardUser = DB::table('mst__staffs__tbls')
                    ->where('name', 'President')
                    ->select('name', 'roles_id')
                    ->first();

                $returnForwardUser = DB::table('mst__staffs__tbls')
                    ->where('name', 'Supervisor')
                    ->select('name', 'roles_id')
                    ->first();
            } else {

                $nextForwardUser = DB::table('mst__staffs__tbls')
                    ->where('name', 'Secretary')
                    ->select('name', 'roles_id')
                    ->first();

                $returnForwardUser = DB::table('mst__staffs__tbls')
                    ->where('name', 'Supervisor')
                    ->select('name', 'roles_id')
                    ->first();
            }
        }

        if ($staff->name === "President") {

            $nextForwardUser = DB::table('mst__staffs__tbls')
                ->where('name', 'President')
                ->select('roles_id', 'name')
                ->first();

            $returnForwardUser = DB::table('mst__staffs__tbls')
                ->where('name', 'Supervisor')
                ->select('name', 'roles_id')
                ->first();
        }




        $user_entry = DB::table('tnelb_application_tbl')
            ->where('application_id', $applicant_id) // Filter by specific application
            ->select('*')
            ->first();



        // $workflows = DB::table('tnelb_workflow')
        //     ->leftjoin('tnelb_application_tbl', 'tnelb_workflow.application_id', '=', 'tnelb_application_tbl.application_id')
        //     ->leftjoin('mst__roles', 'tnelb_workflow.forwarded_to', '=', 'mst__roles.id')
        //     ->where('tnelb_workflow.application_id', $applicant_id) // Filter by specific application
        //     ->select('tnelb_workflow.*', 'mst__roles.name', 'tnelb_application_tbl.form_name', 'tnelb_application_tbl.license_name')
        //     ->orderBy('tnelb_workflow.id', 'desc')
        //     ->get();

        $workflows = DB::table('tnelb_workflow')
            ->leftjoin('tnelb_application_tbl', 'tnelb_workflow.application_id', '=', 'tnelb_application_tbl.application_id')
            ->leftjoin('mst__roles', 'tnelb_workflow.forwarded_to', '=', 'mst__roles.id')
            ->where('tnelb_workflow.application_id', $applicant_id) // Filter by specific application
            ->select('tnelb_workflow.*', 'mst__roles.name', 'tnelb_application_tbl.form_name', 'tnelb_application_tbl.license_name')
            ->orderBy('tnelb_workflow.id', 'desc')
            ->get();

        $workflows1 = DB::table('mst__roles')
            ->select('*')
            ->get();



        $queries = DB::table('tnelb_query_applicable as qa')
            ->leftJoin('tnelb_application_tbl as ta', 'qa.application_id', '=', 'ta.application_id')
            ->where('qa.application_id', $applicant_id)
            ->where('qa.query_status', 'P')
            ->select('qa.*')
            ->orderByDesc('qa.id')
            ->get();





        // Determine view based on user role
        $view = match ($staff->name) {
            'President'  => 'admin.dashboard.applicants_detail_supervisor',
            // 'President'  => 'admin.dashboard.applicants_detail_president',
            // 'Secretary'  => 'admin.dashboard.applicants_detail',
            'Secretary'  => 'admin.dashboard.applicants_detail_supervisor',
            'Supervisor' => 'admin.dashboard.applicants_detail_supervisor',
            'Supervisor2' => 'admin.dashboard.applicants_detail_supervisor',
            'Accountant'    => 'admin.dashboard.applicants_detail_auditor',

            default      => abort(403, 'Unauthorized'),
        };


        return view($view, compact('applicant', 'educationalQualifications', 'workExperience', 'uploadedPhoto', 'documents', 'nextForwardUser', 'returnForwardUser', 'workflows', 'queries', 'user_entry', 'staff'));
    }

    public function presidentDashboard()
    {
        // Count applications based on status
        $applicationCounts = DB::table('tnelb_application_tbl')
            ->select(
                'form_id',
                DB::raw("SUM(CASE WHEN status = 'C' THEN 1 ELSE 0 END) AS completed"),
                DB::raw("SUM(CASE WHEN status = 'P' THEN 1 ELSE 0 END) AS pending"),
                DB::raw("SUM(CASE WHEN status = 'H' THEN 1 ELSE 0 END) AS on_hold"),
                DB::raw("SUM(CASE WHEN status = 'R' THEN 1 ELSE 0 END) AS rejected")
            )
            ->groupBy('form_id')
            ->get();

        return view('admin.dashboard.president_dashboard', compact('applicationCounts'));
    }



    public function secretary_table()
    {
        $workflows = DB::table('mst_workflows as mw')
            ->join('tnelb_application_tbl as ta', 'mw.application_id', '=', 'ta.application_id') // Join condition
            ->select('mw.*', 'ta.applicant_name') // Select fields
            ->get();
        return view('admin.dashboard.secretary_table', compact('workflows'));
        // return view('dashboard.secretary_table');
    }

    /**
     * Get the processing code based on user role.
     */
    private function getProcessedByRole($roleName)
    {
        return match ($roleName) {
            'President'  => 'PR',
            'Secretary'  => 'SE',
            'Supervisor' => 'S',
            'Supervisor2' => 'S',
            'Accountant'    => 'A',
            default      => abort(403, 'Unauthorized'),
        };
    }




    // -----------------form A -------------------


    // -----------------form A -------------------
 public function applicants_detail_forma($applicant_id)
    {


        $returnForwardUser = null;

        $staff = Auth::user();


        if (!$staff || !$staff->roles_id) {
            return abort(403, 'Unauthorized');
        }



        $applicantQuery1 = DB::table('tnelb_ea_applications')
            ->leftJoin('payments', 'tnelb_ea_applications.application_id', '=', 'payments.application_id')
            ->where('tnelb_ea_applications.application_id', $applicant_id)
            ->select(
                'tnelb_ea_applications.*',
                'payments.transaction_id',
                'payments.payment_status',
                'payments.amount',
                'payments.payment_mode',
                'payments.created_at as payment_date',
                'payments.application_fee',
                'payments.late_fee',
            );

        // $applicantQuery2 = DB::table('tnelb_esa_applications')
        //     ->leftJoin('payments', 'tnelb_esa_applications.application_id', '=', 'payments.application_id')
        //     ->where('tnelb_esa_applications.application_id', $applicant_id)
        //     ->where('payments.payment_status', 'success')
        //     ->select(
        //         'tnelb_esa_applications.*',
        //             'payments.transaction_id',
        //             'payments.payment_status',
        //             'payments.amount',
        //             'payments.payment_mode',
        //             'payments.created_at as payment_date'
        //     );

        $applicant = $applicantQuery1
            // ->unionAll($applicantQuery2)
            ->orderByDesc('created_at')
            ->first();


        $formname = $applicant->form_name;

        $license_name = DB::table('mst_licences')->where('form_code', $formname)->first();



        if (!$applicant) {
            return abort(404, 'Applicant not found');
        }


        if ($staff->name === "Supervisor") {

            if ($applicant->application_status == 'RE') {

                // $processed_by = match ($applicant->processed_by) {
                //     'PR'  => 'President',
                //     'SE'  => 'Secretary',
                //     'S'  => 'Supervisor',
                //     'A'  => 'Accountant'
                // };

                // $nextForwardUser = DB::table('mst__staffs__tbls')
                //     ->where('name', $processed_by)
                //     ->select('name', 'roles_id')
                //     ->first(); 

                $nextForwardUser = DB::table('mst__staffs__tbls')
                    ->where('name', 'Secretary')
                    ->select('name', 'roles_id')
                    ->first();
            } else {
                $nextForwardUser = DB::table('mst__staffs__tbls')
                    ->where('name', 'Accountant')
                    ->select('name', 'roles_id')
                    ->first();
            }

            // if ($applicant->application_status == 'RE') {

            //     $processed_by = match ($applicant->processed_by) {
            //         'PR'  => 'President',
            //         'SE'  => 'Secretary',
            //         'S'  => 'Supervisor',
            //         'A'  => 'Accountant'
            //     };

            //     $nextForwardUser = DB::table('mst__staffs__tbls')
            //         ->where('name', $processed_by)
            //         ->select('name', 'roles_id')
            //         ->first();

            //         // dd($nextForwardUser);
            //         // exit;
            // } else {
            //     $nextForwardUser = DB::table('mst__staffs__tbls')
            //         ->where('name', 'Accountant')
            //         ->select('name', 'roles_id')
            //         ->first();

            // }
        }

        if ($staff->name === "Supervisor2") {
            $nextForwardUser = DB::table('mst__staffs__tbls')
                ->where('name', 'Accountant')
                ->select('name', 'roles_id')
                ->first();
        }


        if ($staff->name === "Accountant") {
            $nextForwardUser = DB::table('mst__staffs__tbls')
                ->where('name', 'Secretary')
                ->select('name', 'roles_id')
                ->first();
        }
        if ($staff->name === "Secretary") {

            $nextForwardUser = DB::table('mst__staffs__tbls')
                ->where('name', 'President')
                ->select('name', 'roles_id')
                ->first();


            $returnForwardUser = DB::table('mst__staffs__tbls')
                ->where('name', 'Supervisor')
                ->select('name', 'roles_id')
                ->first();
        }

        if ($staff->name === "President") {

            $nextForwardUser = DB::table('mst__staffs__tbls')
                ->where('name', 'President')
                ->select('name', 'roles_id')
                ->first();

            $returnForwardUser = DB::table('mst__staffs__tbls')
                ->where('name', 'Supervisor')
                ->select('name', 'roles_id')
                ->first();
        }

        // psql -U postgres -d arundb_dec01 -p 5432 -f "D:\laravel_program\tnelb-program-arun-dec01\TNelb-Staging\db\latest_v28112025.sql"

        $proprietordetailsform_A = DB::table('proprietordetailsform_A')
            ->where('application_id', $applicant_id)
            ->orderBy('id', 'Desc')
            ->where('proprietor_flag', '1')
            ->get();


        $staffdetails = DB::table('tnelb_applicant_cl_staffdetails')
            ->where('application_id', $applicant_id)
            ->orderBy('id')
            ->get();

        $showQcWarning = false;


        $licence_name_validitystaff = MstLicence::where('form_code', $formname)->first();

        if ($licence_name_validitystaff && $staffdetails->count() > 0) {

            $today = Carbon::today()->toDateString();
            $applType = trim($applicant->appl_type); // N or R

            /* -----------------------------
       Get fees validity
    ------------------------------ */
            $licence_validitystaff = FeesValidity::where('licence_id', $licence_name_validitystaff->id)
                ->where('form_type', $applType === 'N' ? 'N' : 'R')
                ->where('status', 1)
                ->whereDate('validity_start_date', '<=', $today)
                ->orderBy('validity_start_date', 'desc')
                ->first();

            if ($licence_validitystaff && $licence_validitystaff->validity) {

                /* -----------------------------
                Compare FIRST QC validity
                ------------------------------ */
                $firstQcValidity = Carbon::parse($staffdetails->first()->cc_validity);

                // dd($staffdetails->first()->cc_validity);
                // exit;



                // Licence period (months)
                $licencePeriodEnd = Carbon::now()->addMonths((int) $licence_validitystaff->validity);
                // dd($licencePeriodEnd);
                // exit;

                if ($firstQcValidity->lt($licencePeriodEnd)) {
                    $showQcWarning = true;
                }
            }
        }




        $staff = Auth::user();
        if (!$staff || !$staff->roles_id) {
            return abort(403, 'Unauthorized');
        }


        $user_entry = DB::table('tnelb_ea_applications')
            ->where('application_id', $applicant_id)
            ->select('*')
            
            ->first();

        $documents = DB::table('tnelb_applicant_doc_A')
            ->where('application_id', $applicant_id)
            ->select('*')

            ->first();


        $workflows = DB::table('tnelb_workflow_a')
            ->leftjoin('tnelb_ea_applications', 'tnelb_workflow_a.application_id', '=', 'tnelb_ea_applications.application_id')
            ->leftjoin('mst__roles', 'tnelb_workflow_a.forwarded_to', '=', 'mst__roles.id')
            ->where('tnelb_workflow_a.application_id', $applicant_id)
            ->select('tnelb_workflow_a.*', 'mst__roles.name', 'tnelb_ea_applications.form_name', 'tnelb_ea_applications.license_name')
            ->orderBy('tnelb_workflow_a.id', 'desc')
            ->get();

        // var_dump($workflows);die;

        $queries = DB::table('tnelb_query_applicable as qa')
            ->leftJoin('tnelb_ea_applications as ta', 'qa.application_id', '=', 'ta.application_id')
            ->where('qa.application_id', $applicant_id)
            ->where('qa.query_status', 'P')
            ->select('qa.*')
            ->first() ?? null;

        $showbankWarning = false;

        $banksolvency = Tnelb_banksolvency_a::where('application_id', $applicant_id)->where('status', '1')->first();



        $bankValidity = Carbon::parse($banksolvency->bank_validity);

        // dd($licence_validitystaff->validity);
        // exit;



        // Licence period (months)
        $bankvalidityend = Carbon::now()->addMonths((int) $licence_validitystaff->validity);
        // dd($licencePeriodEnd);
        // exit;

        if ($bankValidity->lt($bankvalidityend)) {
            $showbankWarning = true;
        }

        // $equipmentlist = Equipment_storetmp_A::where('application_id', $applicant_id)->first();


        $equiplist = Mst_equipment_tbl::where('equip_licence_name', 8)
            ->where('status', 1)
            ->orderBy('id')
            ->get();

        $equipmentlist = DB::table('equipmentforma_tbls')
            // ->where('login_id', Auth::user()->login_id)
            ->where('application_id', $applicant_id) // IMPORTANT
            ->get();

        // $view = match ($staff->name) {
        //     'President'  => 'admin.dashboard.applicants_president_forma',
        //     'Secretary'  => 'admin.dashboard.applicants_detail_sec_forma',
        //     'Supervisor' => 'admin.dashboard.applicants_detail_forma',
        //     // 'Supervisor2' => 'admin.dashboard.applicants_detail_supervisor',
        //     'Accountant'    => 'admin.dashboard.applicants_detail_auditor_forma',

        //     default      => abort(403, 'Unauthorized'),
        // };



        if ($formname == 'A') {

            $view = match ($staff->name) {
                'President'  => 'admin.dashboard.applicants_detail_forma',
                'Secretary'  => 'admin.dashboard.applicants_detail_forma',
                'Supervisor' => 'admin.dashboard.applicants_detail_forma',
                // 'Supervisor2' => 'admin.dashboard.applicants_detail_supervisor',
                'Accountant'    => 'admin.dashboard.applicants_detail_auditor_forma',

                default      => abort(403, 'Unauthorized'),
            };
        } else {


            $view = match ($staff->name) {
                'President'  => 'admin.dashboard.formsa.applicants_detail_formsa',
                'Secretary'  => 'admin.dashboard.formsa.applicants_detail_formsa',
                'Supervisor' => 'admin.dashboard.formsa.applicants_detail_formsa',
                // 'Supervisor2' => 'admin.dashboard.applicants_detail_supervisor',
                'Accountant'    => 'admin.dashboard.applicants_detail_auditor_forma',

                default      => abort(403, 'Unauthorized'),
            };
        }

        return view($view, compact(
            'applicant',
            'proprietordetailsform_A',
            'staffdetails',
            'showQcWarning',
            'nextForwardUser',
            'returnForwardUser',
            'user_entry',
            'workflows',
            'queries',
            'documents',
            'banksolvency',
            'equipmentlist',
            'license_name',
            'equiplist',
            'equipmentlist',
            'showbankWarning'
        ));
    }


    // Form A completed

    public function applicants_detail_forma_completed($applicant_id)
    {

        $returnForwardUser = null;

        $staff = Auth::user();


        if (!$staff || !$staff->roles_id) {
            return abort(403, 'Unauthorized');
        }



        $applicantQuery1 = DB::table('tnelb_ea_applications')
            ->leftJoin('payments', 'tnelb_ea_applications.application_id', '=', 'payments.application_id')
            ->where('tnelb_ea_applications.application_id', $applicant_id)
            ->select(
                'tnelb_ea_applications.*',
                'payments.transaction_id',
                'payments.payment_status',
                'payments.amount',
                'payments.payment_mode',
                'payments.created_at as payment_date',
                'payments.application_fee',
                'payments.late_fee',
            );

        // $applicantQuery2 = DB::table('tnelb_esa_applications')
        //     ->leftJoin('payments', 'tnelb_esa_applications.application_id', '=', 'payments.application_id')
        //     ->where('tnelb_esa_applications.application_id', $applicant_id)
        //     ->where('payments.payment_status', 'success')
        //     ->select(
        //         'tnelb_esa_applications.*',
        //             'payments.transaction_id',
        //             'payments.payment_status',
        //             'payments.amount',
        //             'payments.payment_mode',
        //             'payments.created_at as payment_date'
        //     );

        $applicant = $applicantQuery1
            // ->unionAll($applicantQuery2)
            ->orderByDesc('dt_submit')
            ->first();


        $formname = $applicant->form_name;

        $license_name = DB::table('mst_licences')->where('form_code', $formname)->first();



        if (!$applicant) {
            return abort(404, 'Applicant not found');
        }


        if ($staff->name === "Supervisor") {

            if ($applicant->application_status == 'RE') {

                // $processed_by = match ($applicant->processed_by) {
                //     'PR'  => 'President',
                //     'SE'  => 'Secretary',
                //     'S'  => 'Supervisor',
                //     'A'  => 'Accountant'
                // };

                // $nextForwardUser = DB::table('mst__staffs__tbls')
                //     ->where('name', $processed_by)
                //     ->select('name', 'roles_id')
                //     ->first(); 

                $nextForwardUser = DB::table('mst__staffs__tbls')
                    ->where('name', 'Secretary')
                    ->select('name', 'roles_id')
                    ->first();
            } else {
                $nextForwardUser = DB::table('mst__staffs__tbls')
                    ->where('name', 'Accountant')
                    ->select('name', 'roles_id')
                    ->first();
            }


           


            // if ($applicant->application_status == 'RE') {

            //     $processed_by = match ($applicant->processed_by) {
            //         'PR'  => 'President',
            //         'SE'  => 'Secretary',
            //         'S'  => 'Supervisor',
            //         'A'  => 'Accountant'
            //     };

            //     $nextForwardUser = DB::table('mst__staffs__tbls')
            //         ->where('name', $processed_by)
            //         ->select('name', 'roles_id')
            //         ->first();

            //         // dd($nextForwardUser);
            //         // exit;
            // } else {
            //     $nextForwardUser = DB::table('mst__staffs__tbls')
            //         ->where('name', 'Accountant')
            //         ->select('name', 'roles_id')
            //         ->first();

            // }
        }

        if ($staff->name === "Supervisor2") {
            $nextForwardUser = DB::table('mst__staffs__tbls')
                ->where('name', 'Accountant')
                ->select('name', 'roles_id')
                ->first();
        }


        if ($staff->name === "Accountant") {
            $nextForwardUser = DB::table('mst__staffs__tbls')
                ->where('name', 'Secretary')
                ->select('name', 'roles_id')
                ->first();
        }
        if ($staff->name === "Secretary") {

            $nextForwardUser = DB::table('mst__staffs__tbls')
                ->where('name', 'President')
                ->select('name', 'roles_id')
                ->first();


            $returnForwardUser = DB::table('mst__staffs__tbls')
                ->where('name', 'Supervisor')
                ->select('name', 'roles_id')
                ->first();
        }

        if ($staff->name === "President") {

            $nextForwardUser = DB::table('mst__staffs__tbls')
                ->where('name', 'President')
                ->select('name', 'roles_id')
                ->first();

            $returnForwardUser = DB::table('mst__staffs__tbls')
                ->where('name', 'Supervisor')
                ->select('name', 'roles_id')
                ->first();
        }

        // psql -U postgres -d arundb_dec01 -p 5432 -f "D:\laravel_program\tnelb-program-arun-dec01\TNelb-Staging\db\latest_v28112025.sql"
    $proprietordetailsform_A = DB::table('proprietordetailsform_A')
            ->where('application_id', $applicant_id)
            ->orderBy('id', 'Desc')
            ->where('proprietor_flag', '1')
            ->get();


        $staffdetails = DB::table('tnelb_applicant_cl_staffdetails')
            ->where('application_id', $applicant_id)
            ->orderBy('id')
            ->get();

        $showQcWarning = false;


        $licence_name_validitystaff = MstLicence::where('form_code', $formname)->first();

        if ($licence_name_validitystaff && $staffdetails->count() > 0) {

            $today = Carbon::today()->toDateString();
            $applType = trim($applicant->appl_type); // N or R

            /* -----------------------------
       Get fees validity
    ------------------------------ */
            $licence_validitystaff = FeesValidity::where('licence_id', $licence_name_validitystaff->id)
                ->where('form_type', $applType === 'N' ? 'N' : 'R')
                ->where('status', 1)
                ->whereDate('validity_start_date', '<=', $today)
                ->orderBy('validity_start_date', 'desc')
                ->first();

            if ($licence_validitystaff && $licence_validitystaff->validity) {

                /* -----------------------------
                Compare FIRST QC validity
                ------------------------------ */
                $firstQcValidity = Carbon::parse($staffdetails->first()->cc_validity);

                // dd($staffdetails->first()->cc_validity);
                // exit;



                // Licence period (months)
                $licencePeriodEnd = Carbon::now()->addMonths((int) $licence_validitystaff->validity);
                // dd($licencePeriodEnd);
                // exit;

                if ($firstQcValidity->lt($licencePeriodEnd)) {
                    $showQcWarning = true;
                }
            }
        }




        $staff = Auth::user();
        if (!$staff || !$staff->roles_id) {
            return abort(403, 'Unauthorized');
        }


        $user_entry = DB::table('tnelb_ea_applications')
            ->where('application_id', $applicant_id)
            ->select('*')
           
            ->first();

        $documents = DB::table('tnelb_applicant_doc_A')
            ->where('application_id', $applicant_id)
            ->select('*')

            ->first();


        $workflows = DB::table('tnelb_workflow_a')
            ->leftjoin('tnelb_ea_applications', 'tnelb_workflow_a.application_id', '=', 'tnelb_ea_applications.application_id')
            ->leftjoin('mst__roles', 'tnelb_workflow_a.forwarded_to', '=', 'mst__roles.id')
            ->where('tnelb_workflow_a.application_id', $applicant_id)
            ->select('tnelb_workflow_a.*', 'mst__roles.name', 'tnelb_ea_applications.form_name', 'tnelb_ea_applications.license_name')
            ->orderBy('tnelb_workflow_a.created_at', 'desc')
            ->get();

        // var_dump($workflows);die;

        $queries = DB::table('tnelb_query_applicable as qa')
            ->leftJoin('tnelb_ea_applications as ta', 'qa.application_id', '=', 'ta.application_id')
            ->where('qa.application_id', $applicant_id)
            ->where('qa.query_status', 'P')
            ->select('qa.*')
            ->first() ?? null;

        $showbankWarning = false;

        $banksolvency = Tnelb_banksolvency_a::where('application_id', $applicant_id)->where('status', '1')->first();



        $bankValidity = Carbon::parse($banksolvency->bank_validity);

        // dd($licence_validitystaff->validity);
        // exit;



        // Licence period (months)
        $bankvalidityend = Carbon::now()->addMonths((int) $licence_validitystaff->validity);
        // dd($licencePeriodEnd);
        // exit;

        if ($bankValidity->lt($bankvalidityend)) {
            $showbankWarning = true;
        }

        // $equipmentlist = Equipment_storetmp_A::where('application_id', $applicant_id)->first();


        $equiplist = Mst_equipment_tbl::where('equip_licence_name', 8)
            ->where('status', 1)
            ->orderBy('id')
            ->get();

        $equipmentlist = DB::table('equipmentforma_tbls')
            // ->where('login_id', Auth::user()->login_id)
            ->where('application_id', $applicant_id) // IMPORTANT
            ->get();




        $staff = Auth::user();
        if (!$staff || !$staff->roles_id) {
            return abort(403, 'Unauthorized');
        }


        $user_entry = DB::table('tnelb_ea_applications')
            ->where('application_id', $applicant_id)
            ->select('*')
            ->unionAll(
                DB::table('tnelb_esa_applications')
                    ->where('application_id', $applicant_id)
                    ->select('*')
            )
            ->first();

        $documents = DB::table('tnelb_applicant_doc_A')
            ->where('application_id', $applicant_id)
            ->select('*')

            ->first();


        $workflows = DB::table('tnelb_workflow_a')
            ->leftjoin('tnelb_ea_applications', 'tnelb_workflow_a.application_id', '=', 'tnelb_ea_applications.application_id')
            ->leftjoin('mst__roles', 'tnelb_workflow_a.forwarded_to', '=', 'mst__roles.id')
            ->where('tnelb_workflow_a.application_id', $applicant_id)
            ->select('tnelb_workflow_a.*', 'mst__roles.name', 'tnelb_ea_applications.form_name', 'tnelb_ea_applications.license_name')
            ->orderBy('tnelb_workflow_a.created_at', 'desc')
            ->get();

        // var_dump($workflows);die;

        $queries = DB::table('tnelb_query_applicable as qa')
            ->leftJoin('tnelb_ea_applications as ta', 'qa.application_id', '=', 'ta.application_id')
            ->where('qa.application_id', $applicant_id)
            ->where('qa.query_status', 'P')
            ->select('qa.*')
            ->first() ?? null;

     


        // $view = match ($staff->name) {
        //     'President'  => 'admin.dashboard.applicants_president_forma',
        //     'Secretary'  => 'admin.dashboard.applicants_detail_sec_forma',
        //     'Supervisor' => 'admin.dashboard.applicants_detail_forma',
        //     // 'Supervisor2' => 'admin.dashboard.applicants_detail_supervisor',
        //     'Accountant'    => 'admin.dashboard.applicants_detail_auditor_forma',

        //     default      => abort(403, 'Unauthorized'),
        // };



        $view = match ($staff->name) {
            'President'  => 'admin.completedappls.applicants_forma_completed',
            'Secretary'  => 'admin.completedappls.applicants_forma_completed',
            'Supervisor' => 'admin.completedappls.applicants_forma_completed',
            // 'Supervisor2' => 'admin.dashboard.applicants_detail_supervisor',
            'Accountant'    => 'admin.completedappls.applicants_detail_auditor_forma_completed',

            default      => abort(403, 'Unauthorized'),
        };

        return view($view, compact(
            'applicant',
            'proprietordetailsform_A',
            'staffdetails',
            'showQcWarning',
            'nextForwardUser',
            'returnForwardUser',
            'user_entry',
            'workflows',
            'queries',
            'documents',
            'banksolvency',
            'equipmentlist',
            'license_name',
            'equiplist',
            'equipmentlist',
            'showbankWarning'
        ));
    }
    // CMS

    public function homeslider()
    {
        $sliders = Tnelb_homeslider_tbl::with('media')->orderBy('updated_at', 'desc')->get();

        $media = Tnelb_Mst_Media::where([
            ['status', '=', '1'],
            ['type', '=', 'image']
        ])->orderBy('updated_at', 'desc')->get();

        return view('admincms.dashboard.homepage.index', compact('sliders', 'media'));
    }


    public function insertdata(Request $request)
    {
        $request->validate([
            'slider_image' => 'required|integer|exists:tnelb_mst_media,id',
            'slider_caption' => 'required|string',
            'slider_status' => 'required|in:0,1,2',
            'slider_caption_ta' => 'required',
        ]);

        $slider = Tnelb_homeslider_tbl::create([
            'slider_image' => $request->slider_image, // ✅ store only media ID
            'slider_caption' => $request->slider_caption,
            'slider_caption_ta' => $request->slider_caption_ta,
            'slider_status' => $request->slider_status,
            // 'updated_by' => $this->updatedBy
        ]);

        $slider->load('media'); // ✅ load related media info

        return response()->json([
            'success' => 'Slider added successfully',
            'slider' => $slider
        ]);
    }


    public function update(Request $request, $id)
    {
        $request->validate([
            'slider_caption' => 'required|string|max:255',
            'slider_caption_ta' => 'required|string|max:255',
            'slider_status' => 'required|in:0,1,2',
        ]);

        $slider = Tnelb_homeslider_tbl::findOrFail($id);

        $slider->slider_caption = $request->slider_caption;
        $slider->slider_caption_ta = $request->slider_caption_ta;
        $slider->slider_status = $request->slider_status;
        $slider->updated_by = auth()->user()->name;

        if ($request->filled('slider_image')) {
            $slider->slider_image = $request->slider_image;  // Store media image ID
        }

        $slider->save();

        // Load media relation to send full data back
        $slider->load('media');

        return response()->json([
            'success' => true,
            'slider' => $slider,
        ]);
    }




    public function delete($id)
    {
        try {
            $slider = Tnelb_homeslider_tbl::findOrFail($id);

            if ($slider->slider_image && Storage::exists('public/portaladmin/slider/' . $slider->slider_image)) {
                Storage::delete('public/portaladmin/slider/' . $slider->slider_image);
            }

            $slider->delete();

            return response()->json(['success' => true, 'message' => 'Slider deleted successfully!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }



    public function insertmenu(Request $request)
    {

        $request->validate([
            'menu_name' => 'required|string|max:255',
            'menu_type' => 'required|string|max:255',
        ]);

        $menu = Portaladmin_menu::create([
            'menu_name' => $request->menu_name,
            'menu_type' => $request->menu_type,
            'status' => $request->status ?? 1,
        ]);

        return response()->json(['success' => true, 'message' => 'Menu added successfully!', 'data' => $menu]);
    }

    // public function applicants_detail_forma($applicant_id)
    // {
    //     $returnForwardUser = null;



    //     $staff = Auth::user();


    //     if (!$staff || !$staff->roles_id) {
    //         return abort(403, 'Unauthorized');
    //     }



    //     $applicant = DB::table('tnelb_ea_applications')
    //         ->leftJoin('payments', 'tnelb_ea_applications.application_id', '=', 'payments.application_id')
    //         ->where('tnelb_ea_applications.application_id', $applicant_id)
    //         ->select(
    //             'tnelb_ea_applications.*',
    //             'payments.transaction_id',
    //             'payments.payment_status',
    //             'payments.amount',
    //             'payments.payment_mode',
    //             'payments.created_at as payment_date'
    //         )
    //         ->latest('payments.created_at') 
    //         ->first();

    //     if (!$applicant) {
    //         return abort(404, 'Applicant not found');
    //     }


    //     if ($staff->name === "Supervisor") {

    //         if ($applicant->application_status == 'RE') {

    //             $processed_by = match ($applicant->processed_by) {
    //                 'PR'  => 'President',
    //                 'SE'  => 'Secretary',
    //                 'S'  => 'Supervisor',
    //                 'A'  => 'Accountant'
    //             };

    //             $nextForwardUser = DB::table('mst__staffs__tbls')
    //                 ->where('name', $processed_by)
    //                 ->select('name', 'roles_id')
    //                 ->first();

    //                 // dd($nextForwardUser);
    //                 // exit;
    //         } else {
    //             $nextForwardUser = DB::table('mst__staffs__tbls')
    //                 ->where('name', 'Accountant')
    //                 ->select('name', 'roles_id')
    //                 ->first();

    //         }
    //     }

    //     if ($staff->name === "Supervisor2") {
    //         $nextForwardUser = DB::table('mst__staffs__tbls')
    //             ->where('name', 'Accountant')
    //             ->select('name', 'roles_id')
    //             ->first();
    //     }


    //     if ($staff->name === "Accountant") {
    //         $nextForwardUser = DB::table('mst__staffs__tbls')
    //             ->where('name', 'Secretary')
    //             ->select('name', 'roles_id')
    //             ->first();
    //     }
    //     if ($staff->name === "Secretary") {

    //         $nextForwardUser = DB::table('mst__staffs__tbls')
    //             ->where('name', 'President')
    //             ->select('name', 'roles_id')
    //             ->first();


    //         $returnForwardUser = DB::table('mst__staffs__tbls')
    //             ->where('name', 'Supervisor')
    //             ->select('name', 'roles_id')
    //             ->first();
    //     }

    //     if ($staff->name === "President") {

    //         $nextForwardUser = DB::table('mst__staffs__tbls')
    //             ->where('name', 'President')
    //             ->select('name', 'roles_id')
    //             ->first();

    //         $returnForwardUser = DB::table('mst__staffs__tbls')
    //             ->where('name', 'Supervisor')
    //             ->select('name', 'roles_id')
    //             ->first();
    //     }



    //     $proprietordetailsform_A = DB::table('proprietordetailsform_A')
    //         ->where('application_id', $applicant_id)
    //         ->where('proprietor_flag', '1')
    //         ->get();


    //     $staffdetails = DB::table('tnelb_applicant_cl_staffdetails')
    //         ->where('application_id', $applicant_id)
    //         ->get();


    //     $staff = Auth::user();
    //     if (!$staff || !$staff->roles_id) {
    //         return abort(403, 'Unauthorized');
    //     }


    //     $user_entry = DB::table('tnelb_ea_applications')
    //         ->where('application_id', $applicant_id) 
    //         ->select('*')
    //         ->first();

    //           $documents = DB::table('tnelb_applicant_doc_A')
    //         ->where('application_id', $applicant_id) 
    //         ->select('*')
    //         ->first();


    //     $workflows = DB::table('tnelb_workflow_a')
    //         ->leftjoin('tnelb_ea_applications', 'tnelb_workflow_a.application_id', '=', 'tnelb_ea_applications.application_id')
    //         ->leftjoin('mst__roles', 'tnelb_workflow_a.forwarded_to', '=', 'mst__roles.id')
    //         ->where('tnelb_workflow_a.application_id', $applicant_id) 
    //         ->select('tnelb_workflow_a.*', 'mst__roles.name', 'tnelb_ea_applications.form_name', 'tnelb_ea_applications.license_name')
    //         ->orderBy('tnelb_workflow_a.created_at', 'desc')
    //         ->get();

    //     // var_dump($workflows);die;

    //     $queries = DB::table('tnelb_query_applicable as qa')
    //         ->leftJoin('tnelb_ea_applications as ta', 'qa.application_id', '=', 'ta.application_id')
    //         ->where('qa.application_id', $applicant_id) 
    //         ->where('qa.query_status', 'P') 
    //         ->select('qa.*')
    //         ->first() ?? null;


    //     $view = match ($staff->name) {
    //         'President'  => 'admin.dashboard.applicants_president_forma',
    //         'Secretary'  => 'admin.dashboard.applicants_detail_sec_forma',
    //         'Supervisor' => 'admin.dashboard.applicants_detail_forma',
    //         // 'Supervisor2' => 'admin.dashboard.applicants_detail_supervisor',
    //         'Accountant'    => 'admin.dashboard.applicants_detail_auditor_forma',

    //         default      => abort(403, 'Unauthorized'),
    //     };

    //     return view($view, compact(
    //         'applicant',
    //         'proprietordetailsform_A',
    //         'staffdetails',
    //         'nextForwardUser',
    //         'user_entry',
    //         'workflows',
    //         'queries',
    //         'documents'
    //     ));
    // }



    // // Form A completed

    // public function applicants_detail_forma_completed($applicant_id)
    // {
    //     $returnForwardUser = null;


    //     // Get the current user's role ID
    //     $staff = Auth::user();


    //     if (!$staff || !$staff->roles_id) {
    //         return abort(403, 'Unauthorized');
    //     }


    //     // Fetch applicant details
    //     $applicant = DB::table('tnelb_ea_applications')
    //         ->leftJoin('payments', 'tnelb_ea_applications.application_id', '=', 'payments.application_id')
    //         ->where('tnelb_ea_applications.application_id', $applicant_id)
    //         ->select(
    //             'tnelb_ea_applications.*',
    //             'payments.transaction_id',
    //             'payments.payment_status',
    //             'payments.amount',
    //             'payments.payment_mode',
    //             'payments.created_at as payment_date'
    //         )
    //         ->latest('payments.created_at') 
    //         ->first();

    //     if (!$applicant) {
    //         return abort(404, 'Applicant not found');
    //     }

    //     // Fetch next role dynamically from the roles table
    //     if ($staff->name === "Supervisor") {

    //         if ($applicant->application_status == 'RE') {

    //             $processed_by = match ($applicant->processed_by) {
    //                 'PR'  => 'President',
    //                 'SE'  => 'Secretary',
    //                 'S'  => 'Supervisor',
    //                 'A'  => 'Accountant'
    //             };

    //             $nextForwardUser = DB::table('mst__staffs__tbls')
    //                 ->where('name', $processed_by)
    //                 ->select('name', 'roles_id')
    //                 ->first();
    //         } else {
    //             $nextForwardUser = DB::table('mst__staffs__tbls')
    //                 ->where('name', 'Accountant')
    //                 ->select('name', 'roles_id')
    //                 ->first();
    //         }
    //     }

    //     if ($staff->name === "Supervisor2") {
    //         $nextForwardUser = DB::table('mst__staffs__tbls')
    //             ->where('name', 'Accountant')
    //             ->select('name', 'roles_id')
    //             ->first();
    //     }


    //     if ($staff->name === "Accountant") {
    //         $nextForwardUser = DB::table('mst__staffs__tbls')
    //             ->where('name', 'Secretary')
    //             ->select('name', 'roles_id')
    //             ->first();
    //     }
    //     if ($staff->name === "Secretary") {

    //         $nextForwardUser = DB::table('mst__staffs__tbls')
    //             ->where('name', 'President')
    //             ->select('name', 'roles_id')
    //             ->first();


    //         $returnForwardUser = DB::table('mst__staffs__tbls')
    //             ->where('name', 'Supervisor')
    //             ->select('name', 'roles_id')
    //             ->first();
    //     }

    //     if ($staff->name === "President") {

    //         $nextForwardUser = DB::table('mst__staffs__tbls')
    //             ->where('name', 'President')
    //             ->select('name', 'roles_id')
    //             ->first();

    //         $returnForwardUser = DB::table('mst__staffs__tbls')
    //             ->where('name', 'Supervisor')
    //             ->select('name', 'roles_id')
    //             ->first();
    //     }


    //     // Fetch educational qualifications
    //     $proprietordetailsform_A = DB::table('proprietordetailsform_A')
    //         ->where('application_id', $applicant_id)
    //          ->orderBy('updated_at', 'ASC')
    //         ->where('proprietor_flag', '1')
    //         ->get();

    //     // Fetch work experience
    //     $staffdetails = DB::table('tnelb_applicant_cl_staffdetails')
    //         ->where('application_id', $applicant_id)
    //         ->get();

    //     // Ensure user is authenticated
    //     $staff = Auth::user();
    //     if (!$staff || !$staff->roles_id) {
    //         return abort(403, 'Unauthorized');
    //     }


    //     $user_entry = DB::table('tnelb_ea_applications')
    //         ->where('application_id', $applicant_id) 
    //         ->select('*')
    //         ->first();

    //           $documents = DB::table('tnelb_applicant_doc_A')
    //         ->where('application_id', $applicant_id) 
    //         ->select('*')
    //         ->first();


    //     $workflows = DB::table('tnelb_workflow_a')
    //         ->leftjoin('tnelb_ea_applications', 'tnelb_workflow_a.application_id', '=', 'tnelb_ea_applications.application_id')
    //         ->leftjoin('mst__roles', 'tnelb_workflow_a.forwarded_to', '=', 'mst__roles.id')
    //         ->where('tnelb_workflow_a.application_id', $applicant_id) 
    //         ->select('tnelb_workflow_a.*', 'mst__roles.name', 'tnelb_ea_applications.form_name', 'tnelb_ea_applications.license_name')
    //         ->orderBy('tnelb_workflow_a.created_at', 'desc')
    //         ->get();

    //     // var_dump($workflows);die;

    //     $queries = DB::table('tnelb_query_applicable as qa')
    //         ->leftJoin('tnelb_ea_applications as ta', 'qa.application_id', '=', 'ta.application_id')
    //         ->where('qa.application_id', $applicant_id) 
    //         ->where('qa.query_status', 'P') 
    //         ->select('qa.*')
    //         ->first() ?? null;


    //     $view = match ($staff->name) {
    //         'President'  => 'admin.completedappls.applicants_forma_completed',
    //         'Secretary'  => 'admin.completedappls.applicants_forma_completed',
    //         'Supervisor' => 'admin.completedappls.applicants_forma_completed',
    //         // 'Supervisor2' => 'admin.dashboard.applicants_detail_supervisor',
    //         'Accountant'    => 'admin.completedappls.applicants_forma_completed',

    //         default      => abort(403, 'Unauthorized'),
    //     };

    //     return view($view, compact(
    //         'applicant',
    //         'proprietordetailsform_A',
    //         'staffdetails',
    //         'nextForwardUser',
    //         'user_entry',
    //         'workflows',
    //         'queries',
    //         'documents'
    //     ));
    // }


    // // CMS

    // public function homeslider()
    // {
    //     $sliders = Tnelb_homeslider_tbl::with('media')->orderBy('updated_at', 'desc')->get();

    //     $media = Tnelb_Mst_Media::where([
    //         ['status', '=', '1'],
    //         ['type', '=', 'image']
    //     ])->orderBy('updated_at', 'desc')->get();

    //     return view('admincms.dashboard.homepage.index', compact('sliders', 'media'));
    // }


    // public function insertdata(Request $request)
    // {
    //     $request->validate([
    //         'slider_image' => 'required|integer|exists:tnelb_mst_media,id',
    //         'slider_caption' => 'required|string',
    //         'slider_status' => 'required|in:0,1,2',
    //         'slider_caption_ta' => 'required',
    //     ]);

    //     $slider = Tnelb_homeslider_tbl::create([
    //         'slider_image' => $request->slider_image, // ✅ store only media ID
    //         'slider_caption' => $request->slider_caption,
    //         'slider_caption_ta' => $request->slider_caption_ta,
    //         'slider_status' => $request->slider_status,
    //         'updated_by' => $this->updatedBy
    //     ]);

    //     $slider->load('media'); // ✅ load related media info

    //     return response()->json([
    //         'success' => 'Slider added successfully',
    //         'slider' => $slider
    //     ]);
    // }


    // public function update(Request $request, $id)
    // {
    //     $request->validate([
    //         'slider_caption' => 'required|string|max:255',
    //         'slider_caption_ta' => 'required|string|max:255',
    //         'slider_status' => 'required|in:0,1,2',
    //     ]);

    //     $slider = Tnelb_homeslider_tbl::findOrFail($id);

    //     $slider->slider_caption = $request->slider_caption;
    //     $slider->slider_caption_ta = $request->slider_caption_ta;
    //     $slider->slider_status = $request->slider_status;
    //     $slider->updated_by = auth()->user()->name;

    //     if ($request->filled('slider_image')) {
    //         $slider->slider_image = $request->slider_image;  // Store media image ID
    //     }

    //     $slider->save();

    //     // Load media relation to send full data back
    //     $slider->load('media');

    //     return response()->json([
    //         'success' => true,
    //         'slider' => $slider,
    //     ]);
    // }




    // public function delete($id)
    // {
    //     try {
    //         $slider = Tnelb_homeslider_tbl::findOrFail($id);

    //         if ($slider->slider_image && Storage::exists('public/portaladmin/slider/' . $slider->slider_image)) {
    //             Storage::delete('public/portaladmin/slider/' . $slider->slider_image);
    //         }

    //         $slider->delete();

    //         return response()->json(['success' => true, 'message' => 'Slider deleted successfully!']);
    //     } catch (\Exception $e) {
    //         return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    //     }
    // }



    // public function insertmenu(Request $request)
    // {

    //     $request->validate([
    //         'menu_name' => 'required|string|max:255',
    //         'menu_type' => 'required|string|max:255',
    //     ]);

    //     $menu = Portaladmin_menu::create([
    //         'menu_name' => $request->menu_name,
    //         'menu_type' => $request->menu_type,
    //         'status' => $request->status ?? 1,
    //     ]);

    //     return response()->json(['success' => true, 'message' => 'Menu added successfully!', 'data' => $menu]);
    // }

    public function view_application_details($applicant_id)
    {


        $returnForwardUser = null;
        // Fetch applicant details
        $applicant = DB::table('tnelb_application_tbl')
            ->leftjoin('payments', 'tnelb_application_tbl.application_id', '=', 'payments.application_id')
            ->where('tnelb_application_tbl.application_id', $applicant_id)
            ->select('tnelb_application_tbl.*', 'payments.*')
            ->first();


        if (!$applicant) {
            return abort(403, 'Applicant not found');
        }


        // var_dump($applicant->form_id);die;

        if ($applicant->appl_type == "R") {

            // $ids = [$applicant->old_application, $applicant_id];

            // Fetch educational qualifications
            $educationalQualifications = DB::table('tnelb_applicants_edu')
                ->where('application_id', $applicant_id)
                ->get();

            // Fetch work experience
            $workExperience = DB::table('tnelb_applicants_exp')
                ->where('application_id', $applicant_id)
                ->get();

            // Fetch documents
            $documents = DB::table('tnelb_applicants_doc')
                ->where('application_id', $applicant_id)
                ->get();

            // Get the last uploaded photo (if available)
            $uploadedPhoto = TnelbApplicantPhoto::where('application_id', $applicant_id)
                ->whereNotNull('upload_path')
                ->orderByDesc('id')
                ->first();

            // var_dump($workExperience);die;

        } else {

            // Fetch educational qualifications
            $educationalQualifications = DB::table('tnelb_applicants_edu')
                ->where('application_id', $applicant_id)
                ->orderBy('year_of_passing', 'desc')
                ->get();

            // Fetch work experience
            $workExperience = DB::table('tnelb_applicants_exp')
                ->where('application_id', $applicant_id)
                ->get();

            // Fetch documents
            $documents = DB::table('tnelb_applicants_doc')
                ->where('application_id', $applicant_id)
                ->get();

            // Get the last uploaded photo (if available)
            $uploadedPhoto = TnelbApplicantPhoto::where('application_id', $applicant_id)
                ->whereNotNull('upload_path')
                ->orderByDesc('id')
                ->first();
        }




        // Get the current user's role ID
        $staff = Auth::user();


        if (!$staff || !$staff->roles_id) {
            return abort(403, 'Unauthorized');
        }

        // Fetch next role dynamically from the roles table
        if ($staff->name === "Supervisor") {

            if ($applicant->status == 'RE') {

                // $processed_by = match ($applicant->processed_by) {
                //     'PR'  => 'President',
                //     'SE'  => 'Secretary',
                //     'S'  => 'Supervisor',
                //     'A'  => 'Accountant'
                // };
                $processed_by = 'Secretary';


                $nextForwardUser = DB::table('mst__staffs__tbls')
                    ->where('name', $processed_by)
                    ->select('name', 'roles_id')
                    ->first();
            } else {
                $nextForwardUser = DB::table('mst__staffs__tbls')
                    ->where('name', 'Accountant')
                    ->select('name', 'roles_id')
                    ->first();
            }
        }


        if ($staff->name === "Supervisor2") {
            $nextForwardUser = DB::table('mst__staffs__tbls')
                ->where('name', 'Accountant')
                ->select('name', 'roles_id')
                ->first();
        }


        if ($staff->name === "Accountant") {
            $nextForwardUser = DB::table('mst__staffs__tbls')
                ->where('name', 'Secretary')
                ->select('name', 'roles_id')
                ->first();
        }
        if ($staff->name === "Secretary") {

            if ($applicant->form_id == 1) {

                $nextForwardUser = DB::table('mst__staffs__tbls')
                    ->where('name', 'President')
                    ->select('name', 'roles_id')
                    ->first();

                $returnForwardUser = DB::table('mst__staffs__tbls')
                    ->where('name', 'Supervisor')
                    ->select('name', 'roles_id')
                    ->first();
            } else {

                $nextForwardUser = DB::table('mst__staffs__tbls')
                    ->where('name', 'Secretary')
                    ->select('name', 'roles_id')
                    ->first();

                $returnForwardUser = DB::table('mst__staffs__tbls')
                    ->where('name', 'Supervisor')
                    ->select('name', 'roles_id')
                    ->first();
            }
        }

        if ($staff->name === "President") {

            $nextForwardUser = DB::table('mst__staffs__tbls')
                ->where('name', 'President')
                ->select('roles_id', 'name')
                ->first();

            $returnForwardUser = DB::table('mst__staffs__tbls')
                ->where('name', 'Supervisor')
                ->select('name', 'roles_id')
                ->first();
        }




        $user_entry = DB::table('tnelb_application_tbl')
            ->where('application_id', $applicant_id) // Filter by specific application
            ->select('*')
            ->first();



        $workflows = DB::table('tnelb_workflow')
            ->leftjoin('tnelb_application_tbl', 'tnelb_workflow.application_id', '=', 'tnelb_application_tbl.application_id')
            ->leftjoin('mst__roles', 'tnelb_workflow.forwarded_to', '=', 'mst__roles.id')
            ->where('tnelb_workflow.application_id', $applicant_id) // Filter by specific application
            ->select('tnelb_workflow.*', 'mst__roles.name', 'tnelb_application_tbl.form_name', 'tnelb_application_tbl.license_name')
            ->orderBy('tnelb_workflow.id', 'desc')
            ->get();




        $queries = DB::table('tnelb_query_applicable as qa')
            ->leftJoin('tnelb_application_tbl as ta', 'qa.application_id', '=', 'ta.application_id')
            ->where('qa.application_id', $applicant_id)
            ->where('qa.query_status', 'P')
            ->select('qa.*')
            ->orderByDesc('qa.id')
            ->get();



        return view('admin.dashboard.view_application', compact(
            'applicant',
            'educationalQualifications',
            'workExperience',
            'uploadedPhoto',
            'documents',
            'nextForwardUser',
            'returnForwardUser',
            'workflows',
            'queries',
            'user_entry',
            'staff'
        ));
    }
}
