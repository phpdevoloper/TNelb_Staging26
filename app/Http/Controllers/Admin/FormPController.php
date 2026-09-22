<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\FeesValidity;
use App\Models\Admin\Mst_equipment_tbl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

use App\Models\B_Application;
use App\Models\MstLicence;
use App\Models\TnelbFormP;
use App\Models\Admin\SupervisorModel;
use App\Models\Tnelb_banksolvency_a;
use App\Models\TnelbApplicantPhoto;
use App\Models\TnelbApplicantsSign;
use App\Models\TnelbAppsInstitute;
use Carbon\Carbon;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

use App\Services\Competency\CompetencyApplicationService;
use App\Services\Competency\CompetencyCertificateService;
use App\Services\Competency\CompetencyWorkflowService;
use App\Services\Competency\FormPSchema;
use App\Services\FormS\FormSProofDocumentService;
use App\Services\ReturnedApplicationEditScope;

class FormPController extends Controller
{
    protected $today,$dbNow;
    public function __construct()
    {
        $this->today = Carbon::today()->toDateString();
        $this->dbNow  = DB::selectOne("SELECT date_trunc('second', NOW()::timestamp) AS db_now")->db_now;
    }
    public function view_application_formp($applicant_id)
    {


        // $roles = DB::table('tnelb_registers')
        //     ->select('*')
        //         ->get();


        $returnForwardUser = null;
        $bundle = $this->loadFormPAdminBundle($applicant_id);
        $applicant = $bundle['applicant'];
        if (! $applicant) {
            return abort(403, 'Applicant not found');
        }

        $educationalQualifications = $bundle['edu'];
        $workExperience = $bundle['exp'];
        $documents = $bundle['documents'];
        $uploadedPhoto = $bundle['photo'];
        $uploadedSign = $bundle['sign'];
        $institute_details = $bundle['institutes'];




        // Get the current user's role ID
        $staff = Auth::user();



        if (!$staff || !$staff->roles_id) {
            return abort(403, 'Unauthorized');
        }

        // Initialize defaults so variables are always defined.
        $nextForwardUser = null;
        $returnForwardUser = null;

        // Fetch next role dynamically from the roles table
        if ($staff->name === "Supervisor") {

            $nextForwardUser = DB::table('mst_login_users')
            ->where('user_name', 'assistantsecretary')
            ->select('user_name as name', 'role_id as roles_id')
            ->first();
        }


        if ($staff->name === "Assistant Secretary") {
            $nextForwardUser = DB::table('mst__staffs__tbls')
                ->where('name', 'Secretary')
                ->select('name', 'roles_id')
                ->first();
             $returnForwardUser = DB::table('mst__staffs__tbls')
                ->where('name', 'Supervisor')
                ->select('name', 'roles_id')
                ->first();
        }
        if ($staff->name === "Secretary") {
            // For Form P, always allow Secretary to forward to President
            $nextForwardUser = DB::table('mst_login_users')
                ->where('user_name', 'president')
                ->select('user_name as name', 'role_id as roles_id')
                ->first();

            $returnForwardUser = DB::table('mst_login_users')
                ->where('user_name', 'supervisor')
                ->select('user_name as name', 'role_id as roles_id')
                ->first();
        }

        if ($staff->name === "President") {

            $nextForwardUser = DB::table('mst_login_users')
                ->where('user_name', 'president')
                ->select('user_name as name', 'role_id as roles_id')
                ->first();

            $returnForwardUser = DB::table('mst_login_users')
                ->where('user_name', 'supervisor')
                ->select('user_name as name', 'role_id as roles_id')
                ->first();
        }

        $user_entry = DB::table('tnelb_form_p')
            ->where('application_id', $applicant_id) // Filter by specific application
            ->select('*')
            ->first();


        $workflows = DB::table('tnelb_workflow')
            ->leftjoin('tnelb_form_p', 'tnelb_workflow.application_id', '=', 'tnelb_form_p.application_id')
            ->leftjoin('mst_roles', 'tnelb_workflow.forwarded_to', '=', 'mst_roles.r_id')
            ->where('tnelb_workflow.application_id', $applicant_id) // Filter by specific application
            ->select('tnelb_workflow.*', 'mst_roles.role_name as name', 'tnelb_form_p.form_name', 'tnelb_form_p.license_name')
            ->orderBy('tnelb_workflow.id', 'desc')
            ->get();

        $workflows1 = DB::table('mst_roles')
            ->select('*')
            ->get();



        $queries = DB::table('tnelb_query_applicable as qa')
            ->leftJoin('tnelb_form_p as ta', 'qa.application_id', '=', 'ta.application_id')
            ->where('qa.application_id', $applicant_id)
            ->where('qa.query_status', 'P')
            ->select('qa.*')
            ->orderByDesc('qa.id')
            ->get();
=======
        $user_entry = $applicant;
        $workflows = $this->loadFormPWorkflows($applicant_id);
        $queries = $this->loadFormPQueries($applicant_id, true);
>>>>>>> 6c8d5a912fd4fd07ce2dfc80c2cdc80637cee845





        // Determine view based on user role
        $view = match ($staff->name) {
            'Supervisor', 'President', 'Secretary' => 'admin.dashboard.formp.applicants_detail',
            'Assistant Secretary'                  => 'admin.dashboard.formp.applicants_detail',

            default                                => abort(403, 'Unauthorized'),
        };

        return view($view, compact('applicant', 'educationalQualifications', 'workExperience', 'uploadedPhoto', 'uploadedSign', 'documents', 'nextForwardUser', 'returnForwardUser', 'workflows', 'queries', 'user_entry', 'staff','institute_details'));
    }

