<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cl_forma_signs', function (Blueprint $table) {
            $table->id();
            $table->string('login_id')->nullable();
            $table->string('application_id')->nullable();
            $table->string('form_name', 10)->nullable();
            $table->string('cert_name', 10)->nullable();
            $table->string('form_code', 10)->nullable();

            $table->string('name_of_authorised_to_sign', '100')->nullable();

            $table->string('age_of_authorised_to_sign', '20')->nullable();
            $table->string('qualification_of_authorised_to_sign', '100')->nullable();
            $table->string('designation_of_authorised_to_sign', '150')->nullable();
            $table->text('specimen_sign')->nullable();
            $table->integer('row_index')->nullable();
            $table->integer('flag')->nullable();
            $table->timestamps();
        });
    }

    /** database\migrations\2026_09_29_150248_create_cl_forma_signs_table.php
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cl_forma_signs');
    }
};
