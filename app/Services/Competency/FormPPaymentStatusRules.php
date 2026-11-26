<?php

namespace App\Services\Competency;

use App\Services\Payments\PayUCallbackRules;
use Carbon\Carbon;
use Carbon\CarbonInterface;

/**
 * Pure status rules for paying a Form P application (no I/O).
 *
 * A Form P application stays a draft (app_status D, payment_status N) until its payment SUCCEEDS;
 * only the settled payment submits it (D -> P). Other forms keep their existing behaviour.
 */
class FormPPaymentStatusRules
{
    /** app_status values that mean "not yet submitted" for an unpaid Form P application. */
    public const DRAFT_LIKE_STATUSES = ['', 'D', 'P'];

    /** An INITIATED attempt older than this never reached / was abandoned at PayU. */
    public const ABANDON_AFTER_MINUTES = 20;

    /** payment_status codes (and legacy labels) that mean the application is paid / fee-exempt. */
    public const PAID_CODES = ['Y', 'B', 'PAYMENT', 'PAID', 'SUCCESS'];

    /** Open gateway attempt statuses in payment_transactions. */
    public const OPEN_ATTEMPT_STATUSES = ['INITIATED', 'PENDING', 'PENDING_VERIFICATION'];

    public const PRIOR_BLOCK = 'block';

    public const PRIOR_PAID = 'paid';

    public const PRIOR_MARK_FAILED = 'mark_failed';

    public const PRIOR_IGNORE = 'ignore';

    private static function code(mixed $value): string
    {
        return strtoupper(trim((string) $value));
    }

    public static function isFormP(?string $formName): bool
    {
        return self::code($formName) === FormPSchema::FORM_NAME;
    }

    public static function isPaid(?string $paymentStatus): bool
    {
        return in_array(self::code($paymentStatus), self::PAID_CODES, true);
    }

    /**
     * app_status to write when Pay Now is pressed (null = leave unchanged). Form P is never submitted here.
     */
    public static function statusAfterInitiate(?string $formName, ?string $appStatus): ?string
    {
        if (self::isFormP($formName)) {
            return null;
        }

        return self::code($appStatus) === 'D' ? 'P' : null;
    }

    /** A settled payment submits only a draft, so repeated settle calls change nothing. */
    public static function submitsOnPaymentSuccess(?string $appStatus): bool
    {
        return self::code($appStatus) === 'D';
    }

    /**
     * Columns to update after a verified payment failure.
     *
     * @return array<string, string>
     */
    public static function updatesAfterFailure(?string $formName, ?string $appStatus, ?string $paymentStatus): array
    {
        if (! self::isFormP($formName)) {
            return ['app_status' => 'P', 'payment_status' => 'N'];
        }

        if (self::isPaid($paymentStatus)) {
            return [];
        }

        if (in_array(self::code($appStatus), self::DRAFT_LIKE_STATUSES, true)) {
            return ['app_status' => 'D', 'payment_status' => 'N'];
        }

        return ['payment_status' => 'N'];
    }

    /**
     * app_status for a save of an unpaid Form P application: draft-like statuses become D
     * (a paid P stays P); any other status is kept.
     */
    public static function statusOnUnpaidSave(?string $appStatus, ?string $paymentStatus): string
    {
        $status = self::code($appStatus);

        if (in_array($status, self::DRAFT_LIKE_STATUSES, true)) {
            if (self::isPaid($paymentStatus) && $status === 'P') {
                return 'P';
            }

            return 'D';
        }

        return $status;
    }

    /** Unknown / unparseable age counts as old so the applicant is never locked out. */
    public static function isOlderThanAbandonAge(mixed $createdAt, ?CarbonInterface $now = null): bool
    {
        if ($createdAt === null || $createdAt === '') {
            return true;
        }

        try {
            $created = $createdAt instanceof CarbonInterface ? $createdAt : Carbon::parse((string) $createdAt);
        } catch (\Throwable) {
            return true;
        }

        $now = $now ?? Carbon::now();

        return $created->lessThanOrEqualTo($now->copy()->subMinutes(self::ABANDON_AFTER_MINUTES));
    }

    private static function attemptField(mixed $attempt, string $field): mixed
    {
        if (is_array($attempt)) {
            return $attempt[$field] ?? null;
        }
        if (is_object($attempt)) {
            return $attempt->{$field} ?? null;
        }

        return null;
    }

