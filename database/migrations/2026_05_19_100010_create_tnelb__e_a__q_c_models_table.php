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
        Schema::create('tnelb_ea_qc_models', function (Blueprint $table) {
            $table->id();
            $table->string("login_id");
            $table->string("application_id");
             $table->text('form_name');
            $table->text('license_name');
            $table->string('staffname','50')->nullable();
            $table->string('category','20')->nullable();
            $table->string('cc_number','25')->nullable();
            $table->date('cc_validity')->nullable();
            $table->smallInteger('verify')->nullable();
            $table->smallInteger('status')->nullable();
            $table->smallInteger('flag')->nullable();
            $table->string('other', '100')->nullable();
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
        Schema::dropIfExists('tnelb_ea_qc_models');
    }
};
