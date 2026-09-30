<?php

namespace App\Services\Competency;

use App\Models\Competency\CC_CompetencyMeta;
use App\Models\Tnelb_CC_Digitization;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * QC / QSC eligibility for competency applications.
 *
 * New applications get the flags at issue. Digitisation stores them on
 * tnelb_cc_digitization at enrolment, which is not always copied onto meta.
 * Alteration and renewal children must inherit a 1 from the parent chain.
 */
final class CompetencyQcQscService
{
    /**
     * @return array{qc: int, qsc: int}
     */
    public function inheritedFlagsFromMeta(?CC_CompetencyMeta $start): array
    {
        if (! $start) {
            return ['qc' => 0, 'qsc' => 0];
        }

        return $this->inheritedFlags((string) $start->application_id, $start);
    }

    /**
     * Walk this application and each old_application parent. A 1 on meta,
     * the linked digitisation row, or the Form S certificate row is kept.
     *
     * @return array{qc: int, qsc: int}
     */
    public function inheritedFlags(string $applicationId, ?CC_CompetencyMeta $seed = null): array
    {
        $qc = 0;
        $qsc = 0;
        $metaService = app(CompetencyMetaService::class);
        $current = $seed instanceof CC_CompetencyMeta
            ? $seed
            : $metaService->findModel($applicationId);
        $seen = [];

        while ($current instanceof CC_CompetencyMeta && ($qc !== 1 || $qsc !== 1)) {
            $id = trim((string) ($current->application_id ?? ''));
            if ($id === '' || isset($seen[$id])) {
                break;
            }
            $seen[$id] = true;

            [$qc, $qsc] = array_values($this->mergeFlags(
                ['qc' => $qc, 'qsc' => $qsc],
                $this->normalizeFlags($current->qc ?? 0, $current->qsc ?? 0)
            ));
            [$qc, $qsc] = array_values($this->mergeFlags(
                ['qc' => $qc, 'qsc' => $qsc],
                $this->flagsFromDigitization($id)
            ));
            [$qc, $qsc] = array_values($this->mergeFlags(
                ['qc' => $qc, 'qsc' => $qsc],
                $this->flagsFromFormSCertificate($current)
            ));

            $parentId = trim((string) ($current->old_application ?? ''));
            $current = $parentId !== '' ? $metaService->findModel($parentId) : null;
        }

        if (($qc !== 1 || $qsc !== 1) && $applicationId !== '' && ! isset($seen[$applicationId])) {
            [$qc, $qsc] = array_values($this->mergeFlags(
                ['qc' => $qc, 'qsc' => $qsc],
                $this->flagsFromDigitization($applicationId)
            ));
            [$qc, $qsc] = array_values($this->mergeFlags(
                ['qc' => $qc, 'qsc' => $qsc],
                $this->flagsFromFormSCertificate(null, $applicationId)
            ));
        }

        return ['qc' => $qc, 'qsc' => $qsc];
    }

    /**
     * Form S approval stores QC/QSC on cc_forms_cert, sometimes only on one
     * row that shares the certificate number. A 1 anywhere on that licence counts.
     *
     * @return array{qc: int, qsc: int}
     */
    public function flagsFromFormSCertificate(?CC_CompetencyMeta $row = null, ?string $applicationId = null): array
    {
        $formName = strtoupper(trim((string) ($row->form_name ?? '')));
        $applicationId = trim((string) ($applicationId ?? $row->application_id ?? ''));
        if ($row && $formName !== '' && $formName !== 'S') {
            return ['qc' => 0, 'qsc' => 0];
        }

        if (! Schema::hasTable('cc_forms_cert')
            || ! Schema::hasColumn('cc_forms_cert', 'qc')
            || ! Schema::hasColumn('cc_forms_cert', 'qsc')) {
            return ['qc' => 0, 'qsc' => 0];
        }

        $certificateNo = trim((string) ($row->certificate_no ?? ''));
        if ($applicationId === '' && ($certificateNo === '' || $certificateNo === '0')) {
            return ['qc' => 0, 'qsc' => 0];
        }

        $certs = DB::table('cc_forms_cert')
            ->where(function ($query) use ($applicationId, $certificateNo) {
                if ($applicationId !== '') {
                    $query->orWhere('application_id', $applicationId);
                }
                if ($certificateNo !== '' && $certificateNo !== '0') {
                    $query->orWhere('certificate_no', $certificateNo);
                }
            })
            ->get(['qc', 'qsc']);

        $qc = 0;
        $qsc = 0;
        foreach ($certs as $cert) {
            if ((int) ($cert->qc ?? 0) === 1) {
                $qc = 1;
            }
            if ((int) ($cert->qsc ?? 0) === 1) {
                $qsc = 1;
            }
        }

        return ['qc' => $qc, 'qsc' => $qsc];
    }

