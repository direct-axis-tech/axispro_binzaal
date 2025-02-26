<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTransNoAndTransTypeTo0ReconcileResultTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasColumns('0_reconcile_result', ['trans_no', 'trans_type'])) {
            $this->down();
        }
        
        Schema::table('0_reconcile_result', function (Blueprint $table) {
            $table->integer('trans_no')->nullable();
            $table->integer('trans_type')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('0_reconcile_result', function (Blueprint $table) {
            $table->dropColumn(['trans_no', 'trans_type']);
        });
    }
}
