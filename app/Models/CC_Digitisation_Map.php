<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class CC_Digitisation_Map extends Model
{
    use HasFactory;

    protected $table = 'cc_digitisation_map';

    protected $primaryKey = 'm_id';

    protected $dateFormat = 'Y-m-d';

    protected $fillable = [
        'application_id',
        'old_cc_no',
        'new_cc_no',
        'updated_by',
        'updated_at',
        'created_at',
        'temp_id',
        'cc_type',
    ];

    protected $casts = [
        'old_cc_no' => 'integer',
        'updated_by' => 'integer',
        'created_at' => 'date',
        'updated_at' => 'date',
    ];

    public function digitization(): BelongsTo
    {
        return $this->belongsTo(Tnelb_CC_Digitization::class, 'application_id', 'application_id');
    }

    public static function findByApplicationId(string $applicationId): ?self
    {
        $applicationId = trim($applicationId);
        if ($applicationId === '') {
            return null;
        }

        return self::query()->where('application_id', $applicationId)->first();
    }

    # Update New CC No
    # @param string $application_id
    # @param string $licenseNumber
    # @return bool
    public static function UpdateNewCCNo(string $application_id, string $licenseNumber): bool
    {
        return self::where('application_id', $application_id)->update([
            'new_cc_no' => $licenseNumber,
            'updated_by' => Auth::user()->roles_id ?? 0,
            'updated_at' => db_now()
        ]);
    }
}
