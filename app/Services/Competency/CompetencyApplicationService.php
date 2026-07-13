<?php



namespace App\Services\Competency;



use App\Models\CC_Proof_doc;

use App\Models\Competency\CC_CompetencyMeta;

use App\Services\FormS\FormSApplicationWorkflowService;

use Illuminate\Support\Facades\DB;



/**

 * Resolve competency application master rows from per-form meta tables:

 * cc_form_s_meta, cc_form_w_meta, cc_form_wh_meta, cc_form_p_meta.

 */

class CompetencyApplicationService

{

    /** @var list<string> */

    public const CC_META_FORM_CODES = ['S', 'W', 'WH', 'P'];



    public function __construct(

        protected CompetencyWorkflowService $workflowService,

        protected CompetencyMetaService $metaService

    ) {}



    public function supportsCcMetaForm(?string $formName): bool

    {

        return $this->metaService->supportsForm($formName);

    }



    public function findMeta(string $applicationId): ?CC_CompetencyMeta

    {

        return $this->metaService->findModel(trim($applicationId));

    }



    public function isCcMetaFormId(int $formId): bool

    {

        return app(CompetencyAdminQueryService::class)->isCcMetaFormId($formId);

    }



    public function findLegacy(string $applicationId): ?object

    {

        $legacy = DB::table('cc_form_s_meta')->where('application_id', trim($applicationId))->first();

        if ($legacy && in_array(strtoupper(trim((string) ($legacy->form_name ?? ''))), self::CC_META_FORM_CODES, true)) {

            return null;

        }



        return $legacy;

    }



    /** @deprecated Prefer findMeta(); kept for legacy tnelb_form_p fallback. */

    public function findFormP(string $applicationId): ?object

    {

        $applicationId = trim($applicationId);

        $fromCc = DB::table('cc_form_p_meta')->where('application_id', $applicationId)->first();

        if ($fromCc) {

            return $fromCc;

        }



        return DB::table('tnelb_form_p')->where('application_id', $applicationId)->first();

    }



    public function findApplicantWithPayment(string $applicationId): ?object

    {

        $applicationId = trim($applicationId);

        if ($applicationId === '') {

            return null;

        }



        $metaTable = $this->metaService->metaTableForApplicationId($applicationId);

        if ($metaTable) {

            $row = $this->findApplicantRowWithPayment("{$metaTable} as ta", $applicationId);

            if ($row) {

                $meta = $this->findMeta($applicationId);



                return $this->enrichCcMetaProofFields(

                    $this->normalizeMetaRowForAdmin($row, $metaTable, $applicationId),

                    (string) app(FormSApplicationWorkflowService::class)->masterApplication($meta)->application_id

                );

            }

        }



        $legacy = $this->findApplicantRowWithPayment('cc_form_s_meta as ta', $applicationId, function ($query) {

            $query->whereNotIn('ta.form_name', self::CC_META_FORM_CODES);

        });

        if ($legacy) {

            $legacy->_application_source = 'cc_form_s_meta';



            return $legacy;

        }



        $formP = $this->findApplicantRowWithPayment('tnelb_form_p as ta', $applicationId);

        if ($formP) {

            return $this->normalizeFormPRowForAdmin($formP, $applicationId);

        }



        return null;

    }



    /**

     * Join applicant row with payments without clobbering ta.application_id when payment is missing.

     */

    private function findApplicantRowWithPayment(string $table, string $applicationId, ?callable $extraFilter = null): ?object

    {

        $query = DB::table($table)

            ->leftJoin('payments as p', 'p.application_id', '=', 'ta.application_id')

            ->where('ta.application_id', $applicationId);



        if ($extraFilter) {

            $extraFilter($query);

        }



        $row = $query->select([

            DB::raw('ta.*'),

            'p.transaction_id',

            'p.payment_status as gateway_payment_status',

            'p.amount',

            'p.payment_mode',

            'p.late_fee',

            'p.late_months',

            'p.transaction_date',

            'p.application_fee',

        ])->first();



        if ($row) {

            $row->application_id = $applicationId;

        }



        return $row;

    }



    public function normalizeMetaRowForAdmin(object $row, ?string $metaTable = null, ?string $applicationId = null): object

    {

        $applicationId = trim((string) ($applicationId ?? $row->application_id ?? ''));

        if ($applicationId !== '') {

            $row->application_id = $applicationId;

        }

        $metaTable ??= $this->metaService->metaTableForApplicationId($applicationId)

            ?? 'cc_form_s_meta';



        $row->_application_source = $metaTable;

        $row->id = $row->id ?? $row->app_id ?? null;

        $row->status = $row->status ?? $row->app_status ?? null;

        $row->license_name = $row->license_name ?? $row->certificate_name ?? null;

        $row->applicants_address = $row->applicants_address ?? $row->applicant_address ?? null;

        $row->previously_number = $row->previously_number ?? $row->previous_scc_no ?? null;

        $row->previously_valid_to = $row->previously_valid_to ?? $row->scc_to_date ?? null;

        $row->certificate_no = $row->certificate_no ?? $row->wcc_no ?? null;

        $row->certificate_date = $row->certificate_date ?? $row->wcc_to ?? null;



        return $row;

    }



