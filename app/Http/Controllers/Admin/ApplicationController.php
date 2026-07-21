<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\DB;
use App\Models\Admin\SupervisorModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\CC_Forms_Meta;
use App\Services\Competency\CompetencyApplicationService;
use App\Services\Competency\CompetencyAdminQueryService;
use App\Services\Competency\CompetencyWorkflowService;
use App\Models\CC_checklist_applicant;
use App\Models\EA_Application_model;
use Carbon\Carbon;

class ApplicationController extends Controller
{

    protected $today, $dbNow;
    public function __construct()
    {
        $this->today = Carbon::today()->toDateString();
        $this->dbNow  = DB::selectOne("SELECT date_trunc('second', NOW()::timestamp) AS db_now")->db_now;
    }


    public function get_wh_apps()
    {
        $assignedFormID = 3;
        $forms = self::getForms($assignedFormID);
        $ccAdminQuery = app(CompetencyAdminQueryService::class);

        if ($ccAdminQuery->isCcMetaFormId($assignedFormID)) {
            $new_applications = $ccAdminQuery->applicationsByApplType($assignedFormID, 'N');
            $renewal = $ccAdminQuery->applicationsByApplType($assignedFormID, 'R');
            $returned_applications = $ccAdminQuery->supervisorReturnedApplications($assignedFormID);

            return view('admin.supervisor.view', compact('new_applications', 'renewal', 'returned_applications', 'forms'));
        }

        $new_applications = DB::table('tnelb_application_tbl')
            ->where('form_id', $assignedFormID)
            ->where('appl_type', 'N')
            ->where('payment_status', 'payment')
            ->whereIn('status', ['P', 'RE'])
            ->select('*', DB::raw("EXISTS (SELECT 1 FROM tnelb_workflow tw WHERE tw.application_id = tnelb_application_tbl.application_id AND tw.appl_status = 'QU') AS has_return_history"))
            ->orderByDesc('id')
            ->get();

        $renewal = DB::table('tnelb_application_tbl')
            ->where('form_id', $assignedFormID)
            ->where('appl_type', 'R')
            ->whereIn('status', ['P', 'RE'])
            ->select('*', DB::raw("EXISTS (SELECT 1 FROM tnelb_workflow tw WHERE tw.application_id = tnelb_application_tbl.application_id AND tw.appl_status = 'QU') AS has_return_history"))
            ->get();

        $returned_applications = DB::table('tnelb_application_tbl')
            ->where('form_id', $assignedFormID)
            ->where('status', 'QU')
            ->whereIn('payment_status', ['payment', 'paid'])
            ->select('*')
            ->orderByDesc('id')
            ->get();

        return view('admin.supervisor.view', compact('new_applications', 'renewal', 'returned_applications', 'forms'));
    }

    public function get_applications()
    {
        $assignedFormID = (int) Auth::user()->form_id;
        $forms = self::getForms($assignedFormID);
        $ccAdminQuery = app(CompetencyAdminQueryService::class);

        if ($ccAdminQuery->isCcMetaFormId($assignedFormID)) {
            $new_applications = $ccAdminQuery->applicationsByApplType($assignedFormID, 'N');
            $renewal = $ccAdminQuery->applicationsByApplType($assignedFormID, 'R');
            $returned_applications = $ccAdminQuery->supervisorReturnedApplications($assignedFormID);

            return view('admin.supervisor.view', compact('new_applications', 'renewal', 'returned_applications', 'forms'));
        }

        $new_applications = DB::table('tnelb_application_tbl')
            ->where('form_id', $assignedFormID)
            ->where('appl_type', 'N')
            ->where('payment_status', 'payment')
            ->whereIn('status', ['P', 'RE'])
            ->select('*', DB::raw("EXISTS (SELECT 1 FROM tnelb_workflow tw WHERE tw.application_id = tnelb_application_tbl.application_id AND tw.appl_status = 'QU') AS has_return_history"))
            ->orderByDesc('id')
            ->get();

        $renewal = DB::table('tnelb_application_tbl')
            ->where('form_id', $assignedFormID)
            ->where('appl_type', 'R')
            ->whereIn('status', ['P', 'RE'])
            ->select('*', DB::raw("EXISTS (SELECT 1 FROM tnelb_workflow tw WHERE tw.application_id = tnelb_application_tbl.application_id AND tw.appl_status = 'QU') AS has_return_history"))
            ->orderByDesc('id')
            ->get();

        $returned_applications = DB::table('tnelb_application_tbl')
            ->where('form_id', $assignedFormID)
            ->whereIn('payment_status', ['payment', 'paid'])
            ->where(function ($q) {
                $q->where('status', 'QU')
                    ->orWhereRaw("(status IN ('P','RE') AND EXISTS (SELECT 1 FROM tnelb_workflow tw WHERE tw.application_id = tnelb_application_tbl.application_id AND tw.appl_status = 'QU'))");
            })
            ->select('*')
            ->orderByDesc('id')
            ->get();

        return view('admin.supervisor.view', compact('new_applications', 'renewal', 'returned_applications', 'forms'));
    }

