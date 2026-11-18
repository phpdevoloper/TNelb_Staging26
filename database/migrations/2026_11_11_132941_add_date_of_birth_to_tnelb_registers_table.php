<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('tnelb_registers', function (Blueprint $table) {
            $table->date('date_of_birth')->nullable()->after('last_name');
        });
    }

    public function down()
    {
        Schema::table('tnelb_registers', function (Blueprint $table) {
            $table->dropColumn('date_of_birth');
        });
    }
};
