<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnCreatedAtAndModifiedByToVoidedCustAllocTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_voided_cust_allocations', function (Blueprint $table) {
            $table->integer('_modified_by')->nullable();
            $table->dateTime('_created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('0_voided_cust_allocations', function (Blueprint $table) {
            $table->dropColumn('_modified_by', '_created_at');
        });
    }
}