    public function view_application() {}

    public function getForms($form_id)
    {
        return DB::table('tnelb_forms')
            ->where('id', $form_id) // Filter by Form S
            ->select('*')
            ->first();
    }

    public function get_auditor()
    {
        $userRole = Auth::user()->roles_id; // Supervisor Role ID

        $workflows = DB::table('tnelb_application_tbl as ta')
            ->leftJoin('tnelb_forms as f', 'ta.form_id', '=', 'f.id') // Join forms table
            ->where('ta.status', 'A') // Only show new applications
            ->select('ta.*', 'f.form_name')
            ->get();

        return view('admin.application.view', compact('workflows'));
    }


    public function returntoSecretary(Request $request)
    {

        // dd($request->all());exit;

        $applicationid = $request->application_id;

        $staff = Auth::user();

        $staffID = Auth::user()->id;

        $request->validate([
            'application_id' => 'required|string',
            'return_by'      => 'required|string',
            'forwarded_to'   => 'required|string',
            'checkboxes'     => 'nullable|string',
            'queryswitch'    => 'nullable|string',
            'queryType'      => 'array',
            'remarks'        => 'required|string'
        ]);


        $query_status = null;
        $queryTypeJson = json_encode($request->queryType);


        if ($request->queryswitch == 'Yes' && !empty($request->queryType)) {
            $query_status = "P";
        }



        $formType = (object) [
            'form_id' => app(CompetencyApplicationService::class)->findFormId($request->application_id),
        ];


        $processed_by = match ($staff->name) {
            'President'  => 'PR',
            'Secretary'  => 'SE',
            'Supervisor' => 'S',
            'Assistant Secretary'    => 'A',
            default      => abort(403, 'Unauthorized'),
        };

        $raised_by    = ($request->queryswitch === 'Yes') ? $processed_by : $staffID;

        $checklistData = [];

        foreach ($request->check_id as $id => $checkId) {

            $checklistData[] = [
                'id'      => $checkId,
                'checked' => (int) ($request->checklists[$id] ?? 0),
                'verify'  => (int) ($request->status[$id] ?? 0),
            ];
        }


        $appService = app(CompetencyApplicationService::class);
        $workflowService = app(CompetencyWorkflowService::class);

        $applicant = $appService->findApplicantWithPayment($request->application_id);
        if (! $applicant) {
            return response()->json(['status' => 'error', 'message' => 'Applicant not found.'], 404);
        }

        $applicantStatus = $appService->applicationStatus($applicant);
        // var_dump($queryTypeJson);die;
        $Existingcheck = CC_Checklist_applicant::where('applicant_id', $request->application_id)
            ->where('certificate_name', $applicationid)
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

        try {
            DB::beginTransaction();

            $appService = app(CompetencyApplicationService::class);
            $workflowService = app(CompetencyWorkflowService::class);
            $applicant = $appService->findApplicantWithPayment($request->application_id);
            if (! $applicant) {
                DB::rollBack();

                return response()->json(['status' => 'error', 'message' => 'Application not found.'], 404);
            }

            $workflowTable = $appService->resolveWorkflowTable($request->application_id, $applicant);
            $workflowService->record($workflowTable, [
                'application_id' => $request->application_id,
                'appl_status'    => 'PRE',
                'processed_by'   => $request->return_by,
                'forwarded_to'   => $request->forwarded_to,
                'role_id'        => $staff->roles_id,
                'is_verified'    => $request->checkboxes,
                'query_status'   => $query_status,
                'remarks'        => $request->remarks,
                'created_at'     => now(),
                'login_id'       => $staffID,
                'queries'        => $queryTypeJson,
                'raised_by'      => $query_status == 'P' ? $raised_by : '',
            ]);

            $appService->updateApplicationStatus($request->application_id, [
                'status'       => 'PRE',
                'processed_by' => $processed_by,
                'updated_at'   => now(),
            ]);

            // Get role
            $role = DB::table('mst_roles')
                ->where('r_id', $request->forwarded_to)
                ->first();


            $roleName = $role->role_name ?? $role->name ?? 'Secretary';

            DB::commit();

            return response()->json([
                'status'  => "success",
                'message' => "Application Returned to $roleName successfully!",
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }


    public function returntoSupervisor(Request $request)
    {

        // dd($request->all());exit;

        $staff = Auth::user();



        $staffID = Auth::user()->id;


        $request->validate([
            'application_id' => 'required|string',
            'return_by'      => 'required|string',
            'forwarded_to'   => 'required|string',
            'checkboxes'     => 'nullable|string',
            'queryswitch'    => 'nullable|string',
            'queryType'      => 'array',
            'remarks'        => 'nullable|string'
        ]);


        $query_status = null;
        $queryTypeJson = json_encode($request->queryType);


        if ($request->queryswitch == 'Yes' && !empty($request->queryType)) {
            $query_status = "P";
        }



        $formType = (object) [
            'form_id' => app(CompetencyApplicationService::class)->findFormId($request->application_id),
        ];


        $processed_by = match ($staff->name) {
            'President'  => 'PR',
            'Secretary'  => 'SE',
            'Supervisor' => 'S',
            'Assistant Secretary'    => 'A',
            default      => abort(403, 'Unauthorized'),
        };

        $raised_by    = ($request->queryswitch === 'Yes') ? $processed_by : $staffID;
        // var_dump($queryTypeJson);die;


        try {
            DB::beginTransaction();

            $appService = app(CompetencyApplicationService::class);
            $workflowService = app(CompetencyWorkflowService::class);
            $applicant = $appService->findApplicantWithPayment($request->application_id);
            if (! $applicant) {
                DB::rollBack();

                return response()->json(['status' => 'error', 'message' => 'Application not found.'], 404);
            }

            $workflowTable = $appService->resolveWorkflowTable($request->application_id, $applicant);
            $workflowService->record($workflowTable, [
                'application_id' => $request->application_id,
                'appl_status'    => 'RE',
                'processed_by'   => $request->return_by,
                'forwarded_to'   => $request->forwarded_to,
                'role_id'        => $staff->roles_id,
                'is_verified'    => $request->checkboxes,
                'query_status'   => $query_status,
                'remarks'        => $request->remarks,
                'created_at'     => now(),
                'login_id'       => $staffID,
                'queries'        => $queryTypeJson,
                'raised_by'      => $query_status == 'P' ? $raised_by : '',
            ]);

            $appService->updateApplicationStatus($request->application_id, [
                'status'       => 'RE',
                'processed_by' => $processed_by,
                'updated_at'   => now(),
            ]);

            // Get role
            $role = DB::table('mst_roles')
                ->where('r_id', $request->forwarded_to)
                ->first();
            $roleName = $role->role_name ?? $role->name ?? 'selected role';

            DB::commit();

            return response()->json([
                'status'  => "success",
                'message' => "Application Returned to $roleName successfully!",
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Return application to Applicant with query (Secretary/President only).
     * Used by applicants_detail_supervisor (tnelb_application_tbl flow).
     */
    public function returnToApplicant(Request $request)
    {
        $staff = Auth::user();
        $staffID = Auth::user()->id;

        $request->validate([
            'application_id'           => 'required|string',
            'return_applicant_query'   => 'required|array|min:1',
            'return_applicant_query.*' => 'required|string|max:255',
            'remarks'                  => 'nullable|string|max:500',
            'staff_remarks'            => 'nullable|string|max:500',
            'staff_queryType'          => 'nullable|array',
        ]);

        $appService = app(CompetencyApplicationService::class);
        $workflowService = app(CompetencyWorkflowService::class);
        $applicant = $appService->findApplicantWithPayment($request->application_id);

        if (! $applicant) {
            return response()->json(['status' => 'error', 'message' => 'Application not found.'], 404);
        }

        $processed_by = match ($staff->name) {
            'President' => 'PR',
            'Secretary' => 'SE',
            default     => null,
        };

        if ($processed_by === null) {
            return response()->json(['status' => 'error', 'message' => 'Only Secretary or President can return to applicant.'], 403);
        }

        $queryList = $request->return_applicant_query;
        $queryTypeJson = is_array($queryList) ? json_encode($queryList) : json_encode([$queryList]);
        $staff_queryTypeJson = is_array($request->staff_queryType) ? json_encode($request->staff_queryType) : json_encode([$request->staff_queryType]);
        $remarks = $request->remarks ?? '';
        $staff_remarks = $request->staff_remarks ?? '';

        DB::table('tnelb_return_to_applicant_log')->insert([
            'application_id'        => $request->application_id,
            'returned_by_staff_id'  => $staffID,
            'returned_by_role'       => $processed_by,
            'returned_by_name'       => $staff->name ?? null,
            'query_types'           => $queryTypeJson,
            'remarks'               => $remarks,
            'created_at'            => $this->dbNow
        ]);

        $workflowTable = $appService->resolveWorkflowTable($request->application_id, $applicant);
        $workflowService->record($workflowTable, [
            'application_id' => $request->application_id,
            'appl_status'    => 'QU',
            'processed_by'   => $processed_by,
            'forwarded_to'   => null,
            'role_id'        => $staff->roles_id,
            'is_verified'    => 'Yes',
            'query_status'   => 'P',
            'remarks'        => $staff_remarks,
            'created_at'     => $this->dbNow,
            'login_id'       => $staffID,
            'queries'        => $staff_queryTypeJson,
            'raised_by'      => $processed_by,
        ]);

        DB::table('tnelb_query_applicable')->insert([
            'application_id' => $request->application_id,
            'query_type'      => $staff_queryTypeJson,
            'raised_by'       => $processed_by,
            'query_status'    => 'P',
            'created_at'      => $this->dbNow,
        ]);

        $appService->updateApplicationStatus($request->application_id, [
            'status'      => 'QU',
            'processed_by' => $processed_by,
            'updated_at'  => $this->dbNow,
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Application returned to applicant with query successfully!',
        ], 201);
    }

    //--------------------------------return to applicant forma--------------------------------
    // Handles Form A contractor applications
    public function returntoSupervisorforma(Request $request)
    {

        $staff = Auth::user();

        $staffID = Auth::user()->id;

        // dd($request->forwaded_to);
        // exit;
        $request->validate([
            'application_id' => 'required|string',
            'return_by'      => 'required|string',
            'forwarded_to'   => 'required|string',
            'checkboxes'     => 'nullable|string',
            'queryswitch'    => 'nullable|string',
            'queryType'      => 'array',
            'remarks'        => 'nullable|string'
        ]);


        $query_status = null;
        $queryTypeJson = json_encode($request->queryType);
        $forwarded_to    = ($request->return_by === 'President') ? 3 : 1;

        // dd($forwarded_to);exit;

        if ($request->queryswitch == 'Yes' && !empty($request->queryType) || ($request->queryswitch == 'true')) {
            $query_status = "P";
        }


        $formType = DB::table('tnelb_ea_applications')
            ->where('application_id', $request->application_id)

            // ->select('form_id')
            ->first();

        // dd($formType->form_name);
        // exit;

        // $status = match ($staff->name) {
        //     'President' => 'A',
        //     'Secretary'  => ($formType->form_id == 1 ? 'F' : 'A'),
        //     'Supervisor' => 'F',
        //     'Auditor'    => 'F',
        //     default      => abort(403, 'Unauthorized'),
        // };

        $processed_by = match ($staff->name) {
            'President'  => 'PR',
            'Secretary'  => 'SE',
            'Supervisor' => 'S',
            'Assistant Secretary'    => 'A',
            default      => abort(403, 'Unauthorized'),
        };

        $raised_by    = ($request->queryswitch === 'Yes') ? $processed_by : $staffID;
        // var_dump($queryTypeJson);die;

        // Insert data into tnelb_workflow table
        $workflow = WorkflowA::create([ // Ensure this is the correct model
            'application_id' => $request->application_id,
            'appl_status'    => 'RE', // Forwarded
            'processed_by'   => $request->return_by,
            'forwarded_to'   => $forwarded_to,
            'role_id'        => $staffID,
            'is_verified'    => $request->checkboxes,
            'query_status'   => $query_status,
            // "Yes" or "No"
            'remarks'        => $request->remarks,
            'created_at'     => now(), // Automatically managed if model has timestamps
            'login_id'       => $staffID,
            'queries'        => $queryTypeJson,
            'raised_by'      => $query_status == 'P' ? $raised_by : ''
        ]);


        // Update application status
        // DB::table('tnelb_ea_applications')
        //     ->where('application_id', $request->application_id)
        //     ->update([
        //         'application_status'  => 'RE',
        //         'processed_by'  => $processed_by,
        //         'updated_at' => now(),
        //     ]);

        // WorkflowA::where('application_id', $request->application_id)
        //     ->where('processed_by', $request->return_by)
        //     ->where('role_id', $staffID)
        //       ->update([

        //           'created_at' => DB::raw('NOW()'),
        //       ]);

        WorkflowA::where('application_id', $request->application_id)
            ->where('processed_by', $request->return_by)
            ->where('role_id', $staffID)
            ->orderByDesc('id')
            ->limit(1)
            ->update([
                'created_at' => DB::raw('NOW()'),
            ]);



        EA_Application_model::where('application_id', $request->application_id)
            ->update([
                'application_status' =>  'RE',
                'processed_by'  => $processed_by,
                'updated_at' => DB::raw('NOW()'),
            ]);



        //Get Role
        $role = DB::table('mst_roles')
            ->where('r_id', $forwarded_to)
            ->first();
        $roleName = $role->role_name ?? $role->name ?? 'selected role';
        // var_dump($role->name);die;


        return response()->json([
            'status' => "success",
            'message' => "Application Returned to $roleName successfully!",
        ], 201);
    }


    public function renewal_apps()
    {

        $userRole = Auth::user()->roles_id; // Supervisor Role ID



        $assignedFormID = Auth::user()->form_id;

        $forms = self::getForms($assignedFormID);

        switch ($userRole) {

            case $userRole == '1':
                $application_details = DB::table('tnelb_application_tbl')
                    ->where('form_id', $assignedFormID)
                    ->whereIn('status', ['P', 'RE'])
                    ->where('appl_type', '2')
                    ->select('*')
                    ->get();
                break;

            case $userRole == '2':
                $application_details = DB::table('tnelb_application_tbl as ta')
                    ->where('ta.status', ['F', 'RF'])
                    ->where('ta.processed_by', 'S')
                    ->orWhere('ta.processed_by', 'S2')
                    ->where('ta.form_id', $assignedFormID)
                    ->where('appl_type', '2')
                    ->select('ta.*')
                    ->get();
                break;

            case $userRole == '3':
                $application_details = DB::table('tnelb_application_tbl as ta')
                    ->where('ta.processed_by', 'A')
                    ->orWhere('ta.status', 'RF')
                    ->where('ta.form_id', $assignedFormID)
                    ->where('appl_type', '2')
                    ->select('ta.*')
                    ->get();
                break;

            case $userRole == '4':

                $application_details = DB::table('tnelb_application_tbl as ta')
                    ->where('ta.processed_by', 'SE')
                    ->where('ta.appl_type', '2')
                    ->select('ta.*')
                    ->get();


                break;

            default:

                break;
        }


        return view('admin.renewal.renewalapps', compact('application_details'));
    }

    // ----------------forma--------------------------


    public function rejectApplication(Request $request)
    {

        // Validate basic fields
        $request->validate([
            'application_id' => 'required|string',
            'appl_status'    => 'required|string|in:RJ',
            'action_by'      => 'required|string|max:255',
            'login_id'       => 'required|string',  // or your staff table
        ]);

        // Insert into workflow table (recommended approach)


        $appService = app(CompetencyApplicationService::class);
        $workflowService = app(CompetencyWorkflowService::class);
        $applicant = $appService->findApplicantWithPayment($request->application_id);

        $workflowTable = $appService->resolveWorkflowTable($request->application_id, $applicant);
        $workflowService->record($workflowTable, [
            'application_id' => $request->application_id,
            'processed_by'   => $request->action_by,
            'role_id'        => Auth::user()->roles_id,
            'appl_status'    => $request->appl_status,
            'reject_reason'  => $request->reason ?? '',
            'created_at'     => now(),
            'login_id'       => $request->login_id,
            'raised_by'      => $request->action_by,
        ]);

        $appService->updateApplicationStatus($request->application_id, [
            'status' => $request->appl_status,
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Application rejected!',
        ]);
    }


    public function get_rejected()
    {
        $page_title = 'Rejected';
        $assignedFormID = (int) Auth::user()->form_id;
        $ccAdminQuery = app(CompetencyAdminQueryService::class);

        $workflows = $ccAdminQuery->isCcMetaFormId($assignedFormID)
            ? $ccAdminQuery->rejectedApplications($assignedFormID)
            : collect();

        return view('admin.supervisor.rejected', compact('workflows', 'page_title'));
    }


    // returnforma application-------------------------

    public function returntoapplicantForma(Request $request)
    {

        // dd($request->all());exit;

        $staff = Auth::user();

        $staffID = Auth::user()->id;

        $request->validate([
            'application_id' => 'required|string',
            'return_by'      => 'required|string',
            'forwarded_to'   => 'required|string',
            'checkboxes'     => 'nullable|string',
            'queryswitch'    => 'nullable|string',
            'queryType'      => 'array',
            'remarks'        => 'nullable|string'
        ]);


        $query_status = null;
        $queryTypeJson = json_encode($request->queryType);


        if ($request->queryswitch == 'Yes' && !empty($request->queryType) || ($request->queryswitch == 'true')) {
            $query_status = "P";
        }


        $formType = DB::table('tnelb_ea_applications')
            ->where('application_id', $request->application_id)

            // ->select('form_id')
            ->first();


        $processed_by = match ($staff->name) {
            'President'  => 'PR',
            'Secretary'  => 'SE',
            'Supervisor' => 'S',
            'Accountant'    => 'A',
            default      => abort(403, 'Unauthorized'),
        };

        $raised_by    = ($request->queryswitch === 'Yes') ? $processed_by : $staffID;
        // var_dump($queryTypeJson);die;




        // Insert data into tnelb_workflow table
        $workflow = WorkflowA::create([ // Ensure this is the correct model
            'application_id' => $request->application_id,
            'appl_status'    => 'RET',
            'processed_by'   => $request->return_by,
            'forwarded_to'   => $request->forwarded_to,
            'role_id'        => $staffID,
            'is_verified'    => $request->checkboxes,
            'query_status'   => $query_status,
            'return_reason'  => json_encode($request->reasons ?? []),

            // "Yes" or "No"
            'remarks'        => $request->remarks,
            'created_at'     => now(),
            'login_id'       => $staffID,
            'queries'        => $queryTypeJson,
            'raised_by'      => $processed_by,
            'remarks_return'        => $request->remarks_return,
        ]);



        WorkflowA::where('application_id', $request->application_id)
            ->where('processed_by', $request->return_by)
            ->where('role_id', $staffID)
            ->orderByDesc('id')
            ->limit(1)
            ->update([
                'created_at' => DB::raw('NOW()'),
            ]);


        DB::table('tnelb_return_to_applicant_log')->insert([
            'application_id'        => $request->application_id,
            'returned_by_staff_id'  => $staffID,
            'returned_by_role'       => $processed_by,
            'returned_by_name'       => $staff->name ?? null,
            'query_types'           => $queryTypeJson,
            'remarks'               => $request->remarks,
            'return_reasons'         => json_encode($request->reasons ?? []),
            'remarks_return'        => $request->remarks_return,
            'created_at'            => DB::raw('NOW()'),
            'updated_at'            => DB::raw('NOW()'),
        ]);



        EA_Application_model::where('application_id', $request->application_id)
            ->update([
                'application_status' =>  'RET',
                'return_flag' => '1',
                'return_date' => DB::raw('NOW()'),
                'return_reason'  => json_encode($request->reasons ?? []),
                'processed_by'  => $processed_by,
                'remarks_return' => $request->remarks_return,
                'updated_at' => DB::raw('NOW()'),
            ]);


        //Get Role
        $role = DB::table('mst_roles')
            ->where('r_id', $request->forwarded_to)
            // ->select('name')
            ->first();
        // var_dump($role->name);die;


        return response()->json([
            'status' => "success",
            'message' => "Application Returned to Applicant successfully!",
        ], 201);
    }
}
