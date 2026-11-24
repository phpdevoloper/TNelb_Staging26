<?php

namespace App\Services;

use App\Services\Competency\CompetencyApplicationService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Finds the applicant (login_id) that owns an application id, across the competency (S/W/WH/P),
 * Form A family and legacy application tables. Used to stop one applicant downloading another's PDF.
 *
 * Order of trust:
 *  1. Application tables (APPLICATION_SOURCES) - the FIRST row found decides; if that row names no owner
 *     the answer is null (a later table must not fill the gap).
 *  2. CompetencyApplicationService::findApplicantWithPayment() - same rule.
 *  3. UNTRUSTED_SOURCES (mst_workflows, payments, cc_payments) - ONLY when no application row exists
 *     anywhere for the id, and only when every such row names the same applicant. These rows are written
 *     from client input (e.g. login_id_store, updatePayment), so they must never override or compete with an
 *     application record.
 */
class ApplicationOwnerResolver
{
    /** [table, application id column, owner column]. tnelb_workflow / tnelb_workflow_a are deliberately
     *  NOT listed: their login_id is the staff handling a step, not the applicant. */
    private const APPLICATION_SOURCES = [
        ['cc_form_s_meta', 'application_id', 'login_id'],
        ['cc_form_w_meta', 'application_id', 'login_id'],
        ['cc_form_wh_meta', 'application_id', 'login_id'],
        ['cc_form_p_meta', 'application_id', 'login_id'],
        ['tnelb_form_p', 'application_id', 'login_id'],
        ['ccl_forma_meta', 'application_id', 'login_id'],
        ['tnelb_ea_applications', 'application_id', 'login_id'],
        ['tnelb_esa_applications', 'application_id', 'login_id'],
        ['tnelb_esb_applications', 'application_id', 'login_id'],
        ['tnelb_eb_applications', 'application_id', 'login_id'],
        ['tnelb_renewal_applications', 'application_id', 'login_id'],
        ['tnelb_application_tbl', 'application_id', 'login_id'],
    ];

    /** Client-written rows: only consulted when no application row exists (old receipts, workflow stubs). */
    private const UNTRUSTED_SOURCES = [
        ['mst_workflows', 'application_id', 'login_id'],
        ['payments', 'application_id', 'login_id'],
        ['cc_payments', 'application_id', 'login_id'],
    ];

    /** Cache key for the schema check; bump the suffix (or run cache:clear) after adding a source table. */
    private const SCHEMA_CACHE_KEY = 'application_owner_resolver.usable_sources.v2';

    /** @var array<string, array<int, array{0:string,1:string,2:string}>> */
    private static array $usable = [];

    /** login_id of the application's owner, or null when the application cannot be found. */
    public function loginIdFor(string $applicationId): ?string
    {
        $applicationId = trim($applicationId);
        if ($applicationId === '') {
            return null;
        }

        foreach ($this->usableSources('application', self::APPLICATION_SOURCES) as [$table, $idColumn, $ownerColumn]) {
            $row = DB::table($table)->where($idColumn, $applicationId)->first([$ownerColumn]);
            if ($row === null) {
                continue;
            }

            // First application row found decides, even when its owner is blank (fail closed).
            $owner = trim((string) ($row->{$ownerColumn} ?? ''));

            return $owner !== '' ? $owner : null;
        }

        // Defence in depth: competency applications resolved through the meta/payment join. Normally the sources
        // above already matched, so this only costs extra queries when the id is unknown.
        $applicant = app(CompetencyApplicationService::class)->findApplicantWithPayment($applicationId);
        if ($applicant) {
            $owner = trim((string) ($applicant->login_id ?? ''));

            return $owner !== '' ? $owner : null;
        }

        return $this->ownerFromUntrustedRows($applicationId);
    }

    public function isOwnedBy(string $applicationId, ?string $loginId): bool
    {
        $loginId = trim((string) $loginId);
        if ($loginId === '') {
            return false;
        }

        $owner = $this->loginIdFor($applicationId);

        return $owner !== null && hash_equals($owner, $loginId);
    }

    private function ownerFromUntrustedRows(string $applicationId): ?string
    {
        $owners = [];

        foreach ($this->usableSources('untrusted', self::UNTRUSTED_SOURCES) as [$table, $idColumn, $ownerColumn]) {
            $found = DB::table($table)
                ->where($idColumn, $applicationId)
                ->whereNotNull($ownerColumn)
                ->distinct()
                ->pluck($ownerColumn);

            foreach ($found as $owner) {
                $owner = trim((string) $owner);
                if ($owner !== '') {
                    $owners[$owner] = true;
                }
            }
        }

        // Exactly one named applicant, otherwise the id is ambiguous and nobody gets access.
        return count($owners) === 1 ? (string) array_key_first($owners) : null;
    }

    /**
     * Sources whose table and columns exist. The check is cached (information_schema queries are slow)
     * for an hour and memoised per request; run `php artisan cache:clear` after a schema change.
     *
     * @param  array<int, array{0:string,1:string,2:string}>  $sources
     * @return array<int, array{0:string,1:string,2:string}>
     */
    private function usableSources(string $group, array $sources): array
    {
        if (isset(self::$usable[$group])) {
            return self::$usable[$group];
        }

        $compute = static function () use ($sources): array {
            $usable = [];
            foreach ($sources as [$table, $idColumn, $ownerColumn]) {
                if (Schema::hasTable($table) && Schema::hasColumn($table, $idColumn) && Schema::hasColumn($table, $ownerColumn)) {
                    $usable[] = [$table, $idColumn, $ownerColumn];
                }
            }

            return $usable;
        };

        try {
            $usable = Cache::remember(self::SCHEMA_CACHE_KEY . '.' . $group, 3600, $compute);
        } catch (\Throwable) {
            $usable = $compute();
        }

        return self::$usable[$group] = $usable;
    }
}
