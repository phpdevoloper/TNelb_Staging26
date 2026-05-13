<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\MstStaff;
use App\Models\Tnelb_scert;

class Mst_Roles extends Model
{
    use HasFactory;

    protected $table = 'mst_roles';  // Explicitly defining table name
    protected $primaryKey = 'r_id';
    protected $fillable = ['name'];


     public function users()
    {
        return $this->hasMany(Mst_Logins::class, 'role_id');
    }

    public function applications()
    {
        return $this->hasMany(Tnelb_scert::class, 'current_role_id');
    }

}
    // public function staffMembers() {
    //     return $this->hasMany(Mst_Staffs_Tbl::class, 'role_id'); // Ensure foreign key is correctly mapped
    // }