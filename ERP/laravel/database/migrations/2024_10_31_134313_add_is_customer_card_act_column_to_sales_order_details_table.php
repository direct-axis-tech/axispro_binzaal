<?php

use App\Traits\MigratesData;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsCustomerCardActColumnToSalesOrderDetailsTable extends Migration
{
    use MigratesData;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_sales_order_details', function (Blueprint $table) {
            $table->boolean('is_customer_card_act')->nullable(false)->default(0)->after('govt_bank_account');
        });

        $this->migrateData(function () {
            DB::unprepared("SET SESSION sql_mode = ''");
            $customer_card_accounts = array_keys(get_customer_card_accounts());
            DB::table('0_sales_order_details')
                ->whereIn("govt_bank_account", $customer_card_accounts ?: [-1])
                ->update(['is_customer_card_act' => 1]);
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
            $table->dropColumn('is_customer_card_act');
        });
    }
}
