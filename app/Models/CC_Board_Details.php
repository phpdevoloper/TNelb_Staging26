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
                $bmNo = '';
                if ($rawNo !== null && $rawNo !== '') {
                    $bmNo = is_numeric($rawNo)
                        ? (string) (int) round((float) $rawNo)
                        : trim((string) $rawNo);
                }

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
