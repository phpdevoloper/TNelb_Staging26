<?php

namespace App\Enums;

enum ApplicationStatus: string
{
    case DRAFT = 'DRAFT';
    case SUBMITTED = 'SUBMITTED';
    case DIGITIZATION = 'DIGITIZATION';
    case ALTERATION = 'ALTERATION';

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
            self::SUBMITTED => 'info',
            self::DIGITIZATION => 'primary',
            self::ALTERATION => 'warning',
        };
    }
}
