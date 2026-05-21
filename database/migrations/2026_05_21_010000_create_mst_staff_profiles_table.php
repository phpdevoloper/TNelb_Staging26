<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Profile / HR details for a portal user (1-to-1 with mst_login_users.s_id).
     * Kept separate from the auth table so login logic stays focused on credentials.
     */
    public function up(): void
    {
        Schema::create('mst_staff_profiles', function (Blueprint $table) {
            $table->bigIncrements('id');

            // 1-to-1 link to mst_login_users.s_id
            $table->integer('s_id')->unique();

            // Auto-generated HR identifier (e.g. TNELB-EMP-0001)
            $table->string('employee_code', 50)->unique();

            // Required HR fields
            $table->string('full_name', 150);
            $table->string('mobile', 20);
            $table->string('designation', 120);
            $table->date('joining_date');

            // Optional personal fields
            $table->date('date_of_birth')->nullable();
            $table->string('gender', 10)->nullable();
            $table->string('alt_phone', 20)->nullable();
            $table->string('profile_photo', 255)->nullable();

            // Audit
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();

            $table->index('s_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mst_staff_profiles');
    }
};
