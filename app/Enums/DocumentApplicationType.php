<?php

namespace App\Enums;

enum DocumentApplicationType: string
{
    case NEW = 'NEW';
    case RENEWAL = 'RENEWAL';
    case DIGITISATION = 'DIGITISATION';
    case ALTERATION = 'ALTERATION';

    public function label(): string
    {
        return match ($this) {
            self::NEW => 'New Application',
            self::RENEWAL => 'Renewal',
            self::DIGITISATION => 'Digitisation',
            self::ALTERATION => 'Alteration',
        };
    }

    public static function fromWorkflowStage(?string $stage): self
    {
        $normalized = strtoupper(trim((string) $stage));

        return match ($normalized) {
            'RENEWAL' => self::RENEWAL,
            'DIGITISATION', 'DIGITIZATION' => self::DIGITISATION,
            'ALTERATION' => self::ALTERATION,
            default => self::NEW,
        };
    }

    public function isFollowUp(): bool
    {
        return in_array($this, [self::RENEWAL, self::ALTERATION, self::DIGITISATION], true);
    }
}
