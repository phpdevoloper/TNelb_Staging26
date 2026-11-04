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
        Schema::table('tnelb_registers', function (Blueprint $table) {
            if (! Schema::hasColumn('tnelb_registers', 'address_line1')) {
                // Required at validation level; defaulted at DB level so the
                // column can be added to a table that already holds rows.
                $table->string('address_line1', 100)->default('')->after('email');
            }

            if (! Schema::hasColumn('tnelb_registers', 'address_line2')) {
                $table->string('address_line2', 100)->nullable()->after('address_line1');
            }

            if (! Schema::hasColumn('tnelb_registers', 'city')) {
                $table->string('city', 50)->default('')->after('address_line2');
            }
        });

        // The legacy `address` column is kept for backward compatibility but is
        // no longer written to, so it must accept NULL. doctrine/dbal is not
        // installed, so ->nullable()->change() is unavailable here.
        $this->dropAddressNotNull();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tnelb_registers', function (Blueprint $table) {
            $columns = array_values(array_filter(
                ['address_line1', 'address_line2', 'city'],
                fn ($column) => Schema::hasColumn('tnelb_registers', $column)
            ));

            if ($columns) {
                $table->dropColumn($columns);
            }
        });
    }

    /**
     * Allow NULL on the legacy `address` column.
     *
     * @return void
     */
    protected function dropAddressNotNull()
    {
        if (! Schema::hasColumn('tnelb_registers', 'address')) {
            return;
        }

        $connection = Schema::getConnection();

        switch ($connection->getDriverName()) {
            case 'pgsql':
                $connection->statement('ALTER TABLE tnelb_registers ALTER COLUMN address DROP NOT NULL');
                break;
            case 'mysql':
                $connection->statement('ALTER TABLE tnelb_registers MODIFY address TEXT NULL');
                break;
            default:
                // sqlite (tests) and others: nothing to do / not supported in-place.
                break;
        }
    }
};
