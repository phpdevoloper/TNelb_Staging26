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
        Schema::create('mst_checklists', function (Blueprint $table) {
            $table->id();
            $table->string("login_id");
            $table->string("application_id");
            $table->string('form_name',20);
            $table->string('cert_name',20);
            $table->string('appl_type',20);
            $table->string('checklist_name');
            $table->smallInteger('status');
            $table->string('updated_by',20);
            $table->string('ipaddress',20);
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
        Schema::dropIfExists('mst_checklists');
    }
};
