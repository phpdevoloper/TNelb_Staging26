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

        $orgs = [];
        $matched = false;

        static::query()
            ->whereDate('bm_date', $ymd)
            ->get(['bm_id', 'bm_no', 'bm_date', 'bm_member', 'bm_members'])
            ->each(static function (self $row) use ($no, &$orgs, &$matched): void {
                $rawNo = $row->getAttributes()['bm_no'] ?? $row->bm_no;
                if (static::normalizeMeetingNo($rawNo) !== $no) {
                    return;
                }

                $matched = true;
                $org = trim((string) ($row->bm_member ?? ''));
                if ($org === '') {
                    return;
                }

                $orgs[$org] ??= [];
                foreach (preg_split('/[,;|\n]+/', (string) ($row->bm_members ?? '')) ?: [] as $name) {
                    $name = trim((string) $name);
                    if ($name !== '' && ! in_array($name, $orgs[$org], true)) {
                        $orgs[$org][] = $name;
                    }
                }
            });

        if (! $matched) {
            return null;
        }

        ksort($orgs);
        foreach ($orgs as &$names) {
            sort($names);
        }
        unset($names);

        return $orgs;
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
