<?php

use App\Models\Sales\CustomerTransaction;
use App\Models\Sales\SalesOrder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class MigrateServiceRequestItemLineIdsToTransTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('t_srq_item', function (Blueprint $table) {
            $table->bigInteger('srq_id');
            $table->bigInteger('srq_i_id')->index();
            $table->integer('seq');
            $table->unique(['srq_id', 'seq']);
            $table->temporary();
        });

        Schema::create('t_dtd', function (Blueprint $table) {
            $table->bigInteger('dt_id');
            $table->dateTime('trn_at');
            $table->bigInteger('so_id')->index();
            $table->bigInteger('srq_id')->index();
            $table->bigInteger('dt_i_id')->index();
            $table->integer('seq');
            $table->unique(['dt_id', 'seq']);
            $table->temporary();
        });

        Schema::create('t_so_item', function (Blueprint $table) {
            $table->bigInteger('so_id');
            $table->bigInteger('so_i_id')->index();
            $table->integer('seq');
            $table->unique(['so_id', 'seq']);
            $table->temporary();
        });

        DB::transaction(function () {
            DB::table('t_srq_item')->insertUsing(
                [
                    'srq_id',
                    'srq_i_id',
                    'seq'
                ],
                DB::table('0_service_request_items as srq_i')
                    ->select(
                        'srq_i.req_id as srq_id',
                        'srq_i.id as srq_i_id',
                        DB::raw('COUNT(*) OVER (PARTITION BY srq_i.req_id ORDER BY srq_i.id ASC ROWS BETWEEN UNBOUNDED PRECEDING AND CURRENT ROW) as seq')
                    )
                    ->orderBy('srq_i.req_id')
                    ->orderBy('srq_i.id')
            );
    
            DB::table('t_dtd')->insertUsing(
                [
                    'dt_id',
                    'trn_at',
                    'so_id',
                    'srq_id',
                    'dt_i_id',
                    'seq'
                ],
                DB::table('0_debtor_trans_details as dtd')
                    ->leftJoin('0_debtor_trans as dt', function (JoinClause $join) {
                        $join->whereColumn('dtd.debtor_trans_type', 'dt.type')
                            ->whereColumn('dtd.debtor_trans_no', 'dt.trans_no');
                    })
                    ->select(
                        'dt.id as dt_id',
                        'dt.transacted_at as trn_at',
                        'dt.order_ as so_id',
                        'dt.service_req_id as srq_id',
                        'dtd.id as dt_i_id',
                        DB::raw('COUNT(*) OVER (PARTITION BY dt.id ORDER BY dtd.id ASC ROWS BETWEEN UNBOUNDED PRECEDING AND CURRENT ROW) as seq')
                    )
                    ->whereIn('dtd.debtor_trans_type', [CustomerTransaction::INVOICE, CustomerTransaction::DELIVERY, CustomerTransaction::CREDIT])
                    ->whereRaw('(dt.ov_amount + dt.ov_gst + dt.ov_discount + dt.ov_freight + dt.ov_freight_tax) <> 0')
                    ->whereNotNull('dt.service_req_id')
                    ->where('dtd.quantity', '<>', 0)
                    ->orderBy('dt.id')
                    ->orderBy('dtd.id')
            );
    
            DB::table('t_so_item')->insertUsing(
                [
                    'so_id',
                    'so_i_id',
                    'seq'
                ],
                DB::table('0_sales_order_details as so_i')
                    ->select(
                        'so_i.order_no as so_id',
                        'so_i.id as so_i_id',
                        DB::raw('COUNT(*) OVER (PARTITION BY so_i.order_no ORDER BY so_i.id ASC ROWS BETWEEN UNBOUNDED PRECEDING AND CURRENT ROW) as seq')
                    )
                    ->where('so_i.trans_type', SalesOrder::ORDER)
                    ->orderBy('so_i.order_no')
                    ->orderBy('so_i.id')
            );
    
    
            DB::table('t_srq_item')
                ->join('0_service_request_items as srq_i', 'srq_i.id', 't_srq_item.srq_i_id')
                ->join('t_dtd', function (JoinClause $join) {
                    $join->whereColumn('t_dtd.srq_id', 't_srq_item.srq_id')
                        ->whereColumn('t_dtd.seq', 't_srq_item.seq');
                })
                ->join('0_debtor_trans_details as dtd', 'dtd.id', 't_dtd.dt_i_id')
                ->update([
                    'srq_i.invoiced_at' => DB::raw('t_dtd.trn_at'),
                    'dtd.srv_req_line_id' => DB::raw('srq_i.id'),
                ]);
    
            DB::table('t_dtd')
                ->join('0_debtor_trans_details as dtd', 'dtd.id', 't_dtd.dt_i_id')
                ->join('t_so_item', function (JoinClause $join) {
                    $join->whereColumn('t_dtd.so_id', 't_so_item.so_id')
                        ->whereColumn('t_dtd.seq', 't_so_item.seq');
                })
                ->join('0_sales_order_details as so_i', 'so_i.id', 't_so_item.so_i_id')
                ->update([
                    'so_i.srv_req_line_id' => DB::raw('dtd.srv_req_line_id'),
                ]);
        });

        Schema::dropIfExists('t_srq_item');
        Schema::dropIfExists('t_dtd');
        Schema::dropIfExists('t_so_item');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
