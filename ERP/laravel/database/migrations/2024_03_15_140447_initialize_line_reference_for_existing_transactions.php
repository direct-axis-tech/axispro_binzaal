<?php

use App\Models\MetaReference;
use App\Models\Sales\CustomerTransaction;
use App\Models\Sales\CustomerTransactionDetail;
use App\Models\Sales\SalesOrder;
use App\Models\Sales\SalesOrderDetail;
use App\Models\System\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;

class InitializeLineReferenceForExistingTransactions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $transSeq = DB::table('0_debtor_trans_details as dtd')
            ->join('0_debtor_trans as dt', function (JoinClause $join) {
                $join->on('dtd.debtor_trans_type', 'dt.type')
                    ->whereColumn('dtd.debtor_trans_no', 'dt.trans_no');
            })
            ->select(
                'dtd.id as line_id',
                'dt.order_ as order_no',
                'dtd.line_reference'
            )
            ->selectRaw('(count(`dtd`.`id`) over (partition by `dtd`.`debtor_trans_type`, `dtd`.`debtor_trans_no` order by `dtd`.`id` rows between unbounded preceding and current row)) as line_seq')
            ->whereRaw('(`dt`.`ov_amount` + `dt`.`ov_gst` + `dt`.`ov_freight` + `dt`.`ov_freight_tax` + `dt`.`ov_discount`) <> 0')
            ->where('dtd.quantity', '<>', 0)
            ->whereNull('dtd.line_reference')
            ->whereIn('dtd.debtor_trans_type', [CustomerTransaction::INVOICE, CustomerTransaction::DELIVERY]);

        $orderSeq = DB::table('0_sales_order_details as sod')
            ->join('0_sales_orders as so', function (JoinClause $join) {
                $join->on('so.order_no', 'sod.order_no')
                    ->whereColumn('so.trans_type', 'sod.trans_type');
            })
            ->select(
                'sod.id as line_id',
                'sod.order_no',
                'so.ord_date as tran_date',
                'sod.line_reference'
            )
            ->selectRaw('(count(`sod`.`id`) over (partition by `sod`.`trans_type`, `sod`.`order_no` order by `sod`.`id` rows between unbounded preceding and current row)) as line_seq')
            ->where('so.total', '<>', 0)
            ->where('sod.quantity', '<>', 0)
            ->where('so.trans_type', SalesOrder::ORDER);

        $query = DB::query()
            ->fromSub($transSeq, 'trn')
            ->joinSub($orderSeq, 'order', function (JoinClause $join) {
                $join->whereColumn('order.order_no', 'trn.order_no')
                    ->whereColumn('order.line_seq', 'trn.line_seq');
            })
            ->select(
                'order.line_id as order_line_id',
                'trn.line_id as trans_line_id',
                'order.order_no',
                'order.tran_date'
            )
            ->where(function (Builder $query) {
                $query->whereNull('order.line_reference')
                    ->orWhereNull('trn.line_reference');
            })
            ->orderBy('order.line_id');

        try {
            // Process each transaction in chunks
            Auth::login(new User(["id" => User::SYSTEM_USER]));
            $cursor = $query->cursor();
            $chunkSize = 1500;
            $continue = true;
            while ($continue) {
                DB::beginTransaction();
                for ($i = 0; $i < $chunkSize; $i++) {
                    if (!$cursor->valid()) {
                        $continue = false;
                        break;
                    }
                    
                    // Process the row
                    $row = $cursor->current();
                    $orderLine = SalesOrderDetail::find($row->order_line_id);
                    if (!$orderLine->line_reference) {
                        $orderLine->line_reference = MetaReference::getNext(
                            SalesOrderDetail::ORDER_LINE_ITEM,
                            null,
                            sql2date($row->tran_date),
                            true
                        );

                        $orderLine->save();
                    }

                    CustomerTransactionDetail::query()
                        ->whereNull('line_reference')
                        ->whereId($row->trans_line_id)
                        ->update(['line_reference' => $orderLine->line_reference]);
                    $cursor->next();
                }
                DB::commit();
            }
        }

        catch (Throwable $e) {
            DB::rollBack();
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
        //
    }
}
