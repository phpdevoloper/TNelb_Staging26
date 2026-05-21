<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaffProfile extends Model
{
    use HasFactory;

    protected $table = 'mst_staff_profiles';

    protected $primaryKey = 'id';

    public $timestamps = true;

    protected $fillable = [
        's_id',
        'employee_code',
        'full_name',
        'mobile',
        'designation',
        'joining_date',
        'date_of_birth',
        'gender',
        'alt_phone',
        'profile_photo',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'joining_date'  => 'date',
        'date_of_birth' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(Mst_Logins::class, 's_id', 's_id');
    }
}
