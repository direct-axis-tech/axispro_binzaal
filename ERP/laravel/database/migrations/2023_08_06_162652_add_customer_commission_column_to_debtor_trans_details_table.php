<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCustomerCommissionColumnToDebtorTransDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasColumn('0_debtor_trans_details', 'customer_commission')) {
            return;
        }

        Schema::table('0_debtor_trans_details', function (Blueprint $table) {
            $table->decimal('customer_commission')->default(0)->nullable(false)->after('user_commission');
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
            $table->dropColumn('customer_commission');
        });
    }
}
