<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUnitPriceAndGovtFeeColumnsToSuppInvoiceItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_supp_invoice_items', function (Blueprint $table) {
            $table->double('_unit_price')->nullable(false)->default(0)->after('unit_price');
            $table->double('govt_fee')->nullable(false)->default(0)->after('_unit_price');
        });

        try {
            DB::table('0_supp_invoice_items')->update(['_unit_price' => DB::raw('unit_price')]);
        }

        catch (Throwable $e) {
            $this->down();

            throw $e;
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('0_supp_invoice_items', function (Blueprint $table) {
            $table->dropColumn('_unit_price', 'govt_fee');
        });
    }
}
