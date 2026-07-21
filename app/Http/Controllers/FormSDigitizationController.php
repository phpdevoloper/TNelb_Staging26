<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Tnelb_CC_Digitization;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FormSDigitizationController extends BaseController
{

    protected $today, $dbNow;

    public function __construct()
    {
        parent::__construct(); // Call BaseController constructor

        $this->today = Carbon::today()->toDateString();

        $this->dbNow = DB::selectOne(
            "SELECT date_trunc('second', NOW()::timestamp) AS db_now"
        )->db_now;
    }

    public function index()
    {

        if (!Auth::check()) {
            return redirect()->route('logout');
        }
        $authUser = Auth::user();

        $user = [
            'user_id' => $authUser->login_id,
            'salutation' => $authUser->salutation,
            'applicant_name' => $authUser->first_name . ' ' . $authUser->last_name,
        ];
        return view('user_login.digitization.apply-form-s_d', compact('user'));
    }



    public function storeDigitization(Request $request)
    {
        $request->validate([
            'ccnumber'   => 'required|digits_between:1,5',
            'fissue'     => 'required|date',
            'from_date'  => 'required|date|after_or_equal:fissue',
            'to_date'    => 'required|date|after_or_equal:from_date',
            'qc_det'         => 'required',
            'cc_doc'     => 'required|mimes:pdf|max:250'
        ], [
            'from_date.after_or_equal' => 'Date of First Issue must be less than or equal to Validity From date.',
        ]);

        $toDate = Carbon::parse($request->to_date);
        $allowedDate = $toDate->copy()->addYear();

        if (Carbon::today()->gt($allowedDate)) {

            return response()->json([
                'errors' => [
                    'to_date' => [
                        'Apply New Application Validity Period including Renewal exceeds limits'
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

        $certificate = DB::table('wcert')
            ->select('certcode', 'appname', 'add1', 'add2', 'add3')
            ->where('certno', $request->ccnumber)
            ->whereDate('issuedt', $request->fissue)
            ->whereDate('fromdate', $request->from_date)
            ->whereDate('vdate', $request->to_date)
            ->first();

        if (!$certificate) {
            $certificate = DB::table('whcert')
                ->select('certcode', 'appname', 'add1', 'add2', 'add3')
                ->where('certno', $request->ccnumber)
                ->whereDate('issuedt', $request->fissue)
                ->whereDate('fromdate', $request->from_date)
                ->whereDate('vdate', $request->to_date)
                ->first();
        }

        if (!$certificate) {
            $certificate = DB::table('scert')
                ->select('certcode', 'appname', 'add1', 'add2', 'add3')
                ->where('certno', $request->ccnumber)
                ->whereDate('issuedt', $request->fissue)
                ->whereDate('fromdate', $request->from_date)
                ->whereDate('vdate', $request->to_date)
                ->first();
        }

        // -----------------------------------
        // Existing Save Logic
        // -----------------------------------

        $qc_det = $request->qc_det === 'yes' ? 1 : 0;

        $qc = 0;
        $qsc = 0;

        if (!empty($request->cl_type)) {

            if ($request->cl_type == 'EA') {
                $qc = 1;
            } elseif ($request->cl_type == 'ESA') {
                $qsc = 1;
            }
        }

        $now = db_now();
        $original_name = null;
        $fileName = 'pending';
        $qcFileName = null;
        $qcOriginalName = null;

        $record = DB::transaction(function () use (
            $request,
            $now,
            $qc_det,
            $qc,
            &$original_name,
            &$fileName,
            &$qcFileName,
            &$qcOriginalName
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
                'qc_det'           => $qc_det,
                'qc'               => $qc,
                'cl_type'          => $request->cl_type ?? null,
                'licence_no'       => $request->licence_no ?? null,
                'contractor_name'  => $request->contractor_name ?? null,
                'cc_doc'           => 'pending',
                'created_at'       => $now,
                'updated_at'       => $now,
            ]);

            $temp_app_id = 'TEMP' . date('Ymd') . str_pad($row->id, 4, '0', STR_PAD_LEFT);

            if ($request->hasFile('cc_doc')) {
                $file = $request->file('cc_doc');
                $original_name = $file->getClientOriginalName();
                $extension = $file->getClientOriginalExtension();
                $fileName = $temp_app_id . '_' . time() . '_' . $request->cert_name . '.' . $extension;
                $file->move(public_path('uploads/digitization/scc/'), $fileName);
            }

            if ($request->hasFile('qc_doc')) {
                $qcFile = $request->file('qc_doc');
                $qcOriginalName = $qcFile->getClientOriginalName();
                $extension = $qcFile->getClientOriginalExtension();
                $qcFileName = $temp_app_id . '_QC_' . time() . '.' . $extension;
                $qcFile->move(public_path('uploads/digitization/qc/'), $qcFileName);
            }

            $row->update([
                'temp_app_id'      => $temp_app_id,
                'cc_doc'           => $fileName,
                'original_name'    => $original_name,
                'qc_doc'           => $qcFileName,
                'qc_original_name' => $qcOriginalName,
                'updated_at'       => $now,
            ]);

            return $row->fresh();
        });

        return response()->json([
            'status'          => 200,
            'message'         => 'Digitization details saved successfully.',
            'temp_app_id'     => $record->temp_app_id,
            'digitization_id' => $record->id,
            'appname'         => $certificate->appname ?? '',
            'address'         => $certificate
                ? implode(' ', array_filter([
                    $certificate->add1 ?? '',
                    $certificate->add2 ?? '',
                    $certificate->add3 ?? '',
                ]))
                : '',
            'certcode'        => $certificate->certcode ?? '',
            'is_matched'      => $certificate ? 1 : 0,
        ]);
    }
}