    /**
     * View completed Form P application in read-only mode (no action buttons).
     */
    public function view_completed_formp($applicant_id)
    {
        $staff = Auth::user();
        if (!$staff) {
            return abort(403, 'Unauthorized');
        }

        $bundle = $this->loadFormPAdminBundle($applicant_id);
        $applicant = $bundle['applicant'];
        if (! $applicant || strtoupper((string) ($applicant->app_status ?? '')) !== 'A') {
            return abort(404, 'Applicant not found');
        }

        $educationalQualifications = $bundle['edu'];
        $workExperience = $bundle['exp'];
        $documents = $bundle['documents'];
        $uploadedPhoto = $bundle['photo'];
        $uploadedSign = $bundle['sign'];
        $institute_details = $bundle['institutes'];

        $user_entry = $applicant;
        $workflows = $this->loadFormPWorkflows($applicant_id);
        $queries = $this->loadFormPQueries($applicant_id, false);

        return view('admin.dashboard.formp.applicants_detail_completed', compact(
            'applicant',
            'educationalQualifications',
            'workExperience',
            'uploadedPhoto',
            'uploadedSign',
            'documents',
            'workflows',
            'queries',
            'user_entry',
            'staff',
            'institute_details'
        ));
    }

    /**
     * Forward Form P application to next role (`cc_workflow_formp` + `cc_form_p_meta`).
     */
    public function forwardApplicationformp(Request $request, $role)
    {
        $staff = Auth::user();
        $staffID = Auth::user()->id;

        $request->validate([
            'application_id' => 'required|string',
            'processed_by'   => 'required|string',
            'forwarded_to'   => 'required',
            'role_id'        => 'required|integer',
            'checkboxes'     => 'nullable|string',
            'queryswitch'    => 'nullable|string',
            'queryType'      => 'nullable|array',
            'remarks'        => 'nullable|string',
        ]);

        $applicant = $this->findFormPApplication($request->application_id);
        if (!$applicant) {
            return response()->json(['status' => 'error', 'message' => 'Applicant not found.'], 404);
        }

        $queryTypeJson = $request->queryType && is_array($request->queryType) && count($request->queryType) > 0
            ? json_encode($request->queryType) : null;

        $processed_by = match ($staff->name) {
            'President'           => 'PR',
            'Secretary'           => 'SE',
            'Supervisor'          => 'S',
            'Assistant Secretary' => 'AS',
            default               => abort(403, 'Unauthorized'),
        };

        $query_status = ($request->queryswitch === 'Yes') ? 'P' : null;
        $raised_by    = ($request->queryswitch === 'Yes') ? $processed_by : $staffID;

        if ($processed_by === 'AS') {
            $last_workflow = $this->latestFormPWorkflow($request->application_id);
            if ($last_workflow && ($last_workflow->query_status ?? null) === 'P') {
                $query_status = 'P';
                $queries = $last_workflow->queries ?? null;
                $queryTypeJson = is_array($queries) ? json_encode($queries) : $queries;
            }
        }

        $app_status_workflow = ($applicant->app_status ?? '') === 'RE' ? 'RF' : 'F';
        $status = match ($staff->name) {
            'President'           => 'A',
            'Secretary'           => 'F',
            'Supervisor'          => $app_status_workflow,
            'Assistant Secretary' => 'F',
            default               => 'F',
        };

        $this->recordFormPWorkflow([
            'application_id' => $request->application_id,
            'appl_status'    => $app_status_workflow,
            'processed_by'   => $processed_by,
            'forwarded_to'   => $request->forwarded_to,
            'role_id'        => $request->role_id,
            'is_verified'    => $request->checkboxes ?? 'Yes',
            'query_status'   => $query_status,
            'remarks'        => $request->remarks,
            'created_at'     => $this->dbNow,
            'login_id'       => $staffID,
            'queries'        => $queryTypeJson ? json_decode($queryTypeJson, true) : null,
            'raised_by'      => $query_status === 'P' ? $raised_by : $processed_by,
        ]);

        $this->updateFormPApplication($request->application_id, [
            'app_status'   => $status,
            'processed_by' => $processed_by,
            'updated_at'   => now(),
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => "Application forwarded to {$role} successfully!",
        ], 201);
    }

    /**
     * Return Form P application to Supervisor.
     */
    public function returntoSupervisorformp(Request $request)
    {
        $staff = Auth::user();
        $staffID = Auth::user()->id;

        $request->validate([
            'application_id' => 'required|string',
            'return_by'      => 'required|string',
            'forwarded_to'   => 'required',
            'checkboxes'     => 'nullable|string',
            'queryswitch'    => 'nullable|string',
            'queryType'      => 'nullable|array',
            'remarks'        => 'nullable|string',
        ]);

        if (! $this->findFormPApplication($request->application_id)) {
            return response()->json(['status' => 'error', 'message' => 'Applicant not found.'], 404);
        }

        $query_status = null;
        $queryTypeJson = $request->queryType ? json_encode($request->queryType) : null;
        if ($request->queryswitch === 'Yes' && !empty($request->queryType)) {
            $query_status = 'P';
        }

        $processed_by = match ($staff->name) {
            'President'           => 'PR',
            'Secretary'           => 'SE',
            'Assistant Secretary' => 'AS',
            default               => abort(403, 'Unauthorized'),
        };
        $raised_by = ($request->queryswitch === 'Yes') ? $processed_by : $staffID;

        $this->recordFormPWorkflow([
            'application_id' => $request->application_id,
            'appl_status'    => 'RE',
            'processed_by'   => $request->return_by,
            'forwarded_to'   => $request->forwarded_to,
            'role_id'        => $staffID,
            'is_verified'    => $request->checkboxes ?? 'Yes',
            'query_status'   => $query_status,
            'remarks'        => $request->remarks,
            'created_at'     => $this->dbNow,
            'login_id'       => $staffID,
            'queries'        => $queryTypeJson ? json_decode($queryTypeJson, true) : null,
            'raised_by'      => $query_status === 'P' ? $raised_by : '',
        ]);

        $this->updateFormPApplication($request->application_id, [
            'app_status'   => 'RE',
            'processed_by' => $processed_by,
            'updated_at'   => $this->dbNow,
        ]);

        $role = DB::table('mst__roles')->where('id', $request->forwarded_to)->value('name');
        return response()->json([
            'status'  => 'success',
            'message' => 'Application returned to ' . ($role ?? 'Supervisor') . ' successfully!',
        ], 201);
    }

