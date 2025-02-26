<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCouumnMaidIdTo0SuppInvoiceItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_supp_invoice_items', function (Blueprint $table) {
            
            $table->integer('maid_id')->nullable();
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
            
            $table->dropColumn('maid_id');
        });
    }
}
