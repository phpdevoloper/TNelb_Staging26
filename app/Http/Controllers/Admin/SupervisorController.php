<?php

namespace App\Http\Controllers\Admin;

use App\Services\FormS\FormSAlterationService;
use Illuminate\Support\Facades\DB;
use App\Models\Admin\SupervisorModel;
use App\Models\Tnelb_CC_Digitization;
use App\Models\CC_Digitisation_Map;
use App\Models\CC_Checklist_applicant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Helpers\RoleHelper;
use App\Models\Admin\WorkflowA;
use App\Models\CC_Forms_cert;
use App\Models\CC_Forms_Meta;
use App\Models\Cl_Checklist_applicant;
use App\Models\EA_Application_model;
use App\Models\TnelbApplicantPhoto;
use App\Models\TnelbApplicantsSign;
use App\Models\TnelbAppsInstitute;
use App\Services\Competency\CompetencyAdminQueryService;
use App\Services\Competency\CompetencyApplicationService;
use App\Services\Competency\CompetencyCertificateService;
use App\Services\Competency\CompetencyDocumentReviewService;
use App\Services\Competency\CompetencyDocumentSupport;
use App\Services\Competency\CompetencyMetaService;
use App\Services\Competency\CompetencyWorkflowService;
use App\Services\Competency\FormPSchema;
use App\Services\FormS\FormSProofDocumentService;
use Illuminate\Support\Facades\Schema;


use Carbon\Carbon;

use function PHPUnit\Framework\isNull;

class SupervisorController extends Controller
{
    protected $today, $dbNow, $digitisationModel;
    public function __construct()
    {
        $this->today = Carbon::today()->toDateString();
        $this->dbNow  = DB::selectOne("SELECT date_trunc('second', NOW()::timestamp) AS db_now")->db_now;
        $this->digitisationModel = new CC_Digitisation_Map();
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

        $this->syncAlterationParentQcQsc($applicationId, $payload);

        return response()->json([
            'status' => true,
            'message' => 'Updated successfully'
        ]);
    }


