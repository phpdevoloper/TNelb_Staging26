<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Store the structured applicant address (Address Line 1/2, Village/Town/City, State,
 * District, Pin Code) alongside the existing combined `applicant_address` string on the
 * competency application meta tables (Forms S, W, WH, P).
 *
 * All columns are nullable and additive: existing rows keep their combined
 * `applicant_address` untouched, and nothing is back-filled. Lengths mirror
 * the registration form (tnelb_registers / RegisterController validation).
 */
return new class extends Migration
{
    /** @var list<string> */
    private array $tables = [
        'cc_form_s_meta',
        'cc_form_w_meta',
        'cc_form_wh_meta',
        'cc_form_p_meta',
    ];

    public function up(): void
    {
        foreach ($this->tables as $tableName) {
            if (! Schema::hasTable($tableName)) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (! Schema::hasColumn($tableName, 'address_line1')) {
                    $table->string('address_line1', 100)->nullable();
                }
                if (! Schema::hasColumn($tableName, 'address_line2')) {
                    $table->string('address_line2', 100)->nullable();
                }
                if (! Schema::hasColumn($tableName, 'city')) {
                    $table->string('city', 50)->nullable();
                }
                if (! Schema::hasColumn($tableName, 'state')) {
                    $table->string('state', 255)->nullable();
                }
                if (! Schema::hasColumn($tableName, 'district')) {
                    $table->string('district', 255)->nullable();
                }
                if (! Schema::hasColumn($tableName, 'pincode')) {
                    $table->string('pincode', 6)->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $tableName) {
            if (! Schema::hasTable($tableName)) {
                continue;
            }

            $columns = array_values(array_filter(
                ['address_line1', 'address_line2', 'city', 'state', 'district', 'pincode'],
                fn (string $column) => Schema::hasColumn($tableName, $column)
            ));

            if ($columns !== []) {
                Schema::table($tableName, function (Blueprint $table) use ($columns) {
                    $table->dropColumn($columns);
                });
            }
        }
    }
};
