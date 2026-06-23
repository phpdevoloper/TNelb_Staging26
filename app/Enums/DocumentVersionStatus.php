<?php

namespace App\Enums;

enum DocumentVersionStatus: string
{
    case PENDING = 'PENDING';
    case PENDING_L2 = 'PENDING_L2';
    case PENDING_L3 = 'PENDING_L3';
    case APPROVED = 'APPROVED';
    case REJECTED = 'REJECTED';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending (Level 1)',
            self::PENDING_L2 => 'Pending (Level 2)',
            self::PENDING_L3 => 'Pending (Level 3)',
            self::APPROVED => 'Approved',
            self::REJECTED => 'Rejected',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::PENDING, self::PENDING_L2, self::PENDING_L3 => 'warning',
            self::APPROVED => 'success',
            self::REJECTED => 'danger',
        };
    }

    public function isPending(): bool
    {
        return in_array($this, [self::PENDING, self::PENDING_L2, self::PENDING_L3], true);
    }

    public function approvalLevel(): ?int
    {
        return match ($this) {
            self::PENDING => 1,
            self::PENDING_L2 => 2,
            self::PENDING_L3 => 3,
            default => null,
        };
    }

    public static function pendingStatusForLevel(int $level): self
    {
        return match ($level) {
            1 => self::PENDING,
            2 => self::PENDING_L2,
            3 => self::PENDING_L3,
            default => self::PENDING,
        };
    }

    public function nextPendingStatus(int $maxLevel): ?self
    {
        $current = $this->approvalLevel();

        if ($current === null || $current >= $maxLevel) {
            return null;
        }

        return self::pendingStatusForLevel($current + 1);
    }
}
