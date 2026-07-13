<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\DB;
use App\Models\Admin\SupervisorModel;
use App\Models\Tnelb_CC_Digitization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Helpers\RoleHelper;
use App\Models\CC_Forms_Meta;
use App\Services\Competency\CompetencyAdminQueryService;
use App\Services\Competency\CompetencyApplicationService;
use App\Services\Competency\CompetencyCertificateService;
use App\Services\Competency\CompetencyMetaService;
use App\Services\Competency\CompetencyWorkflowService;
use Carbon\Carbon;

use function PHPUnit\Framework\isNull;

class SupervisorController extends Controller
{
    protected $today, $dbNow;
    public function __construct()
    {
        $this->today = Carbon::today()->toDateString();
        $this->dbNow  = DB::selectOne("SELECT date_trunc('second', NOW()::timestamp) AS db_now")->db_now;
    }

    public function index()
    {
        $userFormID = (int) Auth::user()->form_id;
        $ccAdminQuery = app(CompetencyAdminQueryService::class);

        if ($ccAdminQuery->isCcMetaFormId($userFormID)) {
            $metaTable = app(CompetencyMetaService::class)->tableForFormId($userFormID);
            $applications = DB::table($metaTable ?? 'cc_form_s_meta')
                ->select('*')
                ->get();
        } else {
            $applications = DB::table('tnelb_application_tbl')
                ->where('form_id', $userFormID)
                ->select('*')
                ->get();
        }

        return view('admin.dashboards.supervisor', compact('applications'));
    }
        // QSC/QC updation--------------
            public function updateQcQsc(Request $request)
        {
            $applicationId = $request->application_id;
            $payload = [
                'qc' => $request->qc,
                'qsc' => $request->qsc,
                'updated_at' => now(),
            ];

            if (CC_Forms_Meta::where('application_id', $applicationId)->exists()) {
                CC_Forms_Meta::where('application_id', $applicationId)->update($payload);
            } else {
                $metaTable = app(CompetencyMetaService::class)->metaTableForApplicationId($applicationId);
                if ($metaTable) {
                    DB::table($metaTable)->where('application_id', $applicationId)->update($payload);
                } else {
                    DB::table('tnelb_application_tbl')
                        ->where('application_id', $applicationId)
                        ->update($payload);
                }
            }

            return response()->json([
                'status' => true,
                'message' => 'Updated successfully'
            ]);
        }

