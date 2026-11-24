<?php

namespace App\Services\Competency;

use App\Models\MstLicence;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * The amount an applicant must pay, computed on the server from the fee tables (PostgreSQL calc_fees()),
 * so the browser can never choose what is charged. Mirrors
 * Admin\LicenceManagementController::getPaymentDetails (which only feeds the payment popup).
 */
class PaymentFeeService
{
    /**
     * @param  string  $applType        N (new), R (renewal), D (digitisation) or A (alteration)
     * @param  string  $licenceCode     mst_licences.cert_licence_code, e.g. 'P'
     * @param  ?string $issuedLicence   issued certificate number (renewal only)
     * @return array{total:int, base:int, late_fee:int, late_months:int}|null  null when no fee can be determined
     */
    public function calculate(string $applType, string $licenceCode, ?string $issuedLicence = null): ?array
    {
        $applType = strtoupper(trim($applType));
        $licenceCode = trim($licenceCode);

        if (in_array($applType, ['D', 'A'], true)) {
            return ['total' => 0, 'base' => 0, 'late_fee' => 0, 'late_months' => 0];
        }

        $licence = $licenceCode !== '' ? MstLicence::where('cert_licence_code', $licenceCode)->first() : null;
        if (! $licence) {
            return null;
        }

        $issued = $applType === 'R' ? trim((string) $issuedLicence) : null;
        if ($applType === 'R' && $issued === '') {
            return null;
        }

        try {
            $rows = DB::select('SELECT * FROM calc_fees(:appl_type, :licence_id, :issued_licence)', [
                'appl_type' => $applType === '' ? 'N' : $applType,
                'licence_id' => $licence->id,
                'issued_licence' => $issued,
            ]);
        } catch (\Throwable $e) {
            Log::warning('calc_fees failed', ['appl_type' => $applType, 'licence' => $licenceCode, 'error' => $e->getMessage()]);

            return null;
        }

        if (empty($rows)) {
            return null;
        }

        return [
            'total' => (int) round((float) ($rows[0]->total_fee ?? 0)),
            'base' => (int) round((float) ($rows[0]->base_fee ?? 0)),
            'late_fee' => (int) round((float) ($rows[0]->late_fee ?? 0)),
            'late_months' => (int) ($rows[0]->late_months ?? 0),
        ];
    }
}
