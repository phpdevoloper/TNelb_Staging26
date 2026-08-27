<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Tnelb_CC_Digitization;
use App\Services\FileUploadService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FormWDigitizationController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    protected $today, $dbNow;

    public function __construct(protected FileUploadService $fileUpload)
    {
        parent::__construct(); // Call BaseController constructor

        $this->today = Carbon::today()->toDateString();

        $this->dbNow = DB::selectOne(
            "SELECT date_trunc('second', NOW()::timestamp) AS db_now"
        )->db_now;
    }

    public function index(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('logout');
        }
        $authUser = Auth::user();

        $request->validate([
            'form' => 'required|in:W',
        ]);

        $form = $request->form;

        $user = [
            'user_id' => $authUser->login_id,
            'salutation' => $authUser->salutation,
            'applicant_name' => $authUser->first_name . ' ' . $authUser->last_name,
        ];
        return view('user_login.digitization.apply-form-w_d', compact('user', 'form'));
    }

    /**
     * Look up a previously enrolled Form W digitization certificate row.
     * Form W (Wireman) has no contractor/QC step, so this normally returns null,
     * but the endpoint is kept to mirror the Form S flow used by the shared views.
     */
    public function getContractorDetails($loginId, $tempAppId = null, $applicationId = null)
    {
        $query = Tnelb_CC_Digitization::where('login_id', $loginId);

        if (!empty($applicationId)) {
            $query->where('application_id', $applicationId);
        } elseif (!empty($tempAppId)) {
            $query->where('temp_app_id', $tempAppId);
        } else {
            $query->where('form_name', 'W');
        }

        $row = $query->orderByDesc('id')->first();

        if (!$row || $row->licence_no === null || $row->licence_no === '') {
            return null;
        }

        return [
            'cl_type' => $row->cl_type,
            'licence_no' => $row->licence_no,
            'contractor_name' => $row->contractor_name,
        ];
    }

    public function fetchContractorDetails(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['contractorDetails' => null], 401);
        }

        $contractorDetails = $this->getContractorDetails(
            Auth::user()->login_id,
            $request->query('temp_app_id'),
            $request->query('application_id')
        );

        return response()->json([
            'contractorDetails' => $contractorDetails,
        ]);
    }

    public function storeDigitization(Request $request)
    {
        $request->validate([
            'ccnumber'   => 'required|digits_between:1,5',
            'fissue'     => 'required|date',
            'from_date'  => 'required|date|after_or_equal:fissue',
            'to_date'    => 'required|date|after_or_equal:from_date',
            'cc_doc'     => 'required|mimes:pdf|max:250',
            'form_name'  => 'required|in:W',
        ], [
            'from_date.after_or_equal' => 'Date of First Issue must be less than or equal to Validity From date.',
        ]);

        $toDate = Carbon::parse($request->to_date);
        $allowedDate = $toDate->copy()->addYear();

        if (Carbon::today()->gt($allowedDate)) {
            return response()->json([
                'errors' => [
                    'to_date' => [
                        'To date must be less than or equal to 1 year from today.'
                    ]
                ]
            ], 422);
        }

        $now = db_now();
        $original_name = null;
        $fileName = 'pending';

        $record = DB::transaction(function () use (
            $request,
            $now,
            &$original_name,
            &$fileName
        ) {
            $row = Tnelb_CC_Digitization::create([
                'login_id'         => Auth::user()->login_id,
                'temp_app_id'      => 'TEMP' . date('Ymd') . '0000',
                'form_name'        => $request->form_name,
                'cert_name'        => $request->cert_name,
                'ccnumber'         => $request->ccnumber,
                'fissue'           => $request->fissue,
                'from_date'        => $request->from_date,
                'to_date'          => $request->to_date,
                'qc'               => 0,
                'qsc'              => 0,
                'cl_type'          => null,
                'licence_no'       => null,
                'contractor_name'  => null,
                'cc_doc'           => 'pending',
                'created_at'       => $now,
                'updated_at'       => $now,
                'qc_det'           => 0,
            ]);

            $temp_app_id = 'TEMP' . date('Ymd') . str_pad($row->id, 4, '0', STR_PAD_LEFT);

            if ($request->hasFile('cc_doc')) {
                $file = $request->file('cc_doc');
                $original_name = $file->getClientOriginalName();
                $extension = $file->getClientOriginalExtension();
                $fileName = $temp_app_id . '_' . time() . '_' . $request->cert_name . '.' . $extension;
                $fileName = $this->fileUpload->upload($file, 'uploads/digitization/scc', $fileName);
            }

            $row->update([
                'temp_app_id'   => $temp_app_id,
                'cc_doc'        => $fileName,
                'original_name' => $original_name,
                'updated_at'    => $now,
            ]);

            return $row->fresh();
        });

        return response()->json([
            'status'            => 200,
            'message'           => 'Digitization details saved successfully.',
            'temp_app_id'       => $record->temp_app_id,
            'digitization_id'   => $record->id,
            'contractorDetails' => null,
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
        //
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
