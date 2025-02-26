<?php

use App\Models\Sales\CustomerTransaction;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;

class AddBookKeepingFieldsToSalesOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_sales_orders', function (Blueprint $table) {
            $table->integer('created_by')->nullable(false);
            $table->integer('updated_by')->nullable(false);
            $table->dateTime('transacted_at')->nullable(false);
            $table->dateTime('updated_at')->nullable(false);
        });

        try {
            DB::unprepared("SET sql_mode = ''");
            DB::table('0_sales_orders as so')
                ->leftJoin('0_labour_contracts as lc', 'so.contract_id', 'lc.id')
                ->leftJoin('0_debtor_trans as dt', function (JoinClause $join) {
                    $join->on('so.order_no', 'dt.order_')
                        ->where('dt.type', CustomerTransaction::DELIVERY);
                })
                ->whereRaw('(!isnull(lc.id) or !isnull(dt.id))')
                ->where(function (Builder $query) {
                    $query->orWhereRaw("(!ifnull(`so`.`created_by`, 0) and !isnull(coalesce(nullif(`lc`.`created_by`, '0'), nullif(`dt`.`created_by`, '0'))))")
                        ->orWhereRaw("(!ifnull(`so`.`updated_by`, 0) and !isnull(coalesce(nullif(`lc`.`created_by`, '0'), nullif(`dt`.`updated_by`, '0'))))")
                        ->orWhereRaw("(!ifnull(`so`.`transacted_at`, 0) and !isnull(coalesce(nullif(`lc`.`created_at`, '0000-00-00 00:00:00'), nullif(`dt`.`transacted_at`, '0000-00-00 00:00:00'))))")
                        ->orWhereRaw("(!ifnull(`so`.`updated_at`, 0) and !isnull(coalesce(nullif(`lc`.`created_at`, '0000-00-00 00:00:00'), nullif(`dt`.`updated_at`, '0000-00-00 00:00:00'))))");
                })
                ->update([
                    'so.created_by' => DB::raw("coalesce(nullif(`so`.`created_by`, '0'), nullif(`lc`.`created_by`, '0'), nullif(`dt`.`created_by`, '0'))"),
                    'so.updated_by' => DB::raw("coalesce(nullif(`so`.`updated_by`, '0'), nullif(`lc`.`created_by`, '0'), nullif(`dt`.`updated_by`, '0'))"),
                    'so.transacted_at' => DB::raw("coalesce(nullif(`so`.`transacted_at`, '0000-00-00 00:00:00'), nullif(`lc`.`created_at`, '0000-00-00 00:00:00'), nullif(`dt`.`transacted_at`, '0000-00-00 00:00:00'))"),
                    'so.updated_at' => DB::raw("coalesce(nullif(`so`.`updated_at`, '0000-00-00 00:00:00'), nullif(`lc`.`created_at`, '0000-00-00 00:00:00'), nullif(`dt`.`updated_at`, '0000-00-00 00:00:00'))"),
                ]);
        }

        catch (Throwable $exception) {
            $this->down();

            throw $exception;
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
            $table->dropColumn(
                'created_by',
                'updated_by',
                'transacted_at',
                'updated_at'
            );
        });
    }
}