    private function enrichCcMetaProofFields(object $applicationDetails, string $masterApplicationId): object

    {

        $proofRows = CC_Proof_doc::where('application_id', $masterApplicationId)

            ->whereIn('proof_type', ['aadhaar', 'pan'])

            ->get();



        foreach ($proofRows as $proof) {

            $proofType = strtolower((string) ($proof->proof_type ?? ''));

            if ($proofType === 'aadhaar' && ! empty($proof->proof_no)) {

                $applicationDetails->aadhaar = $proof->proof_no;

            } elseif ($proofType === 'pan' && ! empty($proof->proof_no)) {

                $applicationDetails->pancard = $proof->proof_no;

            }

        }



        return $applicationDetails;

    }



    public function normalizeFormPRowForAdmin(object $row, ?string $applicationId = null): object

    {

        $applicationId = trim((string) ($applicationId ?? $row->application_id ?? ''));

        if ($applicationId !== '') {

            $row->application_id = $applicationId;

        }

        $row->_application_source = 'cc_form_p_meta';

        $row->form_name = $row->form_name ?? 'P';

        $row->status = $row->status ?? $row->app_status ?? null;

        $row->license_name = $row->license_name ?? $row->certificate_name ?? null;

        $row->applicants_address = $row->applicants_address ?? $row->applicant_address ?? null;



        return $row;

    }



    public function applicationStatus(object $application): ?string

    {

        return $application->status

            ?? $application->app_status

            ?? null;

    }



    public function formName(object $application): string

    {

        return strtoupper(trim((string) ($application->form_name ?? '')));

    }



    public function usesCcWorkflow(object $application): bool

    {

        $source = (string) ($application->_application_source ?? '');



        if (in_array($source, $this->metaService->allMetaTables(), true)) {

            return $this->workflowService->supportsForm($this->formName($application));

        }



        if ($source === 'tnelb_form_p' || $source === 'cc_form_p_meta') {

            return true;

        }



        return false;

    }



    public function resolveWorkflowTable(string $applicationId, ?object $application = null): string

    {

        $application ??= $this->findApplicantWithPayment($applicationId);

        if ($application && $this->usesCcWorkflow($application)) {

            return $this->workflowService->tableForForm($this->formName($application));

        }



        return 'cc_workflow_forms';

    }



    public function resolveMetaTable(string $applicationId, ?object $application = null): string

    {

        $application ??= $this->findApplicantWithPayment($applicationId);

        $source = (string) ($application->_application_source ?? '');



        if (in_array($source, $this->metaService->allMetaTables(), true)) {

            return $source;

        }



        if ($source === 'tnelb_form_p') {

            return 'cc_form_p_meta';

        }



        $table = $this->metaService->metaTableForApplicationId(trim($applicationId));

        if ($table) {

            return $table;

        }



        return 'cc_form_s_meta';

    }



    /**

     * @param  array<string, mixed>  $fields

     */

    public function updateApplicationStatus(string $applicationId, array $fields): bool

    {

        $applicationId = trim($applicationId);

        if ($applicationId === '') {

            return false;

        }



        $metaTable = $this->metaService->metaTableForApplicationId($applicationId);

        if ($metaTable) {

            $update = [];

            if (isset($fields['status'])) {

                $update['app_status'] = $fields['status'];

            }

            if (isset($fields['app_status'])) {

                $update['app_status'] = $fields['app_status'];

            }

            if (isset($fields['processed_by'])) {

                $update['processed_by'] = $fields['processed_by'];

            }

            if (isset($fields['qc'])) {

                $update['qc'] = $fields['qc'];

            }

            if (isset($fields['qsc'])) {

                $update['qsc'] = $fields['qsc'];

            }

            $update['updated_at'] = $fields['updated_at'] ?? now();



            return DB::table($metaTable)->where('application_id', $applicationId)->update($update) > 0;

        }



        if ($this->findFormP($applicationId)) {

            $update = [];

            if (isset($fields['status'])) {

                $update['app_status'] = $fields['status'];

            }

            if (isset($fields['app_status'])) {

                $update['app_status'] = $fields['app_status'];

            }

            if (isset($fields['processed_by'])) {

                $update['processed_by'] = $fields['processed_by'];

            }

            $update['updated_at'] = $fields['updated_at'] ?? now();



            if (DB::table('cc_form_p_meta')->where('application_id', $applicationId)->exists()) {

                return DB::table('cc_form_p_meta')->where('application_id', $applicationId)->update($update) > 0;

            }



            return DB::table('tnelb_form_p')->where('application_id', $applicationId)->update($update) > 0;

        }



        $update = [];

        if (isset($fields['status'])) {

            $update['status'] = $fields['status'];

        }

        if (isset($fields['processed_by'])) {

            $update['processed_by'] = $fields['processed_by'];

        }

        if (isset($fields['qc'])) {

            $update['qc'] = $fields['qc'];

        }

        if (isset($fields['qsc'])) {

            $update['qsc'] = $fields['qsc'];

        }

        $update['updated_at'] = $fields['updated_at'] ?? now();



        return DB::table('cc_form_s_meta')->where('application_id', $applicationId)->update($update) > 0;

    }