     public function view_applications(Request $request)
    {   
        

        $staff = Auth::user();
        if (!$staff) {
            return abort(403, 'Unauthorized');
        }

        $selectedFormId = (int) ($request->input('form_id') ?? $staff->form_id);
        if ($selectedFormId <= 0) {
            return view('admin.supervisor.view', [
                'workflows' => collect(),
                'new_applications' => collect(),
                'renewal' => collect(),
                'returned_applications' => collect(),
                'is_completed_list' => true,
            ]);
        }

        $formPId = (int) DB::table('mst_licences')->where('cert_licence_code', 'P')->value('id');
        if ($formPId > 0 && $selectedFormId === $formPId) {
            $roleLevel = (int) (optional($staff->role)->role_level ?? 0);
            $roleId = (int) ($staff->roles_id ?? 0);
            $applTypeFilter = in_array($request->input('form_type', ''), ['N', 'R', 'D', 'A'], true) ? strtoupper((string) $request->input('form_type')) : null;
            if ($roleLevel === 1) {
                $query = DB::table('tnelb_form_p as ta')
                    ->whereIn('ta.payment_status', ['payment', 'paid'])
                    ->whereIn('ta.app_status', ['P', 'RE'])
                    ->whereNotExists(function ($q) {
                        $q->select(DB::raw(1))->from('tnelb_workflow as tw')->whereRaw('tw.application_id = ta.application_id');
                    })
                    ->select(
                        'ta.*',
                        DB::raw("'Form P' as form_name"),
                        DB::raw('ta.license_name as license_name'),
                        DB::raw("EXISTS (SELECT 1 FROM tnelb_workflow tw2 WHERE tw2.application_id = ta.application_id AND tw2.appl_status = 'QU') AS has_return_history")
                    );
                if ($applTypeFilter) {
                    $query->where('ta.appl_type', $applTypeFilter);
                }
                $workflows = $query
                    ->orderByDesc('ta.submitted_date')
                    ->orderByDesc('ta.id')
                    ->get();
            } else {
                $twLast = DB::table('tnelb_workflow')->select('application_id', DB::raw('MAX(id) as max_id'))->groupBy('application_id');
                $currentAppIds = DB::table('tnelb_workflow as tw')
                    ->joinSub($twLast, 'tw_last', function ($join) {
                        $join->on('tw.application_id', '=', 'tw_last.application_id')->on('tw.id', '=', 'tw_last.max_id');
                    })
                    ->where('tw.forwarded_to', $roleId)->whereIn('tw.appl_status', ['F', 'RF'])->select('tw.application_id');
                $workflows = DB::query()->fromSub($currentAppIds, 'cur')
                    ->join('tnelb_form_p as ta', 'ta.application_id', '=', 'cur.application_id')
                    ->whereIn('ta.payment_status', ['payment', 'paid'])
                    ->select(
                        'ta.*',
                        DB::raw("'Form P' as form_name"),
                        DB::raw('ta.license_name as license_name'),
                        DB::raw("EXISTS (SELECT 1 FROM tnelb_workflow tw2 WHERE tw2.application_id = ta.application_id AND tw2.appl_status = 'QU') AS has_return_history")
                    )
                    ->orderByDesc('ta.id')
                    ->get();
                if ($applTypeFilter) {
                    $workflows = collect($workflows)->filter(function ($row) use ($applTypeFilter) {
                        return strtoupper((string) ($row->appl_type ?? '')) === $applTypeFilter;
                    })->values();
                }
            }
            // Returned tab: QU (waiting for applicant) + resubmitted (P/RE with QU in history) +
            // Form P apps returned to Supervisor/upper staff with an open workflow query (RE + latest tw.query_status P).
            $twLastFormP = DB::table('tnelb_workflow')
                ->select('application_id', DB::raw('MAX(id) as max_id'))
                ->groupBy('application_id');

            $returnedQuery = DB::table('tnelb_form_p as ta')
                ->whereIn('ta.payment_status', ['payment', 'paid'])
                ->where(function ($q) use ($twLastFormP) {
                    $q->where('ta.app_status', 'QU')
                        ->orWhereRaw("(ta.app_status IN ('P','RE') AND EXISTS (SELECT 1 FROM tnelb_workflow tw WHERE tw.application_id = ta.application_id AND tw.appl_status = 'QU'))")
                        ->orWhere(function ($q2) use ($twLastFormP) {
                            $q2->whereIn('ta.app_status', ['P', 'RE'])
                                ->whereExists(function ($sub) use ($twLastFormP) {
                                    $sub->select(DB::raw(1))
                                        ->from('tnelb_workflow as tw')
                                        ->joinSub($twLastFormP, 'tw_last', function ($join) {
                                            $join->on('tw.application_id', '=', 'tw_last.application_id')
                                                ->on('tw.id', '=', 'tw_last.max_id');
                                        })
                                        ->whereColumn('tw.application_id', 'ta.application_id')
                                        ->where('tw.query_status', 'P');
                                });
                        });
                })
                ->select('ta.*', DB::raw("'Form P' as form_name"), DB::raw('ta.license_name as license_name'))
                ->orderByDesc('ta.submitted_date')
                ->orderByDesc('ta.id');
            if ($applTypeFilter) {
                $returnedQuery->where('ta.appl_type', $applTypeFilter);
            }

            $returned_applications = $returnedQuery->get();

            [$renewal, $new_applications] = collect($workflows)->partition(function ($row) {
                return strtoupper((string) ($row->appl_type ?? '')) === 'R';
            });
            return view('admin.supervisor.view', compact('workflows', 'new_applications', 'renewal', 'returned_applications'));
        }

        $requestedType = strtoupper((string) $request->input('form_type', ''));
        $applTypeFilter = in_array($requestedType, ['N', 'R', 'D', 'A'], true) ? $requestedType : null;

        $ccAdminQuery = app(CompetencyAdminQueryService::class);
        if ($ccAdminQuery->isCcMetaFormId($selectedFormId)) {
            $roleLevel = (int) (optional($staff->role)->role_level ?? 0);
            $roleId = (int) ($staff->roles_id ?? 0);
            $isSupervisorRole = ($roleLevel === 1) || in_array($staff->name ?? '', ['Supervisor', 'Supervisor2'], true);

            if ($isSupervisorRole) {
                $workflows = $ccAdminQuery->supervisorPendingApplications($selectedFormId, $applTypeFilter, $staff);
                $returned_applications = $ccAdminQuery->supervisorReturnedApplications($selectedFormId, $applTypeFilter);
            } else {
                $previousProcessedBy = match ($roleLevel) {
                    2 => ['S', 'S2'],
                    3 => ['A'],
                    4 => ['SE'],
                    default => [],
                };
                $workflows = $ccAdminQuery->rolePendingApplications($selectedFormId, $roleId, $previousProcessedBy, $applTypeFilter);
                $returned_applications = collect();
            }

            [$renewal, $new_applications] = collect($workflows)->partition(function ($row) {
                return strtoupper((string) ($row->appl_type ?? '')) === 'R';
            });

            $new_applications = $new_applications
                ->reject(function ($row) {
                    return strtoupper((string) ($row->appl_type ?? '')) === 'R';
                })
                ->values();

            return view('admin.supervisor.view', compact('workflows', 'new_applications', 'renewal', 'returned_applications'));
        }

        $roleLevel = (int) (optional($staff->role)->role_level ?? 0); // mst_roles.role_level
        $roleId = (int) ($staff->roles_id ?? 0);
        $isSupervisorRole = ($roleLevel === 1) || in_array($staff->name ?? '', ['Supervisor', 'Supervisor2'], true);

        // Supervisor: show (1) apps with no workflow, OR (2) latest workflow RE and forwarded_to Supervisor (resubmitted).
        // Other roles: show apps currently forwarded to them (latest workflow row).
        if ($isSupervisorRole) {
            $supervisorRoleId = RoleHelper::supervisorWorkflowRoleId($staff);
            $twLast = DB::table('tnelb_workflow')
                ->select('application_id', DB::raw('MAX(id) as max_id'))
                ->groupBy('application_id');

            // Match dashboard: no payment_status filter; (no workflow) OR (latest workflow RE + forwarded_to Supervisor)
            $query = DB::table('tnelb_application_tbl as ta')
                ->leftJoinSub($twLast, 'tw_last', function ($join) {
                    $join->on('ta.application_id', '=', 'tw_last.application_id');
                })
                ->leftJoin('tnelb_workflow as tw', function ($join) {
                    $join->on('tw.application_id', '=', 'tw_last.application_id')
                        ->on('tw.id', '=', 'tw_last.max_id');
                })
                ->leftJoin('mst_licences as ml', 'ta.form_id', '=', 'ml.id')
                ->where('ta.form_id', $selectedFormId)
                ->whereIn('ta.status', ['P', 'RE'])
                ->whereIn('ta.payment_status', ['payment', 'paid'])
                ->whereNotExists(function ($q) {
                    $q->select(DB::raw(1))
                        ->from('tnelb_workflow_a as twa')
                        ->whereRaw('twa.application_id = ta.application_id');
                })
                ->where(function ($q) use ($supervisorRoleId) {
                    $q->whereNull('tw.id')
                        ->orWhere(function ($q2) use ($supervisorRoleId) {
                            $q2->where('tw.forwarded_to', $supervisorRoleId)->where('tw.appl_status', 'RE');
                        });
                });

            if ($applTypeFilter) {
                $query->where('ta.appl_type', $applTypeFilter);
            }

            $workflows = $query
                ->select(
                    'ta.*',
                    DB::raw('COALESCE(ml.form_name, ta.form_name) as form_name'),
                    DB::raw('COALESCE(ml.licence_name, ta.license_name) as license_name'),
                    DB::raw("EXISTS (SELECT 1 FROM tnelb_workflow tw2 WHERE tw2.application_id = ta.application_id AND tw2.appl_status = 'QU') AS has_return_history")
                )
                ->distinct()
                ->orderByDesc('ta.submitted_date')
                ->orderByDesc('ta.id')
                ->get();

            // If list still empty, use same fallback as dashboard: all P/RE for this form (no workflow filter)
            if ($workflows->isEmpty()) {
                $fallbackQuery = DB::table('tnelb_application_tbl as ta')
                    ->leftJoin('mst_licences as ml', 'ta.form_id', '=', 'ml.id')
                    ->where('ta.form_id', $selectedFormId)
                    ->whereIn('ta.status', ['P', 'RE'])
                    ->whereIn('ta.payment_status', ['payment', 'paid'])
                    ->whereNotExists(function ($q) {
                        $q->select(DB::raw(1))
                            ->from('tnelb_workflow_a as twa')
                            ->whereRaw('twa.application_id = ta.application_id');
                    });
                if ($applTypeFilter) {
                    $fallbackQuery->where('ta.appl_type', $applTypeFilter);
                }
                $workflows = $fallbackQuery
                    ->select(
                        'ta.*',
                        DB::raw('COALESCE(ml.form_name, ta.form_name) as form_name'),
                        DB::raw('COALESCE(ml.licence_name, ta.license_name) as license_name'),
                        DB::raw("EXISTS (SELECT 1 FROM tnelb_workflow tw2 WHERE tw2.application_id = ta.application_id AND tw2.appl_status = 'QU') AS has_return_history")
                    )
                    ->orderByDesc('ta.submitted_date')
                    ->orderByDesc('ta.id')
                    ->get();
            }

            // Returned tab: QU (waiting for applicant) + resubmitted (P/RE with return history)
            $returned_applications = DB::table('tnelb_application_tbl as ta')
                ->leftJoin('mst_licences as ml', 'ta.form_id', '=', 'ml.id')
                ->where('ta.form_id', $selectedFormId)
                ->whereIn('ta.payment_status', ['payment', 'paid'])
                ->where(function ($q) {
                    $q->where('ta.status', 'QU')
                        ->orWhereRaw("(ta.status IN ('P','RE') AND EXISTS (SELECT 1 FROM tnelb_workflow tw WHERE tw.application_id = ta.application_id AND tw.appl_status = 'QU'))");
                })
                ->when($applTypeFilter, function ($q) use ($applTypeFilter) {
                    return $q->where('ta.appl_type', $applTypeFilter);
                })
                ->select(
                    'ta.*',
                    DB::raw('COALESCE(ml.form_name, ta.form_name) as form_name'),
                    DB::raw('COALESCE(ml.licence_name, ta.license_name) as license_name')
                )
                ->orderByDesc('ta.submitted_date')
                ->orderByDesc('ta.id')
                ->get();
        } else {
            $previousProcessedBy = match ($roleLevel) {
                2 => ['S', 'S2'], // Accountant handles after Supervisor/Supervisor2
                3 => ['A'],       // Secretary handles after Accountant
                4 => ['SE'],      // President handles after Secretary
                default => [],
            };

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

            $fallbackAppIds = DB::table('tnelb_application_tbl as ta')
                ->where('ta.form_id', $selectedFormId)
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

            $query = DB::query()
                ->fromSub($currentAppIds, 'cur')
                ->join('tnelb_application_tbl as ta', 'ta.application_id', '=', 'cur.application_id')
                ->leftJoin('mst_licences as ml', 'ta.form_id', '=', 'ml.id')
                ->where('ta.form_id', $selectedFormId)
                ->whereIn('ta.payment_status', ['payment', 'paid']);

            if ($applTypeFilter) {
                $query->where('ta.appl_type', $applTypeFilter);
            }

            $workflows = $query
                ->select(
                    'ta.*',
                    DB::raw('COALESCE(ml.form_name, ta.form_name) as form_name'),
                    DB::raw('COALESCE(ml.licence_name, ta.license_name) as license_name'),
                    DB::raw("EXISTS (SELECT 1 FROM tnelb_workflow tw2 WHERE tw2.application_id = ta.application_id AND tw2.appl_status = 'QU') AS has_return_history")
                )
                ->distinct()
                ->orderByDesc('ta.submitted_date')
                ->orderByDesc('ta.id')
                ->get();

            $returned_applications = collect();
        }

        [$renewal, $new_applications] = collect($workflows)->partition(function ($row) {
            return strtoupper((string) ($row->appl_type ?? '')) === 'R';
        });

        $new_applications = $new_applications
            ->sortByDesc('submitted_date')
            ->values();

        $renewal = $renewal
            ->sortByDesc('submitted_date')
            ->values();

        return view('admin.supervisor.view', compact('workflows', 'new_applications', 'renewal', 'returned_applications'));
    }