    /**
     * Dashboard: an unpaid Form P draft whose only open attempts are old INITIATED ones (window closed
     * before / at PayU) is shown as a Draft instead of "Submitted / Status".
     *
     * @param  iterable<array|object>  $openAttempts  INITIATED / PENDING / PENDING_VERIFICATION rows
     */
    public static function dashboardTreatsAsAbandonedDraft(
        ?string $formName,
        ?string $appStatus,
        iterable $openAttempts,
        ?CarbonInterface $now = null
    ): bool {
        if (! self::isFormP($formName) || self::code($appStatus) !== 'D') {
            return false;
        }

        $seen = 0;
        foreach ($openAttempts as $attempt) {
            $seen++;
            $status = self::code(self::attemptField($attempt, 'status'));
            if ($status !== 'INITIATED') {
                return false;
            }
            if (! self::isOlderThanAbandonAge(self::attemptField($attempt, 'created_at'), $now)) {
                return false;
            }
        }

        return $seen > 0;
    }

    /**
     * The verify_payment entry for exactly this txnid, or null (error bodies, other txnids, strings).
     *
     * @param  array<string, mixed>  $verifyResponse
     * @return array<string, mixed>|null
     */
    public static function transactionDetailsFor(array $verifyResponse, string $txnid): ?array
    {
        if (trim($txnid) === '') {
            return null;
        }

        $root = $verifyResponse['transaction_details'] ?? null;
        if (! is_array($root) || ! array_key_exists($txnid, $root)) {
            return null;
        }

        return is_array($root[$txnid]) ? $root[$txnid] : null;
    }

    /** PayU explicitly says it has no such transaction. */
    public static function isGatewayNotFound(?array $details): bool
    {
        if (! is_array($details) || $details === []) {
            return false;
        }

        $normalise = static fn ($v): string => str_replace('_', ' ', strtolower(trim((string) $v)));
        $status = $normalise($details['status'] ?? '');
        $mihpayid = $normalise($details['mihpayid'] ?? '');

        if ($status === 'not found') {
            return true;
        }

        return $mihpayid === 'not found' && $status === '';
    }

    /** PayU payment page was opened but nothing was paid yet. */
    public static function isGatewayPageInitiated(?array $details): bool
    {
        if (! is_array($details)) {
            return false;
        }

        return strtolower(trim((string) ($details['unmappedstatus'] ?? ''))) === 'initiated';
    }

    /**
     * A verified gateway payment belongs to this application and covers the fee
     * (udf1, when present, must be the application id).
     *
     * @param  array<string, mixed>  $details
     */
    public static function verifiedPaymentMatches(mixed $expectedAmount, ?string $applicationId, array $details): bool
    {
        if (! PayUCallbackRules::amountMatches($expectedAmount, $details)) {
            return false;
        }

        $udf1 = trim((string) ($details['udf1'] ?? ''));

        return $udf1 === '' || $udf1 === trim((string) $applicationId);
    }

    /**
     * What to do with an earlier open attempt before starting a new Form P payment.
     *
     * @param  string  $txnStatus  payment_transactions.status of the earlier attempt
     * @param  array<string, mixed>|null  $details  verify_payment entry for that txnid (null = unusable answer)
     * @param  string  $outcome  PayUService::OUTCOME_* resolved from the details
     * @param  bool  $isOld  attempt older than ABANDON_AFTER_MINUTES
     */
    public static function priorAttemptAction(string $txnStatus, ?array $details, string $outcome, bool $isOld): string
    {
        if ($details === null) {
            return self::PRIOR_BLOCK;
        }

        $txnStatus = self::code($txnStatus);
        $outcome = strtolower(trim($outcome));

        if ($outcome === 'success') {
            return self::PRIOR_PAID;
        }
        if ($outcome === 'failed') {
            return self::PRIOR_MARK_FAILED;
        }

        if (self::isGatewayNotFound($details)) {
            if ($txnStatus === 'INITIATED') {
                return self::PRIOR_IGNORE;
            }

            return $isOld ? self::PRIOR_IGNORE : self::PRIOR_BLOCK;
        }

        if ($details === []) {
            return self::PRIOR_BLOCK;
        }

        if (self::isGatewayPageInitiated($details)) {
            return ($txnStatus === 'INITIATED' && $isOld) ? self::PRIOR_IGNORE : self::PRIOR_BLOCK;
        }

        if (strtolower(trim((string) ($details['status'] ?? ''))) === 'pending') {
            return self::PRIOR_BLOCK;
        }

        // Unknown gateway state: an INITIATED row never got a real post-back, a PENDING one did.
        return $txnStatus === 'INITIATED' ? self::PRIOR_IGNORE : self::PRIOR_BLOCK;
    }
}
