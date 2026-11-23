<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class OtpVerification extends Model
{
    use HasFactory;

    protected $table = 'otp_verifications';

    protected $fillable = [
        'mobile',
        'otp',
        'otp_expiry',
        'is_verified',
        'verified_at',
        'ip_address',
        'user_agent',
        'attempts',
    ];

    protected $casts = [
        'otp_expiry' => 'datetime',
        'verified_at' => 'datetime',
    ];

    /**
     * Check if OTP has expired
     */
    public function isExpired(): bool
    {
        return $this->otp_expiry && $this->otp_expiry->isPast();
    }

    /**
     * Check if OTP is already verified
     */
    public function isAlreadyVerified(): bool
    {
        return $this->is_verified === true;
    }

    /**
     * Mark OTP as verified
     */
    public function markAsVerified(): self
    {
        $this->update([
            'is_verified' => true,
            'verified_at' => now(),
        ]);
        return $this;
    }

    /**
     * Increment attempt counter
     */
    public function recordAttempt(): void
    {
        $this->increment('attempts');
    }

    /**
     * Check if too many attempts (rate limiting)
     */
    public function hasTooManyAttempts(int $maxAttempts = 5): bool
    {
        return $this->attempts >= $maxAttempts;
    }
}
