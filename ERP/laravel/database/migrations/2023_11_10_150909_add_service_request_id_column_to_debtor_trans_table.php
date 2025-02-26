<?php

use App\Models\Sales\CustomerTransaction;
use App\Models\Sales\SalesOrder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;

class AddServiceRequestIdColumnToDebtorTransTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_debtor_trans', function (Blueprint $table) {
            $table->bigInteger('service_req_id')->after('type')->nullable();
        });

        DB::table('0_service_requests as req')
            ->leftJoin('0_debtor_trans as first', function (JoinClause $join) {
                $join->on('first.trans_no', 'req.trans_no')
                    ->where('first.type', CustomerTransaction::INVOICE);
            })
            ->leftJoin('0_debtor_trans as invoices', function (JoinClause $join) {
                $join->on('first.type', 'invoices.type')
                    ->whereColumn('first.reference', 'invoices.reference');
            })
            ->leftJoin('0_sales_orders as order', function (JoinClause $join) {
                $join->on('order.order_no', 'invoices.order_')
                    ->where('order.trans_type', SalesOrder::ORDER);
            })
            ->leftJoin('0_debtor_trans as all', 'all.order_', 'invoices.order_')
            ->update([
                'all.service_req_id' => DB::raw('req.id'),
                'order.service_req_id' => DB::raw('req.id')
            ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('0_debtor_trans', function (Blueprint $table) {
            $table->dropColumn('service_req_id');
        });
    }
}
