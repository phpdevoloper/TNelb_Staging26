<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\DB;
use App\Models\Admin\SupervisorModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Mst_Form_s_w;

use App\Models\Admin\WorkflowA;

use App\Models\EA_Application_model;
use Carbon\Carbon;

class ApplicationController extends Controller
{

    protected $today,$dbNow;
    public function __construct()
    {
        $this->today = Carbon::today()->toDateString();
        $this->dbNow  = DB::selectOne("SELECT date_trunc('second', NOW()::timestamp) AS db_now")->db_now;


    }
    

    public function get_wh_apps()
    {
        $userRole = Auth::user()->roles_id; // Supervisor Role ID
        $assignedFormID = 3;
        $forms = self::getForms($assignedFormID);
    
        $new_applications = DB::table('tnelb_application_tbl')
        ->where('form_id', $assignedFormID) // Filter by Form S
        ->where('appl_type', 'N') // Filter by Form S
        ->where('payment_status', 'payment') // Filter by Form S
        ->whereIn('status', ['P','RE']) // Only show new applications
        ->select('*', DB::raw("EXISTS (SELECT 1 FROM tnelb_workflow tw WHERE tw.application_id = tnelb_application_tbl.application_id AND tw.appl_status = 'QU') AS has_return_history"))
        ->orderByDesc('id')
        ->get();


        $renewal = DB::table('tnelb_application_tbl')
        ->where('form_id', $assignedFormID) // Filter by Form S
        ->where('appl_type', 'R') // Filter by Form S
        ->whereIn('status', ['P','RE']) // Only show new applications
        ->select('*', DB::raw("EXISTS (SELECT 1 FROM tnelb_workflow tw WHERE tw.application_id = tnelb_application_tbl.application_id AND tw.appl_status = 'QU') AS has_return_history"))
        ->get();

        $returned_applications = DB::table('tnelb_application_tbl')
        ->where('form_id', $assignedFormID)
        ->where('status', 'QU')
        ->whereIn('payment_status', ['payment', 'paid'])
        ->select('*')
        ->orderByDesc('id')
        ->get();
    
        return view('admin.supervisor.view', compact('new_applications','renewal','returned_applications','forms'));
    }

    public function get_applications()
    {
        $userRole = Auth::user()->roles_id; // Supervisor Role ID
        $assignedFormID = Auth::user()->form_id;
        $forms = self::getForms($assignedFormID);
    
        $new_applications = DB::table('tnelb_application_tbl')
        ->where('form_id', $assignedFormID) // Filter by Form S
        ->where('appl_type', 'N') // Filter by Form S
        ->where('payment_status', 'payment') // Filter by Form S
        ->whereIn('status', ['P','RE']) // Only show new applications
        ->select('*', DB::raw("EXISTS (SELECT 1 FROM tnelb_workflow tw WHERE tw.application_id = tnelb_application_tbl.application_id AND tw.appl_status = 'QU') AS has_return_history"))
        ->orderByDesc('id')
        ->get();


        $renewal = DB::table('tnelb_application_tbl')
        ->where('form_id', $assignedFormID) // Filter by Form S
        ->where('appl_type', 'R') // Filter by Form S
        ->whereIn('status', ['P','RE']) // Only show new applications
        ->select('*', DB::raw("EXISTS (SELECT 1 FROM tnelb_workflow tw WHERE tw.application_id = tnelb_application_tbl.application_id AND tw.appl_status = 'QU') AS has_return_history"))
        ->orderByDesc('id')
        ->get();


        $returned_applications = DB::table('tnelb_application_tbl')
            ->where('form_id', $assignedFormID)
            // ->where('status', 'QU')
            ->whereIn('payment_status', ['payment', 'paid'])
            ->where(function ($q) {
                $q->where('status', 'QU')
                    ->orWhereRaw("(status IN ('P','RE') AND EXISTS (SELECT 1 FROM tnelb_workflow tw WHERE tw.application_id = tnelb_application_tbl.application_id AND tw.appl_status = 'QU'))");
            })
            ->select('*')
            ->orderByDesc('id')
            ->get();

        // var_dump($renewal);die;
    
        return view('admin.supervisor.view', compact('new_applications','renewal','returned_applications','forms'));
    }

