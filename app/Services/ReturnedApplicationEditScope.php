<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Maps staff "return to applicant" checkbox labels (stored in tnelb_return_to_applicant_log.query_types)
 * to editable sections on applicant-facing returned-application screens (competency S/W/WH and Form P).
 */
final class ReturnedApplicationEditScope
{
    public const SECTION_FULL = 'full';

    public const SECTION_EDUCATION = 'education';

    public const SECTION_EXPERIENCE = 'experience';

    public const SECTION_PHOTO = 'photo';

    public const SECTION_SIGNATURE = 'signature';

    public const SECTION_AADHAAR_DOC = 'aadhaar_doc';

    public const SECTION_PAN_DOC = 'pan_doc';

    /** Personal details, licence/certificate verification fields, Aadhaar/PAN numbers (not file uploads). */
    public const SECTION_APPLICANT = 'applicant';

    /**
     * @var array<string, list<string>>
     */
    private static array $reasonToSections = [
        'Education document is missing' => [self::SECTION_EDUCATION],
        'Photo is missing' => [self::SECTION_PHOTO],
        'Signature is missing' => [self::SECTION_SIGNATURE],
        'Aadhaar document is missing' => [self::SECTION_AADHAAR_DOC],
        'PAN document is missing' => [self::SECTION_PAN_DOC],
        'Other' => [self::SECTION_FULL],
    ];

    public static function latestReturnLogRow(string $applicationId): ?object
    {
        if (! Schema::hasTable('tnelb_return_to_applicant_log')) {
            return null;
        }

        return DB::table('tnelb_return_to_applicant_log')
            ->where('application_id', $applicationId)
            ->orderByDesc('id')
            ->first();
    }

    /**
     * @return list<string>
     */
    public static function parseQueryTypesJson($queryTypesRaw): array
    {
        $items = is_string($queryTypesRaw) ? json_decode($queryTypesRaw, true) : $queryTypesRaw;
        if (! is_array($items)) {
            $items = ($queryTypesRaw !== null && $queryTypesRaw !== '' && is_string($queryTypesRaw))
                ? [$queryTypesRaw]
                : [];
        }
        $out = [];
        foreach ($items as $item) {
            if (is_string($item) && $item !== '') {
                $out[] = $item;
            }
        }

        return array_values(array_unique($out));
    }

    /**
     * @param  list<string>  $reasons  Values from return log / applicant-facing validation list
     * @return list<string> Unique section keys; SECTION_FULL alone means entire form editable
     */
    public static function editableSectionsFromReasons(array $reasons): array
    {
        $reasons = array_values(array_filter($reasons, static fn ($r) => is_string($r) && $r !== ''));

        if ($reasons === []) {
            return [self::SECTION_FULL];
        }

        $bag = [];
        foreach ($reasons as $reason) {
            $sections = self::$reasonToSections[$reason] ?? null;
            if ($sections === null) {
                continue;
            }
            foreach ($sections as $s) {
                $bag[$s] = true;
            }
        }

        if ($bag === []) {
            return [self::SECTION_FULL];
        }

        if (isset($bag[self::SECTION_FULL])) {
            return [self::SECTION_FULL];
        }

        return array_keys($bag);
    }

    /**
     * @param  list<string>  $sections
     */
    public static function isFullUnlock(array $sections): bool
    {
        return $sections === [] || in_array(self::SECTION_FULL, $sections, true);
    }

    /**
     * Form P edit screen uses data-section-key on .fs-section. Map unlock keys to those keys.
     *
     * @param  list<string>  $editableSections  Output of editableSectionsFromReasons()
     * @return list<string> Visible section keys to keep editable; empty means all (full unlock)
     */
    public static function formPSectionKeysForPartialUi(array $editableSections): array
    {
        if (self::isFullUnlock($editableSections)) {
            return [];
        }

        $keys = [];
        foreach ($editableSections as $s) {
            if ($s === self::SECTION_EDUCATION || $s === self::SECTION_EXPERIENCE) {
                $keys['qualifications'] = true;
            }
            if ($s === self::SECTION_PHOTO || $s === self::SECTION_SIGNATURE || $s === self::SECTION_AADHAAR_DOC || $s === self::SECTION_PAN_DOC) {
                $keys['uploads'] = true;
            }
            if ($s === self::SECTION_APPLICANT) {
                $keys['personal'] = true;
                $keys['contact'] = true;
                $keys['prev_license'] = true;
                $keys['wireman_cert'] = true;
            }
        }

        return array_keys($keys);
    }
}
