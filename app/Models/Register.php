<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable as AuthenticatableTrait;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Register extends Model implements Authenticatable
{
    use AuthenticatableTrait, HasFactory;
    use Notifiable;

    protected $table = 'tnelb_registers';
    // protected $primaryKey = 'id';

    protected $fillable = [
        'salutation',
        'first_name',
        'last_name',
        'date_of_birth',
        'gender',
        'mobile',
        'email',
        'address_line1',
        'address_line2',
        'city',
        'state',
        'district',
        'pincode',
        'login_id',
    ];

    /**
     * Legacy `address` reader.
     *
     * The column is retained for backward compatibility but is no longer
     * written to. When it is empty, fall back to the multi-part address so
     * existing consumers of `$user->address` keep working.
     *
     * @param  string|null  $value
     * @return string|null
     */
    public function getAddressAttribute($value)
    {
        if (filled($value)) {
            return $value;
        }

        $parts = array_filter([
            $this->attributes['address_line1'] ?? null,
            $this->attributes['address_line2'] ?? null,
            $this->attributes['city'] ?? null,
        ], fn ($part) => filled($part));

        return $parts ? implode(', ', $parts) : $value;
    }
}
