<?php

namespace App\Services\Competency;

use App\Casts\CalendarDate;
use Carbon\Carbon;

/**
 * Form P chronology between Date of Birth, year of passing, institute training and power-station
 * experience. Pure (no database): feed it the posted arrays, get back [field => message].
 *
 * Rules:
 *  - year of passing is at least {@see MIN_YEARS_BIRTH_TO_PASSING} years after the year of birth
 *    and not in the future;
 *  - training and power-station From dates are not earlier than the latest year (and month, when
 *    given) of passing;
 *  - power-station experience cannot start before age 18;
 *  - no From/To date is in the future.
 */
class FormPChronology
{
    public const MIN_YEARS_BIRTH_TO_PASSING = 18;

    public const MIN_AGE_FOR_EXPERIENCE = 18;

    /**
     * @param  array<string, mixed>  $input  posted fields: d_o_b, year_of_passing[], month_of_passing[],
     *                                       from_date[], to_date[] (training), work_date_from[], work_date_to[]
     * @return array<string, string>  field key (e.g. "year_of_passing.0") => message
     */
    public static function errorsForInput(array $input, ?Carbon $today = null): array
    {
        $dobYmd = self::ymd($input['d_o_b'] ?? null);

        $edu = [];
        $years = is_array($input['year_of_passing'] ?? null) ? $input['year_of_passing'] : [];
        $months = is_array($input['month_of_passing'] ?? null) ? $input['month_of_passing'] : [];
        foreach ($years as $key => $year) {
            $edu[] = [
                'key' => $key,
                'year' => is_scalar($year) ? (int) $year : 0,
                'month' => isset($months[$key]) && is_scalar($months[$key]) ? (int) $months[$key] : 0,
            ];
        }

        return self::errors(
            $dobYmd,
            $edu,
            self::dateRows($input['from_date'] ?? [], $input['to_date'] ?? []),
            self::dateRows($input['work_date_from'] ?? [], $input['work_date_to'] ?? []),
            $today
        );
    }

    /**
     * @param  list<array{key: int|string, year: int, month: int}>  $edu
     * @param  list<array{key: int|string, from: ?string, to: ?string}>  $training
     * @param  list<array{key: int|string, from: ?string, to: ?string}>  $work
     * @return array<string, string>
     */
    public static function errors(?string $dobYmd, array $edu, array $training, array $work, ?Carbon $today = null): array
    {
        $today = ($today ?? Carbon::now())->copy()->startOfDay();
        $dob = self::dateOrNull($dobYmd);
        $errors = [];

        $latest = null; // first day of the latest month of passing
        foreach ($edu as $row) {
            $year = (int) $row['year'];
            $month = (int) $row['month'];
            if ($year <= 0) {
                continue;
            }

            $field = 'year_of_passing.'.$row['key'];
            if ($dob !== null && $year < $dob->year + self::MIN_YEARS_BIRTH_TO_PASSING) {
                $errors[$field] = 'Year of passing must be at least '.self::MIN_YEARS_BIRTH_TO_PASSING.' years after the year of birth.';
            } elseif ($year > $today->year || ($year === $today->year && $month > $today->month)) {
                $errors[$field] = 'Year of passing cannot be in the future.';
            }

            $monthStart = Carbon::create($year, $month >= 1 && $month <= 12 ? $month : 1, 1)->startOfDay();
            if ($latest === null || $monthStart->gt($latest)) {
                $latest = $monthStart;
            }
        }

        $earliestExperience = $dob?->copy()->addYears(self::MIN_AGE_FOR_EXPERIENCE);

        foreach ($training as $row) {
            self::checkPeriod($errors, 'from_date.'.$row['key'], 'to_date.'.$row['key'], 'Training', $row, $latest, null, $dob, $today);
        }
        foreach ($work as $row) {
            self::checkPeriod($errors, 'work_date_from.'.$row['key'], 'work_date_to.'.$row['key'], 'Experience', $row, $latest, $earliestExperience, $dob, $today);
        }

        return $errors;
    }

    /**
     * @param  array<string, string>  $errors
     * @param  array{key: int|string, from: ?string, to: ?string}  $row
     */
    private static function checkPeriod(array &$errors, string $fromField, string $toField, string $label, array $row, ?Carbon $latestPassing, ?Carbon $earliestStart, ?Carbon $dob, Carbon $today): void
    {
        $from = self::dateOrNull($row['from']);
        if ($from !== null) {
            if ($from->gt($today)) {
                $errors[$fromField] = $label.' From date cannot be in the future.';
            } elseif ($earliestStart !== null && $from->lt($earliestStart)) {
                $errors[$fromField] = $label.' cannot start before age '.self::MIN_AGE_FOR_EXPERIENCE.' (Date of Birth: '.$dob->format('d-m-Y').').';
            } elseif ($latestPassing !== null && $from->lt($latestPassing)) {
                $errors[$fromField] = $label.' From date cannot be earlier than the latest year of passing ('.$latestPassing->format('m/Y').').';
            }
        }

        $to = self::dateOrNull($row['to']);
        if ($to !== null && $to->gt($today)) {
            $errors[$toField] = $label.' To date cannot be in the future.';
        }
    }

    /**
     * @return list<array{key: int|string, from: ?string, to: ?string}>
     */
    private static function dateRows(mixed $from, mixed $to): array
    {
        $from = is_array($from) ? $from : [];
        $to = is_array($to) ? $to : [];
        $rows = [];
        foreach ($from + $to as $key => $_) {
            $rows[] = [
                'key' => $key,
                'from' => self::ymd($from[$key] ?? null),
                'to' => self::ymd($to[$key] ?? null),
            ];
        }

        return $rows;
    }

    private static function ymd(mixed $value): ?string
    {
        if (! is_scalar($value) || trim((string) $value) === '') {
            return null;
        }

        return CalendarDate::ymd(trim((string) $value));
    }

    private static function dateOrNull(?string $ymd): ?Carbon
    {
        if ($ymd === null || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $ymd)) {
            return null;
        }

        try {
            return Carbon::createFromFormat('Y-m-d', $ymd)->startOfDay();
        } catch (\Throwable) {
            return null;
        }
    }
}