    /**
     * @return array{qc: int, qsc: int}
     */
    public function flagsFromDigitization(
        ?string $applicationId = null,
        ?string $tempAppId = null,
        ?string $loginId = null
    ): array {
        $applicationId = trim((string) $applicationId);
        $tempAppId = trim((string) $tempAppId);
        $loginId = trim((string) $loginId);

        if ($applicationId !== '') {
            $row = Tnelb_CC_Digitization::where('application_id', $applicationId)
                ->orderByDesc('id')
                ->first();
            if ($row) {
                return $this->normalizeFlags($row->qc ?? 0, $row->qsc ?? 0);
            }
        }

        if ($tempAppId !== '') {
            $query = Tnelb_CC_Digitization::where('temp_app_id', $tempAppId);
            if ($loginId !== '') {
                $query->where('login_id', $loginId);
            }
            $row = $query->orderByDesc('id')->first();
            if ($row) {
                return $this->normalizeFlags($row->qc ?? 0, $row->qsc ?? 0);
            }
        }

        return ['qc' => 0, 'qsc' => 0];
    }

    /**
     * Keep an existing 1; never downgrade eligibility to 0.
     *
     * @param  array{qc?: mixed, qsc?: mixed}|object  $existing
     * @param  array{qc?: mixed, qsc?: mixed}  $incoming
     * @return array{qc: int, qsc: int}
     */
    public function mergeFlags(object|array $existing, array $incoming): array
    {
        $existingQc = is_array($existing) ? ($existing['qc'] ?? 0) : ($existing->qc ?? 0);
        $existingQsc = is_array($existing) ? ($existing['qsc'] ?? 0) : ($existing->qsc ?? 0);

        return [
            'qc' => ((int) $existingQc === 1 || (int) ($incoming['qc'] ?? 0) === 1) ? 1 : 0,
            'qsc' => ((int) $existingQsc === 1 || (int) ($incoming['qsc'] ?? 0) === 1) ? 1 : 0,
        ];
    }

    /**
     * @return array{qc: int, qsc: int}
     */
    public function normalizeFlags(mixed $qc, mixed $qsc): array
    {
        return [
            'qc' => ((int) $qc === 1) ? 1 : 0,
            'qsc' => ((int) $qsc === 1) ? 1 : 0,
        ];
    }

    /**
     * Fill qc/qsc on a loaded applicant object for admin/PDF views when the
     * child row was saved before inheritance was persisted.
     */
    public function overlayOnApplicant(object $applicant): void
    {
        $applType = strtoupper(trim((string) ($applicant->appl_type ?? '')));
        if (! in_array($applType, ['A', 'R', 'D'], true)) {
            return;
        }

        $applicationId = trim((string) ($applicant->application_id ?? ''));
        if ($applicationId === '') {
            return;
        }

        $merged = $this->mergeFlags($applicant, $this->inheritedFlags($applicationId));
        $applicant->qc = $merged['qc'];
        $applicant->qsc = $merged['qsc'];
    }
}
