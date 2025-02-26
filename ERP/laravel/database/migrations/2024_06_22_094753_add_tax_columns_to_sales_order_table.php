<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTaxColumnsToSalesOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_sales_orders', function (Blueprint $table) {
            $table->double('_tax')->nullable(false)->default(0)->after('total');
            $table->boolean('_tax_included')->nullable(false)->default(0)->after('_tax');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('0_sales_orders', function (Blueprint $table) {
            $table->dropColumn('_tax', '_tax_included');
        });
    }
}
