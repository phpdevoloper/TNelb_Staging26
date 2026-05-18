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


if (!function_exists('db_now')) {
    function db_now() {
        return DB::selectOne("SELECT date_trunc('second', NOW()::timestamp) AS db_now")->db_now;
    }
}

?>