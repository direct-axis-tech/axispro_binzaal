<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\JoinClause;

class AddTransDatesColumnsToCustAllocationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('0_cust_allocations', 'date_alloc_to')) {
            Schema::table('0_cust_allocations', function (Blueprint $table) {
                $table->date('date_alloc_to')->nullable()->after('trans_type_from');
            });
        }

        try {
            DB::table('0_cust_allocations as alloc')
                ->leftJoin('0_debtor_trans as t_to', function (JoinClause $join) {
                    $join->on('t_to.debtor_no', 'alloc.person_id')
                        ->whereColumn('t_to.type', 'alloc.trans_type_to')
                        ->whereColumn('t_to.trans_no', 'alloc.trans_no_to');
                })
                ->leftJoin('0_sales_orders as o_to', function (JoinClause $join) {
                    $join->on('o_to.debtor_no', 'alloc.person_id')
                        ->whereColumn('o_to.trans_type', 'alloc.trans_type_to')
                        ->whereColumn('o_to.order_no', 'alloc.trans_no_to');
                })
                ->leftJoin('0_debtor_trans as t_from', function (JoinClause $join) {
                    $join->on('t_from.debtor_no', 'alloc.person_id')
                        ->whereColumn('t_from.type', 'alloc.trans_type_from')
                        ->whereColumn('t_from.trans_no', 'alloc.trans_no_from');
                })
                ->update([
                    'alloc.date_alloc_to' => DB::raw('coalesce(`t_to`.`tran_date`, `o_to`.`ord_date`, `t_from`.`tran_date`, `alloc`.`date_alloc`, date(`alloc`.`stamp`))'),
                    'alloc.date_alloc' => DB::raw('coalesce(`t_from`.`tran_date`, `alloc`.`date_alloc`, date(`alloc`.`stamp`))'),
                    'alloc.stamp' => DB::raw('alloc.stamp')
                ]);

            Schema::table('0_cust_allocations', function (Blueprint $table) {
                $table->date('date_alloc')->nullable(false)->default(null)->change();
                $table->date('date_alloc_to')->nullable(false)->change();
            });
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
        Schema::table('0_cust_allocations', function (Blueprint $table) {
            $table->dropColumn('date_alloc_to');
            $table->date('date_alloc')->nullable(true)->change();
        });
    }
}
