<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\JoinClause;
use App\Models\Sales\CustomerTransaction;
use App\Models\Sales\SalesOrder;
use Illuminate\Support\Facades\DB;

class MigratePaymentTermsInDebtotTransTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        DB::table('0_debtor_trans as inv')
            ->leftJoin('0_sales_orders as sorder', function (JoinClause $join) {
                $join->on('inv.order_', 'sorder.order_no')
                    ->where('sorder.trans_type', '=', SalesOrder::ORDER);
            })
            ->whereRaw('inv.payment_terms != sorder.payment_terms')
            ->where('inv.type', CustomerTransaction::INVOICE)
            ->update([
                'inv.payment_terms' => DB::raw('sorder.payment_terms'),
                'inv.prep_amount'   => DB::raw('inv.ov_amount + inv.ov_gst + inv.ov_freight + inv.ov_freight_tax + inv.ov_discount'),
            ]);


        DB::table('0_debtor_trans as credit')
            ->leftJoin('0_debtor_trans_details as credit_details', function (JoinClause $join) {
                $join->on('credit_details.debtor_trans_type', 'credit.type')
                    ->whereColumn('credit_details.debtor_trans_no', 'credit.trans_no');
            })
            ->leftJoin('0_debtor_trans_details as invoice_details', function (JoinClause $join) {
                $join->on('invoice_details.id', 'credit_details.src_id')
                    ->where('invoice_details.debtor_trans_type',  CustomerTransaction::INVOICE);
            })
            ->leftJoin('0_debtor_trans as invoice', function (JoinClause $join) {
                $join->on('invoice_details.debtor_trans_type', 'invoice.type')
                    ->whereColumn('invoice_details.debtor_trans_no', 'invoice.trans_no');
            })
            ->leftJoin('0_voided as voided', function (JoinClause $join) {
                $join->on('voided.type', 'credit.type')
                    ->whereColumn('voided.id', 'credit.trans_no');
            })
            ->where('credit.type', CustomerTransaction::CREDIT)
            ->whereNotNull('invoice.type')
            ->whereNull('voided.id')
            ->whereRaw('credit.payment_terms != invoice.payment_terms')
            ->update([
                'credit.payment_terms' => DB::raw('invoice.payment_terms'),
                'credit.prep_amount'   => DB::raw('credit.ov_amount + credit.ov_gst + credit.ov_freight + credit.ov_freight_tax + credit.ov_discount'),
            ]);

        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
       
    }
}