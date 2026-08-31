<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\CC_Digitisation_Map;
use App\Models\Tnelb_CC_Digitization;
use App\Services\FileUploadService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FormSDigitizationController extends BaseController
{

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
            'form' => 'required|in:S,W,H,P',
        ]);

        $form = $request->form;

        $user = [
            'user_id' => $authUser->login_id,
            'salutation' => $authUser->salutation,
            'applicant_name' => $authUser->first_name . ' ' . $authUser->last_name,
        ];
        $contractorDetails = $this->getContractorDetails($authUser->login_id);

        return view('user_login.digitization.apply-form-s_d', compact('user', 'form', 'contractorDetails'));
    }

    public function getContractorDetails($loginId, $tempAppId = null, $applicationId = null)
    {
        $query = Tnelb_CC_Digitization::where('login_id', $loginId);

        if (!empty($applicationId)) {
            $query->where('application_id', $applicationId);
        } elseif (!empty($tempAppId)) {
            $query->where('temp_app_id', $tempAppId);
        } else {
            $query->where('form_name', 'S');
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
            'qc_det'         => 'required',
            'cc_doc'     => 'required|mimes:pdf|max:250',
            'form_name'       => 'required|in:S,W,H,P',
            'cert_name'       => 'required|in:C,B,H,P',
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

        if ($request->qc_det == 'yes') {

            $request->validate([
                'cl_type'         => 'required',
                'licence_no'      => 'required|digits_between:1,5',
                'contractor_name' => 'required|max:100',
                'qc_doc'          => 'required|mimes:pdf|max:250',
            ], [
                'licence_no.digits_between' => 'Licence Number must contain numbers only (1 to 5 digits).',
            ]);
        }
        
        
        $now = db_now();
        $original_name = null;
        $fileName = 'pending';
        $qcFileName = null;
        $qcOriginalName = null;

        $record = DB::transaction(function () use (
            $request,
            $now,
            &$original_name,
            &$fileName,
            &$qcFileName,
            &$qcOriginalName
        ) {

            $qc = 0;
            $qsc = 0;
            $qc_det = 0;

            $qc_det = $request->qc_det === 'yes' ? 1 : 0;

            if (!empty($request->cl_type)) {

                if($request->cl_type == 'EA') {
                    $qc = 1;
                } elseif ($request->cl_type == 'ESA') {
                    $qsc = 1;
                }
            }

        
            $row = Tnelb_CC_Digitization::create([
                'login_id'         => Auth::user()->login_id,
                'temp_app_id'      => 'TEMP' . date('Ymd') . '0000',
                'form_name'        => $request->form_name,
                'cert_name'        => $request->cert_name,
                'ccnumber'         => $request->ccnumber,
                'fissue'           => $request->fissue,
                'from_date'        => $request->from_date,
                'to_date'          => $request->to_date,
                'qc'               => $qc,
                'qsc'              => $qsc,
                'cl_type'          => $request->cl_type ?? null,
                'licence_no'       => $request->licence_no ?? null,
                'contractor_name'  => $request->contractor_name ?? null,
                'cc_doc'           => 'pending',
                'created_at'       => $now,
                'updated_at'       => $now,
                'qc_det'           => $qc_det ?? 0,
                'cc_type'          => $request->cert_name,
            ]);

            $temp_app_id = 'TEMP' . date('Ymd') . str_pad($row->id, 4, '0', STR_PAD_LEFT);

            if ($request->hasFile('cc_doc')) {
                $file = $request->file('cc_doc');
                $original_name = $file->getClientOriginalName();
                $extension = $file->getClientOriginalExtension();
                $fileName = $temp_app_id . '_' . time() . '_' . $request->cert_name . '.' . $extension;
                $fileName = $this->fileUpload->upload($file, 'uploads/digitization/scc', $fileName);
            }

            if ($request->hasFile('qc_doc')) {
                $qcFile = $request->file('qc_doc');
                $qcOriginalName = $qcFile->getClientOriginalName();
                $extension = $qcFile->getClientOriginalExtension();
                $qcFileName = $temp_app_id . '_QC_' . time() . '.' . $extension;
                $qcFileName = $this->fileUpload->upload($qcFile, 'uploads/digitization/qc/', $qcFileName);
            }

            CC_Digitisation_Map::create([
                'application_id' => $request->application_id,
                'old_cc_no' => $request->ccnumber,
                'created_at' => $now,
                'temp_id' => $temp_app_id,
                'cc_type' => $request->cert_name,
            ]);

            $row->update([
                'temp_app_id'      => $temp_app_id,
                'cc_doc'           => $fileName,
                'original_name'    => $original_name,
                'qc_doc'           => $qcFileName,
                'updated_at'       => $now,
            ]);

            return $row->fresh();
        });

        $contractorDetails = null;
        if (!empty($record->licence_no)) {
            $contractorDetails = [
                'cl_type' => $record->cl_type,
                'licence_no' => $record->licence_no,
                'contractor_name' => $record->contractor_name,
            ];
        }

        return response()->json([
            'status'            => 200,
            'message'           => 'Digitization details saved successfully.',
            'temp_app_id'       => $record->temp_app_id,
            'digitization_id'   => $record->id,
            'contractorDetails' => $contractorDetails,
        ]);
    }
}
