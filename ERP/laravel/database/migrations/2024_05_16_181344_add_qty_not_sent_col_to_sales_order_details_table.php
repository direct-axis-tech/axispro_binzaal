<?php

use App\Traits\MigratesData;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddQtyNotSentColToSalesOrderDetailsTable extends Migration
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
            $table->double('qty_not_sent')->nullable(false)->default(0)->after('qty_sent')->index();
        });

        $this->migrateData(function () {
            DB::table('0_sales_order_details')
                ->update([
                    'qty_not_sent' => DB::raw('quantity - qty_sent')
                ]);
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
            $table->double('qty_not_sent');
        });
    }
}
