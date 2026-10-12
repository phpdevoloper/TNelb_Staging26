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
                'status'  => false,
                'message' => 'Certificate details not found - Enter valid details.'
            ]);
        }

        // Application ID
        $application_id = $certificate->application_id;

        // Get all experience records
        $cc_exp = DB::table('cc_exp')
            ->where('application_id', $application_id)
            ->orderBy('exp_id', 'DESC')
            ->get()
            ->map(function ($item) {
                foreach (
                    [
                        'from_date',
                        'to_date',
                        'date_from',
                        'date_to',
                        'start_date',
                        'end_date',
                    ] as $field
                ) {
                    if (isset($item->$field) && !empty($item->$field)) {
                        $item->$field = \Carbon\Carbon::parse($item->$field)->format('d-m-Y');
                    }
                }

                return $item;
            });

        // Get applicant details
        $applicant_details = DB::table('cc_form_s_meta')
            ->where('application_id', $application_id)
            ->first();

        // Applicant details response
        $applicantData = $applicant_details ? [
            'applicant_name' => $applicant_details->applicant_name ?? '-',
            'fathers_name'   => $applicant_details->fathers_name ?? '-',
            'dob'            => !empty($applicant_details->d_o_b)
                ? \Carbon\Carbon::parse($applicant_details->d_o_b)->format('d-m-Y')
                : '-',
            'age'            => $applicant_details->age ?? '-',
        ] : [
            'applicant_name' => '-',
            'fathers_name'   => '-',
            'dob'            => '-',
            'age'            => '-',
        ];

        // Certificate details: dates in dd-mm-yyyy
        $certificateData = [
            'certificate_no' => $certificate->certificate_no,
            'dateof_issue'   => !empty($certificate->dateof_issue)
                ? \Carbon\Carbon::parse($certificate->dateof_issue)->format('d-m-Y')
                : '-',
            'valid_from'     => !empty($certificate->valid_from)
                ? \Carbon\Carbon::parse($certificate->valid_from)->format('d-m-Y')
                : '-',
            'valid_to'       => !empty($certificate->valid_to)
                ? \Carbon\Carbon::parse($certificate->valid_to)->format('d-m-Y')
                : '-',
        ];

        // Certificate found but no experience
        if ($cc_exp->isEmpty()) {
            return response()->json([
                'status'            => true,
                'message'           => 'Certificate found, but no experience data found.',
                'application_id'    => $application_id,
                'certificate'       => $certificateData,
                'applicant_details' => $applicantData,
                'data'              => [],
            ]);
        }

        // Certificate and experience found
        return response()->json([
            'status'            => true,
            'message'           => 'Certificate and experience data found.',
            'application_id'    => $application_id,
            'certificate'       => $certificateData,
            'applicant_details' => $applicantData,
            'data'              => $cc_exp,
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

        // Get all experience records
        $cc_exp = DB::table('cc_exp')
            ->where('application_id', $application_id)
            ->orderBy('exp_id', 'DESC')
            ->get()
            ->map(function ($item) {
                foreach (
                    [
                        'from_date',
                        'to_date',
                        'date_from',
                        'date_to',
                        'start_date',
                        'end_date',
                    ] as $field
                ) {
                    if (isset($item->$field) && !empty($item->$field)) {
                        $item->$field = \Carbon\Carbon::parse($item->$field)->format('d-m-Y');
                    }
                }

                return $item;
            });

        // Get applicant details
        $applicant_details = DB::table('cc_form_s_meta')
            ->where('application_id', $application_id)
            ->first();

        if (!$applicant_details) {

            $applicant_details = DB::table('cc_form_w_cert')
                ->where('application_id', $application_id)
                ->first();
        }

        // Applicant details response
        $applicantData = $applicant_details ? [
            'applicant_name' => $applicant_details->applicant_name ?? '-',
            'fathers_name'   => $applicant_details->fathers_name ?? '-',
            'dob'            => !empty($applicant_details->d_o_b)
                ? \Carbon\Carbon::parse($applicant_details->d_o_b)->format('d-m-Y')
                : '-',
            'age'            => $applicant_details->age ?? '-',
        ] : [
            'applicant_name' => '-',
            'fathers_name'   => '-',
            'dob'            => '-',
            'age'            => '-',
        ];

        // Certificate details: dates in dd-mm-yyyy
        $certificateData = [
            'certificate_no' => $certificate->certificate_no,
            'dateof_issue'   => !empty($certificate->dateof_issue)
                ? \Carbon\Carbon::parse($certificate->dateof_issue)->format('d-m-Y')
                : '-',
            'valid_from'     => !empty($certificate->valid_from)
                ? \Carbon\Carbon::parse($certificate->valid_from)->format('d-m-Y')
                : '-',
            'valid_to'       => !empty($certificate->valid_to)
                ? \Carbon\Carbon::parse($certificate->valid_to)->format('d-m-Y')
                : '-',
        ];

        // Certificate found but no experience
        if ($cc_exp->isEmpty()) {
            return response()->json([
                'status'            => true,
                'message'           => 'Certificate found, but no experience data found.',
                'application_id'    => $application_id,
                'certificate'       => $certificateData,
                'applicant_details' => $applicantData,
                'data'              => [],
            ]);
        }

        // Certificate and experience found
        return response()->json([
            'status'            => true,
            'message'           => 'Certificate and experience data found.',
            'application_id'    => $application_id,
            'certificate'       => $certificateData,
            'applicant_details' => $applicantData,
            'data'              => $cc_exp,
        ]);
    }
}
