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
        Schema::create('mapping_digi_cls', function (Blueprint $table) {
            $table->id();
            $table->string('login_id');
            $table->string('application_id')->nullable();
            $table->string('form_name', 10);
            $table->string('cert_name', 10)->nullable();
            $table->string('form_code', 10)->nullable();
            $table->string('temp_app_id', 50)->nullable();
            $table->string('clnumber', 50)->nullable();
            $table->date('fissue');
            $table->date('from_date');
            $table->date('to_date');
            $table->text('cl_doc');
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
        Schema::dropIfExists('mapping_digi_cls');
    }
};
