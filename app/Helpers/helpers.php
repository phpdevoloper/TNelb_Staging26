<?php 

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Crypt;

if (!function_exists('format_date_input')) {
    function format_date_input($date)
    {
        return $date ? \Carbon\Carbon::createFromFormat('d-m-Y', $date)->format('Y-m-d') : '';
    }
}

if (!function_exists('format_date')) {
    function format_date($date)
    {
        return Carbon::parse($date)->format('d-m-Y');
    }
}

if (!function_exists('format_date_other')) {
    function format_date_other($date)
    {
        return Carbon::parse($date)->format('d-m-Y h:i:s A'); // 12-hour format with AM/PM
    }
}


if (!function_exists('activeMenu')) {
    function activeMenu($pattern, $class = 'active') {
        return Request::is($pattern) ? $class : '';
    }
}

if (!function_exists('activeParent')) {
    function activeParent(array $patterns, $class = 'active') {
        foreach ($patterns as $pattern) {
            if (Request::is($pattern)) {
                return $class;
            }
        }
        return '';
    }
}

function calculateDaysDifference($givenDate)
{
    // Create DateTime objects for the given date and current date
    $now = new DateTime(); // current date
    $date = new DateTime($givenDate); // given date
    
    // Calculate the difference
    $interval = $now->diff($date);
    
    // Return the number of days
    return $interval->days;
}

if (!function_exists('format_edu_passing_month')) {
    /**
     * Education "month of passing": numeric 1–12 / 01–12 → Jan, Feb, …; recognises 3-letter names.
     *
     * @param  mixed  $month  Raw value from DB (e.g. month_passing)
     */
    function format_edu_passing_month($month): string
    {
        $raw = trim((string) ($month ?? ''));
        if ($raw === '') {
            return '';
        }

        static $labels = [
            '01' => 'Jan', '02' => 'Feb', '03' => 'Mar', '04' => 'Apr',
            '05' => 'May', '06' => 'Jun', '07' => 'Jul', '08' => 'Aug',
            '09' => 'Sep', '10' => 'Oct', '11' => 'Nov', '12' => 'Dec',
        ];

        if (ctype_digit($raw)) {
            $key = str_pad($raw, 2, '0', STR_PAD_LEFT);

            return $labels[$key] ?? $raw;
        }

        $alpha = strtolower(substr($raw, 0, 3));
        $alphaMap = [
            'jan' => 'Jan', 'feb' => 'Feb', 'mar' => 'Mar', 'apr' => 'Apr',
            'may' => 'May', 'jun' => 'Jun', 'jul' => 'Jul', 'aug' => 'Aug',
            'sep' => 'Sep', 'oct' => 'Oct', 'nov' => 'Nov', 'dec' => 'Dec',
        ];

        return $alphaMap[$alpha] ?? $raw;
    }
}

if (!function_exists('safeDecrypt')) {
    /**
     * Safely decrypt a value. Returns null if value is empty or decryption fails.
     *
     * @param mixed $value
     * @return string|null
     */
    function safeDecrypt($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        try {
            return Crypt::decryptString($value);
        } catch (\Throwable $e) {
            return null;
        }
    }
}

if (!function_exists('displayProofNumber')) {
    /**
     * Show Aadhaar/PAN from encrypted cc_proof_doc.proof_no or legacy plain values.
     */
    function displayProofNumber($value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        return (string) (app(\App\Services\FormS\SensitiveProofCryptService::class)
            ->decryptProofNumber((string) $value) ?? '');
    }
}


if (!function_exists('format_total_exp_years')) {
    /**
     * Normalize work-experience years for storage/display (e.g. 2.0 instead of 2.00).
     *
     * @param  mixed  $value
     * @return string|null
     */
    function format_total_exp_years($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (is_string($value)) {
            $value = trim($value);
            if ($value === '') {
                return null;
            }
        }
        if (! is_numeric($value)) {
            return (string) $value;
        }

        return number_format((float) $value, 1, '.', '');
    }
}

if (! function_exists('legacy_total_exp_from_duration')) {
    /**
     * Legacy decimal-years value for experience[] / total_exp when only Y/M/D (or nothing) is stored.
     * Prefers an existing total_exp; otherwise builds from total_y / total_m / total_d.
     *
     * @param  mixed  $totalExp
     * @param  mixed  $years
     * @param  mixed  $months
     * @param  mixed  $days
     */
    function legacy_total_exp_from_duration($totalExp, $years = null, $months = null, $days = null): string
    {
        $formatted = format_total_exp_years($totalExp);
        if ($formatted !== null) {
            return $formatted;
        }

        $y = (int) ($years ?? 0);
        $m = (int) ($months ?? 0);
        $d = (int) ($days ?? 0);
        if ($y === 0 && $m === 0 && $d === 0) {
            return '';
        }

        $yearsDec = $y + ($m / 12) + ($d / 365.25);

        return number_format(round($yearsDec * 10) / 10, 1, '.', '');
    }
}


