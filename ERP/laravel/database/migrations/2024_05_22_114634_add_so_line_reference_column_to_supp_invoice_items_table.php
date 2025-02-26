<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSoLineReferenceColumnToSuppInvoiceItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_supp_invoice_items', function (Blueprint $table) {
            $table->string('so_line_reference')->nullable()->default(null)->after('po_detail_item_id');
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
            $table->dropColumn('so_line_reference');
        });
    }
}
