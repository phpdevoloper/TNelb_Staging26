<?php

namespace App\Services\FormS;

use App\Casts\CalendarDate;
use App\Models\CC_Board_Details;
use App\Models\CC_Education;
use App\Models\CC_Experience;
use App\Models\CC_Forms_Meta;
use App\Models\Competency\CC_Form_S_Meta;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * Form S submission-time data checks shared by the controller save paths, the alteration service
 * and the payment step:
 *  - chronology between Date of Birth, year of passing and experience dates;
 *  - §7b board meeting rows against the Board records (cc_board_details).
 *
 * New applications (appl_type N) are checked strictly. Renewal / alteration / digitisation (R, A, D)
 * and returned applications may carry rows over unchanged: a row identical to one on the user's
 * record is exempt, but only when the Date of Birth is unchanged too. "On the user's record" means
 * FINALISED Form S applications of the same login (payment status Y/B), never drafts or unpaid
 * applications, so rows cannot be laundered through a throw-away draft. New or changed rows are
 * always checked.
 */
class FormSSubmissionGuard
{
    public const BOARD_MEMBER_EMP_TYPE = 'board_member_tnelb';

    public const CARRY_OVER_APPL_TYPES = ['R', 'A', 'D'];

    public const FINALISED_PAYMENT_STATUSES = ['Y', 'B'];

    /** Experience From date >= DOB + this many years: Form S New and Digitisation (ELB digitisation item 21). */
    public const MIN_EXPERIENCE_AGE = 20;

    /** Previous rule, kept for Form S renewal / alteration (R, A) until the Board extends the 20-year rule. */
    public const LEGACY_MIN_EXPERIENCE_AGE = 18;

    /** 18 for renewal / alteration (R, A), 20 for everything else (New N, Digitisation D, unknown). */
    public static function minExperienceStartAge(mixed $applType): int
    {
        $type = is_scalar($applType) ? strtoupper(trim((string) $applType)) : '';

        return in_array($type, ['R', 'A'], true) ? self::LEGACY_MIN_EXPERIENCE_AGE : self::MIN_EXPERIENCE_AGE;
    }

    public static function allowsCarryOver(mixed $applType): bool
    {
        return is_string($applType)
            && in_array(strtoupper(trim($applType)), self::CARRY_OVER_APPL_TYPES, true);
    }

    /**
     * Rows already on the user's record.
     *
     * @param  bool  $sameApplication  true: what is saved under $applicationId (returned applications);
     *                                 false: rows of the user's OTHER finalised Form S applications.
     * @return array{edu: array<string, true>, work: array<string, true>, board: array<string, true>, dobs: array<string, true>}
     */
    public static function carriedOverRows(string $loginId, ?string $applicationId, bool $sameApplication): array
    {
        $carried = ['edu' => [], 'work' => [], 'board' => [], 'dobs' => []];
        if ($loginId === '') {
            return $carried;
        }

        $formsQuery = CC_Forms_Meta::query()->where('login_id', $loginId)->where('form_name', 'S');
        if ($sameApplication) {
            if ($applicationId === null || $applicationId === '') {
                return $carried;
            }
            $formsQuery->where('application_id', $applicationId);
        } else {
            $formsQuery->whereIn('payment_status', self::FINALISED_PAYMENT_STATUSES);
            if ($applicationId !== null && $applicationId !== '') {
                $formsQuery->where('application_id', '!=', $applicationId);
            }
        }

        $forms = $formsQuery->get(['application_id', 'd_o_b']);
        $applicationIds = $forms->pluck('application_id')->filter()->unique()->values()->all();
        if ($applicationIds === []) {
            return $carried;
        }

        foreach ($forms as $form) {
            $dob = CalendarDate::ymd($form->d_o_b);
            if ($dob !== null) {
                $carried['dobs'][$dob] = true;
            }
        }

        CC_Education::query()
            ->where('login_id', $loginId)
            ->whereIn('application_id', $applicationIds)
            ->get(['year_of_passing', 'month_passing'])
            ->each(function ($row) use (&$carried) {
                $carried['edu'][self::eduKey((int) $row->year_of_passing, (int) $row->month_passing)] = true;
            });

        CC_Experience::query()
            ->where('login_id', $loginId)
            ->whereIn('application_id', $applicationIds)
            ->get(['from_date', 'to_date', 'work_to_till_date', 'emp_type', 'board_meeting_date',
                'board_meeting_details', 'org_name', 'member_name'])
            ->each(function ($row) use (&$carried) {
                $carried['work'][self::workKey(
                    CalendarDate::ymd($row->from_date),
                    CalendarDate::ymd($row->to_date),
                    (int) ($row->work_to_till_date ?? 0) === 1
                )] = true;

                if (strtolower(trim((string) ($row->emp_type ?? ''))) === self::BOARD_MEMBER_EMP_TYPE) {
                    $carried['board'][self::boardKey(
                        (string) CalendarDate::ymd($row->board_meeting_date),
                        (string) $row->board_meeting_details,
                        (string) $row->org_name,
                        (string) $row->member_name
                    )] = true;
                }
            });

        return $carried;
    }

