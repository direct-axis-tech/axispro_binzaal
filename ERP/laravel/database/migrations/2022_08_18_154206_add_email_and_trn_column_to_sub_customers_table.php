<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddEmailAndTrnColumnToSubCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumns('0_sub_customers', ['email', 'trn'])) {
            Schema::table('0_sub_customers', function (Blueprint $table) {
                $table->string('trn')->nullable()->default(null)->after('mobile');
                $table->string('email')->nullable()->default(null)->after('mobile');
            });
        }

        DB::statement(
            'ALTER TABLE `0_sub_customers` '
                . 'MODIFY COLUMN created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,'
                . 'MODIFY COLUMN updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // nothing to do
    }
}
