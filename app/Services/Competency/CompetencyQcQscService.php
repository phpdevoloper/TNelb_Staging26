<?php

namespace App\Services\Competency;

use App\Models\Competency\CC_CompetencyMeta;
use App\Models\Tnelb_CC_Digitization;

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
     * Walk this application and each old_application parent. A 1 on meta or
     * on the linked digitisation row is kept (eligibility is not cleared).
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

            if ((int) ($current->qc ?? 0) === 1) {
                $qc = 1;
            }
            if ((int) ($current->qsc ?? 0) === 1) {
                $qsc = 1;
            }

            $fromDigi = $this->flagsFromDigitization($id);
            if ($fromDigi['qc'] === 1) {
                $qc = 1;
            }
            if ($fromDigi['qsc'] === 1) {
                $qsc = 1;
            }

            $parentId = trim((string) ($current->old_application ?? ''));
            $current = $parentId !== '' ? $metaService->findModel($parentId) : null;
        }

        if (($qc !== 1 || $qsc !== 1) && $applicationId !== '' && ! isset($seen[$applicationId])) {
            $fromDigi = $this->flagsFromDigitization($applicationId);
            if ($fromDigi['qc'] === 1) {
                $qc = 1;
            }
            if ($fromDigi['qsc'] === 1) {
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
