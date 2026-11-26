<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Board meeting master rows (`cc_board_details`).
 *
 * @property int $bm_id
 * @property float|string|null $bm_no
 * @property \Illuminate\Support\Carbon|null $bm_date
 * @property string|null $bm_member
 * @property string|null $bm_members
 * @property \Illuminate\Support\Carbon|null $update_at
 * @property \Illuminate\Support\Carbon|null $created_at
 */
class CC_Board_Details extends Model
{
    use HasFactory;

    protected $table = 'cc_board_details';

    protected $primaryKey = 'bm_id';

    public const UPDATED_AT = 'update_at';

    protected $fillable = [
        'bm_no',
        'bm_date',
        'bm_member',
        'bm_members',
        'update_at',
        'created_at',
    ];

    protected $casts = [
        'bm_no' => 'decimal:0',
        'bm_date' => 'date',
        'update_at' => 'date',
        'created_at' => 'date',
    ];

    /** Normalise a stored/typed meeting number: "12", "12.0" and " 12 " all become "12". */
    public static function normalizeMeetingNo(mixed $raw): string
    {
        if ($raw === null || $raw === '') {
            return '';
        }

        return is_numeric($raw)
            ? (string) (int) round((float) $raw)
            : trim((string) $raw);
    }

    /**
     * Look up one board meeting by date + number (Form S 7b gate).
     *
     * Returns the organisations and member names recorded for that meeting, or null when
     * no meeting matches. Only this meeting's data is returned, never the whole master table.
     *
     * @param  string  $ymd  Meeting date, YYYY-MM-DD.
     * @param  string  $meetingNo  Meeting number as typed by the applicant.
     * @return array<string, list<string>>|null  [organisation => [member names]]
     */
    public static function lookupMeeting(string $ymd, string $meetingNo): ?array
    {
        $no = static::normalizeMeetingNo($meetingNo);
        if ($no === '' || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $ymd)) {
            return null;
        }

        $rows = [];
        $matched = false;

        static::query()
            ->whereDate('bm_date', $ymd)
            ->get(['bm_id', 'bm_no', 'bm_date', 'bm_member', 'bm_members'])
            ->each(static function (self $row) use ($no, &$rows, &$matched): void {
                $rawNo = $row->getAttributes()['bm_no'] ?? $row->bm_no;
                if (static::normalizeMeetingNo($rawNo) !== $no) {
                    return;
                }

                $matched = true;
                $rows[] = [(string) ($row->bm_member ?? ''), (string) ($row->bm_members ?? '')];
            });

        return $matched ? static::groupOrganisations($rows) : null;
    }

    /**
     * Representing Organisation dropdown order given by ELB (Form S New item 13 / Digitisation item 15).
     * Organisations not in this list follow, alphabetically.
     */
    public const ORGANISATION_ORDER = [
        'President',
        'Secretary',
        'GM/HR/TANGEDCO',
        'SE/TANGEDCO/Ennore',
        'DGM/NLC',
        'DOTE/CPT HOD',
        'SE/TANGEDCO/Central',
        'GM/HRM.TANGEDCO',
        'SE/Ennore',
        'SE/PWD',
        'SE/CEA',
        'Employment & Training',
        'NPTI',
        'GM/NLC',
    ];

    /**
     * [organisation => [member names]] from raw (bm_member, bm_members) pairs. Organisations that differ
     * only in case / spaces ("president" / "President") are merged under the ELB spelling (or the first one
     * seen); members are de-duplicated case-insensitively and sorted. Pure, so it can be unit-tested.
     *
     * @param  list<array{0:string,1:string}>  $rows
     * @return array<string, list<string>>
     */
    public static function groupOrganisations(array $rows): array
    {
        $canonical = [];
        foreach (self::ORGANISATION_ORDER as $name) {
            $canonical[self::organisationKey($name)] = $name;
        }

        $labels = [];
        $members = [];
        foreach ($rows as [$org, $memberList]) {
            $org = trim((string) preg_replace('/\s+/', ' ', (string) $org));
            if ($org === '') {
                continue;
            }

            $key = self::organisationKey($org);
            $labels[$key] ??= $canonical[$key] ?? $org;
            $members[$key] ??= [];
            foreach (preg_split('/[,;|\n]+/', (string) $memberList) ?: [] as $name) {
                $name = trim((string) $name);
                if ($name !== '') {
                    $members[$key][mb_strtolower($name, 'UTF-8')] ??= $name;
                }
            }
        }

        $rank = array_flip(array_keys($canonical));
        $keys = array_keys($labels);
        usort($keys, static function (string $a, string $b) use ($rank, $labels): int {
            $ra = $rank[$a] ?? PHP_INT_MAX;
            $rb = $rank[$b] ?? PHP_INT_MAX;

            return $ra !== $rb ? $ra <=> $rb : strcasecmp($labels[$a], $labels[$b]);
        });

        $orgs = [];
        foreach ($keys as $key) {
            $names = array_values($members[$key]);
            sort($names);
            $orgs[$labels[$key]] = $names;
        }

        return $orgs;
    }

    /**
     * The organisation key in a lookupMeeting() result that matches $org ignoring case / extra spaces,
     * so values saved as "president" still match "President". Null when the meeting has no such organisation.
     *
     * @param  array<string, list<string>>  $meeting
     */
    public static function findOrganisation(array $meeting, string $org): ?string
    {
        $wanted = self::organisationKey($org);
        if ($wanted === '') {
            return null;
        }

        foreach (array_keys($meeting) as $name) {
            if (self::organisationKey((string) $name) === $wanted) {
                return (string) $name;
            }
        }

        return null;
    }

    /** True when $member is one of $members, ignoring case and surrounding spaces. */
    public static function hasMember(array $members, string $member): bool
    {
        $wanted = mb_strtolower(trim($member), 'UTF-8');
        if ($wanted === '') {
            return false;
        }

        foreach ($members as $name) {
            if (mb_strtolower(trim((string) $name), 'UTF-8') === $wanted) {
                return true;
            }
        }

        return false;
    }

    private static function organisationKey(string $org): string
    {
        return mb_strtolower(trim((string) preg_replace('/\s+/', ' ', $org)), 'UTF-8');
    }

    /**
     * Master list for Form S 7b dependent dropdowns (date → meeting no → org/member).
     *
     * @return list<array{bm_id:int,bm_no:string,bm_date:string,bm_member:string,bm_members:string}>
     */
    public static function masterForFormS7b(): array
    {
        return static::query()
            ->orderByDesc('bm_date')
            ->orderBy('bm_no')
            ->get(['bm_id', 'bm_no', 'bm_date', 'bm_member', 'bm_members'])
            ->map(static function (self $row): array {
                $rawNo = $row->getAttributes()['bm_no'] ?? $row->bm_no;
                $bmNo = static::normalizeMeetingNo($rawNo);

                $bmDate = $row->bm_date;
                $dateStr = $bmDate
                    ? $bmDate->format('Y-m-d')
                    : '';

                return [
                    'bm_id' => (int) $row->bm_id,
                    'bm_no' => $bmNo,
                    'bm_date' => $dateStr,
                    'bm_member' => (string) ($row->bm_member ?? ''),
                    'bm_members' => (string) ($row->bm_members ?? ''),
                ];
            })
            ->filter(static fn (array $row): bool => $row['bm_date'] !== '' && $row['bm_no'] !== '')
            ->values()
            ->all();
    }
}