    public function view_application(){
        
    }

    public function getForms($form_id){
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


    public function returntoSupervisor(Request $request){

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
        


        $formType = DB::table('tnelb_application_tbl')
                        ->where('application_id', $request->application_id)
                        ->select('form_id')
                        ->first();

                        
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

            // Insert data into tnelb_workflow table
            SupervisorModel::create([ // Ensure this is the correct model
                'application_id' => $request->application_id,
                'appl_status'    => 'RE', // Forwarded
                'processed_by'   => $request->return_by,
                'forwarded_to'   => $request->forwarded_to,
                'role_id'        => $staff->roles_id,
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
            DB::table('tnelb_application_tbl')
                ->where('application_id', $request->application_id)
                ->update([
                    'status'       => 'RE', // Role-based forwarding
                    'processed_by' => $processed_by, // Role-based forwarding
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
        // dd($request->all());
        // exit;
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

        $applicant = DB::table('tnelb_application_tbl')
            ->where('application_id', $request->application_id)
            ->first();

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

        $queryList = $request->return_applicant_query;
        $queryTypeJson = is_array($queryList) ? json_encode($queryList) : json_encode([$queryList]);
        $staff_queryTypeJson = is_array($request->staff_queryType) ? json_encode($request->staff_queryType) : json_encode([$request->staff_queryType]);
        $remarks = $request->remarks ?? '';
        $staff_remarks = $request->staff_remarks ?? '';

        // Record the return-to-applicant entry for audit
        DB::table('tnelb_return_to_applicant_log')->insert([
            'application_id'        => $request->application_id,
            'returned_by_staff_id'  => $staffID,
            'returned_by_role'       => $processed_by,
            'returned_by_name'       => $staff->name ?? null,
            'query_types'           => $queryTypeJson,
            'remarks'               => $remarks,
            'created_at'            => $this->dbNow
        ]);

        SupervisorModel::create([
            'application_id' => $request->application_id,
            'appl_status'    => 'QU',
            'processed_by'   => $processed_by,
            'forwarded_to'   => null, // Not forwarded to any role; returned to applicant
            'role_id'        => $staff->roles_id, // Role id from mst_roles (FK), not user id
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

        DB::table('tnelb_application_tbl')
            ->where('application_id', $request->application_id)
            ->update([
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
                ->whereIn('status', ['P','RE']) 
                ->where('appl_type', '2')
                ->select('*')
                ->get();
            break;

            case $userRole == '2':
                $application_details = DB::table('tnelb_application_tbl as ta')
                ->where('ta.status',['F','RF'])
                ->where('ta.processed_by','S')
                ->orWhere('ta.processed_by','S2')
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


        DB::table('tnelb_workflow')->insert([
            'application_id' => $request->application_id,
            'processed_by'   => $request->action_by,
            'role_id'        => Auth::user()->roles_id, // Current user role (Secretary)
            'appl_status'    => $request->appl_status,
            'reject_reason' => $request->reason??'',
            'created_at'     => now(),
            'login_id'         =>$request->login_id
        ]);


        // Also update applications table (set status to Rejected)
        Mst_Form_s_w::where('application_id', $request->input('application_id'))
            ->update([
                'status'     => $request->appl_status, // RJ
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
        $userRole = Auth::user()->roles_id; // Get logged-in user's role
        $assignedFormID = Auth::user()->form_id;

        $workflows = DB::table('tnelb_application_tbl')
            ->where('form_id', $assignedFormID) // Filter by Form S
            ->where('status', 'RJ') // Only show new applications
            // ->whereIn('processed_by', ['S', 'A', 'SE', 'PR']) // Only show new applications
            ->select('*')
            ->get();

        return view('admin.supervisor.rejected', compact('workflows', 'page_title'));
    }


    // returnforma application-------------------------

  public function returntoapplicantForma(Request $request){

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
