<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddGlRelatedColumnsToPayrollsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_payrolls', function (Blueprint $table) {
            $table->string('trans_type')->nullable();
            $table->string('trans_no')->nullable();
            $table->string('trans_ref')->nullable();
            $table->dateTime('journalized_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('0_payrolls', function (Blueprint $table) {
            $table->dropColumn('trans_type', 'trans_no', 'trans_ref', 'journalized_at');
        });
    }
}
