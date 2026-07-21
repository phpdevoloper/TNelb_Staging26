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
        Schema::create('cc_checklist_applicant', function (Blueprint $table) {
            $table->id();
            $table->string('login_id', 100);
            $table->string('applicant_id',50);
            $table->integer('cert_license_id');
            $table->string('cert_name', 20);
            $table->integer('checklist_id');
            $table->tinyInteger('checked')->default(0);
            $table->tinyInteger('verify')->default(0);

            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cc_checklist_applicant');
    }
};
