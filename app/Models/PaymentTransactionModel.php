<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentTransactionModel extends Model
{
    use HasFactory;

    protected $table = 'payment_transactions';

    protected $fillable = [
        'application_id',
        'txnid',
        'amount',
        'application_fee',
        'late_fee',
        'late_months',
        'gateway',
        'payment_method',
        'mihpayid',
        'status',
        'gateway_response',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'application_fee' => 'integer',
        'late_fee' => 'integer',
        'late_months' => 'integer',
    ];
}