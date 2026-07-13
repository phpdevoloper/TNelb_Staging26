<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CC_Payments extends Model
{
    use HasFactory;

    protected $table = 'cc_payments';

    protected $primaryKey = 'p_id';

    protected $fillable = [
        'login_id',
        'application_id',
        'transaction_id',
        'form_name',
        'cert_name',
        'late_fee',
        'late_months',
        'transaction_date',
        'application_fee',
        'amount_paid',
        'payment_status',
        'payment_mode',
        'updated_at',
        'created_at',
    ];
}