if (!function_exists('db_now')) {
    function db_now() {
        return DB::selectOne("SELECT date_trunc('second', NOW()::timestamp) AS db_now")->db_now;
    }
}

if (!function_exists('competency_document_log_download_url')) {
    /**
     * @param  \App\Models\DocumentsLog|\App\Models\CC_Doc_Log|null  $log
     */
    function competency_document_log_download_url($log): ?string
    {
        if (!$log) {
            return null;
        }

        $logId = $log->getKey();
        if ($logId === null) {
            return null;
        }

        if (\Illuminate\Support\Facades\Route::has('competency.documents.download')) {
            return route('competency.documents.download', $logId);
        }

        return route('form-s.documents.download', $logId);
    }
}

if (!function_exists('form_s_document_log_download_url')) {
    /** @deprecated Use competency_document_log_download_url() */
    function form_s_document_log_download_url(?\App\Models\DocumentsLog $log): ?string
    {
        return competency_document_log_download_url($log);
    }
}

if (!function_exists('competency_find_document_log')) {
    /**
     * Locate the active documents_log row for a competency workflow document group.
     *
     * @param  list<int>  $workflowAppPks  tnelb_application_tbl.id values (child first, then parent)
     */
    function competency_find_document_log(
        string $moduleType,
        int $moduleRefId,
        string $documentType = 'certificate',
        array $workflowAppPks = [],
        ?string $storedPath = null
    ): ?\App\Models\DocumentsLog {
        $workflowAppPks = array_values(array_unique(array_filter(array_map('intval', $workflowAppPks))));

        foreach ($workflowAppPks as $workflowAppPk) {
            if ($workflowAppPk <= 0) {
                continue;
            }

            $log = \App\Models\DocumentsLog::query()
                ->where('application_id', $workflowAppPk)
                ->where('module_type', $moduleType)
                ->where('module_ref_id', $moduleRefId)
                ->where('document_type', $documentType)
                ->where('is_active', true)
                ->orderByDesc('id')
                ->first();

            if ($log) {
                return $log;
            }
        }

        if ($storedPath !== null && trim($storedPath) !== '') {
            $storedPath = trim($storedPath);

            return \App\Models\DocumentsLog::query()
                ->where('is_active', true)
                ->where(function ($q) use ($storedPath) {
                    $q->where('file_path', $storedPath)
                        ->orWhere('old_file_path', $storedPath);
                })
                ->orderByDesc('id')
                ->first();
        }

        return null;
    }
}

if (!function_exists('competency_find_cc_document_log')) {
    /**
     * Locate the active cc_doc_log row for a competency workflow document group.
     *
     * @param  list<int>  $workflowAppPks  cc_form_s_meta.app_id values (child first, then parent)
     */
    function competency_find_cc_document_log(
        string $moduleType,
        int $moduleRefId,
        string $documentType = 'certificate',
        array $workflowAppPks = [],
        ?string $storedPath = null
    ): ?\App\Models\CC_Doc_Log {
        $workflowAppPks = array_values(array_unique(array_filter(array_map('intval', $workflowAppPks))));

        foreach ($workflowAppPks as $workflowAppPk) {
            if ($workflowAppPk <= 0) {
                continue;
            }

            $log = \App\Models\CC_Doc_Log::query()
                ->where('application_id', $workflowAppPk)
                ->where('module_type', $moduleType)
                ->where('module_ref_id', $moduleRefId)
                ->where('document_type', $documentType)
                ->where('is_active', true)
                ->orderByDesc('doc_id')
                ->first();

            if ($log) {
                return $log;
            }
        }

        if ($storedPath !== null && trim($storedPath) !== '') {
            $storedPath = trim($storedPath);

            return \App\Models\CC_Doc_Log::query()
                ->where('is_active', true)
                ->where(function ($q) use ($storedPath) {
                    $q->where('file_path', $storedPath)
                        ->orWhere('old_file_path', $storedPath);
                })
                ->orderByDesc('doc_id')
                ->first();
        }

        return null;
    }
}

if (!function_exists('form_s_find_document_log')) {
    /** @deprecated Use competency_find_document_log() */
    function form_s_find_document_log(
        string $moduleType,
        int $moduleRefId,
        string $documentType = 'certificate',
        array $workflowAppPks = [],
        ?string $storedPath = null
    ): ?\App\Models\DocumentsLog {
        return competency_find_document_log($moduleType, $moduleRefId, $documentType, $workflowAppPks, $storedPath);
    }
}

