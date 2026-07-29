<?php

namespace App\Services;

use App\Models\CC_Education;
use App\Models\CC_Experience;
use App\Models\TnelbAppsInstitute;
use App\Models\TnelbFormP;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Restores locked applicant-return payloads from the database before validation/update (partial QU submit).
 */
final class ReturnedApplicationPayloadMerge
{
    public static function mergeEducationArraysIntoRequest(Request $request, string $applicationId): void
    {
        $rows = self::educationRowsForApplication($applicationId);
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
        $rows = self::experienceRowsForApplication($applicationId);

        if ($formName === 'S') {
            $empType = [];
            $employer = [];
            $orgAddress = [];
            $contractorCat = [];
            $licenceNo = [];
            $intimation = [];
            $from = [];
            $to = [];
            $total = [];
            $designation = [];
            $nature = [];
            $voltage = [];
            $kva = [];
            $workId = [];
            $existingW = [];
            $existingRelieve = [];
            $removedW = [];
            $removedRelieve = [];
            $qsRecognized = [];
            $endorsedLicenseType = [];
            $endorsedLicenseNo = [];
            $endorsedContractor = [];
            $existingEndorsed = [];
            $removedEndorsed = [];

            foreach ($rows as $row) {
                $type = $row->emp_type ?: 'company';
                $empType[] = $type;

                $employerName = $row->org_name ?? $row->company_name ?? '';
                if ($employerName === '' && strtolower((string) $type) !== 'contractor' && ! empty($row->emp_cate)) {
                    $employerName = $row->emp_cate;
                }
                $employer[] = $employerName;
                $orgAddress[] = $row->org_address ?? '';

                $decoded = self::decodeFormSContractorEmpCate($row->emp_cate);
                $contractorCat[] = $decoded['category'] ?? '';
                $licenceNo[] = $decoded['licence'] ?? '';

                $intimation[] = $row->intimation_date ? Carbon::parse($row->intimation_date)->format('Y-m-d') : '';
                $from[] = $row->from_date ? Carbon::parse($row->from_date)->format('Y-m-d') : '';
                $to[] = $row->to_date ? Carbon::parse($row->to_date)->format('Y-m-d') : '';
                $te = legacy_total_exp_from_duration(
                    $row->total_exp ?? $row->experience ?? null,
                    $row->total_y ?? null,
                    $row->total_m ?? null,
                    $row->total_d ?? null
                );
                // Form S §7b board rows often have no dates/YMD; keep a numeric placeholder for experience[].
                if ($te === '' && strtolower((string) ($row->emp_type ?? '')) === 'board_member_tnelb') {
                    $te = '0';
                }
                $total[] = $te;
                $designation[] = $row->designation ?? '';
                $nature[] = $row->nature_work ?? '';
                $voltage[] = $row->voltage_level ?? '';
                $kva[] = $row->transformer_kva ?? '';
                $workId[] = $row->id;
                $existingW[] = $row->support_document ?? $row->upload_document ?? '';
                $existingRelieve[] = $row->releive_document ?? '';
                $removedW[] = '0';
                $removedRelieve[] = '0';
                $qsRecognized[] = $row->qualified_supervisor_recognized ?? '';
                $endorsedLicenseType[] = $row->endorsed_license_type ?? '';
                $endorsedLicenseNo[] = $row->endorsed_license_number ?? '';
                $endorsedContractor[] = $row->endorsed_contractor_name ?? '';
                $existingEndorsed[] = $row->endorsed_support_document ?? '';
                $removedEndorsed[] = '0';
            }

            $workLevel = $employer;

            $request->merge([
                'work_employment_type' => $empType,
                'work_employer_name' => $employer,
                'work_organisation_address' => $orgAddress,
                'work_contractor_category' => $contractorCat,
                'work_licence_number' => $licenceNo,
                'work_intimation_date' => $intimation,
                'work_date_from' => $from,
                'work_date_to' => $to,
                'work_experience_total' => $total,
                'work_nature_of_work' => $nature,
                'work_voltage_level' => $voltage,
                'work_transformer_kva' => $kva,
                'work_level' => $workLevel,
                'experience' => $total,
                'designation' => $designation,
                'work_id' => $workId,
                'existing_work_document' => $existingW,
                'existing_work_relieving_document' => $existingRelieve,
                'removed_document_work' => $removedW,
                'removed_document_work_relieving' => $removedRelieve,
                'work_qualified_supervisor_recognized' => $qsRecognized,
                'work_endorsed_license_type' => $endorsedLicenseType,
                'work_endorsed_license_number' => $endorsedLicenseNo,
                'work_endorsed_contractor_name' => $endorsedContractor,
                'existing_work_endorsed_document' => $existingEndorsed,
                'removed_work_endorsed_document' => $removedEndorsed,
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
                $wl[] = $row->org_name ?? $row->company_name ?? '';
                $from[] = $row->from_date ? Carbon::parse($row->from_date)->format('Y-m-d') : '';
                $to[] = $row->to_date ? Carbon::parse($row->to_date)->format('Y-m-d') : '';
                $te = legacy_total_exp_from_duration(
                    $row->total_exp ?? $row->experience ?? null,
                    $row->total_y ?? null,
                    $row->total_m ?? null,
                    $row->total_d ?? null
                );
                $total[] = $te;
                $exp[] = $te;
                $designation[] = $row->designation ?? '';
                $workId[] = $row->id;
                $existingW[] = $row->support_document ?? $row->upload_document ?? '';
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
            $wl[] = $row->org_name ?? $row->company_name ?? '';
            $exp[] = legacy_total_exp_from_duration(
                $row->experience ?? $row->total_exp ?? null,
                $row->total_y ?? null,
                $row->total_m ?? null,
                $row->total_d ?? null
            );
            $designation[] = $row->designation ?? '';
            $workId[] = $row->id;
            $existingW[] = $row->support_document ?? $row->upload_document ?? '';
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

    /**
     * @return array{category: ?string, licence: ?string}
     */
    private static function decodeFormSContractorEmpCate(?string $stored): array
    {
        if ($stored === null || $stored === '') {
            return ['category' => null, 'licence' => null];
        }
        if (str_contains($stored, '||')) {
            $parts = explode('||', $stored, 2);

            return [
                'category' => (($parts[0] ?? '') !== '') ? $parts[0] : null,
                'licence' => (($parts[1] ?? '') !== '') ? $parts[1] : null,
            ];
        }

        return ['category' => $stored, 'licence' => null];
    }

    private static function educationRowsForApplication(string $applicationId): Collection
    {
        $ccRows = CC_Education::where('application_id', $applicationId)->orderBy('edu_id')->get();
        if ($ccRows->isNotEmpty()) {
            return $ccRows->map(static function (CC_Education $row) {
                $row->id = $row->edu_id;

                return $row;
            });
        }

        return collect(
            DB::table('tnelb_applicants_edu')
                ->where('application_id', $applicationId)
                ->orderBy('id')
                ->get()
        );
    }

    private static function experienceRowsForApplication(string $applicationId): Collection
    {
        $ccRows = CC_Experience::where('application_id', $applicationId)->orderBy('exp_id')->get();
        if ($ccRows->isNotEmpty()) {
            return $ccRows;
        }

        return collect(
            DB::table('tnelb_applicants_exp')
                ->where('application_id', $applicationId)
                ->orderBy('exp_id')
                ->get()
        );
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
            'previously_valid_to' => $fmtDate($form->previously_valid_to ?? $form->previously_date ?? null),
            'previously_issue_date' => $fmtDate($form->previously_issue_date ?? null),
            'previously_valid_from' => $fmtDate($form->previously_valid_from ?? null),
            'wireman_details' => $form->wireman_details ?? null,
            'aadhaar' => preg_replace('/\D/', '', (string) $aadhaarPlain),
            'pancard' => strtoupper(preg_replace('/[^A-Z0-9]/i', '', (string) (safeDecrypt($form->pancard) ?? ''))),
            'certificate_no' => $form->certificate_no,
            'certificate_valid_to' => $fmtDate($form->certificate_valid_to ?? $form->certificate_date ?? null),
            'certificate_issue_date' => $fmtDate($form->certificate_issue_date ?? null),
            'certificate_valid_from' => $fmtDate($form->certificate_valid_from ?? null),
            'license_number' => $form->license_number,
            'l_verify' => (string) ($form->license_verify ?? '0'),
            'cert_verify' => (string) ($form->cert_verify ?? '0'),
        ]);
    }
}
