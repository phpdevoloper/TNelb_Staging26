<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FormAprocessController extends Controller
{
    public function checkCompetencyCertificateadmin(Request $request)
    {
        $request->validate([
            'certificate_no' => 'required',
            'dateof_issue'   => 'required|date',
            'valid_from'     => 'required|date',
            'valid_to'       => 'required|date',
        ]);

        // Find certificate
        $certificate = DB::table('cc_forms_cert')
            ->where('certificate_no', $request->certificate_no)
            ->whereDate('dateof_issue', $request->dateof_issue)
            ->whereDate('valid_from', $request->valid_from)
            ->whereDate('valid_to', $request->valid_to)
            ->orderBy('cc_id', 'DESC')
            ->first();

        // Certificate not found
        if (!$certificate) {

            return response()->json([
                'status' => false,
                'message' => 'Certificate details not found - Enter valid details.'
            ]);
        }

        // Application ID
        $application_id = $certificate->application_id;

        // Get all experience records
        $cc_exp = DB::table('cc_exp')
            ->where('application_id', $application_id)
            ->orderBy('exp_id', 'DESC')
            ->get();

        // Certificate details
        $certificateData = [
            'certificate_no' => $certificate->certificate_no,
            'dateof_issue'   => $certificate->dateof_issue,
            'valid_from'     => $certificate->valid_from,
            'valid_to'       => $certificate->valid_to,
        ];

        // Certificate found but no experience
        if ($cc_exp->isEmpty()) {

            return response()->json([
                'status' => true,
                'message' => 'Certificate found, but no experience data found.',
                'application_id' => $application_id,
                'certificate' => $certificateData,
                'data' => []
            ]);
        }

        // Certificate + experience found
        return response()->json([
            'status' => true,
            'message' => 'Certificate and experience data found.',
            'application_id' => $application_id,
            'certificate' => $certificateData,
            'data' => $cc_exp
        ]);
    }


    // -----------other staff----------------


    public function checkCompetencyotherCertificateadmin(Request $request)
    {
        $request->validate([
            'certificate_no' => 'required',
            'dateof_issue'   => 'required|date',
            'valid_from'     => 'required|date',
            'valid_to'       => 'required|date',
        ]);

        // -------------------------------------------------
        // STEP 1: Find certificate in cc_forms_cert
        // -------------------------------------------------

        $certificate = DB::table('cc_forms_cert')
            ->where('certificate_no', $request->certificate_no)
            ->whereDate('dateof_issue', $request->dateof_issue)
            ->whereDate('valid_from', $request->valid_from)
            ->whereDate('valid_to', $request->valid_to)
            // ->orderBy('cc_id', 'DESC')
            ->first();


        // -------------------------------------------------
        // STEP 2: If not found, check cc_form_w_cert
        // -------------------------------------------------

        if (!$certificate) {

            $certificate = DB::table('cc_form_w_cert')
                ->where('certificate_no', $request->certificate_no)
                ->whereDate('dateof_issue', $request->dateof_issue)
                ->whereDate('valid_from', $request->valid_from)
                ->whereDate('valid_to', $request->valid_to)
                // ->orderBy('id', 'DESC')
                ->first();
        }


        // -------------------------------------------------
        // STEP 3: Certificate not found
        // -------------------------------------------------

        if (!$certificate) {

            return response()->json([
                'status' => false,
                'message' => 'Certificate details not found - Enter valid details.'
            ]);
        }


        // -------------------------------------------------
        // STEP 4: Application ID
        // -------------------------------------------------

        $application_id = $certificate->application_id;


        // -------------------------------------------------
        // STEP 5: Get all experience records
        // -------------------------------------------------

        $cc_exp = DB::table('cc_exp')
            ->where('application_id', $application_id)
            ->orderBy('exp_id', 'DESC')
            ->get();


        // -------------------------------------------------
        // STEP 6: Certificate details
        // -------------------------------------------------

        $certificateData = [
            'certificate_no' => $certificate->certificate_no,
            'dateof_issue'   => $certificate->dateof_issue,
            'valid_from'     => $certificate->valid_from,
            'valid_to'       => $certificate->valid_to,
        ];


        // -------------------------------------------------
        // STEP 7: Certificate found but no experience
        // -------------------------------------------------

        if ($cc_exp->isEmpty()) {

            return response()->json([
                'status' => true,
                'message' => 'Certificate found, but no experience data found.',
                'application_id' => $application_id,
                'certificate' => $certificateData,
                'data' => []
            ]);
        }


        // -------------------------------------------------
        // STEP 8: Certificate + experience found
        // -------------------------------------------------

        return response()->json([
            'status' => true,
            'message' => 'Certificate and experience data found.',
            'application_id' => $application_id,
            'certificate' => $certificateData,
            'data' => $cc_exp
        ]);
    }
}
