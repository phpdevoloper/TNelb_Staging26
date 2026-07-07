<?php

namespace App\Support;

use Illuminate\Support\Collection;

/**
 * Splits Form S work experience rows into §7a (previous) and §7b (current / board member).
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
     * @param  iterable<int, object>  $expDetails
     * @return array{previous: Collection, current: Collection, is7bBoardMemberPrefill: bool}
     */
    public static function partition(iterable $expDetails): array
    {
        $previous = collect();
        $current = collect();

        foreach ($expDetails as $expRow) {
            $empType = self::mapEmpType((string) ($expRow->emp_type ?? ''));

            if ($empType === self::BOARD_MEMBER_TYPE) {
                $current->push($expRow);
                continue;
            }

            $fromDate = $expRow->from_date ?? null;
            $toDate = $expRow->to_date ?? null;
            $isOpenEnded = $fromDate && ($toDate === null || $toDate === '');

            if ($isOpenEnded) {
                $current->push($expRow);
            } else {
                $previous->push($expRow);
            }
        }

        $is7bBoardMember = $current->contains(
            fn ($row) => self::mapEmpType((string) ($row->emp_type ?? '')) === self::BOARD_MEMBER_TYPE
        );

        return [
            'previous' => $previous,
            'current' => $current,
            'is7bBoardMemberPrefill' => $is7bBoardMember,
        ];
    }
}
