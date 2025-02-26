<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDiscountTaxedColumnToDebtorTransDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_debtor_trans_details', function (Blueprint $table) {
            $table->boolean('discount_taxed')->nullable(false)->default(1)->after('discount_amount');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('0_debtor_trans_details', function (Blueprint $table) {
            $table->dropColumn('discount_taxed');
        });
    }
}