    public static function eduKey(int $year, int $month): string
    {
        return $year.'-'.$month;
    }

    /** Till-date rows store "today" as To on every save, so they are keyed by From only. */
    public static function workKey(?string $fromYmd, ?string $toYmd, bool $till = false): string
    {
        return ($fromYmd ?? '').'|'.($till ? 'TILL' : ($toYmd ?? ''));
    }

    public static function boardKey(string $dateYmd, string $no, string $org, string $member): string
    {
        return $dateYmd.'|'.CC_Board_Details::normalizeMeetingNo($no).'|'.mb_strtolower(trim((string) preg_replace('/\s+/', ' ', $org)), 'UTF-8').'|'.mb_strtolower(trim((string) preg_replace('/\s+/', ' ', $member)), 'UTF-8');
    }

    /**
     * @param  list<array{key: int|string, year: int, month: int}>  $edu
     * @param  list<array{key: int|string, from: ?string, to: ?string, till: bool}>  $work  dates as Y-m-d
     * @param  array|null  $carried  rows exempt from the checks (null = strict)
     * @return array<string, string> [field key => message]
     *
     * "Not in the future" is checked even without a Date of Birth; the DOB-relative rules need one.
     * Carried-over rows are only exempt while the Date of Birth is unchanged.
     */
    public static function chronologyErrors(
        ?string $dobYmd,
        array $edu,
        array $work,
        ?array $carried = null,
        ?int $minExperienceAge = null
    ): array {
        $minExperienceAge ??= self::LEGACY_MIN_EXPERIENCE_AGE;
        $dob = null;
        if ($dobYmd !== null && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dobYmd)) {
            try {
                $dob = Carbon::createFromFormat('Y-m-d', $dobYmd)->startOfDay();
            } catch (\Throwable $e) {
                $dob = null;
            }
        }

        if ($dob === null || ! isset($carried['dobs'][$dobYmd])) {
            $carried = null;
        }

        $today = Carbon::now()->startOfDay();
        $earliestStart = $dob?->copy()->addYears($minExperienceAge);
        $errors = [];

        foreach ($edu as $row) {
            $year = (int) $row['year'];
            $month = (int) $row['month'];
            if ($year <= 0 || isset($carried['edu'][self::eduKey($year, $month)])) {
                continue;
            }

            if ($dob !== null && $year < $dob->year + 15) {
                $errors['year_of_passing.'.$row['key']] = 'Year of passing is not possible for the Date of Birth entered.';
            } elseif ($year > $today->year || ($year === $today->year && $month > $today->month)) {
                $errors['year_of_passing.'.$row['key']] = 'Year of passing cannot be in the future.';
            }
        }

