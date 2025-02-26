<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeDefaultValueOfSalesmanInCustBranch extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_cust_branch', function (Blueprint $table) {
            $table->integer('salesman')->default(-1)->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('0_cust_branch', function (Blueprint $table) {
            $table->integer('salesman')->default(0)->nullable(false)->change();
        });
    }
}
