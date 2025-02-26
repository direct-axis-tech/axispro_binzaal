<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateIndexOnDateAllocToOnSuppAllocsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_supp_allocations', function (Blueprint $table) {
            $table->index(['date_alloc_to']);
            $table->index(['date_alloc']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('0_supp_allocations', function (Blueprint $table) {
            $table->dropIndex(['date_alloc_to']);
            $table->dropIndex(['date_alloc']);
        });
    }
}
