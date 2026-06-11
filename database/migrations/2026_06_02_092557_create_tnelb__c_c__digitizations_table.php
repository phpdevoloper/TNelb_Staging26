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
        Schema::create('tnelb_cc_digitization', function (Blueprint $table) {
            $table->id();
            $table->string("login_id");
            $table->string("application_id")->nullable();
            $table->string('form_name',20);
            $table->string('cert_name',20);
            $table->string("temp_app_id", 50);
           
            $table->string('ccnumber',20);
            $table->date('fissue');
            $table->date('from_date');
            $table->date('to_date');
            $table->smallInteger('qc')->nullable();
            $table->smallInteger('qsc')->nullable();
            $table->text('cc_doc');
            $table->smallInteger('flag')->nullable();
            $table->string('other1')->nullable();
            $table->string('other2')->nullable();
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
        Schema::dropIfExists('tnelb_cc_digitization');
    }
};