    /**
     * List completed (approved) applications for a given form_id and optional form_type (N/R).
     * Reuses admin.supervisor.view with is_completed_list = true for title/back link.
     */
    public function view_completed_applications(Request $request)
    {
        $staff = Auth::user();
        if (!$staff) {
            return abort(403, 'Unauthorized');
        }

        $selectedFormId = (int) $request->input('form_id');
        $applTypeFilter = in_array($request->input('form_type', ''), ['N', 'R', 'D', 'A'], true) ? strtoupper((string) $request->input('form_type')) : null;

        if ($selectedFormId <= 0) {
            return view('admin.supervisor.view', [
                'workflows' => collect(),
                'new_applications' => collect(),
                'renewal' => collect(),
                'is_completed_list' => true,
            ]);
        }

        $licence = DB::table('mst_licences')->where('id', $selectedFormId)->first();
        $formCode = $licence ? strtoupper((string) ($licence->cert_licence_code ?? '')) : '';

        $formPId = (int) DB::table('mst_licences')->where('cert_licence_code', 'P')->value('id');

        if ($formPId > 0 && $selectedFormId === $formPId) {
            $query = DB::table('tnelb_form_p as ta')
                ->where('ta.app_status', 'A')
                ->select(
                    'ta.*',
                    DB::raw("'Form P' as form_name"),
                    DB::raw('ta.license_name as license_name'),
                    'ta.license_number',
                    'ta.issued_at',
                    'ta.expires_at'
                );
            if ($applTypeFilter) {
                $query->where('ta.appl_type', $applTypeFilter);
            }
            $workflows = $query->orderByDesc('ta.id')->get();
            [$renewal, $new_applications] = collect($workflows)->partition(function ($row) {
                return strtoupper((string) ($row->appl_type ?? '')) === 'R';
            });
            return view('admin.supervisor.view', compact('workflows', 'new_applications', 'renewal') + ['is_completed_list' => true]);
        }

        // Contractor forms: EA, SA, B, SB etc. use separate tables
        $contractorTablesByCode = [
            'EA' => 'tnelb_ea_applications',
            'SA' => 'tnelb_esa_applications',
            'B'  => 'tnelb_eb_applications',
            'SB' => 'tnelb_esb_applications',
        ];

        if (isset($contractorTablesByCode[$formCode]) && \Illuminate\Support\Facades\Schema::hasTable($contractorTablesByCode[$formCode])) {
            $tbl = $contractorTablesByCode[$formCode];
            $query = DB::table($tbl . ' as ta')
                ->leftJoin('tnelb_license as tl', 'tl.application_id', '=', 'ta.application_id')
                ->leftJoin('tnelb_renewal_license as tr', 'tr.application_id', '=', 'ta.application_id')
                ->whereIn('ta.application_status', ['F', 'RF', 'A'])
                ->select(
                    'ta.*',
                    DB::raw('ta.license_name as license_name'),
                    DB::raw('COALESCE(tl.license_number, tr.license_number) as license_number'),
                    DB::raw('COALESCE(tl.issued_at, tr.issued_at) as issued_at'),
                    DB::raw('COALESCE(tl.expires_at, tr.expires_at) as expires_at')
                );
            if ($applTypeFilter) {
                $query->where('ta.appl_type', $applTypeFilter);
            }
            $workflows = $query->orderByDesc('ta.updated_at')->get();
            [$renewal, $new_applications] = collect($workflows)->partition(function ($row) {
                return strtoupper((string) ($row->appl_type ?? '')) === 'R';
            });
            return view('admin.supervisor.view', compact('workflows', 'new_applications', 'renewal') + ['is_completed_list' => true]);
        }

        // Competency S/W/WH: cc_form_s_meta (not legacy tnelb_application_tbl)
        $ccAdminQuery = app(CompetencyAdminQueryService::class);
        if ($ccAdminQuery->isCcMetaFormId($selectedFormId)) {
            $workflows = $ccAdminQuery->completedApplications($selectedFormId, $applTypeFilter);
            [$renewal, $new_applications] = collect($workflows)->partition(function ($row) {
                return strtoupper((string) ($row->appl_type ?? '')) === 'R';
            });

            return view('admin.supervisor.view', compact('workflows', 'new_applications', 'renewal') + ['is_completed_list' => true]);
        }

        // Legacy competency / amendments still on tnelb_application_tbl
        $query = DB::table('tnelb_application_tbl as ta')
            ->leftJoin('mst_licences as ml', 'ta.form_id', '=', 'ml.id')
            ->leftJoin('tnelb_license as tl', 'tl.application_id', '=', 'ta.application_id')
            ->leftJoin('tnelb_renewal_license as tr', 'tr.application_id', '=', 'ta.application_id')
            ->where('ta.form_id', $selectedFormId)
            ->where('ta.status', 'A')
            ->select(
                'ta.*',
                DB::raw('COALESCE(ml.form_name, ta.form_name) as form_name'),
                DB::raw('COALESCE(ml.licence_name, ta.license_name) as license_name'),
                DB::raw('COALESCE(tl.license_number, tr.license_number) as license_number'),
                DB::raw('COALESCE(tl.issued_at, tr.issued_at) as issued_at'),
                DB::raw('COALESCE(tl.expires_at, tr.expires_at) as expires_at')
            )
            ->orderByDesc('ta.id');

        if ($applTypeFilter) {
            $query->where('ta.appl_type', $applTypeFilter);
        }

        $workflows = $query->get();
        [$renewal, $new_applications] = collect($workflows)->partition(function ($row) {
            return strtoupper((string) ($row->appl_type ?? '')) === 'R';
        });

        return view('admin.supervisor.view', compact('workflows', 'new_applications', 'renewal') + ['is_completed_list' => true]);
    }

