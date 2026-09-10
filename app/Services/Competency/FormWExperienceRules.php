<?php

namespace App\Services\Competency;

use App\Services\FormS\FormSWorkTillDate;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Validator;

/**
 * Form W work-experience policy for New, Renewal, Digitisation, and Alteration.
 *
 * Work rows are optional. Voltage / nature / transformer and Form S 730-day
 * (650V exclusion) minimums do not apply.
 */
final class FormWExperienceRules
{
    public function usesFormSCountableMinimum(): bool
    {
        return false;
    }

    public function validatePostedRows(Request $request, Validator $validator): void
    {
        $levels = is_array($request->work_level ?? null) ? $request->work_level : [];
        $exps = is_array($request->experience ?? null) ? $request->experience : [];
        $designations = is_array($request->designation ?? null) ? $request->designation : [];
        $fromDates = is_array($request->work_date_from ?? null) ? $request->work_date_from : [];
        $toDates = is_array($request->work_date_to ?? null) ? $request->work_date_to : [];
        $sections = is_array($request->work_exp_section ?? null) ? $request->work_exp_section : [];
        $tillFlags = is_array($request->work_to_till_date ?? null) ? $request->work_to_till_date : [];

        $max = max(
            count($levels),
            count($exps),
            count($designations),
            count($fromDates),
            count($toDates),
            count($sections)
        );

        for ($i = 0; $i < $max; $i++) {
            $section = strtolower(trim((string) ($sections[$i] ?? '')));
            $wl = trim((string) ($levels[$i] ?? ''));
            $ex = trim((string) ($exps[$i] ?? ''));
            $des = trim((string) ($designations[$i] ?? ''));
            $from = trim((string) ($fromDates[$i] ?? ''));
            $to = trim((string) ($toDates[$i] ?? ''));
            $isTill = FormSWorkTillDate::isChecked($tillFlags[$i] ?? '0');
            $isCurrent = $section === 'current';

            $any = ($wl !== '' || $ex !== '' || $des !== '' || $from !== '' || $to !== '' || $isTill);
            if (! $any) {
                continue;
            }

            if ($wl === '') {
                $validator->errors()->add("work_level.$i", 'Work level is required.');
            }
            if ($des === '') {
                $validator->errors()->add("designation.$i", 'Designation is required.');
            }
            if (! $isCurrent) {
                if ($from === '') {
                    $validator->errors()->add("work_date_from.$i", 'From date is required.');
                }
                if ($to === '' && ! $isTill) {
                    $validator->errors()->add("work_date_to.$i", 'To date is required.');
                }
            }
            if ($ex === '') {
                $validator->errors()->add("experience.$i", 'Experience (in years) is required.');
            }

            if (! $isCurrent && $from !== '' && ($to !== '' || $isTill)) {
                try {
                    $fromC = Carbon::parse($from)->startOfDay();
                    $toEff = $to !== ''
                        ? $to
                        : (FormSWorkTillDate::toDateString($tillFlags[$i] ?? '0') ?? now()->toDateString());
                    $toC = Carbon::parse($toEff)->startOfDay();
                    if ($toC->lt($fromC)) {
                        $validator->errors()->add("work_date_to.$i", 'To date must be greater than or equal to From date.');
                    }
                } catch (\Throwable $e) {
                    // Invalid dates are covered by the field-level rules above.
                }
            }
        }
    }
}
