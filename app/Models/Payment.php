<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $table = 'payments';

    protected $fillable = [
        'login_id',
        'application_id',
        'transaction_id',
        'payment_status',
        'amount',
        'form_name',
        'license_name',
        'payment_mode',
        'late_fee',
        'late_fees',
        'late_months',
        'application_fee',
        'transaction_date',
    ];
}
