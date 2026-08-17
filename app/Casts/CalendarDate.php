<?php

namespace App\Casts;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Contracts\Database\Eloquent\SerializesCastableAttributes;

/**
 * Store and read a calendar date (Y-m-d) without UTC shifting it back one day
 * (Asia/Kolkata midnight → previous UTC date).
 *
 * Signatures match Laravel 9 CastsAttributes (untyped $model/$value, no return type).
 */
class CalendarDate implements CastsAttributes, SerializesCastableAttributes
{
    /**
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  mixed  $value
     * @param  array<string, mixed>  $attributes
     * @return \Carbon\Carbon|null
     */
    public function get($model, string $key, $value, array $attributes)
    {
        $ymd = self::ymd($value);
        if ($ymd === null) {
            return null;
        }

        return Carbon::createFromFormat('Y-m-d', $ymd, config('app.timezone'))->startOfDay();
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  mixed  $value
     * @param  array<string, mixed>  $attributes
     * @return string|null
     */
    public function set($model, string $key, $value, array $attributes)
    {
        return self::ymd($value);
    }

    /**
     * Keep Y-m-d when the model is converted with toArray() / toJson().
     * Without this, Laravel serializes the Carbon value as UTC ISO-8601
     * (2019-08-17 00:00 IST → 2019-08-16T18:30:00.000000Z).
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  mixed  $value
     * @param  array<string, mixed>  $attributes
     */
    public function serialize($model, string $key, $value, array $attributes)
    {
        return self::ymd($value);
    }

    public static function ymd(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof DateTimeInterface) {
            return Carbon::instance(\DateTime::createFromInterface($value))
                ->timezone((string) config('app.timezone'))
                ->format('Y-m-d');
        }

        $s = trim((string) $value);
        if ($s === '') {
            return null;
        }

        if (preg_match('/^(\d{1,2})[-\/](\d{1,2})[-\/](\d{4})$/', $s, $m)) {
            return sprintf('%04d-%02d-%02d', (int) $m[3], (int) $m[2], (int) $m[1]);
        }

        if (preg_match('/^(\d{4}-\d{2}-\d{2})$/', $s)) {
            return $s;
        }

        // Date-only midnight with no offset — keep the calendar day.
        if (preg_match('/^(\d{4}-\d{2}-\d{2})[ T]00:00:00/', $s) && ! preg_match('/[Zz]|[+-]\d{2}/', $s)) {
            return substr($s, 0, 10);
        }

        // IST midnight written as UTC 18:30 the previous day (timestamp without time zone).
        if (preg_match('/^(\d{4}-\d{2}-\d{2})[ T]18:30:00/', $s) && ! preg_match('/[Zz]|[+-]\d{2}/', $s)) {
            return Carbon::parse($s, 'UTC')->timezone((string) config('app.timezone'))->format('Y-m-d');
        }

        try {
            return Carbon::parse($s)->timezone((string) config('app.timezone'))->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }
}