if (!function_exists('competency_document_path_url')) {
    /**
     * Build browser URL from stored DB path using external upload path configuration.
     * Example: {APP_URL}/competency/FORM_S/NEW/EDUCATION/260712_SC261111114_EDU_001.pdf
     */
    function competency_document_path_url(?string $storedPath): ?string
    {
        if ($storedPath === null || trim($storedPath) === '') {
            return null;
        }

        $storedPath = trim(str_replace('\\', '/', $storedPath));

        if (str_starts_with($storedPath, 'http://') || str_starts_with($storedPath, 'https://')) {
            return $storedPath;
        }

        if (preg_match('#^FORM_[A-Z]+/#', $storedPath)) {
            return \App\Services\Competency\CompetencyDocumentSupport::publicUrlForStoredPath($storedPath);
        }

        return '/' . ltrim($storedPath, '/');
    }
}

if (!function_exists('proof_document_url')) {
    /**
     * Browser URL for Aadhaar/PAN uploads (versioned FORM_* or legacy encrypted blob).
     */
    function proof_document_url(?string $storedPath, string $legacyType = 'aadhaar'): ?string
    {
        if ($storedPath === null || trim($storedPath) === '') {
            return null;
        }

        $storedPath = trim(str_replace('\\', '/', $storedPath));

        if (preg_match('#^FORM_[A-Z]+/#', $storedPath)) {
            return competency_document_path_url($storedPath);
        }

        if (str_starts_with($storedPath, 'http://') || str_starts_with($storedPath, 'https://')) {
            return $storedPath;
        }

        return route('document.show', [
            'type' => $legacyType,
            'filename' => basename($storedPath),
        ]);
    }
}

if (!function_exists('competency_document_url')) {
    /**
     * Resolve a browser URL for competency documents.
     * Versioned FORM_* paths: /competency/FORM_S/NEW/EDUCATION/...pdf
     *
     * @param  list<int>  $workflowAppPks
     */
    function competency_document_url(
        ?string $storedPath = null,
        string $moduleType = '',
        int $moduleRefId = 0,
        string $documentType = 'certificate',
        array $workflowAppPks = []
    ): ?string {
        $storedPath = $storedPath !== null ? trim((string) $storedPath) : '';

        if ($storedPath !== '') {
            return competency_document_path_url($storedPath);
        }

        $log = null;
        $ccLog = null;

        if ($moduleType !== '' && $moduleRefId > 0) {
            $log = competency_find_document_log($moduleType, $moduleRefId, $documentType, $workflowAppPks, null);
            $ccLog = $log ? null : competency_find_cc_document_log($moduleType, $moduleRefId, $documentType, $workflowAppPks, null);
        }

        if ($ccLog && ! empty($ccLog->file_path)) {
            return competency_document_path_url($ccLog->file_path);
        }

        if ($log && ! empty($log->file_path)) {
            return competency_document_path_url($log->file_path);
        }

        return null;
    }
}

if (!function_exists('form_s_document_url')) {
    /** @deprecated Use competency_document_url() */
    function form_s_document_url(
        ?string $storedPath = null,
        string $moduleType = '',
        int $moduleRefId = 0,
        string $documentType = 'certificate',
        array $workflowAppPks = []
    ): ?string {
        return competency_document_url($storedPath, $moduleType, $moduleRefId, $documentType, $workflowAppPks);
    }
}

if (!function_exists('competency_uses_versioned_storage')) {
    function competency_uses_versioned_storage(?string $formName): bool
    {
        return \App\Services\Competency\CompetencyDocumentSupport::usesVersionedStorage($formName);
    }
}

if (!function_exists('competency_media_url')) {
    /**
     * Resolve a browser URL for applicant photo/signature (or other competency media).
     * Versioned FORM_* paths are served via documents_log download route.
     */
    function competency_media_url(?string $path): ?string
    {
        if ($path === null || trim($path) === '') {
            return null;
        }

        $path = trim($path);

        $log = \App\Models\DocumentsLog::query()
            ->where('is_active', true)
            ->where(function ($q) use ($path) {
                $q->where('file_path', $path)
                    ->orWhere('old_file_path', $path);
            })
            ->orderByDesc('id')
            ->first();

        if ($log) {
            return competency_document_path_url($log->file_path);
        }

        $ccLog = \App\Models\CC_Doc_Log::query()
            ->where('is_active', true)
            ->where(function ($q) use ($path) {
                $q->where('file_path', $path)
                    ->orWhere('old_file_path', $path);
            })
            ->orderByDesc('doc_id')
            ->first();

        if ($ccLog) {
            return competency_document_path_url($ccLog->file_path);
        }

        if (preg_match('#^FORM_[A-Z]+/#', $path)) {
            return competency_document_path_url($path);
        }

        return str_starts_with($path, 'http://') || str_starts_with($path, 'https://')
            ? $path
            : url($path);
    }
}

?>