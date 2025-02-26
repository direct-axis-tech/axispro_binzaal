<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDiscountTaxedColumnToVoidedSalesOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_voided_sales_orders', function (Blueprint $table) {
            $table->boolean('_discount_taxed')->nullable(false)->default(1)->after('_tax_included');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('0_voided_sales_orders', function (Blueprint $table) {
            $table->dropColumn('_discount_taxed');
        });
    }
}
