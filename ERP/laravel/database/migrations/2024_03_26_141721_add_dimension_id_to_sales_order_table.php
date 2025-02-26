<?php

use App\Models\Sales\CustomerTransaction;
use App\Models\Sales\SalesOrder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\JoinClause;

class AddDimensionIdToSalesOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_sales_orders', function (Blueprint $table) {
            $table->integer('dimension_id')->default(0)->after('ship_via');
        });

        try {
            DB::table('0_debtor_trans as dt')
                ->join('0_sales_orders as so', function (JoinClause $join) {
                    $join->on('dt.order_', 'so.order_no')
                        ->where('so.trans_type', SalesOrder::ORDER);
                })
                ->where('dt.type', CustomerTransaction::INVOICE)
                ->whereRaw("(`dt`.`ov_amount` + `dt`.`ov_gst` + `dt`.`ov_freight` + `dt`.`ov_freight_tax` + `dt`.`ov_discount`) <> 0")
                ->update([
                    'so.dimension_id' => DB::raw('dt.dimension_id')
                ]);
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
        Schema::table('0_sales_orders', function (Blueprint $table) {
            $table->dropColumn('dimension_id');
        });
    }
}