    public function view_applications(Request $request)
    {


        $staff = Auth::user();

        // dd($staff->roles_id);exit;
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
                        $q->select(DB::raw(1))->from('cc_workflow_forms as tw')->whereRaw('tw.application_id = ta.application_id');
                    })
                    ->select(
                        'ta.*',
                        DB::raw("'Form P' as form_name"),
                        DB::raw('ta.license_name as license_name'),
                        DB::raw("EXISTS (SELECT 1 FROM cc_workflow_forms tw2 WHERE tw2.application_id = ta.application_id AND tw2.appl_status = 'QU') AS has_return_history")
                    );
                if ($applTypeFilter) {
                    $query->where('ta.appl_type', $applTypeFilter);
                }
                $workflows = $query
                    ->orderByDesc('ta.submitted_date')
                    ->orderByDesc('ta.id')
                    ->get();
            } elseif ($roleLevel === 3) {
                $query = DB::table('tnelb_form_p as ta')
                    ->whereIn('ta.payment_status', ['payment', 'paid'])
                    ->whereIn('ta.app_status', ['P', 'PRE'])
                    ->whereNotExists(function ($q) {
                        $q->select(DB::raw(1))->from('cc_workflow_forms as tw')->whereRaw('tw.application_id = ta.application_id');
                    })
                    ->select(
                        'ta.*',
                        DB::raw("'Form P' as form_name"),
                        DB::raw('ta.license_name as license_name'),
                        DB::raw("EXISTS (SELECT 1 FROM cc_workflow_forms tw2 WHERE tw2.application_id = ta.application_id AND tw2.appl_status = 'QU') AS has_return_history")
                    );
                if ($applTypeFilter) {
                    $query->where('ta.appl_type', $applTypeFilter);
                }
                $workflows = $query
                    ->orderByDesc('ta.submitted_date')
                    ->orderByDesc('ta.id')
                    ->get();
            } else {
                $twLast = DB::table('cc_workflow_forms')->select('application_id', DB::raw('MAX(w_id) as max_id'))->groupBy('application_id');
                $currentAppIds = DB::table('cc_workflow_forms as tw')
                    ->joinSub($twLast, 'tw_last', function ($join) {
                        $join->on('tw.application_id', '=', 'tw_last.application_id')->on('tw.w_id', '=', 'tw_last.max_id');
                    })
                    ->where('tw.forwarded_to', $roleId)->whereIn('tw.appl_status', ['F', 'RF'])->select('tw.application_id');
                $workflows = DB::query()->fromSub($currentAppIds, 'cur')
                    ->join('tnelb_form_p as ta', 'ta.application_id', '=', 'cur.application_id')
                    ->whereIn('ta.payment_status', ['payment', 'paid'])
                    ->select(
                        'ta.*',
                        DB::raw("'Form P' as form_name"),
                        DB::raw('ta.license_name as license_name'),
                        DB::raw("EXISTS (SELECT 1 FROM cc_workflow_forms tw2 WHERE tw2.application_id = ta.application_id AND tw2.appl_status = 'QU') AS has_return_history")
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
            // MAX() must stay in a scalar subquery — joinSub(MAX) inside WHERE EXISTS is invalid in PostgreSQL.
            $returnedQuery = DB::table('tnelb_form_p as ta')
                ->whereIn('ta.payment_status', ['payment', 'paid'])
                ->where(function ($q) {
                    $q->where('ta.app_status', 'QU')
                        ->orWhereRaw("(ta.app_status IN ('P','RE') AND EXISTS (SELECT 1 FROM cc_workflow_forms tw WHERE tw.application_id = ta.application_id AND tw.appl_status = 'QU'))")
                        ->orWhereRaw("(ta.app_status IN ('P','RE') AND EXISTS (
                            SELECT 1 FROM cc_workflow_forms tw
                            WHERE tw.application_id = ta.application_id
                              AND tw.query_status = 'P'
                              AND tw.w_id = (
                                  SELECT MAX(twx.w_id) FROM cc_workflow_forms twx
                                  WHERE twx.application_id = ta.application_id
                              )
                        ))");
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
            $twLast = DB::table('cc_workflow_forms')
                ->select('application_id', DB::raw('MAX(id) as max_id'))
                ->groupBy('application_id');

            // Match dashboard: no payment_status filter; (no workflow) OR (latest workflow RE + forwarded_to Supervisor)
            $query = DB::table('cc_form_s_meta as ta')
                ->leftJoinSub($twLast, 'tw_last', function ($join) {
                    $join->on('ta.application_id', '=', 'tw_last.application_id');
                })
                ->leftJoin('cc_workflow_forms as tw', function ($join) {
                    $join->on('tw.application_id', '=', 'tw_last.application_id')
                        ->on('tw.id', '=', 'tw_last.max_id');
                })
                ->leftJoin('mst_licences as ml', 'ta.form_id', '=', 'ml.id')
                ->where('ta.form_id', $selectedFormId)
                ->whereIn('ta.status', ['P', 'RE'])
                ->whereIn('ta.payment_status', ['payment', 'paid'])
                ->whereNotExists(function ($q) {
                    $q->select(DB::raw(1))
                        ->from('cc_workflow_forms_a as twa')
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
                    DB::raw("EXISTS (SELECT 1 FROM cc_workflow_forms tw2 WHERE tw2.application_id = ta.application_id AND tw2.appl_status = 'QU') AS has_return_history")
                )
                ->distinct()
                ->orderByDesc('ta.submitted_date')
                ->orderByDesc('ta.id')
                ->get();

            // If list still empty, use same fallback as dashboard: all P/RE for this form (no workflow filter)
            if ($workflows->isEmpty()) {
                $fallbackQuery = DB::table('cc_form_s_meta as ta')
                    ->leftJoin('mst_licences as ml', 'ta.form_id', '=', 'ml.id')
                    ->where('ta.form_id', $selectedFormId)
                    ->whereIn('ta.status', ['P', 'RE'])
                    ->whereIn('ta.payment_status', ['payment', 'paid'])
                    ->whereNotExists(function ($q) {
                        $q->select(DB::raw(1))
                            ->from('cc_workflow_forms_a as twa')
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
                        DB::raw("EXISTS (SELECT 1 FROM cc_workflow_forms tw2 WHERE tw2.application_id = ta.application_id AND tw2.appl_status = 'QU') AS has_return_history")
                    )
                    ->orderByDesc('ta.submitted_date')
                    ->orderByDesc('ta.id')
                    ->get();
            }

            // Returned tab: QU (waiting for applicant) + resubmitted (P/RE with return history)
            $returned_applications = DB::table('cc_form_s_meta as ta')
                ->leftJoin('mst_licences as ml', 'ta.form_id', '=', 'ml.id')
                ->where('ta.form_id', $selectedFormId)
                ->whereIn('ta.payment_status', ['payment', 'paid'])
                ->where(function ($q) {
                    $q->where('ta.status', 'QU')
                        ->orWhereRaw("(ta.status IN ('P','RE') AND EXISTS (SELECT 1 FROM cc_workflow_forms tw WHERE tw.application_id = ta.application_id AND tw.appl_status = 'QU'))");
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

            $twLast = DB::table('cc_workflow_forms')
                ->select('application_id', DB::raw('MAX(id) as max_id'))
                ->groupBy('application_id');

            $twaLast = DB::table('cc_workflow_forms_a')
                ->select('application_id', DB::raw('MAX(id) as max_id'))
                ->groupBy('application_id');

            $currentFromTw = DB::table('cc_workflow_forms as tw')
                ->joinSub($twLast, 'tw_last', function ($join) {
                    $join->on('tw.application_id', '=', 'tw_last.application_id')
                        ->on('tw.w_id', '=', 'tw_last.max_id');
                })
                ->where('tw.forwarded_to', $roleId)
                ->whereIn('tw.appl_status', ['F', 'RF'])
                ->select('tw.application_id');

            $currentFromTwa = DB::table('cc_workflow_forms_a as tw')
                ->joinSub($twaLast, 'tw_last', function ($join) {
                    $join->on('tw.application_id', '=', 'tw_last.application_id')
                        ->on('tw.w_id', '=', 'tw_last.max_id');
                })
                ->where('tw.forwarded_to', $roleId)
                ->whereIn('tw.appl_status', ['F', 'RF'])
                ->select('tw.application_id');

            $fallbackAppIds = DB::table('cc_form_s_meta as ta')
                ->where('ta.form_id', $selectedFormId)
                ->whereIn('ta.status', ['F', 'RF'])
                ->whereIn('ta.payment_status', ['payment', 'paid'])
                ->when(!empty($previousProcessedBy), function ($q) use ($previousProcessedBy) {
                    return $q->whereIn('ta.processed_by', $previousProcessedBy);
                })
                ->whereNotExists(function ($q) {
                    $q->select(DB::raw(1))
                        ->from('cc_workflow_forms as tw')
                        ->whereRaw('tw.application_id = ta.application_id');
                })
                ->whereNotExists(function ($q) {
                    $q->select(DB::raw(1))
                        ->from('cc_workflow_forms_a as twa')
                        ->whereRaw('twa.application_id = ta.application_id');
                })
                ->select('ta.application_id');

            $currentAppIds = $currentFromTw->union($currentFromTwa)->union($fallbackAppIds);

            $query = DB::query()
                ->fromSub($currentAppIds, 'cur')
                ->join('cc_form_s_meta as ta', 'ta.application_id', '=', 'cur.application_id')
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
                    DB::raw("EXISTS (SELECT 1 FROM cc_workflow_forms tw2 WHERE tw2.application_id = ta.application_id AND tw2.appl_status = 'QU') AS has_return_history")
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

    public function view_applications_tbl(Request $request)
    {


        $staff = Auth::user();

        // dd($staff->roles_id);exit;
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
            // MAX() must stay in a scalar subquery — joinSub(MAX) inside WHERE EXISTS is invalid in PostgreSQL.
            $returnedQuery = DB::table('tnelb_form_p as ta')
                ->whereIn('ta.payment_status', ['payment', 'paid'])
                ->where(function ($q) {
                    $q->where('ta.app_status', 'QU')
                        ->orWhereRaw("(ta.app_status IN ('P','RE') AND EXISTS (SELECT 1 FROM tnelb_workflow tw WHERE tw.application_id = ta.application_id AND tw.appl_status = 'QU'))")
                        ->orWhereRaw("(ta.app_status IN ('P','RE') AND EXISTS (
                            SELECT 1 FROM tnelb_workflow tw
                            WHERE tw.application_id = ta.application_id
                              AND tw.query_status = 'P'
                              AND tw.id = (
                                  SELECT MAX(twx.id) FROM tnelb_workflow twx
                                  WHERE twx.application_id = ta.application_id
                              )
                        ))");
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
            'EA' => 'ccl_forma_meta',
            'SA' => 'tnelb_esa_applications',
            'B'  => 'tnelb_eb_applications',
            'SB' => 'tnelb_esb_applications',
        ];

        if (isset($contractorTablesByCode[$formCode]) && \Illuminate\Support\Facades\Schema::hasTable($contractorTablesByCode[$formCode])) {
            $tbl = $contractorTablesByCode[$formCode];
            $query = DB::table($tbl . ' as ta')
                ->leftJoin('cl_forma_lic as tl', 'tl.application_id', '=', 'ta.application_id')
                ->leftJoin('cl_forma_lic as tr', 'tr.application_id', '=', 'ta.application_id')
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
            ->leftJoin('cl_forma_lic as tl', 'tl.application_id', '=', 'ta.application_id')
            ->leftJoin('cl_forma_lic as tr', 'tr.application_id', '=', 'ta.application_id')
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

    public function applicationDetailsModal(Request $request)
    {
        $applicationId = trim((string) $request->query('application_id', ''));
        if ($applicationId === '') {
            return response('<p class="text-danger mb-0">Application id is required.</p>', 400);
        }

        $applicant = app(CompetencyApplicationService::class)->findApplicantWithPayment($applicationId);
        if (! $applicant) {
            return response('<p class="text-danger mb-0">Application details were not found.</p>', 404);
        }

        $formName = strtoupper(trim((string) ($applicant->form_name ?? '')));
        if (str_starts_with($formName, 'FORM ')) {
            $formName = trim(substr($formName, 5));
        }
        $isFormP = $formName === 'P';

        $educationalQualifications = collect();
        $workExperience = collect();
        $uploadedPhoto = null;
        $uploadedSign = null;
        $alterationProofs = collect();
        $parentApplicantForAlter = null;
        $instituteDetails = collect();

        if (CompetencyDocumentSupport::usesVersionedStorage($formName)) {
            $workflowApp = CC_Forms_Meta::findByApplicationId($applicationId, $formName)
                ?: CC_Forms_Meta::findByApplicationId($applicationId);
            if ($workflowApp) {
                $reviewContext = app(CompetencyDocumentReviewService::class)->buildStaffReviewContext($workflowApp);
                $educationalQualifications = $reviewContext['educationalQualifications'] ?? collect();
                $workExperience = $reviewContext['workExperience'] ?? collect();
                $uploadedPhoto = $reviewContext['uploadedPhoto'] ?? null;
                $uploadedSign = $reviewContext['uploadedSign'] ?? null;
                $alterationProofs = $reviewContext['alterationProofs'] ?? collect();
                $parentApp = $reviewContext['parentApplication'] ?? null;
                if (($applicant->appl_type ?? '') === 'A') {
                    $parentApplicantForAlter = $parentApp;
                    if ($parentApplicantForAlter) {
                        $parentApplicantForAlter->applicants_address = $parentApplicantForAlter->applicants_address
                            ?? $parentApplicantForAlter->applicant_address
                            ?? null;
                        $parentApplicantForAlter->applicant_name = $parentApplicantForAlter->applicant_name
                            ?? $parentApplicantForAlter->applicants_name
                            ?? null;
                    }
                }
            }
        }

        if ($isFormP) {
            $formPRelated = $this->loadFormPModalRelated($applicationId, $applicant);
            if ($educationalQualifications->isEmpty()) {
                $educationalQualifications = $formPRelated['edu'];
            }
            if ($workExperience->isEmpty()) {
                $workExperience = $formPRelated['exp'];
            }
            $uploadedPhoto = $uploadedPhoto ?: $formPRelated['photo'];
            $uploadedSign = $uploadedSign ?: $formPRelated['sign'];
            $instituteDetails = $formPRelated['institutes'];
            $applicant = $formPRelated['applicant'];
        }

        return view('admin.supervisor.partials.application_details_modal_body', [
            'applicant' => $applicant,
            'educationalQualifications' => $educationalQualifications,
            'workExperience' => $workExperience,
            'uploadedPhoto' => $uploadedPhoto,
            'uploadedSign' => $uploadedSign,
            'alterationProofs' => $alterationProofs,
            'parentApplicantForAlter' => $parentApplicantForAlter,
            'instituteDetails' => $instituteDetails,
            'isFormP' => $isFormP,
        ]);
    }

    private function loadFormPModalRelated(string $applicantId, object $applicant): array
    {
        $fromCc = Schema::hasTable(FormPSchema::META_TABLE)
            && DB::table(FormPSchema::META_TABLE)->where('application_id', $applicantId)->exists();

        $edu = collect();
        $exp = collect();
        $photo = null;
        $sign = null;

        if ($fromCc) {
            $edu = Schema::hasTable('cc_edu')
                ? DB::table('cc_edu')->where('application_id', $applicantId)->get()
                : collect();
            $exp = Schema::hasTable('cc_exp')
                ? DB::table('cc_exp')->where('application_id', $applicantId)->get()
                : collect();
            $proofService = app(FormSProofDocumentService::class);
            $photo = $proofService->loadPhotoForView($applicantId);
            $sign = $proofService->loadSignForView($applicantId);
            $documents = Schema::hasTable('cc_proof_doc')
                ? DB::table('cc_proof_doc')->where('application_id', $applicantId)->get()
                : collect();
            foreach ($documents as $proof) {
                $proofType = strtolower((string) ($proof->proof_type ?? ''));
                $proofName = strtoupper((string) ($proof->proof_name ?? ''));
                if ($proofType === 'aadhaar' || $proofName === FormSProofDocumentService::PROOF_AADHAAR) {
                    if (! empty($proof->proof_no) && empty($applicant->aadhaar)) {
                        $applicant->aadhaar = $proof->proof_no;
                    }
                    if (! empty($proof->proof_doc)) {
                        $applicant->aadhaar_doc = $proof->proof_doc;
                    }
                } elseif ($proofType === 'pan' || $proofName === FormSProofDocumentService::PROOF_PAN) {
                    if (! empty($proof->proof_no) && empty($applicant->pancard)) {
                        $applicant->pancard = $proof->proof_no;
                    }
                    if (! empty($proof->proof_doc)) {
                        $applicant->pan_doc = $proof->proof_doc;
                        $applicant->pancard_doc = $proof->proof_doc;
                    }
                }
            }
        } else {
            $edu = Schema::hasTable('tnelb_applicants_edu')
                ? DB::table('tnelb_applicants_edu')->where('application_id', $applicantId)->get()
                : collect();
            $exp = Schema::hasTable('tnelb_applicants_exp')
                ? DB::table('tnelb_applicants_exp')->where('application_id', $applicantId)->get()
                : collect();
            $photo = class_exists(TnelbApplicantPhoto::class)
                ? TnelbApplicantPhoto::where('application_id', $applicantId)->first()
                : null;
            $sign = class_exists(TnelbApplicantsSign::class)
                ? TnelbApplicantsSign::where('application_id', $applicantId)->first()
                : null;
        }

        $edu = $edu->map(function ($row) {
            if (is_object($row) && empty($row->id) && ! empty($row->edu_id)) {
                $row->id = $row->edu_id;
            }

            return $row;
        });
        $exp = $exp->map(function ($row) {
            if (! is_object($row)) {
                return $row;
            }
            if (empty($row->id) && ! empty($row->exp_id)) {
                $row->id = $row->exp_id;
            }
            if (empty($row->upload_document) && ! empty($row->support_document)) {
                $row->upload_document = $row->support_document;
            }
            if (empty($row->company_name) && ! empty($row->org_name)) {
                $row->company_name = $row->org_name;
            }

            return $row;
        });

        $institutes = (class_exists(TnelbAppsInstitute::class) && Schema::hasTable('tnelb_applicant_institute'))
            ? TnelbAppsInstitute::where('application_id', $applicantId)
                ->where(function ($q) {
                    $q->where('institute_status', 1)->orWhereNull('institute_status');
                })
                ->get()
            : collect();

        return [
            'applicant' => $applicant,
            'edu' => $edu,
            'exp' => $exp,
            'photo' => $photo,
            'sign' => $sign,
            'institutes' => $institutes,
        ];
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
    public function view_forma($type, $form_type)
    {
        $staff = Auth::user();

        $roleName = $staff->name ?? '';
        $role_id = $staff->role_id;

        $type = strtoupper(trim($type));
        $form_type = strtoupper(trim($form_type));

        $query = DB::table('ccl_forma_meta as ta')
            ->where('ta.form_name', $type)
            ->where('ta.payment_status', 'paid');

        /*
    |--------------------------------------------------------------------------
    | Filter application type
    |--------------------------------------------------------------------------
    */
        if (in_array($form_type, ['N', 'R', 'D', 'A'], true)) {
            $query->whereRaw(
                "TRIM(UPPER(ta.appl_type)) = ?",
                [$form_type]
            );
        }

        /*
    |--------------------------------------------------------------------------
    | Supervisor
    |--------------------------------------------------------------------------
    */
        if (in_array($roleName, ['Supervisor', 'Supervisor2'], true)) {

            $query->whereIn('ta.application_status', ['P', 'RE'])
                ->where(function ($q) {
                    $q->whereIn('ta.processed_by', ['A', 'SE', 'S'])
                        ->orWhereNull('ta.processed_by');
                });
        }

        /*
    |--------------------------------------------------------------------------
    | Assistant Secretary
    |--------------------------------------------------------------------------
    */ elseif ($roleName === 'Assistant Secretary') {

            $query->whereIn('ta.application_status', ['F', 'RF'])
                ->where('ta.processed_by', 'S');
        }

        /*
    |--------------------------------------------------------------------------
    | Secretary
    |--------------------------------------------------------------------------
    */ elseif ($roleName === 'Secretary') {

            $query->whereIn('ta.application_status', ['F', 'RF', 'RE','PRE'])
                ->whereIn('ta.processed_by', ['A', 'PR']);
        }

        /*
    |--------------------------------------------------------------------------
    | President
    |--------------------------------------------------------------------------
    */ elseif ($roleName === 'President') {

            $query->whereIn('ta.application_status', ['F', 'RF'])
                ->where('ta.processed_by', 'SE');
        }

        $workflows = $query
            ->orderBy('ta.updated_at', 'DESC')
            ->select('ta.*')
            ->get();

        return view(
            'admin.supervisor.view_forma',
            compact('workflows', 'role_id', 'type', 'form_type')
        );
    }


    public function completed_forma()
    {
        $userRole = Auth::user()->roles_id;

        // $assignedForms = DB::table('ccl_forma_meta as ta')
        // ->whereIn('ta.application_status', ['F', 'RF','A']) // Filter by status
        // ->select('ta.*') // Select all columns from applicant_formA
        // ->get();


        // var_dump($assignedForms);die;

        $workflows = DB::table('ccl_forma_meta')
            ->whereIn('application_status', ['F', 'RF', 'A'])
            ->orderby('updated_at', 'DESC')
            ->select('*')
            ->get();

        $applicationIds = $workflows->pluck('application_id');


        $licenses = DB::table('cl_forma_lic')
            ->whereIn('application_id', $applicationIds)
            ->select('application_id', 'license_number')
            ->get()
            ->keyBy('application_id');

        $renewalLicenses = DB::table('cl_forma_lic')
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
            'remarks'        => 'required|string'
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

        $checklistData = [];

        foreach ($request->check_id as $id => $checkId) {

            $checklistData[] = [
                'id'      => $checkId,
                'checked' => (int) ($request->checklists[$id] ?? 0),
                'verify'  => (int) ($request->status[$id] ?? 0),
            ];
        }
        // dd($applicant->certificate_name); exit;
        // dd(([
        //     'login_id'        => Auth::id(),
        //     'applicant_id'    => $request->application_id,
        //     'cert_license_id' => $applicant->id,
        //     // 'check_id'       => $request->check_id[array_key_first($request->check_id)],
        //     'checklist_json'  => json_encode($checklistData),
        //     'updated_by'      => Auth::id(),
        // ]));
        // exit;


        $Existingcheck = CC_Checklist_applicant::where('applicant_id', $request->application_id)
            ->where('certificate_name', $applicant->certificate_name)
            ->first();

        if ($Existingcheck) {
            $Existingcheck->update([

                // 'certificate_name'       => $request->certificate_name,
                'checklist_json'  => json_encode($checklistData),
                'updated_by'      => Auth::id(),
            ]);
        } else {

            CC_Checklist_applicant::create([
                'login_id'        => Auth::id(),
                'applicant_id'    => $request->application_id,
                'cert_license_id' => $applicant->id,
                'certificate_name'       => $applicant->certificate_name,
                'checklist_json'  => json_encode($checklistData),
                'updated_by'      => Auth::id(),
            ]);
        }



        $formType = (object) ['form_id' => $applicant->form_id ?? null];

        $status = $appService->resolveForwardStatus($staff->name, $applicant, $formType);

        // $chklistStatus = $request->input('chklist_status', []);
        $workflowTable = $appService->resolveWorkflowTable($request->application_id, $applicant);
        $workflowPayload = [
            'application_id' => $request->application_id,
            'appl_status'    => $isReturnedApplication ? 'RF' : 'F',
            'processed_by'   => $processed_by,
            'forwarded_to'   => $request->forwarded_to,
            'role_id'        => $request->role_id,
            'is_verified'    => $request->checkboxes ?? 'Yes',
            'query_status'   => $query_status,
            // 'chklist_status' => $chklistStatus,
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
            $qcPayload = [
                'qc' => $request->qc,
                'qsc' => $request->qsc,
            ];
            DB::table($appService->resolveMetaTable($request->application_id, $applicant))
                ->where('application_id', $request->application_id)
                ->update($qcPayload);
            $this->syncAlterationParentQcQsc($request->application_id, $qcPayload);
        }



        if ($role == 'assistantsecretary') {


            $role = 'Assistant Secretary';
        }

        // dd($role); exit;

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
            'remarks'        => 'nullable|string',
            'staff_verification'  => 'nullable|string',
        ]);



        $staffVerification = json_decode(
            $request->staff_verification,
            true
        );

        if (!is_array($staffVerification)) {
            $staffVerification = [];
        }


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


        // $formType = DB::table('ccl_forma_meta')
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

        foreach ($staffVerification as $staffVerify) {

            $staffId = $staffVerify['staff_id'] ?? null;

            if (!$staffId) {
                continue;
            }

            $verifyFlag = (int) (
                $staffVerify['verify_flag'] ?? 0
            );


            $staffDetail = DB::table('cl_staff_tbl')
                ->where('id', $staffId)
                ->first();


            if (!$staffDetail) {
                continue;
            }


            // OTHERS does not have CC verification
            if (
                isset($staffDetail->staff_category) &&
                $staffDetail->staff_category === 'OTHERS'
            ) {
                continue;
            }


            DB::table('cl_staff_detail_adminstore')
                ->updateOrInsert(

                    [
                        'application_id' =>
                        $request->application_id,

                        'staff_cc_no' =>
                        $staffDetail->staff_cc_no,
                    ],

                    [
                        'staff_cc_first_issue' =>
                        $staffDetail->staff_cc_first_issue,

                        'staff_cc_validity_from' =>
                        $staffDetail->staff_cc_validity_from,

                        'staff_cc_validity_to' =>
                        $staffDetail->staff_cc_validity_to,

                        'verify_flag' =>
                        $verifyFlag,

                        'processed_by' =>
                        $staffID,

                        'updated_at' =>
                        now(),

                        'created_at' =>
                        now(),
                    ]
                );
        }
        $checklistData = [];

        $checkIds = $request->input('check_id', []);
        $checklists = $request->input('checklists', []);
        $statuses = $request->input('status', []);

        foreach ($checkIds as $id => $checkId) {

            $checklistData[] = [
                'id'      => $checkId,
                'checked' => (int) ($checklists[$id] ?? 0),
                'verify'  => (int) ($statuses[$id] ?? 0),
            ];
        }
        // dd($applicant->certificate_name); exit;
        // dd(([
        //     'login_id'        => Auth::id(),
        //     'applicant_id'    => $request->application_id,
        //     'cert_license_id' => $applicant->id,
        //     // 'check_id'       => $request->check_id[array_key_first($request->check_id)],
        //     'checklist_json'  => json_encode($checklistData),
        //     'updated_by'      => Auth::id(),
        // ]));
        // exit;


        $Existingcheck = Cl_Checklist_applicant::where('applicant_id', $request->application_id)
            ->where('licence_name', $applicant->certificate_name)
            ->first();

        if ($Existingcheck) {
            $Existingcheck->update([

                // 'certificate_name'       => $request->certificate_name,
                'checklist_json'  => json_encode($checklistData),
                'updated_by'      => Auth::id(),
            ]);
        } else {

            // dd( json_encode($checklistData)); exit;

            Cl_Checklist_applicant::create([
                'login_id'        => Auth::id(),
                'applicant_id'    => $request->application_id,
                'cert_license_id' => $applicant->id,
                'licence_name'       => $applicant->license_name,
                'checklist_json'  => json_encode($checklistData),
                'updated_by'      => Auth::id(),
            ]);
        }

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
        // dd($request->all()); exit;

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

        $application = DB::table('ccl_forma_meta')
            ->where('application_id', $request->application_id)
            ->first();

            $old_license_number = $application->license_number;

            // dd($old_license_number); exit;

        if (!$application) {
            return response()->json([
                'error' => 'Application not found'
            ], 404);
        }

        DB::beginTransaction();

        try {

            /* -------------------- BASIC UPDATE -------------------- */

            $processed = Auth::user()->name === 'President' ? 'PR' : 'SE';



           $dataupdate = DB::table('ccl_forma_meta')
                ->where('application_id', $request->application_id)
                ->update([
                    'application_status' => 'A',
                    'processed_by'       => $processed,
                    'updated_at'         => now(),
                ]);



                // dd($dataupdate); exit;

            $appl_type = trim($application->appl_type); // R or N

            $issuedAt = now()->format('Y-m-d H:i:s');
            // $issuedAt  = $application->dt_submit;

            $expiresAt = null;
            $newSerial = null;


            /* -------------------- GET LICENCE VALIDITY MONTHS -------------------- */

            $form = DB::table('mst_licences')
                ->where('cert_licence_code', $request->licensename)
                ->where('status', 1)
                ->first();

            if (!$form) {
                throw new \Exception(
                    'Licence master record not found.'
                );
            }

            $validity = DB::table('mst_fees_validity')
                ->where('licence_id', $form->id)
                ->where('form_type', $appl_type)
                ->where('status', 1)
                ->whereDate('validity_start_date', '<=', now())
                ->orderBy('validity_start_date', 'desc')
                ->first();

            if (!$validity) {
                throw new \Exception(
                    'Licence validity configuration not found.'
                );
            }

            $monthsToAdd = $validity->validity;


            /* -------------------- NORMAL EXPIRY CALCULATION -------------------- */

            if ($appl_type === 'R') {

                // Renewal → old expiry + months

                $oldExpiry = DB::table('cl_forma_lic')
                    ->where('application_id', $request->oldapplicationId)
                    ->value('valid_to');

                $baseExpiry = $oldExpiry
                    ? Carbon::parse($oldExpiry)
                    : now();

                $expiresAt = $baseExpiry
                    ->copy()
                    ->addMonths($monthsToAdd)
                    ->toDateString();
            } else {

                // Fresh → today + months

                $expiresAt = now()
                    ->addMonths($monthsToAdd)
                    ->toDateString();

                    // dd($expiresAt); exit;
            }


            /* -------------------- OVERRIDE (POPUP CONFIRMED) -------------------- */

            if ($request->validity_override === 'YES') {

                $qcValidity = $request->qc_validity_date
                    ? Carbon::parse($request->qc_validity_date)
                    : null;

                $bankValidity = $request->bank_validity
                    ? Carbon::parse($request->bank_validity)
                    : null;

                $expiresAt = collect([
                    Carbon::parse($expiresAt),
                    $qcValidity,
                    $bankValidity,
                ])
                    ->filter()
                    ->min()
                    ->toDateString();
            }


            /* -------------------- LICENSE INSERT / UPDATE -------------------- */

            if ($appl_type === 'R') {

            // $issuedAt = $old_license_number->dateof_issue;

            $license_validitydetails = DB::table('cl_forma_lic')
            ->where('license_number', $old_license_number)
            ->orderByDesc('id')
            ->first();

            // dd($license_validitydetails); exit;

            $issuedAt = $license_validitydetails->dateof_issue;

             $validityfrom = now()->format('Y-m-d');

            //  dd($issuedAt, $validityfrom, $expiresAt); exit;


                DB::table('cl_forma_lic')->insert([

                    // 'login_id'       => $application->login_id,
                    'application_id' => $request->application_id,

                      'license_number' => $application->license_number,

                    'issued_by'      => $request->roleid,

                    'dateof_issue'      => $issuedAt,

                    'valid_from'      => $validityfrom,

                    'valid_to'     => $expiresAt,
                    'cert_status' => 'A',
                    'cert_pdf' => '',
                    'created_at' => now(),
                    'updated_at' => now()

                ]);

                // $newSerial = $application->license_number;
            } else {

                $prefix    = $application->license_name;

                $yearMonth = now()->format('Ym');


                $lastSerial = DB::table('cl_forma_lic')
                    ->where(
                        'license_number',
                        'LIKE',
                        "L{$prefix}{$yearMonth}%"
                    )
                    ->orderByDesc('license_number')
                    ->value('license_number');


                $next = $lastSerial
                    ? str_pad(
                        (int) substr($lastSerial, -5) + 1,
                        5,
                        '0',
                        STR_PAD_LEFT
                    )
                    : '00001';


                $newSerial = "L{$prefix}{$yearMonth}{$next}";




                DB::table('cl_forma_lic')->insert([

                    'application_id' => $request->application_id,

                    'license_number' => $newSerial,

                    'issued_by'      => $request->roleid,

                    'dateof_issue'      => $issuedAt,

                    'valid_from'      => $issuedAt,

                    'valid_to'     => $expiresAt,
                    'cert_status' => 'A',
                    'cert_pdf' => '',
                    'created_at' => now(),
                    'updated_at' => now()


                ]);
            }


            /* =========================================================
           CL DIGITISATION MAPPING
           ========================================================= */
            $digitisationMapping = null;

            /*
           * Find mapping_digi_cls record using the current
           * application_id.
           */
            if (trim($application->appl_type) === 'D') {

                $digitisationCL = DB::table('mapping_digi_cls')
                    ->where(
                        'application_id',
                        $request->application_id
                    )
                    ->first();


                if ($digitisationCL && !empty($digitisationCL->clnumber)) {

                    $oldCLNumber = $digitisationCL->clnumber;


                    /*
             * Store:
             *
             * old_cl_no = old CL number
             * new_cl_no = generated licence number
             */

                    DB::table('cc_digitisation_map')->insert([

                        'old_cl_no' => $oldCLNumber,

                        'new_cl_no'  => $newSerial,

                        'created_at' => now(),

                        'updated_at' => now(),

                    ]);


                    /*
             * Data sent back to AJAX
             */

                    $digitisationMapping = [

                        'old_cl_no' => $oldCLNumber,

                        'new_cl_no'  => $newSerial,

                    ];
                }
            }


            /* -------------------- WORKFLOW -------------------- */

            $workflowId = DB::table('tnelb_workflow_a')->insertGetId([

                'application_id' => $request->application_id,

                'processed_by'   => $request->processed_by,

                'role_id'        => Auth::user()->roles_id,

                'appl_status'    => 'A',

                'remarks'        => $request->remarks
                    ?? 'No remarks provided',

                'forwarded_to'   => $request->forwarded_to,

                'created_at'     => now(),

                'updated_at'     => now(),

            ]);


            // UPDATE SAME RECORD

            DB::table('tnelb_workflow_a')
                ->where('id', $workflowId)
                ->update([

                    'created_at' => DB::raw('NOW()'),

                    'updated_at' => DB::raw('NOW()'),

                ]);


            DB::commit();

//   $datacheck = DB::table('ccl_forma_meta')
//                 ->where('application_id', $request->application_id)
//                 ->first();

//                 dd($datacheck); exit;
            /* -------------------- SUCCESS RESPONSE -------------------- */

            return response()->json([

                'status' => 'success',

                'message' => $appl_type === 'R'

                    ? "Renewal expires on "
                    . date(
                        'd/m/Y',
                        strtotime($expiresAt)
                    )

                    : "License expires on "
                    . date(
                        'd/m/Y',
                        strtotime($expiresAt)
                    ),

                'license_number' => $newSerial,

                'issued_at' => $issuedAt,

                'expires_at' => $expiresAt,


                'digitisation' => $digitisationMapping,

            ], 200);
        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([

                'error' => 'Approval failed',

                'msg'   => $e->getMessage(),

            ], 500);
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

    private function syncAlterationParentQcQsc(string $applicationId, array $payload): void
    {
        $row = CC_Forms_Meta::findByApplicationId($applicationId);
        if (! $row) {
            $metaTable = app(CompetencyMetaService::class)->metaTableForApplicationId($applicationId);
            $row = $metaTable
                ? DB::table($metaTable)->where('application_id', $applicationId)->first()
                : null;
        }
        if (! $row || strtoupper((string) ($row->appl_type ?? '')) !== 'A') {
            return;
        }

        $parentId = trim((string) ($row->old_application ?? ''));
        if ($parentId === '') {
            return;
        }

        $parentTable = app(CompetencyMetaService::class)->metaTableForApplicationId($parentId);
        if (! $parentTable) {
            return;
        }

        $parentPayload = array_intersect_key($payload, array_flip(['qc', 'qsc', 'updated_at']));
        if ($parentPayload === []) {
            return;
        }

        DB::table($parentTable)->where('application_id', $parentId)->update($parentPayload);
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
                $update['qc'] = $qc ?? 0;
            }
            if ($qsc !== null) {
                $update['qsc'] = $qsc ?? 0;
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
        DB::table($metaTable)
            ->where('application_id', $application->application_id)
            ->update($update);
    }

    private function competencyCertificateService(): CompetencyCertificateService
    {
        return app(CompetencyCertificateService::class);
    }

    /** Persist issued certificate into the per-form cc_*_cert table (N/R/D share one table). */
    private function persistCompetencyCertificate(
        object $application,
        string $applicationId,
        string $certificateNo,
        mixed $issuedAt,
        mixed $validFrom,
        mixed $validTo
    ): void {

        $this->competencyCertificateService()->issueOrUpdate(
            (string) ($application->form_name ?? ''),
            [
                'application_id' => $applicationId,
                'certificate_no' => $certificateNo,
                'dateof_issue' => $issuedAt,
                'valid_from' => $validFrom ?? $issuedAt,
                'valid_to' => $validTo,
                'cert_status' => 'A',
            ]
        );
    }

    private function nextCompetencyCertificateNumber(object $application): string
    {
        $prefix = $application->license_name;
        $yearMonth = date('Ym');
        $certTable = $this->competencyCertificateService()->certTableForForm($application->form_name ?? null)
            ?? 'cc_forms_cert';

        $lastSerial = DB::table($certTable)
            ->where('certificate_no', 'LIKE', "C{$prefix}{$yearMonth}%")
            ->orderByDesc('certificate_no')
            ->value('certificate_no');

        if ($lastSerial) {
            $newNumber = str_pad((int) substr($lastSerial, -5) + 1, 5, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '00001';
        }

        return "C{$prefix}{$yearMonth}{$newNumber}";
    }

    private function resolvePreviousCertExpiryForRenewal(object $application, string $applicationId): ?string
    {
        $certService = $this->competencyCertificateService();
        $parentId = trim((string) ($application->old_application ?? ''));

        if ($parentId !== '') {
            $parentCert = $certService->asLicenseDetails($parentId, $application->form_name ?? null);

            return $parentCert->expires_at ?? null;
        }

        $certificateNo = trim((string) ($application->license_number ?? ''));
        if ($certificateNo === '') {
            return null;
        }

        $certTable = $certService->certTableForForm($application->form_name ?? null);
        if (!$certTable) {
            return null;
        }

        return DB::table($certTable)
            ->where('certificate_no', $certificateNo)
            ->where('application_id', '!=', $applicationId)
            ->orderByDesc('valid_to')
            ->value('valid_to');
    }

    public function approveApplication(Request $request)
    {

        // dd($request->all()); exit;

        $request->validate([
            'application_id' => 'required|string',
            'processed_by' => 'required|string',
            'forwarded_to' => 'integer',
            'remarks' => 'required|string',
        ]);

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



            // Regenerate licence PDF on the issued certificate application (parent for alterations).
            // A failure here rolls the approval back. Nothing after this point is saved.
            $pdfApplicationId = $appl_type === 'A'
                ? trim((string) ($application->old_application ?? $request->application_id))
                : $request->application_id;

            try {
                $pdfResponse = app(LicensepdfController::class)->generatePDF($pdfApplicationId);
                $pdfFailed = ! $pdfResponse instanceof \Symfony\Component\HttpFoundation\Response
                    || $pdfResponse->isRedirection()
                    || $pdfResponse->getStatusCode() >= 400;
                if ($pdfFailed) {
                    throw new \RuntimeException('Licence PDF could not be generated.');
                }

                $storedPath = 'private_documents/license_pdfs/' . $pdfApplicationId . '.pdf';
                if (! \Illuminate\Support\Facades\Storage::disk('local')->exists($storedPath)) {
                    throw new \RuntimeException('Licence PDF file was not stored.');
                }
            } catch (\Throwable $e) {
                DB::rollBack();
                Log::warning('Failed to generate/store encrypted licence PDF after approval', [
                    'application_id' => $pdfApplicationId,
                    'alteration_application_id' => $appl_type === 'A' ? $request->application_id : null,
                    'error' => $e->getMessage(),
                ]);

                $detail = trim($e->getMessage());
                $prefix = 'Licence PDF could not be generated.';
                if (str_starts_with($detail, $prefix)) {
                    $detail = trim(substr($detail, strlen($prefix)));
                }

                return response()->json([
                    'status' => 'error',
                    'error' => trim($prefix . ' Approval was not saved. ' . $detail),
                ], 422);
            }


            /* -------------------- DIGITIZATION -------------------- */
            $message = '';
            if ($appl_type == 'D') {
                $digitization = $this->digitisationModel->UpdateNewCCNo($request->application_id, $licenseNumber);
                if ($digitization) {
                    $message = 'New CC No updated successfully!';
                } else {
                    $message = 'Failed to update new CC No!';
                }
            }



            $chklistStatus = $request->input('chklist_status', []);

            $checklistData = [];

            foreach ($request->check_id as $id => $checkId) {

                $checklistData[] = [
                    'id' => $checkId,
                    'checked' => (int) ($request->checklists[$id] ?? 0),
                    'verify' => (int) ($request->status[$id] ?? 0),
                ];
            }


            if ($appl_type == 'A') {
                $formName = (string) ($application->form_name ?? '');
                $certTable = $this->competencyCertificateService()->certTableForForm($formName) ?: 'cc_forms_cert';
                $metaService = app(CompetencyMetaService::class);
                $metaTable = $metaService->tableForForm($formName)
                    ?: $metaService->metaTableForApplicationId($request->application_id);

                $alter_insert = DB::table($certTable)->where('application_id', $request->application_id)->first();

                if (! $alter_insert && $metaTable) {
                    $metadata = DB::table($metaTable)->where('application_id', $request->application_id)->first();
                    $parentId = trim((string) ($metadata->old_application ?? $application->old_application ?? ''));
                    $licensedetails = $parentId !== ''
                        ? DB::table($certTable)
                        ->where('application_id', $parentId)
                        ->orderByDesc('cc_id')
                        ->first()
                        : null;

                    if ($licensedetails) {
                        DB::table($certTable)->insert([
                            'application_id' => $request->application_id,
                            'certificate_no' => $licenseNumber,
                            'dateof_issue' => $licensedetails->dateof_issue,
                            'valid_from' => $licensedetails->valid_from,
                            'valid_to' => $licensedetails->valid_to,
                            'cert_status' => 'A',
                            'created_at' => $this->dbNow,
                        ]);
                    }
                }
            }

            $appService = app(CompetencyApplicationService::class);

            $workflowService = app(CompetencyWorkflowService::class);
            $applicant = $appService->findApplicantWithPayment($request->application_id);
            if (!$applicant) {
                return response()->json(['status' => 'error', 'message' => 'Applicant not found.'], 404);
            }

            $applicantStatus = $appService->applicationStatus($applicant);
            $isReturnedApplication = $applicantStatus === 'RE';

            $Existingcheck = CC_Checklist_applicant::where('applicant_id', $request->application_id)
                ->where('certificate_name', $applicant->certificate_name)
                ->first();


            $metaService = app(CompetencyMetaService::class);
            $metaTableForCert = $metaService->metaTableForApplicationId($request->application_id)
                ?: $metaService->tableForForm((string) ($application->form_name ?? ''));
            if ($metaTableForCert) {
                DB::table($metaTableForCert)
                    ->where('application_id', $request->application_id)
                    ->update([
                        'certificate_no' => $licenseNumber,
                        'updated_at' => now(),
                    ]);
            }

            if ($Existingcheck) {
                $Existingcheck->update([

                    // 'certificate_name'       => $request->certificate_name,
                    'checklist_json' => json_encode($checklistData),
                    'updated_by' => Auth::id(),
                ]);
            } else {

                CC_Checklist_applicant::create([
                    'login_id' => Auth::id(),
                    'applicant_id' => $request->application_id,
                    'cert_license_id' => $applicant->id,
                    'certificate_name' => $applicant->certificate_name,
                    'checklist_json' => json_encode($checklistData),
                    'updated_by' => Auth::id(),
                ]);
            }


            $workflowTable = app(CompetencyApplicationService::class)
                ->resolveWorkflowTable($request->application_id, $application);
            app(CompetencyWorkflowService::class)->record($workflowTable, [
                'application_id' => $request->application_id,
                'processed_by' => $request->processed_by,
                'role_id' => Auth::user()->roles_id,
                'appl_status' => 'A',
                'remarks' => $request->remarks ?? 'No remarks provided',
                'forwarded_to' => $request->forwarded_to ?? null,
                'created_at' => $this->dbNow,
                'login_id' => Auth::id(),
                'raised_by' => $processed ?: 'PR',
            ]);


            $payload = [
                'qc' => $request->qc,
                'qsc' => $request->qsc,
                'updated_at' => $this->dbNow,
            ];

            if (CC_Forms_cert::where('certificate_no', $licenseNumber)->exists()) {
                $updated = CC_Forms_cert::where('certificate_no', $licenseNumber)->update($payload);
            }



            DB::commit();

            if ($appl_type === 'R') {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Application Renewed successfully!',
                    'license_number' => $licenseNumber,
                    'issued_at' => $issuedAt,
                    'expires_at' => $expiresAt,
                ], 200);
            }

            if ($appl_type === 'A') {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Alteration approved successfully! Certificate has been regenerated.',
                    'license_number' => $licenseNumber,
                    'issued_at' => $issuedAt,
                    'expires_at' => $expiresAt,
                ], 200);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Application approved successfully! <br>' . $message,
                'license_number' => $licenseNumber,
                'issued_at' => $issuedAt,
                'expires_at' => $expiresAt,
            ], 200);
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
        $certService = $this->competencyCertificateService();
        $formName = (string) ($application->form_name ?? '');



        // Renewal flow — one cc_*_cert row per application_id
        if ($applType === 'R') {
            $licenseDetails = $certService->asLicenseDetails($applicationId, $formName);
            $previousCertExpiry = $this->resolvePreviousCertExpiryForRenewal($application, $applicationId);
            $previousExpiryCarbon = $previousCertExpiry
                ? Carbon::parse($previousCertExpiry)
                : Carbon::parse(db_now());
            $now = Carbon::parse(db_now());

            if (!$licenseDetails || $now->greaterThan(Carbon::parse($licenseDetails->expires_at))) {
                $issuedAt = $previousExpiryCarbon->copy()->addDay()->format('Y-m-d');
                $effectiveApplType = $applType;

                if ($now->greaterThan($previousExpiryCarbon)) {
                    $effectiveApplType = 'N';
                }

                $licensePeriod = $this->resolveLicenseValidity($licenceId, $effectiveApplType);
                $monthsToAdd = (int) ($licensePeriod->validity ?? 0);

                $expiresAt = $previousExpiryCarbon->copy()
                    ->addMonths($monthsToAdd)
                    ->subDay()
                    ->format('Y-m-d');

                $licenseNumber = (string) $application->license_number;

                $this->persistCompetencyCertificate(
                    $application,
                    $applicationId,
                    $licenseNumber,
                    $now,
                    $issuedAt,
                    $expiresAt
                );



                return [$licenseNumber, $issuedAt, $expiresAt];
            }

            // dd($licenseDetails->license_number); exit;

            return [
                $licenseDetails->license_number,
                $licenseDetails->issued_at,
                $licenseDetails->expires_at,
            ];
        }

        if ($applType === 'A') {
            $metaTable = (string) ($application->_approval_source ?? app(CompetencyMetaService::class)->metaTableForApplicationId($applicationId) ?? '');
            if ($metaTable === '') {
                throw new \RuntimeException('Alteration application meta table not found.');
            }

            $result = app(FormSAlterationService::class)->applyApprovedAlterationChanges($applicationId, $metaTable);
            // dd($result); exit;
            return [
                $result['license_number'],
                $result['issued_at'],
                $result['expires_at'],
            ];
        }

        if ($applType === 'D') {

            $licenseDetails = $certService->asLicenseDetails($applicationId, $formName);


            if ($licenseDetails) {
                return [
                    $licenseDetails->license_number,
                    $licenseDetails->issued_at,
                    $licenseDetails->valid_from ?? $licenseDetails->issued_from,
                    $licenseDetails->expires_at,
                ];
            }

            $licenseNumber = $this->nextCompetencyCertificateNumber($application);
            $digitization = Tnelb_CC_Digitization::where('application_id', $applicationId)->first();
            $expiresAt = $digitization->to_date;
            $fromDate = $digitization->from_date;
            $issuedAt = $digitization->fissue;

            $this->persistCompetencyCertificate(
                $application,
                $applicationId,
                $licenseNumber,
                $issuedAt,
                $fromDate,
                $expiresAt
            );

            return [$licenseNumber, $issuedAt, $expiresAt];
        }

        // Fresh licence (N) flow
        $licenseDetails = $certService->asLicenseDetails($applicationId, $formName);

        if ($licenseDetails) {
            return [
                $licenseDetails->license_number,
                $licenseDetails->issued_at,
                $licenseDetails->valid_from ?? $licenseDetails->issued_from,
                $licenseDetails->expires_at,
            ];
        }

        $now = db_now();
        $licenseNumber = $this->nextCompetencyCertificateNumber($application);
        $issuedAt = $now;

        $licensePeriod = $this->resolveLicenseValidity($licenceId, $applType);
        $monthsToAdd = (int) ($licensePeriod->validity ?? 0);

        $expiresAt = Carbon::parse($now)->copy()->addMonths($monthsToAdd)
            ->subDay()
            ->toDateString();

        $this->persistCompetencyCertificate(
            $application,
            $applicationId,
            $licenseNumber,
            $issuedAt,
            $now,
            $expiresAt
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
