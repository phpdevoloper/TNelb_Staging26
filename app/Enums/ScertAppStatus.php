<?php

namespace App\Enums;

enum ScertAppStatus: string
{
    case DRAFT = 'draft';
    case SUBMITTED = 'submitted';
    case DIGITIZATION = 'digitization';
    case ALTERATION = 'alteration';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::SUBMITTED => 'Submitted',
            self::DIGITIZATION => 'Digitization',
            self::ALTERATION => 'Alteration',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::DRAFT => 'secondary',
            self::SUBMITTED => 'success',
            self::DIGITIZATION => 'info',
            self::ALTERATION => 'warning',
        };
    }
}