    public function view_auditor()
    {
        $userRole = Auth::user()->roles_id; // Auditor's Role ID (2)

        $workflows = DB::table('tnelb_application_tbl as ta')
            ->join('tnelb_workflow as tw', 'ta.application_id', '=', 'tw.application_id') // Ensure it's processed
            ->join('tnelb_forms as f', 'ta.form_id', '=', 'f.id') // Join forms table
            ->where('tw.forwarded_to', $userRole) // Assigned to Auditor
            ->where('tw.appl_status', 'F') // Status must be Forwarded
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('tnelb_workflow as sub_tw')
                    ->whereRaw('sub_tw.application_id = ta.application_id')
                    ->whereRaw('sub_tw.role_id != tw.forwarded_to'); // Ensure it's processed by someone else
            })
            ->select('ta.*', 'f.form_name')
            ->distinct()
            ->get();

        return view('admin.supervisor.recentapply', compact('workflows'));
    }



    public function get_completed()
    {
        $assignedFormID = (int) Auth::user()->form_id;
        $ccAdminQuery = app(CompetencyAdminQueryService::class);

        $workflows = $ccAdminQuery->isCcMetaFormId($assignedFormID)
            ? $ccAdminQuery->inProgressApplications($assignedFormID, ['F', 'A', 'RF'], ['S', 'A', 'SE', 'PR'])
            : collect();

        return view('admin.supervisor.completed', compact('workflows'));
    }

    public function get_completed_wh()
    {
        $assignedFormID = 3;
        $workflows = app(CompetencyAdminQueryService::class)
            ->inProgressApplications($assignedFormID, ['F', 'A', 'RF'], ['S2', 'A', 'SE', 'PR']);

        return view('admin.supervisor.completed', compact('workflows'));
    }


    /*
    * Get the FORM A applications
    */
    public function view_forma($type)
    {
        $staff = Auth::user();
        $roleName = $staff->name ?? '';

        $role_id = $staff->role_id;
        // dd($role_id);exit;

        // Base query for Form A contractor applications
        $query = DB::table('tnelb_ea_applications as ta')
            ->where('ta.form_name', 'A')
            ->where('ta.payment_status', 'paid');

        // Optional filter: appl_type = N (New) or R (Renewal), driven by ?form_type=
        $requestedType = strtoupper((string) request()->query('form_type', ''));

        if (in_array($requestedType, ['N', 'R'], true)) {
            $query->where('ta.appl_type', $requestedType);
        }

        // Supervisor should see applications that are still with Supervisor
        // (i.e. processed_by = 'S' and not yet forwarded further, typically P / RE / F)
        if (in_array($roleName, ['Supervisor', 'Supervisor2'], true)) {
            $query->whereIn('ta.application_status', ['P', 'RE'])
                ->where(function ($q) {
                    $q->whereIn('ta.processed_by', ['A', 'SE', 'S'])
                        ->orWhereNull('ta.processed_by');
                });
        }

        // Assistant Secretary should see only applications that have been forwarded
        // by Supervisor to Assistant Secretary (processed_by = 'A', usually status F/RF)
        elseif ($roleName === 'Assistant Secretary') {
            $query->whereIn('ta.application_status', ['F', 'RF'])
                ->where('ta.processed_by', 'S');
        } elseif ($roleName === 'Secretary') {
            $query->whereIn('ta.application_status', ['F', 'RF', 'RE'])
                ->whereIn('ta.processed_by', ['A', 'PR']);
        } elseif ($roleName === 'President') {
            $query->whereIn('ta.application_status', ['F', 'RF'])
                ->where('ta.processed_by', 'SE');
        }

        $workflows = $query
            ->orderBy('ta.updated_at', 'DESC')
            ->select('ta.*')
            ->get();




        return view('admin.supervisor.view_forma', compact('workflows', 'role_id'));
    }


    public function completed_forma()
    {
        $userRole = Auth::user()->roles_id;

        // $assignedForms = DB::table('tnelb_ea_applications as ta')
        // ->whereIn('ta.application_status', ['F', 'RF','A']) // Filter by status
        // ->select('ta.*') // Select all columns from applicant_formA
        // ->get();


        // var_dump($assignedForms);die;

        $workflows = DB::table('tnelb_ea_applications')
            ->whereIn('application_status', ['F', 'RF', 'A'])
            ->orderby('updated_at', 'DESC')
            ->select('*')
            ->get();

        $applicationIds = $workflows->pluck('application_id');


        $licenses = DB::table('tnelb_license')
            ->whereIn('application_id', $applicationIds)
            ->select('application_id', 'license_number')
            ->get()
            ->keyBy('application_id');

        $renewalLicenses = DB::table('tnelb_renewal_license')
            ->whereIn('application_id', $applicationIds)
            ->select('application_id', 'license_number')
            ->get()
            ->keyBy('application_id');

        return view('admin.supervisor.completed_forma', compact(
            'workflows',
            'licenses',
            'renewalLicenses'
        ));
    }

    public function forwardApplication(Request $request, $role)
    {
        // dd($request->all());exit;


        $staff = Auth::user();

        $staffID = Auth::user()->id;

        $request->validate([
            'application_id' => 'required|string',
            'processed_by'   => 'required|string',
            'forwarded_to'   => 'required|string',
            'role_id'        => 'required|integer',
            'checkboxes'     => 'nullable|string',
            'queryswitch'    => 'nullable|string',
            'queryType'      => 'array',
            'remarks'        => 'nullable|string'
        ]);


        $appService = app(CompetencyApplicationService::class);
        $workflowService = app(CompetencyWorkflowService::class);

        $applicant = $appService->findApplicantWithPayment($request->application_id);
        if (! $applicant) {
            return response()->json(['status' => 'error', 'message' => 'Applicant not found.'], 404);
        }

        $applicantStatus = $appService->applicationStatus($applicant);
        $isReturnedApplication = $applicantStatus === 'RE';

        $queryTypeJson = $request->queryType && is_array($request->queryType) && count($request->queryType) > 0
            ? json_encode($request->queryType) : null;

        $processed_by = match ($staff->name) {
            'President'   => 'PR',
            'Secretary'   => 'SE',
            'Supervisor'  => 'S',
            'Supervisor2' => 'S2',
            'Assistant Secretary'     => 'A',
            default       => abort(403, 'Unauthorized'),
        };

        $query_status = ($request->queryswitch === 'Yes') ? 'P' : null;
        $raised_by    = ($request->queryswitch === 'Yes') ? $processed_by : $staffID;




        // if ($processed_by == 'A') {
        //     $last_workflow = SupervisorModel::where('application_id', $request->application_id)
        //         ->orderBy('id', 'desc')   // latest entry first
        //         ->first();

        //     $query_status = $last_workflow->query_status == 'P' ? 'P' : '';
        //     if ($last_workflow->query_status == 'P') {
        //         $query_status = 'P';
        //         $queryTypeJson = $last_workflow->queries;
        //     }
        // }




        // DB::table('tnelb_query_applicable')->insert([
        //     'application_id' => $request->application_id,
        //     'query_type'     => $queryTypeJson,
        //     'raised_by'      => $raised_by,
        //     'query_status'   => $query_status,
        //     'created_at'     => now(),
        // ]);


        $formType = (object) ['form_id' => $applicant->form_id ?? null];

        $status = $appService->resolveForwardStatus($staff->name, $applicant, $formType);

        $chklistStatus = $request->input('chklist_status', []);
        $workflowTable = $appService->resolveWorkflowTable($request->application_id, $applicant);
        $workflowPayload = [
            'application_id' => $request->application_id,
            'appl_status'    => $isReturnedApplication ? 'RF' : 'F',
            'processed_by'   => $processed_by,
            'forwarded_to'   => $request->forwarded_to,
            'role_id'        => $request->role_id,
            'is_verified'    => $request->checkboxes ?? 'Yes',
            'query_status'   => $query_status,
            'chklist_status' => $chklistStatus,
            'remarks'        => $request->remarks,
            'created_at'     => $this->dbNow,
            'login_id'       => $staffID,
            'queries'        => $queryTypeJson ? json_decode($queryTypeJson, true) : null,
            'raised_by'      => $query_status == 'P' ? $raised_by : $processed_by,
        ];

        $workflowService->record($workflowTable, $workflowPayload);

        $appService->updateApplicationStatus($request->application_id, [
            'status' => $status,
            'processed_by' => $processed_by,
            'updated_at' => $this->dbNow,
        ]);

            if ($request->filled('qc') || $request->filled('qsc')) {
           DB::table($appService->resolveMetaTable($request->application_id, $applicant))
            ->where('application_id', $request->application_id)
            ->update([
                'qc' => $request->qc,
                'qsc' => $request->qsc,
            ]);
            }

            // dd($role); exit;

            if($request->forwarded_to =='assistantsecretary '){

                dd('111'); exit;
                $role= 'Assistant Secretary';
            }

        return response()->json([
            'status' => "success",
            'message' => "Application Forwarded to $role successfully!",
        ], 201);
    }

    public function forwardApplication_bk27042026(Request $request, $role)
    {


        $staff = Auth::user();

        $staffID = Auth::user()->id;

        $request->validate([
            'application_id' => 'required|string',
            'processed_by'   => 'required|string',
            'forwarded_to'   => 'required|string',
            'role_id'        => 'required|integer',
            'checkboxes'     => 'nullable|string',
            'queryswitch'    => 'nullable|string',
            'queryType'      => 'array',
            'remarks'        => 'nullable|string'
        ]);


        $appService = app(CompetencyApplicationService::class);
        $applicant = $appService->findApplicantWithPayment($request->application_id);

        $applicantStatus = $applicant ? $appService->applicationStatus($applicant) : null;
        $isReturnedApplication = $applicantStatus === 'RE';

        $queryTypeJson = $request->queryType && is_array($request->queryType) && count($request->queryType) > 0
            ? json_encode($request->queryType) : null;

        $processed_by = match ($staff->name) {
            'President'   => 'PR',
            'Secretary'   => 'SE',
            'Supervisor'  => 'S',
            'Supervisor2' => 'S2',
            'Accountant'     => 'A',
            default       => abort(403, 'Unauthorized'),
        };

        $query_status = ($request->queryswitch === 'Yes') ? 'P' : null;
        $raised_by    = ($request->queryswitch === 'Yes') ? $processed_by : $staffID;


        // if ($processed_by == 'A') {
        //     $last_workflow = SupervisorModel::where('application_id', $request->application_id)
        //         ->orderBy('id', 'desc')   // latest entry first
        //         ->first();

        //     $query_status = $last_workflow->query_status == 'P' ? 'P' : '';
        //     if ($last_workflow->query_status == 'P') {
        //         $query_status = 'P';
        //         $queryTypeJson = $last_workflow->queries;
        //     }
        // }




        // DB::table('tnelb_query_applicable')->insert([
        //     'application_id' => $request->application_id,
        //     'query_type'     => $queryTypeJson,
        //     'raised_by'      => $raised_by,
        //     'query_status'   => $query_status,
        //     'created_at'     => now(),
        // ]);


        $formType = (object) [
            'form_id' => app(CompetencyApplicationService::class)->findFormId($request->application_id),
        ];



        $status = match ($staff->name) {
            'President'   => 'A',
            'Secretary'   => $formType->form_id == 1 ? 'F' : 'A',
            'Supervisor'  => $isReturnedApplication ? 'RF' : 'F',
            'Supervisor2' => $isReturnedApplication ? 'RF' : 'F',
            'Accountant'  => 'F',
            default       => abort(403, 'Unauthorized'),
        };


        // Insert data into tnelb_workflow table
        $workflow = SupervisorModel::create([ // Ensure this is the correct model
            'application_id' => $request->application_id,
            'appl_status'    => $isReturnedApplication ? 'RF' : 'F', // Forwarded
            'processed_by'   => $request->processed_by,
            'forwarded_to'   => $request->forwarded_to,
            'role_id'        => $request->role_id,
            'is_verified'    => $request->checkboxes ?? 'Yes',
            'query_status'   => $query_status,
            // "Yes" or "No"
            'remarks'        => $request->remarks,
            'created_at'     => $this->dbNow,
            'login_id'       => $staffID,
            'queries'        => $queryTypeJson,
            'raised_by'      => $query_status == 'P' ? $raised_by : '',
        ]);



        // Update application status
        if ($applicant) {
            $appService->updateApplicationStatus($request->application_id, [
                'status' => $status,
                'processed_by' => $processed_by,
                'updated_at' => $this->dbNow,
            ]);
        }

        return response()->json([
            'status' => "success",
            'message' => "Application Forwarded to $role successfully!",
        ], 201);
    }

    // Forwarded for application  FORM A




    public function forwardApplicationforma(Request $request, $role)
    {

        // dd($request->all());die;

        $staff = Auth::user();

        $staffID = Auth::user()->id;

        $request->validate([
            'application_id' => 'required|string',
            'processed_by'   => 'required|string',
            'forwarded_to'   => 'required|string',
            'role_id'        => 'required|integer',
            'checkboxes'     => 'nullable|string',
            'queryswitch'    => 'nullable|string',
            'queryType'      => 'array',
            'remarks'        => 'nullable|string'
        ]);


        $applicant = EA_Application_model::where('application_id', $request->application_id)
            ->select('*')
            ->first();

        $applicantStatus = $applicant ? $applicant->application_status : null;
        $isReturnedApplication = $applicantStatus === 'RE';
        $queryTypeJson = $request->queryType && is_array($request->queryType) && count($request->queryType) > 0
            ? json_encode($request->queryType) : null;


        $processed_by = match ($staff->name) {
            'President'   => 'PR',
            'Secretary'   => 'SE',
            'Supervisor'  => 'S',
            'Supervisor2' => 'S2',
            'Assistant Secretary'     => 'A',
            default       => abort(403, 'Unauthorized'),
        };



        $query_status = ($request->queryswitch === 'Yes') ? 'P' : null;
        $raised_by    = ($request->queryswitch === 'Yes') ? $processed_by : $staffID;


        if ($processed_by == 'A') {
            $last_workflow = WorkflowA::where('application_id', $request->application_id)
                ->orderBy('id', 'desc')   // latest entry first
                ->first();

            $query_status = $last_workflow->query_status == 'P' ? 'P' : '';
            if ($last_workflow->query_status == 'P') {
                $query_status = 'P';
                $queryTypeJson = $last_workflow->queries;
            }
        }




        // DB::table('tnelb_query_applicable')->insert([
        //     'application_id' => $request->application_id,
        //     'query_type'     => $queryTypeJson,
        //     'raised_by'      => $raised_by,
        //     'query_status'   => $query_status,
        //     'created_at'     => now(),
        // ]);


        // $formType = DB::table('tnelb_ea_applications')
        //     ->where('application_id', $request->application_id)
        //     ->select('form_id')
        //     ->first();



        $status = match ($staff->name) {
            'President'   => 'A',
            // 'Secretary'  => $formType->form_id == 1 ? 'F' : 'A',
            'Secretary'   => $isReturnedApplication ? 'RF' : 'F',
            'Supervisor'  => $isReturnedApplication ? 'RF' : 'F',
            'Supervisor2' => $isReturnedApplication ? 'RF' : 'F',
            'Assistant Secretary'  => 'F',
            default       => abort(403, 'Unauthorized'),
        };

        // dd($request->queryswitch);

        // die;
        // dd($request->returnflag);exit;

        $forwarded = $request->forwarded_to;


        // dd($forwarded);exit;

        // Insert data into tnelb_workflow table
        $workflow = WorkflowA::create([ // Ensure this is the correct model
            'application_id' => $request->application_id,
            'appl_status'    => $isReturnedApplication ? 'RF' : 'F', // Forwarded
            'processed_by'   => $request->processed_by,
            'forwarded_to'   => $forwarded,
            'role_id'        => $request->role_id,
            'is_verified'    => $request->checkboxes ?? 'Yes',
            'query_status'   => $query_status,
            // "Yes" or "No"
            'remarks'        => $request->remarks,
            'created_at'     => now(), // Automatically managed if model has timestamps
            'login_id'       => $staffID,
            'queries'        => $queryTypeJson,
            'raised_by'      => $query_status == 'P' ? $raised_by : '',
        ]);

        WorkflowA::where('application_id', $request->application_id)
            ->where('processed_by', $request->processed_by)
            ->where('role_id', $request->role_id)
            ->orderByDesc('id')
            ->limit(1)
            ->update([
                'created_at' => DB::raw('NOW()'),
            ]);





        EA_Application_model::where('application_id', $request->application_id)
            ->update([
                'application_status' => $status, // Role-based forwarding
                'processed_by'  => $processed_by, // Role-based forwarding
                'updated_at' => DB::raw('NOW()'),
            ]);




        $message = "Application Forwarded to $role successfully!";


        return response()->json([
            'status'  => 'success',
            'message' => $message,
        ], 201);
    }



    public function approveApplicationForma(Request $request)
    {
        $request->validate([
            'application_id'    => 'required|string',
            'processed_by'      => 'required|string',
            'forwarded_to'      => 'nullable|integer',
            'remarks'           => 'nullable|string',
            'validity_override' => 'nullable|string',
            'oldapplicationId'  => 'nullable|string',
            'qc_validity_date'  => 'nullable|date',
            'bank_validity'     => 'nullable|date',
            'licensename'       => 'required|string',
        ]);

        $application = DB::table('tnelb_ea_applications')
            ->where('application_id', $request->application_id)
            ->first();

        if (!$application) {
            return response()->json(['error' => 'Application not found'], 404);
        }

        DB::beginTransaction();

        try {
            /* -------------------- BASIC UPDATE -------------------- */

            $processed = Auth::user()->name === 'President' ? 'PR' : 'SE';

            DB::table('tnelb_ea_applications')
                ->where('application_id', $request->application_id)
                ->update([
                    'application_status' => 'A',
                    'processed_by'       => $processed,
                    'updated_at'         => now(),
                ]);

            $appl_type = trim($application->appl_type); // R or N
            // $issuedAt  = now()->format('Y-m-d H:i:s');
            $issuedAt  = $application->dt_submit;
            $expiresAt = null;
            $newSerial = null;

            /* -------------------- GET LICENCE VALIDITY MONTHS -------------------- */

            $form = DB::table('mst_licences')
                ->where('cert_licence_code', $request->licensename)
                ->where('status', 1)
                ->first();

            $validity = DB::table('mst_fees_validity')
                ->where('licence_id', $form->id)
                ->where('form_type', $appl_type)
                ->where('status', 1)
                ->whereDate('validity_start_date', '<=', now())
                ->orderBy('validity_start_date', 'desc')
                ->first();

            $monthsToAdd = $validity->validity ?? 0;

            /* -------------------- NORMAL EXPIRY CALCULATION -------------------- */

            if ($appl_type === 'R') {

                // Renewal → old expiry + months
                $oldExpiry = DB::table('tnelb_renewal_license')
                    ->where('application_id', $request->oldapplicationId)
                    ->value('expires_at');

                $baseExpiry = $oldExpiry
                    ? Carbon::parse($oldExpiry)
                    : now();

                $expiresAt = $baseExpiry->copy()->addMonths($monthsToAdd)->toDateString();
            } else {

                // Fresh → today + months
                $expiresAt = now()->addMonths($monthsToAdd)->toDateString();


                // dd($monthsToAdd);exit;
            }

            /* -------------------- OVERRIDE (POPUP CONFIRMED) -------------------- */

            if ($request->validity_override === 'YES') {

                // dd('111');
                // exit;

                $qcValidity   = $request->qc_validity_date
                    ? Carbon::parse($request->qc_validity_date)
                    : null;

                $bankValidity = $request->bank_validity
                    ? Carbon::parse($request->bank_validity)
                    : null;

                $expiresAt = collect([
                    Carbon::parse($expiresAt),
                    $qcValidity,
                    $bankValidity,
                ])->filter()->min()->toDateString();

                // dd($expiresAt);
                // exit;
            }

            /* -------------------- LICENSE INSERT / UPDATE -------------------- */

            if ($appl_type === 'R') {

                DB::table('tnelb_renewal_license')->insert([
                    'login_id'       => $application->login_id,
                    'license_number' => $application->license_number,
                    'application_id' => $request->application_id,
                    'issued_by'      => $request->processed_by,
                    'issued_at'      => $issuedAt,
                    'expires_at'     => $expiresAt,
                    'created_at'     => now(),
                ]);

                $newSerial = $application->license_number;
            } else {

                $prefix    = $application->license_name;
                $yearMonth = now()->format('Ym');

                $lastSerial = DB::table('tnelb_license')
                    ->where('license_number', 'LIKE', "L{$prefix}{$yearMonth}%")
                    ->orderByDesc('license_number')
                    ->value('license_number');

                $next = $lastSerial ? str_pad((int)substr($lastSerial, -5) + 1, 5, '0', STR_PAD_LEFT) : '00001';

                $newSerial = "L{$prefix}{$yearMonth}{$next}";

                DB::table('tnelb_license')->insert([
                    'application_id' => $request->application_id,
                    'license_number' => $newSerial,
                    'issued_by'      => $request->processed_by,
                    'issued_at'      => $issuedAt,
                    'expires_at'     => $expiresAt,
                ]);
            }


            $workflowId = DB::table('tnelb_workflow_a')->insertGetId([
                'application_id' => $request->application_id,
                'processed_by'   => $request->processed_by,
                'role_id'        => Auth::user()->roles_id,
                'appl_status'    => 'A',
                'remarks'        => $request->remarks ?? 'No remarks provided',
                'forwarded_to'   => $request->forwarded_to,
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);

            // 2️⃣ UPDATE SAME RECORD (guaranteed)
            DB::table('tnelb_workflow_a')
                ->where('id', $workflowId)
                ->update([
                    'created_at' => DB::raw('NOW()'),
                    'updated_at' => DB::raw('NOW()'),
                ]);


            DB::commit();

            return response()->json([
                'status'         => 'success',
                'message'        => $appl_type === 'R'
                    ? "Renewal expires on " . date('d/m/Y', strtotime($expiresAt))
                    : "License expires on " . date('d/m/Y', strtotime($expiresAt)),
                'license_number' => $newSerial,
                'issued_at'      => $issuedAt,
                'expires_at'     => $expiresAt,
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Approval failed',
                'msg'   => $e->getMessage()
            ], 500);
        }
    }

    public function approveApplicationForma_beforepopup_bk(Request $request)
    {

        // DB::table('tnelb_application_tbl')
        //     ->where('application_id', 'WB251111111')
        //     ->update([
        //         'd_o_b' => '01-01-1945'
        //     ]);

        //     DB::table('tnelb_ea_applications')
        //     ->where('application_id', 'AEA25000001')
        //     ->update([
        //         'payment_status' => 'paid',
        //         'application_status' => 'P',

        //     ]);

        // $show = DB::table('tnelb_ea_applications')
        //         ->where('application_id', 'AEA25000001')
        //         ->first();

        // $show = DB::table('tnelb_license')->get()->toArray();

        //         $show = DB::table('tnelb_license')
        //           ->where('application_id', 'AEA25000004')
        //           ->delete();
        // if ($show) {
        //     echo "Record deleted successfully";
        // } else {
        //     echo "No record found";
        // }



        //  dd($show);
        // exit;

        // dd($request->all());
        // exit;

        $request->validate([
            'application_id' => 'required|string',
            'processed_by'   => 'required|string',
            'forwarded_to'   => 'integer',
            'remarks'        => 'nullable|string',
        ]);

        // Fetch the application details
        $application = DB::table('tnelb_ea_applications')
            ->where('application_id', $request->application_id)

            ->first();

        if (!$application) {
            return response()->json(['error' => 'Application not found.'], 404);
        }
        $login_id = $application->login_id;


        $formname = $application->form_name;

        $licensename = $request->licensename;

        // dd($licensename);
        // exit;



        // Get form type
        // $formType = DB::table('tnelb_forms')->where('id', $application->form_id)->first();


        // if (!in_array($formType->form_name, ['FORM S', 'FORM W'])) {
        //     return response()->json(['error' => 'This application cannot be approved by the secretary.'], 403);
        // }

        DB::beginTransaction();
        try {
            // ...  earlier code to update application_status etc ...
            $staff = Auth::user()->name;

            if ($staff == "President") {
                $processed = 'PR';
            } else {
                $processed = 'SE';
            }



            DB::table('tnelb_ea_applications')
                ->where('application_id', $request->application_id)
                ->update([
                    'application_status'     => 'A',
                    'processed_by' => isset($processed) ? $processed : 'PR',
                    'updated_at' => now(),
                ]);



            $appl_type = preg_replace('/\s+/', '', $application->appl_type);

            // Ensure these are defined for later use
            $issuedAt = null;
            $expiresAt = null;
            $newSerial = null;



            if ($appl_type == "R") {



                $license_details = DB::table('tnelb_renewal_license')
                    ->where('application_id', $request->application_id)
                    ->first();

                $now = now();

                // If no renewal record OR renewal expired -> proceed to renew
                if (!$license_details || $now->greaterThan(Carbon::parse($license_details->expires_at))) {

                    $formid = DB::table('mst_licences')
                        ->where('cert_licence_code', $licensename)
                        ->where('status', '1')
                        ->first();


                    $today = Carbon::today()->toDateString();

                    $licenseperiod = DB::table('mst_fees_validity')
                        ->where('licence_id', $formid->id)
                        ->where('form_type', $appl_type)
                        ->where('status', 1)
                        ->whereDate('validity_start_date', '<=', $today)
                        ->orderBy('validity_start_date', 'desc')
                        ->first();

                    $monthsToAdd = $licenseperiod->validity ?? 0;
                    // dd($monthsToAdd);
                    // exit;
                    // 🔥 Get original expiry date from tnelb_license table
                    $oldExpiry = DB::table('tnelb_license')
                        ->where('application_id', $request->oldapplicationId)
                        ->value('expires_at');   // single column

                    // If no expiry found, use NOW as fallback
                    $expirySourceDate = $oldExpiry ? Carbon::parse($oldExpiry) : now();



                    // 🔥 Add the validity months to old expiry
                    $expiresAt = $expirySourceDate->copy()->addMonths($monthsToAdd)->format('Y-m-d');

                    // dd($expiresAt);
                    //           exit;

                    $issuedAt = now()->format('Y-m-d H:i:s');

                    DB::table('tnelb_renewal_license')->insert([
                        'login_id'       => $login_id,
                        'license_number' => $application->license_number,
                        'application_id' => $request->application_id,
                        'issued_by'      => $request->processed_by,
                        'issued_at'      => $issuedAt,
                        'expires_at'     => $expiresAt,
                        'created_at'     => now(),
                    ]);

                    $newSerial = $application->license_number;
                } else {
                    // existing renewal record still valid -> reuse its values
                    $newSerial = $license_details->license_number;
                    $issuedAt = $license_details->issued_at;
                    $expiresAt = $license_details->expires_at;
                }
            } else {


                // Fresh license (N)
                $license_details = DB::table('tnelb_license')
                    ->where('application_id', $request->application_id)
                    ->first();



                if ($license_details) {

                    //                    dd('exist');
                    // exit;
                    // use existing license
                    $newSerial = $license_details->license_number;
                    $issuedAt = $license_details->issued_at;
                    $expiresAt = $license_details->expires_at;
                } else {
                    //                                dd('new');
                    // exit;
                    // create new license
                    $prefix = $application->license_name;
                    $yearMonth = date('Ym');

                    $lastSerial = DB::table('tnelb_license')
                        ->where('license_number', 'LIKE', "L{$prefix}{$yearMonth}%")
                        ->orderBy('license_number', 'desc')
                        ->value('license_number');

                    if ($lastSerial) {
                        $lastNumber = (int) substr($lastSerial, -5);
                        $newNumber = str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
                    } else {
                        $newNumber = '00001';
                    }

                    $newSerial = "L{$prefix}{$yearMonth}{$newNumber}";
                    $issuedAt = now()->format('Y-m-d H:i:s');

                    //  dd($licensename);
                    //  exit;
                    $formid = DB::table('mst_licences')
                        ->where('cert_licence_code', $licensename)
                        ->where('status', '1')
                        ->first();

                    $today = Carbon::today()->toDateString();

                    // $today = '2025-12-31';

                    //  dd($today);
                    //  exit;

                    $licenseperiod = DB::table('mst_fees_validity')
                        ->where('licence_id', $formid->id)
                        ->where('form_type', $appl_type)
                        ->where('status', 1)
                        ->whereDate('validity_start_date', '<=', $today)
                        ->orderBy('validity_start_date', 'desc')
                        ->first();


                    // dd($formid->id);
                    // exit;

                    // dd($licenseperiod->validity);
                    // exit;

                    $monthsToAdd = $licenseperiod->validity ?? 0;

                    // dd($monthsToAdd);
                    // exit;

                    // H:i:s
                    $expiresAt = now()->copy()->addMonths($monthsToAdd)->format('Y-m-d');

                    // dd($licenseperiod->validity);
                    // exit;

                    //   dd([
                    //     'issuedAt'   => $issuedAt,
                    //     'expiresAt'  => $expiresAt,
                    //     'newSerial'  => $newSerial,
                    //     'app_id'     => $request->application_id,
                    //     'processed'  => $request->processed_by,
                    // ]);
                    //             exit;
                    DB::table('tnelb_license')->insert([
                        'application_id' => $request->application_id,
                        'license_number' => $newSerial,
                        'issued_by'      => $request->processed_by,
                        'issued_at'      => $issuedAt,
                        'expires_at'     => $expiresAt,
                    ]);
                }
            }

            // store workflow entry etc (your existing code)
            DB::table('tnelb_workflow_a')->insert([
                'application_id' => $request->application_id,
                'processed_by'   => $request->processed_by,
                'role_id'        => Auth::user()->roles_id,
                'appl_status'    => 'A',
                'remarks'        => $request->remarks ?? 'No remarks provided',
                'forwarded_to'   => $request->forwarded_to ?? null,
                'created_at'     => now(),
            ]);

            DB::commit();

            $message = ($appl_type === "R")
                // $message = str_starts_with($request->application_id, 'R')
                ? "Renewal Application Extended from " . date('d/m/Y', strtotime($issuedAt)) . " to " . date('d/m/Y', strtotime($expiresAt)) . " successfully!"
                : "Fresh Application  Extended from " . date('d/m/Y', strtotime($issuedAt)) . " to " . date('d/m/Y', strtotime($expiresAt)) . " successfully!";



            return response()->json([
                'status'        => 'success',
                'message'       => $message,
                'license_number' => $newSerial,
                'issued_at'     => $issuedAt,
                'expires_at'    => $expiresAt,
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Something went wrong: ' . $e->getMessage()], 500);
        }
    }


    /**
     * Resolve application for secretary/president approval — CC meta first, legacy fallback.
     */
    private function resolveCompetencyApplicationForApproval(string $applicationId): ?object
    {
        $metaService = app(CompetencyMetaService::class);

        foreach ($metaService->allMetaTables() as $metaTable) {
            $cc = DB::table($metaTable)->where('application_id', $applicationId)->first();
            if (! $cc) {
                continue;
            }

            $licenseNumber = trim((string) ($cc->certificate_no ?? ''));
            if ($licenseNumber === '' && strtoupper((string) ($cc->appl_type ?? '')) === 'R') {
                $parentId = trim((string) ($cc->old_application ?? ''));
                if ($parentId !== '') {
                    $parentCert = app(CompetencyCertificateService::class)->asLicenseDetails(
                        $parentId,
                        $cc->form_name ?? null
                    );
                    $licenseNumber = trim((string) ($parentCert->license_number ?? ''));
                }
            }

            return (object) [
                'id' => $cc->app_id,
                'application_id' => $cc->application_id,
                'login_id' => $cc->login_id,
                'form_name' => $cc->form_name,
                'license_name' => $cc->certificate_name,
                'form_id' => $cc->form_id,
                'appl_type' => $cc->appl_type,
                'license_number' => $licenseNumber !== '' ? $licenseNumber : null,
                'old_application' => $cc->old_application ?? null,
                'status' => $cc->app_status,
                'app_status' => $cc->app_status,
                'payment_status' => $cc->payment_status,
                'processed_by' => $cc->processed_by,
                '_approval_source' => $metaTable,
            ];
        }

        return null;
    }

    private function markCompetencyApplicationApproved(object $application, string $processedBy, ?string $qc = null, ?string $qsc = null): void
    {
        $update = [
            'processed_by' => $processedBy,
            'updated_at' => now(),
        ];

        $metaTable = (string) ($application->_approval_source ?? '');
        if (in_array($metaTable, app(CompetencyMetaService::class)->allMetaTables(), true)) {
            $update['app_status'] = 'A';
            if ($qc !== null) {
                $update['qc'] = $qc;
            }
            if ($qsc !== null) {
                $update['qsc'] = $qsc;
            }
            DB::table($metaTable)->where('application_id', $application->application_id)->update($update);

            return;
        }

        // Legacy non-competency rows only (not Form S/W/WH in cc_form_s_meta).
        $update['status'] = 'A';
        if ($qc !== null) {
            $update['qc'] = $qc;
        }
        if ($qsc !== null) {
            $update['qsc'] = $qsc;
        }
        DB::table('tnelb_application_tbl')
            ->where('application_id', $application->application_id)
            ->update($update);
    }

    /** Dual-write: mirror legacy issue into cc_forms_cert / cc_form_*_cert (one table per form). */
    private function syncIssuedCertToCcTable(
        object $application,
        string $licenseNumber,
        mixed $issuedAt,
        mixed $validFrom,
        mixed $expiresAt,
        string $processedBy
    ): void {
        app(CompetencyCertificateService::class)->syncFromLegacyIssue(
            $application->form_name ?? null,
            (string) $application->application_id,
            $licenseNumber,
            $issuedAt,
            $validFrom,
            $expiresAt,
            $processedBy
        );
    }

    public function approveApplication(Request $request)
    {

        $request->validate([
            'application_id' => 'required|string',
            'processed_by'   => 'required|string',
            'forwarded_to'   => 'integer',
            'remarks'        => 'nullable|string',
        ]);

        // dd($request->all());
        // exit;
        // Fetch the application details (CC meta first for S/W/WH)
        $application = $this->resolveCompetencyApplicationForApproval($request->application_id);


        if (!$application) {
            return response()->json(['error' => 'Application not found.'], 404);
        }

        $login_id = $application->login_id;


        $licenceId = (int) ($application->form_id ?? 0);
        if ($licenceId <= 0) {
            return response()->json(['error' => 'Invalid form/licence mapping for this application.'], 422);
        }

        DB::beginTransaction();
        try {
            // Update application status to "Approved"
            $staff = Auth::user()->name;

            $processed = $staff === 'President' ? 'PR' : 'SE';

            // Normalize application type once (N = New, R = Renewal)
            $appl_type = strtoupper(preg_replace('/\s+/', '', (string) $application->appl_type));

            $this->markCompetencyApplicationApproved(
                $application,
                $processed ?: 'PR',
                $request->input('qc'),
                $request->input('qsc')
            );

            // Issue or renew licence and get final number + dates
            [$licenseNumber, $issuedAt, $expiresAt] = $this->issueOrRenewLicense(
                $application,
                $licenceId,
                $appl_type,
                $request->processed_by,
                $request->application_id,
                $login_id
            );

            // dd('success'); exit;

            // Generate Licence PDF, encrypt it, and store its path (non-blocking)
            try {
                app(LicensepdfController::class)->generatePDF($request->application_id);
            } catch (\Throwable $e) {
                Log::warning('Failed to generate/store encrypted licence PDF after approval', [
                    'application_id' => $request->application_id,
                    'error' => $e->getMessage(),
                ]);
            }
            //    dd($this->dbNow); exit;
            $chklistStatus = $request->input('chklist_status', []);
            
            $workflowTable = app(CompetencyApplicationService::class)
                ->resolveWorkflowTable($request->application_id, $application);
            app(CompetencyWorkflowService::class)->record($workflowTable, [
                'application_id' => $request->application_id,
                'processed_by'   => $request->processed_by,
                'role_id'        => Auth::user()->roles_id,
                'appl_status'    => 'A',
                'chklist_status' => $chklistStatus,
                'remarks'        => $request->remarks ?? 'No remarks provided',
                'forwarded_to'   => $request->forwarded_to ?? null,
                'created_at'     => $this->dbNow,
                'login_id'       => Auth::id(),
                'raised_by'      => $processed ?: 'PR',
            ]);

            DB::commit();

            if ($appl_type === 'R') {
                return response()->json([
                    'status'        => 'success',
                    'message'        => 'Application Renewed successfully!',
                    'license_number' => $licenseNumber,
                    'issued_at'      => $issuedAt,
                    'expires_at'     => $expiresAt,
                ], 200);
            } else {

                return response()->json([
                    'status'        => 'success',
                    'message'        => 'Application approved successfully!',
                    'license_number' => $licenseNumber,
                    'issued_at'      => $issuedAt,
                    'expires_at'     => $expiresAt,
                ], 200);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            dd([
                'message' => $e->getMessage(),
                'line'    => $e->getLine(),
                'file'    => $e->getFile(),
            ]);
            return response()->json(['error' => 'Something went wrong: ' . $e->getMessage()], 500);
        }
    }

    public function approveApplication_bk_27042026(Request $request)
    {
        $request->validate([
            'application_id' => 'required|string',
            'processed_by'   => 'required|string',
            'forwarded_to'   => 'integer',
            'remarks'        => 'nullable|string',
        ]);

        // dd($request->all());
        // exit;
        // Fetch the application details (cc_form_s_meta first for S/W/WH)
        $application = $this->resolveCompetencyApplicationForApproval($request->application_id);


        if (!$application) {
            return response()->json(['error' => 'Application not found.'], 404);
        }

        $login_id = $application->login_id;


        $licenceId = (int) ($application->form_id ?? 0);
        if ($licenceId <= 0) {
            return response()->json(['error' => 'Invalid form/licence mapping for this application.'], 422);
        }

        DB::beginTransaction();
        try {
            // Update application status to "Approved"
            $staff = Auth::user()->name;

            $processed = $staff === 'President' ? 'PR' : 'SE';

            // Normalize application type once (N = New, R = Renewal)
            $appl_type = strtoupper(preg_replace('/\s+/', '', (string) $application->appl_type));

            $this->markCompetencyApplicationApproved(
                $application,
                $processed ?: 'PR',
                null,
                null
            );


            // Issue or renew licence and get final number + dates
            [$licenseNumber, $issuedAt, $expiresAt] = $this->issueOrRenewLicense(
                $application,
                $licenceId,
                $appl_type,
                $request->processed_by,
                $request->application_id,
                $login_id
            );

            // Generate Licence PDF, encrypt it, and store its path (non-blocking)
            try {
                app(LicensepdfController::class)->generatePDF($request->application_id);
            } catch (\Throwable $e) {
                Log::warning('Failed to generate/store encrypted licence PDF after approval', [
                    'application_id' => $request->application_id,
                    'error' => $e->getMessage(),
                ]);
            }


            DB::table('tnelb_workflow')->insert([
                'application_id' => $request->application_id,
                'processed_by'   => $request->processed_by,
                'role_id'        => Auth::user()->roles_id, // Current user role (Secretary)
                'appl_status'    => 'A',
                'remarks'        => $request->remarks ?? 'No remarks provided',
                'forwarded_to'   => $request->forwarded_to ?? null, // No forwarding since it's approved
                'created_at'     => $this->dbNow,
            ]);


            DB::commit();

            if ($appl_type === 'R') {
                return response()->json([
                    'status'        => 'success',
                    'message'        => 'Application Renewed successfully!',
                    'license_number' => $licenseNumber,
                    'issued_at'      => $issuedAt,
                    'expires_at'     => $expiresAt,
                ], 200);
            } else {

                return response()->json([
                    'status'        => 'success',
                    'message'        => 'Application approved successfully!',
                    'license_number' => $licenseNumber,
                    'issued_at'      => $issuedAt,
                    'expires_at'     => $expiresAt,
                ], 200);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Something went wrong: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Issue a fresh licence or create/reuse a renewal record and return
     * [licenseNumber, issuedAt, expiresAt].
     */
    private function issueOrRenewLicense(object $application, int $licenceId, string $applType, string $processedBy, string $applicationId, string $loginId): array
    {

        // Renewal flow
        if ($applType === 'R') {
            $licenseDetails = DB::table('tnelb_renewal_license')
                ->where('application_id', $applicationId)
                ->first();



            $previousCertExpiry = DB::table('tnelb_license')
                ->where('license_number', $application->license_number)
                ->value('expires_at');

            $now = db_now();

            // dd($previousCertExpiry, $now);
            // exit;

            // dd($applType); exit;
            if (!$licenseDetails || $now->greaterThan(Carbon::parse($licenseDetails->expires_at))) {


                $issuedAt = Carbon::parse($previousCertExpiry)
                    ->addDays(1)
                    ->format('Y-m-d');


                if ($now > $previousCertExpiry) {
                    $applType = 'N';
                }


                $licensePeriod = $this->resolveLicenseValidity($licenceId, $applType);

                $monthsToAdd   = (int) ($licensePeriod->validity ?? 0);

                // $expiresAtone     = $now->copy()->addMonths($monthsToAdd)->format('Y-m-d');

                $expiresAt = Carbon::parse($previousCertExpiry)->copy()
                    ->addMonths($monthsToAdd)
                    ->subDay()
                    ->format('Y-m-d');




                DB::table('tnelb_renewal_license')->insert([
                    'login_id'       => $loginId,
                    'license_number' => $application->license_number,
                    'application_id' => $applicationId,
                    'issued_by'      => $processedBy,
                    'issued_at'      => $now,
                    'issued_from'    => $issuedAt,
                    'expires_at'     => $expiresAt,
                    'created_at'     => $now,
                ]);

                $licenseNumber = $application->license_number;
                $certIssueDate = $now;
                $certValidFrom = $issuedAt;
            } else {

                // Existing renewal record still valid – reuse its values


                $licenseNumber = $licenseDetails->license_number;
                $issuedAt      = $licenseDetails->issued_at;
                $expiresAt     = $licenseDetails->expires_at;
                $certIssueDate = $licenseDetails->issued_at;
                $certValidFrom = $licenseDetails->issued_from ?? $licenseDetails->issued_at;
            }

            $this->syncIssuedCertToCcTable(
                $application,
                (string) $licenseNumber,
                $certIssueDate,
                $certValidFrom,
                $expiresAt,
                $processedBy
            );

            return [$licenseNumber, $issuedAt, $expiresAt];
        }



        if ($applType === 'D') {
            $licenseDetails = DB::table('cc_forms_cert')
                ->where('application_id', $applicationId)
                ->first();

            if ($licenseDetails) {
                $this->syncIssuedCertToCcTable(
                    $application,
                    (string) $licenseDetails->certificate_no,
                    $licenseDetails->dateof_issue,
                    $licenseDetails->valid_from ?? $licenseDetails->dateof_issue,
                    $licenseDetails->valid_to,
                    $processedBy
                );

                return [
                    $licenseDetails->certificate_no,
                    $licenseDetails->dateof_issue,
                    $licenseDetails->valid_from,
                    $licenseDetails->valid_to
                ];
            }

            // Create a brand new licence entry
            $prefix    = $application->license_name;
            $yearMonth = date('Ym');

            $lastSerial = DB::table('cc_forms_cert')
                ->where('certificate_no', 'LIKE', "C{$prefix}{$yearMonth}%")
                ->orderBy('certificate_no', 'desc')
                ->value('certificate_no');

            if ($lastSerial) {
                $lastNumber   = (int) substr($lastSerial, -5);
                $newNumber    = str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
            } else {
                $newNumber = '00001';
            }

            $now = db_now();

            $licenseNumber = "C{$prefix}{$yearMonth}{$newNumber}";
            $issuedAt      = $now;

            $licensePeriod = $this->resolveLicenseValidity($licenceId, $applType);
            $monthsToAdd   = (int) ($licensePeriod->validity ?? 0);


            $expiresAt = Tnelb_CC_Digitization::where('application_id', $applicationId )->first()->to_date; //first issue
            $from_date = Tnelb_CC_Digitization::where('application_id', $applicationId )->first()->from_date; //latest from_date
            $issuedAt = Tnelb_CC_Digitization::where('application_id', $applicationId )->first()->fissue; //latest to date
            // dd($issuedAt); exit;
            $issuedBy = Auth::user()->roles_id;

            // dd($issuedBy, $issuedAt); exit;

          
            $licence_insert = DB::table('cc_forms_cert')->insert([
                'application_id' => $applicationId,
                'certificate_no' => $licenseNumber,
                'issued_by'      => $issuedBy,
                'dateof_issue'      => $issuedAt, //first issue
                'valid_from'    => $from_date, //latest from_date
                'valid_to'     => $expiresAt, //latest to date
                'created_at'     => $now,
            ]);

            $this->syncIssuedCertToCcTable(
                $application,
                $licenseNumber,
                $issuedAt,
                $from_date,
                $expiresAt,
                $processedBy
            );

            return [$licenseNumber, $issuedAt, $expiresAt];
        }

        // Fresh licence (N) flow
        $licenseDetails = DB::table('tnelb_license')
            ->where('application_id', $applicationId)
            ->first();

        if ($licenseDetails) {
            $this->syncIssuedCertToCcTable(
                $application,
                (string) $licenseDetails->license_number,
                $licenseDetails->issued_at,
                $licenseDetails->issued_from ?? $licenseDetails->issued_at,
                $licenseDetails->expires_at,
                $processedBy
            );

            return [
                $licenseDetails->license_number,
                $licenseDetails->issued_at,
                $licenseDetails->issued_from,
                $licenseDetails->expires_at
            ];
        }

        // Create a brand new licence entry
        $prefix    = $application->license_name;
        $yearMonth = date('Ym');

        $lastSerial = DB::table('tnelb_license')
            ->where('license_number', 'LIKE', "C{$prefix}{$yearMonth}%")
            ->orderBy('license_number', 'desc')
            ->value('license_number');

        if ($lastSerial) {
            $lastNumber   = (int) substr($lastSerial, -5);
            $newNumber    = str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '00001';
        }

        $now = db_now();

        $licenseNumber = "C{$prefix}{$yearMonth}{$newNumber}";
        $issuedAt      = $now;

        $licensePeriod = $this->resolveLicenseValidity($licenceId, $applType);
        $monthsToAdd   = (int) ($licensePeriod->validity ?? 0);


        $expiresAt = Carbon::parse($now)->copy()->addMonths($monthsToAdd)
            ->subDay()
            ->toDateString();

        // dd($licensePeriod->validity); exit;

        $licence_insert = DB::table('tnelb_license')->insert([
            'application_id' => $applicationId,
            'certificate_no' => $licenseNumber,
            'issued_by'      => Auth::user()->roles_id,
            'dateof_issue'      => $issuedAt, //first issue
            'valid_from'    => $now,
            'valid_to'     => $expiresAt,
            'created_at'     => $now,
        ]);

        

        $this->syncIssuedCertToCcTable(
            $application,
            $licenseNumber,
            $issuedAt,
            $now,
            $expiresAt,
            $processedBy
        );

        return [$licenseNumber, $issuedAt, $expiresAt];
    }

    /**
     * Resolve licence validity configuration for a given licence and application type.
     *
     * @throws \RuntimeException when no validity period is configured.
     */
    private function resolveLicenseValidity(int $licenceId, string $applType): object
    {
        $today = Carbon::today()->toDateString();

        $licensePeriod = DB::table('mst_fees_validity')
            ->where('licence_id', $licenceId)
            ->where('form_type', $applType)
            ->where('status', 1)
            ->whereDate('validity_start_date', '<=', $today)
            ->orderBy('validity_start_date', 'desc')
            ->first();

        if (!$licensePeriod) {
            throw new \RuntimeException("Validity period not configured for this licence (licence_id={$licenceId}, form_type={$applType}).");
        }

        return $licensePeriod;
    }
}
