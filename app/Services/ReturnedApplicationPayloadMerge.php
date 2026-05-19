<?php

namespace App\Services;

use App\Models\Mst_education;
use App\Models\Mst_experience;
use App\Models\TnelbAppsInstitute;
use App\Models\TnelbFormP;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * Restores locked applicant-return payloads from the database before validation/update (partial QU submit).
 */
final class ReturnedApplicationPayloadMerge
{
    public static function mergeEducationArraysIntoRequest(Request $request, string $applicationId): void
    {
        $rows = Mst_education::where('application_id', $applicationId)->orderBy('id')->get();
        $level = [];
        $inst = [];
        $month = [];
        $year = [];
        $cert = [];
        $eduId = [];
        $existingDoc = [];
        $removed = [];

        foreach ($rows as $row) {
            $level[] = $row->educational_level ?? '';
            $inst[] = $row->institute_name ?? '';
            $month[] = $row->month_passing ?? '';
            $year[] = $row->year_of_passing ?? '';
            $cert[] = $row->certificate_no ?? $row->percentage ?? '';
            $eduId[] = $row->id;
            $existingDoc[] = $row->upload_document ?? '';
            $removed[] = '0';
        }

        $request->merge([
            'educational_level' => $level,
            'institute_name' => $inst,
            'month_of_passing' => $month,
            'month_passing' => $month,
            'year_of_passing' => $year,
            'certificate_no' => $cert,
            'edu_id' => $eduId,
            'existing_document' => $existingDoc,
            'removed_document' => $removed,
        ]);
    }

    public static function mergeExperienceArraysIntoRequest(Request $request, string $applicationId, string $formName): void
    {
        $rows = Mst_experience::where('application_id', $applicationId)->orderBy('id')->get();

        if ($formName === 'S') {
            $empType = [];
            $employer = [];
            $intimation = [];
            $from = [];
            $to = [];
            $total = [];
            $designation = [];
            $workId = [];
            $existingW = [];
            $removedW = [];

            foreach ($rows as $row) {
                $empType[] = $row->emp_type ?: 'company';
                $employer[] = $row->emp_cate ?? $row->company_name ?? '';
                $intimation[] = $row->intimation_date ? Carbon::parse($row->intimation_date)->format('Y-m-d') : '';
                $from[] = $row->from_date ? Carbon::parse($row->from_date)->format('Y-m-d') : '';
                $to[] = $row->to_date ? Carbon::parse($row->to_date)->format('Y-m-d') : '';
                $te = $row->total_exp ?? $row->experience ?? '';
                $total[] = $te;
                $designation[] = $row->designation ?? '';
                $workId[] = $row->id;
                $existingW[] = $row->upload_document ?? '';
                $removedW[] = '0';
            }

            $workLevel = $employer;

            $request->merge([
                'work_employment_type' => $empType,
                'work_employer_name' => $employer,
                'work_intimation_date' => $intimation,
                'work_date_from' => $from,
                'work_date_to' => $to,
                'work_experience_total' => $total,
                'work_level' => $workLevel,
                'experience' => $total,
                'designation' => $designation,
                'work_id' => $workId,
                'existing_work_document' => $existingW,
                'removed_document_work' => $removedW,
            ]);

            return;
        }

        if ($formName === 'W') {
            $wl = [];
            $from = [];
            $to = [];
            $total = [];
            $exp = [];
            $designation = [];
            $workId = [];
            $existingW = [];
            $removedW = [];

            foreach ($rows as $row) {
                $wl[] = $row->company_name ?? '';
                $from[] = $row->from_date ? Carbon::parse($row->from_date)->format('Y-m-d') : '';
                $to[] = $row->to_date ? Carbon::parse($row->to_date)->format('Y-m-d') : '';
                $te = $row->total_exp ?? $row->experience ?? '';
                $total[] = $te;
                $exp[] = $te;
                $designation[] = $row->designation ?? '';
                $workId[] = $row->id;
                $existingW[] = $row->upload_document ?? '';
                $removedW[] = '0';
            }

            $request->merge([
                'work_level' => $wl,
                'work_date_from' => $from,
                'work_date_to' => $to,
                'work_experience_total' => $total,
                'experience' => $exp,
                'designation' => $designation,
                'work_id' => $workId,
                'existing_work_document' => $existingW,
                'removed_document_work' => $removedW,
            ]);

            return;
        }

        // WH and others (including Form P when caller passes a different layout key): simple rows
        $wl = [];
        $exp = [];
        $designation = [];
        $workId = [];
        $existingW = [];
        $removedW = [];

        foreach ($rows as $row) {
            $wl[] = $row->company_name ?? '';
            $exp[] = $row->experience ?? $row->total_exp ?? '';
            $designation[] = $row->designation ?? '';
            $workId[] = $row->id;
            $existingW[] = $row->upload_document ?? '';
            $removedW[] = '0';
        }

        $request->merge([
            'work_level' => $wl,
            'experience' => $exp,
            'designation' => $designation,
            'work_id' => $workId,
            'existing_work_document' => $existingW,
            'removed_document_work' => $removedW,
        ]);
    }

