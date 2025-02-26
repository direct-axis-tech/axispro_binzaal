<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTransDatesColumnsToVoidedCustAllocationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('0_voided_cust_allocations', 'date_alloc_to')) {
            Schema::table('0_voided_cust_allocations', function (Blueprint $table) {
                $table->date('date_alloc_to')->nullable()->after('trans_type_from');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('0_voided_cust_allocations', function (Blueprint $table) {
            $table->dropColumn('date_alloc_to');
        });
    }
}
