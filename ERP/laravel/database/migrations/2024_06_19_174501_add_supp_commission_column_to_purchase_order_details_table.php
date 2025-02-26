<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSuppCommissionColumnToPurchaseOrderDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_purch_order_details', function (Blueprint $table) {
            $table->decimal('supp_commission', 14, 2)->nullable(false)->default(0)->after('std_cost_unit');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('0_purch_order_details', function (Blueprint $table) {
            $table->dropColumn('supp_commission');
        });
    }
}