        foreach ($work as $row) {
            if (isset($carried['work'][self::workKey($row['from'], $row['to'], (bool) $row['till'])])) {
                continue;
            }

            $from = self::dateOrNull($row['from']);
            if ($from !== null) {
                if ($earliestStart !== null && $from->lt($earliestStart)) {
                    $errors['work_date_from.'.$row['key']] = $minExperienceAge === self::LEGACY_MIN_EXPERIENCE_AGE
                        ? 'Experience cannot start before age 18 (Date of Birth: '.$dob->format('d-m-Y').').'
                        : 'Experience cannot start before the applicant is '.$minExperienceAge.' years old (Date of Birth: '.$dob->format('d-m-Y').').';
                } elseif ($from->gt($today)) {
                    $errors['work_date_from.'.$row['key']] = 'Experience From date cannot be in the future.';
                }
            }

            $to = $row['till'] ? null : self::dateOrNull($row['to']);
            if ($to !== null && $to->gt($today)) {
                $errors['work_date_to.'.$row['key']] = 'Experience To date cannot be in the future.';
            }
        }

        return $errors;
    }

    private static function dateOrNull(?string $ymd): ?Carbon
    {
        if ($ymd === null || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $ymd)) {
            return null;
        }
        try {
            return Carbon::createFromFormat('Y-m-d', $ymd)->startOfDay();
        } catch (\Throwable $e) {
            return null;
        }
    }

    /** Meeting date + number must exist, and the organisation / member must belong to that meeting. */
    public static function boardRowError(string $dateYmd, string $no, string $org, string $member): ?string
    {
        if ($dateYmd === '' || trim($no) === '') {
            return 'Date of Meeting and Meeting details are required for a Board member.';
        }

        $meeting = CC_Board_Details::lookupMeeting($dateYmd, $no);
        if ($meeting === null) {
            return 'Date of Meeting and Meeting details do not match the Board records.';
        }

        // Case-insensitive: lookupMeeting() merges "president" / "President", and rows saved before that
        // keep whichever spelling the dropdown offered at the time.
        $orgKey = CC_Board_Details::findOrganisation($meeting, $org);
        if ($orgKey === null || ! CC_Board_Details::hasMember($meeting[$orgKey], $member)) {
            return 'Representing Organisation / Name of member does not match the Board records for this meeting.';
        }

        return null;
    }

    /**
     * Chronology errors for the work / education arrays POSTED in a request.
     *
     * @return array<string, string>
     */
    public static function chronologyErrorsForRequest(
        Request $request,
        ?string $dobYmd,
        ?array $carried,
        ?int $minExperienceAge = null
    ): array {
        $edu = [];
        $years = $request->input('year_of_passing', []);
        $months = $request->input('month_of_passing', $request->input('month_passing', []));
        foreach ((is_array($years) ? $years : []) as $key => $rawYear) {
            if (! is_scalar($rawYear)) {
                continue;
            }
            $month = is_array($months) && isset($months[$key]) && is_scalar($months[$key])
                ? (int) ltrim((string) $months[$key], '0')
                : 0;
            $edu[] = ['key' => $key, 'year' => (int) $rawYear, 'month' => $month];
        }

        $work = [];
        $fromDates = $request->input('work_date_from', []);
        $toDates = $request->input('work_date_to', []);
        $tillFlags = $request->input('work_to_till_date', []);
        $toDates = is_array($toDates) ? $toDates : [];
        $tillFlags = is_array($tillFlags) ? $tillFlags : [];
        foreach ((is_array($fromDates) ? $fromDates : []) as $key => $fromRaw) {
            $toRaw = $toDates[$key] ?? '';
            $tillRaw = $tillFlags[$key] ?? '0';
            $work[] = [
                'key' => $key,
                'from' => is_scalar($fromRaw) ? CalendarDate::ymd(trim((string) $fromRaw)) : null,
                'to' => is_scalar($toRaw) ? CalendarDate::ymd(trim((string) $toRaw)) : null,
                'till' => is_scalar($tillRaw) && FormSWorkTillDate::isChecked($tillRaw),
            ];
        }

        return self::chronologyErrors($dobYmd, $edu, $work, $carried, $minExperienceAge);
    }

    /**
     * Board-meeting errors for every board-member row POSTED in a request. The row's employment
     * type decides, never a client-side switch.
     *
     * @return array<string, string>
     */
    public static function boardErrorsForRequest(Request $request, ?array $carried): array
    {
        $types = $request->input('work_employment_type', []);
        $errors = [];
        foreach ((is_array($types) ? $types : []) as $key => $type) {
            if (! is_string($type) || strtolower(trim($type)) !== self::BOARD_MEMBER_EMP_TYPE) {
                continue;
            }

            $scalar = static function ($bag) use ($request, $key): string {
                $values = $request->input($bag, []);
                $v = is_array($values) ? ($values[$key] ?? '') : '';

                return is_scalar($v) ? trim((string) $v) : '';
            };

            $date = (string) CalendarDate::ymd($scalar('work_board_meeting_date'));
            $no = CC_Board_Details::normalizeMeetingNo($scalar('work_board_meeting_details'));
            $org = $scalar('work_employer_name') !== '' ? $scalar('work_employer_name') : $scalar('work_level');
            $member = $scalar('work_board_member_name');

            if (isset($carried['board'][self::boardKey($date, $no, $org, $member)])) {
                continue;
            }

            $error = self::boardRowError($date, $no, $org, $member);
            if ($error !== null) {
                $errors['work_board.'.$key] = $error;
            }
        }

        return $errors;
    }

    /**
     * Payment step: the application becomes "submitted" when PayU is initiated, so re-check the
     * SAVED data here. A client can post anything as a draft; it cannot skip this.
     */
    public static function checkSavedApplication(CC_Form_S_Meta $form): ?string
    {
        if (strtoupper(trim((string) ($form->form_name ?? ''))) !== 'S') {
            return null;
        }

        $applicationId = (string) ($form->application_id ?? '');
        $loginId = (string) ($form->login_id ?? '');
        if ($applicationId === '') {
            return null;
        }

        $dobYmd = CalendarDate::ymd($form->d_o_b);
        if ($dobYmd === null) {
            return 'Date of Birth is required.';
        }

        $carried = self::allowsCarryOver($form->appl_type ?? null)
            ? self::carriedOverRows($loginId, $applicationId, false)
            : null;

        $edu = [];
        foreach (CC_Education::query()->where('application_id', $applicationId)->get(['year_of_passing', 'month_passing']) as $i => $row) {
            $edu[] = ['key' => $i, 'year' => (int) $row->year_of_passing, 'month' => (int) $row->month_passing];
        }

        $work = [];
        $boardRows = [];
        foreach (CC_Experience::query()->where('application_id', $applicationId)->get() as $i => $row) {
            if (strtolower(trim((string) ($row->emp_type ?? ''))) === self::BOARD_MEMBER_EMP_TYPE) {
                $boardRows[] = $row;
            }
            $work[] = [
                'key' => $i,
                'from' => CalendarDate::ymd($row->from_date),
                'to' => CalendarDate::ymd($row->to_date),
                'till' => (int) ($row->work_to_till_date ?? 0) === 1,
            ];
        }

        $errors = self::chronologyErrors(
            $dobYmd,
            $edu,
            $work,
            $carried,
            self::minExperienceStartAge($form->appl_type ?? null)
        );
        if ($errors !== []) {
            return reset($errors);
        }

        foreach ($boardRows as $row) {
            $date = (string) CalendarDate::ymd($row->board_meeting_date);
            $no = (string) $row->board_meeting_details;
            $org = trim((string) $row->org_name);
            $member = trim((string) $row->member_name);
            if (isset($carried['board'][self::boardKey($date, $no, $org, $member)])) {
                continue;
            }

            $error = self::boardRowError($date, $no, $org, $member);
            if ($error !== null) {
                return $error;
            }
        }

        return null;
    }
}
