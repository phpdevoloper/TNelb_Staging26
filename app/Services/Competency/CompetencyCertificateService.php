<?php

namespace App\Services\Competency;

use App\Models\CC_Form_p_cert;
use App\Models\CC_Form_w_cert;
use App\Models\CC_Form_wh_cert;
use App\Models\CC_Forms_cert;
use App\Models\CC_Forms_Meta;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Resolves issued certificates for competency forms S, W, WH, P.
 * Each form stores new + renewal certs in one table (replaces tnelb_license / tnelb_renewal_license).
 */
class CompetencyCertificateService
{
    /** @var array<string, class-string<Model>> */
    private const FORM_MODEL_MAP = [
        'S' => CC_Forms_cert::class,
        'W' => CC_Form_w_cert::class,
        'WH' => CC_Form_wh_cert::class,
        'P' => CC_Form_p_cert::class,
    ];

    /** @return list<string> */
    public function supportedFormNames(): array
    {
        return array_keys(self::FORM_MODEL_MAP);
    }

    public function supportsForm(?string $formName): bool
    {
        return isset(self::FORM_MODEL_MAP[$this->normalizeFormName($formName)]);
    }

    /** @return class-string<Model>|null */
    public function modelClassForForm(?string $formName): ?string
    {
        return self::FORM_MODEL_MAP[$this->normalizeFormName($formName)] ?? null;
    }

    public function certTableForForm(?string $formName): ?string
    {
        $class = $this->modelClassForForm($formName);

        return $class ? (new $class())->getTable() : null;
    }

    public function resolveFormName(string $applicationId, ?string $formName = null): ?string
    {
        $formName = $this->normalizeFormName($formName);
        if ($formName !== '' && $this->supportsForm($formName)) {
            return $formName;
        }

        $metaService = app(CompetencyMetaService::class);
        foreach ($metaService->allMetaTables() as $table) {
            $fromMeta = DB::table($table)->where('application_id', $applicationId)->value('form_name');
            if ($fromMeta && $this->supportsForm($fromMeta)) {
                return $this->normalizeFormName($fromMeta);
            }
        }

        $fromFormP = DB::table('cc_form_p_meta')->where('application_id', $applicationId)->value('form_name');
        if ($fromFormP && $this->supportsForm($fromFormP)) {
            return $this->normalizeFormName($fromFormP);
        }

        $fromLegacy = DB::table('tnelb_application_tbl')->where('application_id', $applicationId)->value('form_name');

        return ($fromLegacy && $this->supportsForm($fromLegacy))
            ? $this->normalizeFormName($fromLegacy)
            : null;
    }

    public function findByApplicationId(string $applicationId, ?string $formName = null): ?Model
    {
        $applicationId = trim($applicationId);
        if ($applicationId === '') {
            return null;
        }

        $formName = $this->resolveFormName($applicationId, $formName);
        $modelClass = $formName ? $this->modelClassForForm($formName) : null;
        if (! $modelClass) {
            return null;
        }

        return $modelClass::where('application_id', $applicationId)->first();
    }

    public function asLicenseDetails(string $applicationId, ?string $formName = null): ?object
    {
        $cert = $this->findByApplicationId($applicationId, $formName);
        if (! $cert) {
            return null;
        }

        return $this->mapCertToLicenseDetails($cert);
    }

    public function asWorkflowLicense(string $applicationId, ?string $formName = null): ?object
    {
        $cert = $this->findByApplicationId($applicationId, $formName);
        if (! $cert) {
            return null;
        }

        return (object) [
            'license_number' => $cert->certificate_no,
            'expires_at' => $cert->valid_to,
        ];
    }

    public function mapCertToLicenseDetails(Model $cert): object
    {
        return (object) [
            'application_id' => $cert->application_id,
            'license_number' => $cert->certificate_no,
            'certificate_no' => $cert->certificate_no,
            'issued_at' => $cert->dateof_issue,
            'issued_from' => $cert->valid_from,
            'expires_at' => $cert->valid_to,
            'valid_from' => $cert->valid_from,
            'valid_to' => $cert->valid_to,
            'issued_by' => $cert->issued_by,
            'cert_status' => $cert->cert_status,
            'cert_pdf' => $cert->cert_pdf,
        ];
    }

    /**
     * Persist issued certificate (new or renewal) — one row per application_id.
     *
     * @param  array{
     *     application_id: string,
     *     certificate_no: string,
     *     dateof_issue: mixed,
     *     valid_from: mixed,
     *     valid_to: mixed,
     *     issued_by?: string|null,
     *     cert_status?: string|null,
     *     cert_pdf?: string|null,
     * }  $data
     */
    public function issueOrUpdate(string $formName, array $data): Model
    {
        $formName = $this->normalizeFormName($formName);
        $modelClass = $this->modelClassForForm($formName);
        if (! $modelClass) {
            throw new \InvalidArgumentException("Unsupported competency form for certificate issue: {$formName}");
        }

        $applicationId = trim((string) ($data['application_id'] ?? ''));
        $certificateNo = trim((string) ($data['certificate_no'] ?? ''));
        if ($applicationId === '' || $certificateNo === '') {
            throw new \InvalidArgumentException('application_id and certificate_no are required to issue a certificate.');
        }

        $now = now();
        $payload = [
            'application_id' => $applicationId,
            'certificate_no' => $certificateNo,
            'dateof_issue' => $data['dateof_issue'] ?? null,
            'valid_from' => $data['valid_from'] ?? null,
            'valid_to' => $data['valid_to'] ?? null,
            'issued_by' => $data['issued_by'] ?? null,
            'cert_status' => $data['cert_status'] ?? 'A',
            'cert_pdf' => $data['cert_pdf'] ?? null,
            'updated_at' => $now,
        ];

        /** @var Model|null $existing */
        $existing = $modelClass::where('application_id', $applicationId)->first();
        if ($existing) {
            $existing->update($payload);

            return $existing->fresh();
        }

        return $modelClass::create(array_merge($payload, [
            'created_at' => $now,
        ]));
    }

    /**
     * Mirror a legacy issue result into the per-form cert table (dual-write during migration).
     * Failures are logged and rethrown so the approval transaction rolls back.
     */
    public function syncFromLegacyIssue(
        ?string $formName,
        string $applicationId,
        string $certificateNo,
        mixed $issuedAt,
        mixed $validFrom,
        mixed $expiresAt,
        ?string $issuedBy = null
    ): ?Model {
        if (! $this->supportsForm($formName)) {
            return null;
        }

        try {
            return $this->issueOrUpdate((string) $formName, [
                'application_id' => $applicationId,
                'certificate_no' => $certificateNo,
                'dateof_issue' => $issuedAt,
                'valid_from' => $validFrom ?? $issuedAt,
                'valid_to' => $expiresAt,
                'issued_by' => $issuedBy,
                'cert_status' => 'A',
            ]);
        } catch (\Throwable $e) {
            Log::error('CompetencyCertificateService: syncFromLegacyIssue failed', [
                'form_name' => $formName,
                'application_id' => $applicationId,
                'certificate_no' => $certificateNo,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    private function normalizeFormName(?string $formName): string
    {
        return strtoupper(trim((string) $formName));
    }
}
