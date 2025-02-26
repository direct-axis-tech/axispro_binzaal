<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddReceivableCommissionColumnsToTable0SalesOrderDetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_sales_order_details', function (Blueprint $table) {
            $table->decimal('receivable_commission_amount', 10, 2)->nullable(false)->default(0.00)->after('returnable_to');
            $table->string('receivable_commission_account', 15)->nullable()->after('receivable_commission_amount');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('0_sales_order_details', function (Blueprint $table) {
            $table->dropColumn('receivable_commission_amount');
            $table->dropColumn('receivable_commission_account');
        });
    }
}