    /**
     * Return Form P application back to the applicant with query.
     */
    public function returnToApplicantFormp(Request $request)
    {
        $staff = Auth::user();
        $staffID = $staff?->id;

        if (!$staff || !$staffID) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 401);
        }

        $request->validate([
            'application_id'           => 'required|string',
            'return_applicant_query'   => 'required|array|min:1',
            'return_applicant_query.*' => 'required|string|max:255',
            'remarks'                  => 'nullable|string|max:500',
        ]);

        $applicant = $this->findFormPApplication($request->application_id);

        if (!$applicant) {
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

        $queryList     = $request->return_applicant_query;
        $queryTypeJson = is_array($queryList) ? json_encode($queryList) : json_encode([$queryList]);
        $remarks       = $request->remarks ?? '';

        // Log return-to-applicant for audit
        DB::table('tnelb_return_to_applicant_log')->insert([
            'application_id'        => $request->application_id,
            'returned_by_staff_id'  => $staffID,
            'returned_by_role'      => $processed_by,
            'returned_by_name'      => $staff->name ?? null,
            'query_types'           => $queryTypeJson,
            'remarks'               => $remarks,
            'created_at'            => $this->dbNow,
        ]);

        $this->recordFormPWorkflow([
            'application_id' => $request->application_id,
            'appl_status'    => 'QU',
            'processed_by'   => $processed_by,
            'forwarded_to'   => null,
            'role_id'        => $staff->roles_id,
            'is_verified'    => 'Yes',
            'query_status'   => 'P',
            'remarks'        => $remarks,
            'created_at'     => $this->dbNow,
            'login_id'       => $staffID,
            'queries'        => is_string($queryTypeJson) ? json_decode($queryTypeJson, true) : $queryTypeJson,
            'raised_by'      => $processed_by,
        ]);

        // Store query for other views
        DB::table('tnelb_query_applicable')->insert([
            'application_id' => $request->application_id,
            'query_type'     => $queryTypeJson,
            'raised_by'      => $processed_by,
            'query_status'   => 'P',
            'created_at'     => $this->dbNow,
        ]);

        // Mark Form P app as under query (QU) for applicant
        $this->updateFormPApplication($request->application_id, [
            'app_status'   => 'QU',
            'processed_by' => $processed_by,
            'updated_at'   => $this->dbNow,
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Application returned to applicant with query successfully!',
        ], 201);
    }

    /**
     * Submit corrections for a returned (QU) Form P application from the applicant side.
     * Reuses the FormPController@update logic to save changes, then marks the application as resubmitted (RE),
     * preserves payment_status, resolves queries, and adds a workflow entry.
     */
    public function submitReturnedApplicationFormP(Request $request, $appl_id)
    {
        if (!Auth::check()) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 401);
        }

        $app = $this->findFormPApplication($appl_id);
        if (!$app) {
            return response()->json(['status' => 'error', 'message' => 'Application not found.'], 404);
        }
        if ((string) $app->app_status !== 'QU') {
            return response()->json(['status' => 'error', 'message' => 'This Form P application is not under query.'], 400);
        }

        $loginId = session('login_id');
        if (!$loginId || (string) $app->login_id !== (string) $loginId) {
            return response()->json(['status' => 'error', 'message' => 'You can only submit corrections for your own application.'], 403);
        }

        // Ensure update() receives the correct application_id
        $request->merge(['application_id' => $appl_id]);

        /** @var \App\Http\Controllers\FormPController $userFormPController */
        $userFormPController = app(\App\Http\Controllers\FormPController::class);

        $queryReasonsForSubmit = [];
        $returnLogRow = ReturnedApplicationEditScope::latestReturnLogRow($appl_id);
        if ($returnLogRow) {
            $queryReasonsForSubmit = ReturnedApplicationEditScope::parseQueryTypesJson($returnLogRow->query_types ?? null);
        }
        $returnedEditableSections = ReturnedApplicationEditScope::editableSectionsFromReasons($queryReasonsForSubmit);
        $userFormPController->mergeReturnedPartialSubmitFromDb($request, $appl_id, $returnedEditableSections);

        $response = $userFormPController->update($request);
        $data = json_decode($response->getContent(), true);

        if (isset($data['status']) && $data['status'] === 'success') {
            // Preserve original payment_status and mark as resubmitted
            $this->updateFormPApplication($appl_id, [
                    'app_status'     => 'RE',
                    'processed_by'   => 'AP',
                    'updated_at'     => $this->dbNow,
                    'payment_status' => $app->payment_status,
                ]);

            DB::table('tnelb_query_applicable')
                ->where('application_id', $appl_id)
                ->where('query_status', 'P')
                ->update(['query_status' => 'R', 'updated_at' => $this->dbNow]);

            $supervisorRoleId = DB::table('mst__staffs__tbls')
                ->where('name', 'Supervisor')
                ->value('roles_id');

            if ($supervisorRoleId) {
                $this->recordFormPWorkflow([
                    'application_id' => $appl_id,
                    'appl_status'    => 'RE',
                    'processed_by'   => 'AP',
                    'forwarded_to'   => $supervisorRoleId,
                    'role_id'        => $supervisorRoleId,
                    'is_verified'    => 'Yes',
                    'query_status'   => null,
                    'remarks'        => 'Resubmitted by applicant after query (Form P).',
                    'queries'        => null,
                    'raised_by'      => 'AP',
                    'login_id'       => $loginId,
                    'created_at'     => $this->dbNow,
                ]);
            }

            return response()->json([
                'status'        => 'success',
                'message'       => 'Application Submitted',
                'application_id'=> $appl_id,
                'redirect'      => route('dashboard'),
            ]);
        }

        return $response;
    }

    /**
     * Reject Form P application.
     */
    public function rejectApplicationformp(Request $request)
    {
        $request->validate([
            'application_id' => 'required|string',
            'appl_status'    => 'required|string|in:RJ',
            'action_by'       => 'required|string|max:255',
            'login_id'        => 'required',
            'reason'          => 'nullable|string',
        ]);

        if (! $this->findFormPApplication($request->application_id)) {
            return response()->json(['success' => false, 'message' => 'Applicant not found.'], 404);
        }

        $this->recordFormPWorkflow([
            'application_id' => $request->application_id,
            'processed_by'   => $request->action_by,
            'role_id'        => Auth::user()->roles_id,
            'appl_status'    => $request->appl_status,
            'remarks'        => $request->reason ?? '',
            'created_at'     => $this->dbNow,
            'login_id'       => $request->login_id,
        ]);

        $this->updateFormPApplication($request->application_id, [
                'app_status'   => $request->appl_status,
                'updated_at'   => $this->dbNow,
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Application rejected!',
        ]);
    }



 // --------------------completed SA ---------------------------------
    public function completed_formb()
    {


        $userRole = Auth::user()->roles_id;

        // $assignedForms = DB::table('tnelb_eb_applications as ta')
        // ->whereIn('ta.application_status', ['F', 'RF','A']) // Filter by status
        // ->select('ta.*') // Select all columns from applicant_formA
        // ->get();


        // var_dump($assignedForms);die;

        $workflows = DB::table('tnelb_eb_applications')
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


        return view('admin.supervisor.formb.completed_formb', compact('workflows',
                'licenses',
                'renewalLicenses'));
    }
    // -----------Auditor------------------

      public function view_formb_pending()
    {


// dd('111');exit;
        $userRole = Auth::user()->roles_id;



        // ESA Applications Query
        $unionQuery = DB::table('tnelb_eb_applications')
            ->whereIn('application_status', ['F'])
            ->whereIn('processed_by', ['S'])
            ->select(
                'application_id',
                'form_name',
                'application_status',
                'processed_by',
                'dt_submit',
                DB::raw("'EB' as source_table")
            );

        // Combine both queries


        // Execute the union and order globally
        $workflows = DB::query()
            ->fromSub($unionQuery, 'combined')
            ->orderBy('dt_submit', 'DESC')
            ->get();



        // Identify source (EA or ESA) using the alias we added
        $sourceTable = $workflows->first()->source_table ?? null;


        $workflows_esa = DB::table('tnelb_eb_applications')
            ->whereIn('application_status', ['F'])
            ->whereIn('processed_by', ['S'])
            ->orderBy('dt_submit', 'DESC')
            ->get();

        // Load view based on type

        return view('admin.auditor.formb.view_formb', compact('workflows_esa'));

    }


        // ------------secretary-------------------------

     public function view_sec_formsa_completed(Request $request)
    {
     $workflows = DB::table('tnelb_eb_applications as ta')
        ->whereIn('ta.application_status', ['F','A', 'RE'])
        ->orderByDesc('updated_at')
            // ->where('ta.form_id', $formId)
            ->select('ta.*')
            ->get();
        // $formId = $request->query('form_id');

       // $workflows = DB::table('tnelb_eb_applications as ta')
       //      ->whereIn('ta.processed_by', ['A', 'SPRE'])
       //      ->orWhere('ta.application_status', 'RF')
       //      // ->where('ta.form_id', $formId)
       //      ->select('ta.*')
       //      ->get();

        return view('admin.secretary.formsa.view_completed_formsa', compact('workflows'));

    }


    public function view_sec_formb_pending(Request $request)
    {

        // return $type;

      $workflows_esa = DB::table('tnelb_eb_applications as ta')
            ->whereIn('ta.processed_by', ['AS', 'SPRE'])
            // ->orWhere('ta.application_status', 'F')
            ->orderByDesc('updated_at')
            // ->where('ta.form_id', $formId)
            ->select('ta.*')

            ->get();

    // $workflows_esa = DB::table('tnelb_eb_applications as ta')
    //         ->whereIn('ta.processed_by', ['A', 'SPRE'])
    //         ->orWhere('ta.application_status', 'F')
    //         ->orderByDesc('updated_at')
    //         // ->where('ta.form_id', $formId)
    //         ->select('ta.*')

    //         ->get();

            // ---------------

    //    $workflows = DB::table('tnelb_eb_applications as ta')
    // ->where(function($q) {
    //     $q->where('ta.processed_by', '=', 'A')
    //       ->orWhereIn('ta.application_status', ['RF', 'F']);
    // })
    // ->orderby('updated_at', 'DESC')
    // // ->where('ta.appl_type', '=', 'N')
    // ->select('ta.*')
    // ->get();

        // $workflows = DB::table('tnelb_eb_applications as ta')
        //     ->where('ta.processed_by', 'A')
        //     ->orWhere('ta.application_status', 'RF')
        //     // ->where('ta.form_id', $formId)
        //     ->select('ta.*')
        //     ->get();

        // dd($workflows->first()->form_name);
        // exit;


    return view('admin.secretary.formb.view_pending_formb', compact('workflows_esa'));





    }


        public function view_sec_formb_completed(Request $request)
    {
     $workflows = DB::table('tnelb_eb_applications as ta')
        ->whereIn('ta.application_status', ['F','A', 'RE'])
        ->orderByDesc('updated_at')
            // ->where('ta.form_id', $formId)
            ->select('ta.*')
            ->get();
        // $formId = $request->query('form_id');

       // $workflows = DB::table('tnelb_eb_applications as ta')
       //      ->whereIn('ta.processed_by', ['A', 'SPRE'])
       //      ->orWhere('ta.application_status', 'RF')
       //      // ->where('ta.form_id', $formId)
       //      ->select('ta.*')
       //      ->get();

       $applicationIds = $workflows->pluck('application_id');

    // Fetch from both tables
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

        return view('admin.secretary.formb.view_completed_formb', compact('workflows',
        'licenses',
        'renewalLicenses'));

    }


    // ---------------president----------------

     public function president_completed_formb(Request $request)
    {
        // dd($request->application_id);
        // exit;
        // return $type;

        $formId = $request->query('form_id');

        $workflows = B_Application::whereIn('application_status', ['A', 'RF'])
                ->where('processed_by', 'PR')
                ->orderBy('updated_at', 'DESC')
                ->get();

                 $applicationIds = $workflows->pluck('application_id');

    // Fetch from both tables
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

        return view('admin.president.formb.formb_completed', compact( 'workflows',
        'licenses',
        'renewalLicenses'));

    }

        public function president_pending_formb(Request $request)
    {


        $formId = $request->query('form_id');


        $workflows_esa = B_Application::whereIn('application_status', ['F', 'RF'])
            ->whereIn('processed_by', ['SE'])
            ->orderBy('updated_at', 'DESC')
            ->get();


            return view('admin.president.formb.formb_pendings', compact('workflows_esa'));

    }


    // ----------completed--------------

     public function applicants_detail_formb_completed($applicant_id)
    {
        $returnForwardUser = null;

        $staff = Auth::user();


        if (!$staff || !$staff->roles_id) {
            return abort(403, 'Unauthorized');
        }





    $applicant = DB::table('tnelb_eb_applications')
        ->leftJoin('payments', 'tnelb_eb_applications.application_id', '=', 'payments.application_id')
        ->where('tnelb_eb_applications.application_id', $applicant_id)
        ->where('payments.payment_status', 'success')
        ->select(
            'tnelb_eb_applications.*',
                'payments.transaction_id',
                'payments.payment_status',
                'payments.amount',
                'payments.payment_mode',
                'payments.created_at as payment_date',
                'payments.application_fee',
                'payments.late_fee',

        )->orderByDesc('dt_submit')
        ->first();




        $formname = $applicant->form_name;

        $license_name = DB::table('mst_licences')->where('form_code', $formname)->first();



        if (!$applicant) {
            return abort(404, 'Applicant not found');
        }


        if ($staff->name === "Supervisor") {

            if ($applicant->application_status == 'RE') {
                $nextForwardUser = DB::table('mst__staffs__tbls')
                    ->where('name', 'Secretary')
                    ->select('name', 'roles_id')
                    ->first();
            } else {
                $nextForwardUser = DB::table('mst__staffs__tbls')
                    ->where('name', 'Assistant Secretary')
                    ->select('name', 'roles_id')
                    ->first();
            }
        }

        if ($staff->name === "Assistant Secretary") {
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



        $cl_ownership_table = DB::table('cl_ownership_table')
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


        $user_entry = DB::table('tnelb_eb_applications')
            ->where('application_id', $applicant_id)
            ->select('*')

        ->first();

        $documents = DB::table('tnelb_applicant_doc_A')
            ->where('application_id', $applicant_id)
            ->select('*')

            ->first();


        $workflows = DB::table('tnelb_workflow_a')
            ->leftjoin('tnelb_eb_applications', 'tnelb_workflow_a.application_id', '=', 'tnelb_eb_applications.application_id')
            ->leftjoin('mst__roles', 'tnelb_workflow_a.forwarded_to', '=', 'mst__roles.id')
            ->where('tnelb_workflow_a.application_id', $applicant_id)
            ->select('tnelb_workflow_a.*', 'mst__roles.name', 'tnelb_eb_applications.form_name', 'tnelb_eb_applications.license_name')
            ->orderBy('tnelb_workflow_a.id', 'desc')
            ->get();

        // var_dump($workflows);die;

        $queries = DB::table('tnelb_query_applicable as qa')
            ->leftJoin('tnelb_eb_applications as ta', 'qa.application_id', '=', 'ta.application_id')
            ->where('qa.application_id', $applicant_id)
            ->where('qa.query_status', 'P')
            ->select('qa.*')
            ->first() ?? null;

        $showbankWarning = false;

        $banksolvency = Tnelb_banksolvency_a::where('application_id', $applicant_id)->where('status', '1')->first();



        $bankValidity = Carbon::parse($banksolvency->bank_validity);

       $equiplist = Mst_equipment_tbl::where('equip_licence_name', 10)
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



                    $view = match ($staff->name) {
                    'President'           => 'admin.completedappls.formb.applicants_formb_completed',
                    'Secretary'           => 'admin.completedappls.formb.applicants_formb_completed',
                    'Supervisor'          => 'admin.completedappls.formb.applicants_formb_completed',
                    'Assistant Secretary' => 'admin.completedappls.formb.applicants_detail_auditor_formb_completed',

                    default               => abort(403, 'Unauthorized'),
                };


        return view($view, compact(
            'applicant',
            'cl_ownership_table',
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

       // ---------------approve Form P application--------------------

    public function approveApplicationformp(Request $request)
    {
        $request->validate([
            'application_id' => 'required|string',
            'processed_by'   => 'required|string',
            'remarks'        => 'nullable|string',
        ]);

        $application = $this->findFormPApplication($request->application_id);

        if (!$application) {
            return response()->json(['error' => 'Application not found.'], 404);
        }

        $login_id  = $application->login_id ?? null;
        $appl_type = strtoupper(trim($application->appl_type ?? 'N')); // N or R

        // Get licence config for Form P (cert_licence_code = 'P')
        $licenceId = (int) DB::table('mst_licences')
            ->where('cert_licence_code', 'P')
            ->value('id');

        if ($licenceId <= 0) {
            return response()->json(['error' => 'Licence configuration for Form P not found.'], 422);
        }


        // If licence already exists for this Form P application (fresh case),
        // do not approve again – inform the user instead.
        if ($appl_type === 'N') {
            $existingLicence = DB::table('cl_forma_lic')

        $existingCcCert = DB::table('cc_form_p_cert')
            ->where('application_id', $request->application_id)
            ->first();
        if ($appl_type === 'N' && $existingCcCert) {
            return response()->json([
                'error' => 'Licence already exists for this application ID, so it cannot be approved again.',
            ], 422);
        }

        // If licence already exists for this Form P application (legacy fresh case),
        if ($appl_type === 'N' && ! $existingCcCert) {
            $existingLicence = DB::table('tnelb_license')

                ->where('application_id', $request->application_id)
                ->first();

            if ($existingLicence) {
                return response()->json([
                    'error' => 'Licence already exists for this application ID, so it cannot be approved again.',
                ], 422);
            }
        }

        DB::beginTransaction();

        try {
            // Determine processed_by based on the current user
            $processed = match (Auth::user()->name) {
                'President' => 'PR',
                'Secretary' => 'SE',
                default     => 'SE',
            };

            // -------------------- BASIC APPLICATION UPDATE --------------------
            $this->updateFormPApplication($request->application_id, [
                    'app_status'   => 'A',
                    'processed_by' => $processed,
                    'updated_at'   => now(),
                ]);

        // -------------------- GET LICENCE VALIDITY MONTHS --------------------

            $today = Carbon::today()->toDateString();
            $licenseperiod = DB::table('mst_fees_validity')
                ->where('licence_id', $licenceId)
                ->where('form_type', $appl_type)
                ->where('status', 1)
                ->whereDate('validity_start_date', '<=', $today)
                ->orderBy('validity_start_date', 'desc')
                ->first();

            if ($licenseperiod) {
                $monthsToAdd = (int) ($licenseperiod->validity ?? 0);
            } else {
                // Fallback: no configured validity → treat as 0 months but still approve
                $monthsToAdd = 0;
                Log::warning('No validity configuration found for Form P licence; defaulting to 0 months', [
                    'licence_id' => $licenceId,
                    'form_type'  => $appl_type,
                ]);
            }

            $issuedAt  = null;
            $expiresAt = null;
            $newSerial = null;
            $certService = app(CompetencyCertificateService::class);
            $certTable = FormPSchema::CERT_TABLE;
            $prefix = $application->license_name ?? $application->certificate_name ?? FormPSchema::LICENSE_NAME;

            if ($appl_type === 'R') {
                $oldApplicationId = $application->old_application ?? null;
                $oldExpiry = null;
                if ($oldApplicationId) {
                    $oldExpiry = DB::table($certTable)->where('application_id', $oldApplicationId)->value('valid_to')
                        ?: DB::table('cl_forma_lic')->where('application_id', $oldApplicationId)->value('expires_at');
                }
                $baseExpiry = $oldExpiry ? Carbon::parse($oldExpiry) : now();
                $issuedAt  = $baseExpiry->copy()->format('Y-m-d H:i:s');
                $expiresAt = $baseExpiry->copy()->addMonths($monthsToAdd)->format('Y-m-d');


                // Prefer existing licence number on the application, otherwise fall back to last licence
                $newSerial = $application->license_number
                    ?? DB::table('cl_forma_lic')
                        ->where('application_id', $oldApplicationId)
                        ->value('license_number');

                if (!$newSerial) {
                    // As a final fallback, generate a fresh licence number
                    $prefix    = $application->license_name ?? 'P';
                    $yearMonth = now()->format('Ym');
                    $lastSerial = DB::table('cl_forma_lic')
                        ->where('license_number', 'LIKE', "L{$prefix}{$yearMonth}%")
                        ->orderBy('license_number', 'desc')
                        ->value('license_number');

                    if ($lastSerial) {
                        $lastNumber = (int) substr($lastSerial, -5);
                        $nextNumber = str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
                    } else {
                        $nextNumber = '00001';
                    }

                    $newSerial = "L{$prefix}{$yearMonth}{$nextNumber}";

                $newSerial = $application->certificate_no
                    ?? $application->license_number
                    ?? ($oldApplicationId ? DB::table($certTable)->where('application_id', $oldApplicationId)->value('certificate_no') : null)
                    ?? ($oldApplicationId ? DB::table('tnelb_license')->where('application_id', $oldApplicationId)->value('license_number') : null);

                if (! $newSerial) {
                    $newSerial = $this->nextFormPCertificateNumber($prefix);

                }
            } else {

                // Fresh → issue today + configured months
                // First check if licence already exists for this application (idempotent behaviour)
                $existingLicence = DB::table('cl_forma_lic')
                    ->where('application_id', $request->application_id)
                    ->first();

                if ($existingLicence) {
                    // Reuse existing licence details, do NOT insert again
                    $newSerial = $existingLicence->license_number;
                    $issuedAt  = $existingLicence->issued_at;
                    $expiresAt = $existingLicence->expires_at;
                } else {
                    $prefix    = $application->license_name ?? 'P';
                    $yearMonth = now()->format('Ym');

                    $lastSerial = DB::table('cl_forma_lic')
                        ->where('license_number', 'LIKE', "L{$prefix}{$yearMonth}%")
                        ->orderBy('license_number', 'desc')
                        ->value('license_number');

                    if ($lastSerial) {
                        $lastNumber = (int) substr($lastSerial, -5);
                        $nextNumber = str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
                    } else {
                        $nextNumber = '00001';
                    }

                    $newSerial = "L{$prefix}{$yearMonth}{$nextNumber}";
                    $issuedAt  = now()->format('Y-m-d H:i:s');
                    $expiresAt = now()->copy()->addMonths($monthsToAdd)->format('Y-m-d');

                    DB::table('cl_forma_lic')->insert([
                        'application_id' => $request->application_id,
                        'license_number' => $newSerial,
                        'issued_by'      => $request->processed_by,
                        'issued_at'      => $issuedAt,
                        'expires_at'     => $expiresAt,
                    ]);

                $existingCc = DB::table($certTable)->where('application_id', $request->application_id)->first();
                if ($existingCc) {
                    $newSerial = $existingCc->certificate_no;
                    $issuedAt  = $existingCc->dateof_issue;
                    $expiresAt = $existingCc->valid_to;
                } else {
                    $newSerial = $this->nextFormPCertificateNumber($prefix);
                    $issuedAt  = now()->format('Y-m-d H:i:s');
                    $expiresAt = now()->copy()->addMonths($monthsToAdd)->format('Y-m-d');

                }
            }

            $certService->issueOrUpdate('P', [
                'application_id' => $request->application_id,
                'certificate_no' => $newSerial,
                'dateof_issue' => $issuedAt,
                'valid_from' => $issuedAt,
                'valid_to' => $expiresAt,
                'cert_status' => 'A',
            ]);

            $certFields = ['updated_at' => now()];
            if (Schema::hasColumn(FormPSchema::META_TABLE, 'certificate_no')) {
                $certFields['certificate_no'] = $newSerial;
            }
            if (Schema::hasColumn(FormPSchema::META_TABLE, 'license_number')) {
                $certFields['license_number'] = $newSerial;
            }
            $this->updateFormPApplication($request->application_id, $certFields);

            $this->recordFormPWorkflow([
                'application_id' => $request->application_id,
                'processed_by'   => $request->processed_by,
                'role_id'        => Auth::user()->roles_id,
                'appl_status'    => 'A',
                'remarks'        => $request->remarks ?? 'No remarks provided',
                'forwarded_to'   => Auth::user()->roles_id,
                'created_at'     => now(),
            ]);

            // -------------------- LICENCE PDF (best-effort) --------------------
            $pdfStoragePath = null;
            try {
                $pdfStoragePath = app(LicensepdfController::class)->generateFormPLicencePdfs($request->application_id);
            } catch (\Throwable $e) {
                Log::warning('Failed to generate Form P licence PDF after approval', [
                    'application_id' => $request->application_id,
                    'error'          => $e->getMessage(),
                ]);
            }

            DB::commit();

            $appId = $request->application_id;

            return response()->json([
                'status'         => 'success',
                'message'        => $appl_type === 'R'
                    ? "Renewal approved till " . date('d/m/Y', strtotime($expiresAt))
                    : "License issued till " . date('d/m/Y', strtotime($expiresAt)),
                'license_number' => $newSerial,
                'issued_at'      => $issuedAt,
                'expires_at'     => $expiresAt,
                // Bilingual PDF (EN + TA in one file); EN/TA routes serve the same document when stored as bilingual.
                'license_pdf_en_url' => $pdfStoragePath ? route('admin.formp.licence.en', ['application_id' => $appId]) : null,
                'license_pdf_ta_url' => $pdfStoragePath ? route('admin.formp.licence.ta', ['application_id' => $appId]) : null,
                'license_pdf_storage_path' => $pdfStoragePath,
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Approval failed',
                'msg'   => $e->getMessage(),
            ], 500);
        }
    }

    private function findFormPApplication(string $applicationId): ?object
    {
        return DB::table(FormPSchema::META_TABLE)->where('application_id', $applicationId)->first()
            ?: DB::table('tnelb_form_p')->where('application_id', $applicationId)->first();
    }

    private function updateFormPApplication(string $applicationId, array $fields): void
    {
        foreach ([FormPSchema::META_TABLE, 'tnelb_form_p'] as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }
            if (! DB::table($table)->where('application_id', $applicationId)->exists()) {
                continue;
            }
            $payload = [];
            foreach ($fields as $col => $val) {
                if (Schema::hasColumn($table, $col)) {
                    $payload[$col] = $val;
                }
            }
            if ($payload !== []) {
                DB::table($table)->where('application_id', $applicationId)->update($payload);
            }
        }
    }

    private function recordFormPWorkflow(array $payload): void
    {
        $applicationId = (string) ($payload['application_id'] ?? '');
        if ($applicationId !== '' && DB::table(FormPSchema::META_TABLE)->where('application_id', $applicationId)->exists()) {
            app(CompetencyWorkflowService::class)->record(FormPSchema::WORKFLOW_TABLE, $payload);

            return;
        }
        SupervisorModel::create($payload);
    }

    private function nextFormPCertificateNumber(string $prefix): string
    {
        $yearMonth = now()->format('Ym');
        $like = 'C'.$prefix.$yearMonth.'%';
        $lastSerial = DB::table(FormPSchema::CERT_TABLE)
            ->where('certificate_no', 'LIKE', $like)
            ->orderByDesc('certificate_no')
            ->value('certificate_no');
        $nextNumber = $lastSerial
            ? str_pad((int) substr((string) $lastSerial, -5) + 1, 5, '0', STR_PAD_LEFT)
            : '00001';

        return 'C'.$prefix.$yearMonth.$nextNumber;
    }

    /**
     * @return array{
     *     applicant: ?object,
     *     edu: \Illuminate\Support\Collection,
     *     exp: \Illuminate\Support\Collection,
     *     documents: \Illuminate\Support\Collection,
     *     photo: mixed,
     *     sign: mixed,
     *     institutes: \Illuminate\Support\Collection
     * }
     */
    private function loadFormPAdminBundle(string $applicantId): array
    {
        $fromCc = true;
        $applicant = DB::table(FormPSchema::META_TABLE)->where('application_id', $applicantId)->first();
        if (! $applicant) {
            $fromCc = false;
            $applicant = DB::table('tnelb_form_p')->where('application_id', $applicantId)->first();
        }

        if ($applicant) {
            if (empty($applicant->applicants_address) && ! empty($applicant->applicant_address)) {
                $applicant->applicants_address = $applicant->applicant_address;
            }
            if (empty($applicant->license_name) && ! empty($applicant->certificate_name)) {
                $applicant->license_name = $applicant->certificate_name;
            }
            if (Schema::hasTable('cc_payments')) {
                $payQuery = DB::table('cc_payments')->where('application_id', $applicantId);
                if (Schema::hasColumn('cc_payments', 'p_id')) {
                    $payQuery->orderByDesc('p_id');
                }
                $pay = $payQuery->first();
                if ($pay) {
                    $applicant->payment_status = $applicant->payment_status ?? ($pay->payment_status ?? null);
                    $applicant->amount = $applicant->amount ?? ($pay->amount_paid ?? $pay->amount ?? null);
                }
            }
        }

        $edu = collect();
        $exp = collect();
        $documents = collect();
        $photo = null;
        $sign = null;

        if ($applicant && $fromCc) {
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
            $aadhaarPath = $proofService->resolveProofPath($applicantId, FormSProofDocumentService::PROOF_AADHAAR);
            if ($aadhaarPath) {
                $applicant->aadhaar_doc = $aadhaarPath;
            }
            $panPath = $proofService->resolveProofPath($applicantId, FormSProofDocumentService::PROOF_PAN);
            if ($panPath) {
                $applicant->pan_doc = $panPath;
                $applicant->pancard_doc = $panPath;
            }
        } elseif ($applicant) {
            $edu = Schema::hasTable('tnelb_applicants_edu')
                ? DB::table('tnelb_applicants_edu')->where('application_id', $applicantId)->get()
                : collect();
            $exp = Schema::hasTable('tnelb_applicants_exp')
                ? DB::table('tnelb_applicants_exp')->where('application_id', $applicantId)->get()
                : collect();
            $photo = TnelbApplicantPhoto::where('application_id', $applicantId)->first();
            $sign = TnelbApplicantsSign::where('application_id', $applicantId)->first();
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

        $institutes = Schema::hasTable('tnelb_applicant_institute')
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
            'documents' => $documents,
            'photo' => $photo,
            'sign' => $sign,
            'institutes' => $institutes,
        ];
    }

    private function loadFormPWorkflows(string $applicationId)
    {
        $workflows = app(CompetencyWorkflowService::class)->historyForApplication('P', $applicationId);
        if ($workflows->isNotEmpty()) {
            return $workflows;
        }

        if (! Schema::hasTable('tnelb_workflow')) {
            return collect();
        }

        return DB::table('tnelb_workflow')
            ->where('application_id', $applicationId)
            ->orderByDesc('id')
            ->get();
    }

    private function latestFormPWorkflow(string $applicationId): ?object
    {
        if (Schema::hasTable(FormPSchema::WORKFLOW_TABLE)
            && DB::table(FormPSchema::WORKFLOW_TABLE)->where('application_id', $applicationId)->exists()
        ) {
            return DB::table(FormPSchema::WORKFLOW_TABLE)
                ->where('application_id', $applicationId)
                ->orderByDesc('w_id')
                ->first();
        }

        return SupervisorModel::where('application_id', $applicationId)->orderByDesc('id')->first();
    }

    private function loadFormPQueries(string $applicationId, bool $pendingOnly = false)
    {
        $q = DB::table('tnelb_query_applicable')
            ->where('application_id', $applicationId);
        if ($pendingOnly) {
            $q->where('query_status', 'P');
        }

        return $q->orderByDesc('id')->get();
    }
}
