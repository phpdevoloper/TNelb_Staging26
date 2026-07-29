<?php

namespace App\Support;

use Illuminate\Support\Collection;

/**
 * Splits Form S work experience rows into §7a (previous experience rows) and §7b
 * (single board-member form — not a multi-row experience type).
 */
class FormSExperiencePartition
{
    public const BOARD_MEMBER_TYPE = 'board_member_tnelb';

    public static function mapEmpType(string $raw): string
    {
        return match ($raw) {
            'board_member' => self::BOARD_MEMBER_TYPE,
            default => $raw,
        };
    }

    public static function isBoardMemberRow(object $row): bool
    {
        return self::mapEmpType((string) ($row->emp_type ?? '')) === self::BOARD_MEMBER_TYPE;
    }

    /**
     * @param  iterable<int, object>  $expDetails
     * @return array{standard: Collection, boardMember: Collection}
     */
    public static function splitBoardMember(iterable $expDetails): array
    {
        $standard = collect();
        $boardMember = collect();

        foreach ($expDetails as $expRow) {
            if (self::isBoardMemberRow($expRow)) {
                $boardMember->push($expRow);
            } else {
                $standard->push($expRow);
            }
        }

        return [
            'standard' => $standard->values(),
            'boardMember' => $boardMember->values(),
        ];
    }

    /**
     * §7a = all normal experience rows (including open-ended / Till date).
     * §7b = at most one board-member row for the one-time board form.
     *
     * @param  iterable<int, object>  $expDetails
     * @return array{previous: Collection, current: Collection, is7bBoardMemberPrefill: bool}
     */
    public static function partition(iterable $expDetails): array
    {
        $split = self::splitBoardMember($expDetails);
        $previous = $split['standard'];
        // One-time form: keep only the first board-member record if several exist.
        $current = $split['boardMember']->take(1)->values();

        return [
            'previous' => $previous,
            'current' => $current,
            'is7bBoardMemberPrefill' => $current->isNotEmpty(),
        ];
    }
}
