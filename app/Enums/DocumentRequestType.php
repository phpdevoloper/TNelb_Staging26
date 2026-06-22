<?php

namespace App\Enums;

enum DocumentRequestType: string
{
    case INITIAL = 'INITIAL';
    case RENEWAL = 'RENEWAL';
    case ALTERATION = 'ALTERATION';

    public function label(): string
    {
        return match ($this) {
            self::INITIAL => 'Initial Upload',
            self::RENEWAL => 'Renewal Request',
            self::ALTERATION => 'Alteration Request',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::INITIAL => 'info',
            self::RENEWAL => 'primary',
            self::ALTERATION => 'primary',
        };
    }
}