    public function staffRoleCode(?string $staffName): string

    {

        return match ($staffName) {

            'President' => 'PR',

            'Secretary' => 'SE',

            'Supervisor', 'Supervisor2' => 'S',

            'Assistant Secretary' => 'A',

            default => 'A',

        };

    }



    public function resolveForwardStatus(string $staffName, object $application, ?object $formType = null): string

    {

        $applicantStatus = $this->applicationStatus($application);

        $isReturnedApplication = $applicantStatus === 'RE';



        return match ($staffName) {

            'President' => 'A',

            'Secretary' => ($formType->form_id ?? null) == 1 ? 'F' : 'A',

            'Supervisor', 'Supervisor2' => $isReturnedApplication ? 'RF' : 'F',

            'Assistant Secretary' => 'F',

            default => 'F',

        };

    }



    public function findFormId(string $applicationId): ?int

    {

        $meta = $this->findMeta($applicationId);

        if ($meta && $meta->form_id) {

            return (int) $meta->form_id;

        }



        $formP = $this->findFormP($applicationId);

        if ($formP) {

            return (int) ($formP->form_id ?? 6);

        }



        return null;

    }



    public function licensePdfApplication(string $applicationId): ?object

    {

        $applicationId = trim($applicationId);

        if ($applicationId === '') {

            return null;

        }



        $meta = $this->findMeta($applicationId);

        if ($meta) {

            $table = $this->metaService->tableForForm($meta->form_name ?? '');



            return $this->normalizeMetaRowForAdmin((object) $meta->toArray(), $table);

        }



        return DB::table('cc_form_s_meta')

            ->where('application_id', $applicationId)

            ->whereNotIn('form_name', self::CC_META_FORM_CODES)

            ->first() ?: null;

    }



    public function licensePdfApplicant(string $applicationId, ?object $application = null): ?object

    {

        $applicationId = trim($applicationId);

        $application ??= $this->licensePdfApplication($applicationId);

        if (! $application) {

            return null;

        }



        if ($this->findMeta($applicationId)) {

            $app = $this->findApplicantWithPayment($applicationId);

            if (! $app) {

                return null;

            }



            $cert = app(CompetencyCertificateService::class)->asLicenseDetails(

                $applicationId,

                $app->form_name ?? null

            );



            return (object) [

                'application_id' => $applicationId,

                'name' => $app->applicant_name ?? null,

                'fathers_name' => $app->fathers_name ?? null,

                'applicants_address' => $app->applicants_address ?? $app->applicant_address ?? null,

                'd_o_b' => $app->d_o_b ?? null,

                'age' => $app->age ?? null,

                'license_name' => $app->license_name ?? $app->certificate_name ?? null,

                'form_name' => $app->form_name ?? null,

                'license_number' => $cert->license_number ?? null,

                'issued_by' => $cert->issued_by ?? null,

                'issued_at' => $cert->issued_at ?? null,

                'issued_from' => $cert->valid_from ?? $cert->issued_from ?? null,

                'expires_at' => $cert->expires_at ?? null,

            ];

        }



        $applType = strtoupper(trim((string) ($application->appl_type ?? '')));

        if ($applType === 'R') {

            return DB::table('cc_form_s_meta')

                ->join('tnelb_renewal_license', 'tnelb_renewal_license.application_id', '=', 'cc_form_s_meta.application_id')

                ->where('cc_form_s_meta.application_id', $applicationId)

                ->select(

                    'cc_form_s_meta.application_id',

                    'cc_form_s_meta.applicant_name AS name',

                    'cc_form_s_meta.fathers_name',

                    'cc_form_s_meta.applicants_address',

                    'cc_form_s_meta.d_o_b',

                    'cc_form_s_meta.age',

                    'cc_form_s_meta.license_name',

                    'cc_form_s_meta.form_name',

                    'tnelb_renewal_license.license_number',

                    'tnelb_renewal_license.issued_by',

                    'tnelb_renewal_license.issued_at',

                    'tnelb_renewal_license.issued_from',

                    'tnelb_renewal_license.expires_at'

                )

                ->first();

        }



        return DB::table('cc_form_s_meta')

            ->join('cc_forms_cert', 'cc_forms_cert.application_id', '=', 'cc_form_s_meta.application_id')

            ->where('cc_form_s_meta.application_id', $applicationId)

            ->select(

                'cc_form_s_meta.application_id',

                'cc_form_s_meta.applicant_name AS name',

                'cc_form_s_meta.fathers_name',

                'cc_form_s_meta.applicants_address',

                'cc_form_s_meta.d_o_b',

                'cc_form_s_meta.age',

                'cc_form_s_meta.license_name',

                'cc_form_s_meta.form_name',

                'cc_forms_cert.license_number',

                'cc_forms_cert.issued_by',

                'cc_forms_cert.issued_at',

                'cc_forms_cert.issued_from',

                'cc_forms_cert.expires_at'

            )

            ->first();

    }

}

