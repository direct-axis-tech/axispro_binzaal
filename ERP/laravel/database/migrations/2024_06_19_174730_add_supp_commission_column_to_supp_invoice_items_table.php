<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSuppCommissionColumnToSuppInvoiceItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_supp_invoice_items', function (Blueprint $table) {
            $table->decimal('supp_commission', 14, 2)->nullable(false)->default(0)->after('unit_tax');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('0_supp_invoice_items', function (Blueprint $table) {
            $table->dropColumn('supp_commission');
        });
    }
}
