<?php

namespace App\Enums;

enum DocumentStorageType: string
{
    case TEMP = 'TEMP';
    case PERMANENT = 'PERMANENT';

    public function label(): string
    {
        return match ($this) {
            self::TEMP => 'Temporary',
            self::PERMANENT => 'Permanent',
        };
    }
}
