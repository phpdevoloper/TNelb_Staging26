<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\Mst_checklist;
use App\Models\Admin\Mst_filepath_module;
use App\Models\MstLicence;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ChecklistController extends Controller
{


    protected $userId;
    protected $today;
    public function __construct()
    {


        $this->middleware(function ($request, $next) {
            if (!Auth::check()) {
                // Not logged in
                return redirect()->route('login');
            }

            // If logged in, store the user ID
            $this->userId = Auth::id();

            return $next($request);
        });

        $this->today = now()->toDateString();
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $checklist = Mst_checklist::all();

        $all_licences = MstLicence::where('status', 1)

            ->orderBy('created_at', 'asc')
            ->get();

        $all_formmodule = Mst_filepath_module::where('status', 1)
            ->get();

        $checklist_data = DB::table('mst_checklists as e')
            ->leftJoin('mst_licences as ml', 'ml.id', '=', 'e.cert_license_id')
            // ->where('e.status', 1)
            ->orderByDesc('e.created_at')
            ->select(
                'e.*',
                'ml.licence_name'
            )
            ->get();

        return view('admincms.checklist.index', compact('checklist', 'all_licences', 'all_formmodule', 'checklist_data'));
    }

    public function updateStatus(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
            'status' => 'required|in:0,1',
        ]);

        DB::table('mst_checklists')
            ->where('id', $request->id)
            ->update([
                'status' => $request->status,
                'updated_at' => now(),
            ]);

        return response()->json([
            'status' => true,
            'message' => $request->status ? 'Checklist activated successfully.' : 'Checklist deactivated successfully.',
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function store(Request $request)
    {

        // dd($request->all()); exit;
        $validator = Validator::make($request->all(), [
            'cert_license_id' => 'required|integer',
            'appl_type'       => 'required|in:N,R,D,A',
            'checklist_name'  => 'required|string|max:255',
        ], [
            'cert_license_id.required' => 'Please choose the Licence Name.',
            'appl_type.required'       => 'Please choose the Application Type.',
            'checklist_name.required'  => 'Please enter the Checklist Name.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ]);
        }

        // Check duplicate
        $exists = DB::table('mst_checklists')
            ->where('cert_license_id', $request->cert_license_id)
            ->where('appl_type', strtoupper($request->appl_type))
            ->where('checklist_name', strtoupper(trim($request->checklist_name)))
            ->where('status', 1)
            ->exists();

        if ($exists) {
            return response()->json([
                'status' => false,
                'message' => 'Checklist already exists.'
            ]);
        }

        $checklist = DB::table('mst_checklists')->insert([
            'cert_license_id' => $request->cert_license_id,
            'appl_type'       => strtoupper($request->appl_type),
            'checklist_name'  => strtoupper(trim($request->checklist_name)),
            'updated_by'  => $this->userId,
            'status'      => 1,
            'ipaddress'   => $request->ip(),
        ]);

        $row = DB::table('mst_checklists as e')
            ->leftJoin('mst_licences as ml', 'ml.id', '=', DB::raw('CAST(e.cert_license_id AS INTEGER)'))
            ->where('e.id', $checklist)
            ->select('e.*', 'ml.licence_name')
            ->first();

        return response()->json([
            'status' => true,
            'message' => 'Checklist added successfully.',
            'data' => $row
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