    public static function mergeFormPInstituteArraysIntoRequest(Request $request, string $applicationId): void
    {
        $rows = TnelbAppsInstitute::where('application_id', $applicationId)->orderBy('id')->get();

        $fmtDate = static function ($v): ?string {
            if ($v === null || $v === '') {
                return null;
            }
            try {
                return Carbon::parse($v)->format('Y-m-d');
            } catch (\Throwable $e) {
                return null;
            }
        };

        $addr = [];
        $from = [];
        $to = [];
        $duration = [];
        $instId = [];
        $existDoc = [];
        $removed = [];

        foreach ($rows as $row) {
            $addr[] = $row->institute_name_address ?? '';
            $from[] = $fmtDate($row->from_date) ?? '';
            $to[] = $fmtDate($row->to_date) ?? '';
            $duration[] = $row->duration ?? '';
            $instId[] = $row->id;
            $existDoc[] = $row->upload_doc ?? '';
            $removed[] = '0';
        }

        $request->merge([
            'institute_name_address' => $addr,
            'from_date' => $from,
            'to_date' => $to,
            'duration' => $duration,
            'institute_id' => $instId,
            'exist_institute_document' => $existDoc,
            'removed_document_inst' => $removed,
        ]);
    }

    /**
     * Lock applicant identity / licence fields on partial Form P submit (same rationale as competency merge).
     */
    public static function mergeFormPApplicantScalarsIntoRequest(Request $request, TnelbFormP $form): void
    {
        $aadhaarPlain = safeDecrypt($form->aadhaar) ?? '';

        $fmtDate = static function ($v): ?string {
            if ($v === null || $v === '') {
                return null;
            }
            try {
                return Carbon::parse($v)->format('Y-m-d');
            } catch (\Throwable $e) {
                return null;
            }
        };

        $request->merge([
            'applicant_name' => $form->applicant_name,
            'fathers_name' => $form->fathers_name,
            'applicants_address' => $form->applicants_address,
            'd_o_b' => $fmtDate($form->d_o_b) ?? '',
            'age' => $form->age,
            'previously_number' => $form->previously_number,
            'previously_date' => $fmtDate($form->previously_date),
            'wireman_details' => $form->wireman_details ?? null,
            'aadhaar' => preg_replace('/\D/', '', (string) $aadhaarPlain),
            'certificate_no' => $form->certificate_no,
            'certificate_date' => $fmtDate($form->certificate_date),
            'license_number' => $form->license_number,
            'l_verify' => (string) ($form->license_verify ?? '0'),
            'cert_verify' => (string) ($form->cert_verify ?? '0'),
        ]);
    }
}
