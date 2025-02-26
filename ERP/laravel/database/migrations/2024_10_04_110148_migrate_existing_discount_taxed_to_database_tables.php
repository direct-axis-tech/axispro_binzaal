<?php

use Carbon\CarbonImmutable;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class MigrateExistingDiscountTaxedToDatabaseTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tranDate = CarbonImmutable::parse(DB::table('0_fiscal_year')->orderBy('begin')->limit(1)->value('begin'));
        $endDate = CarbonImmutable::parse(DB::table('0_fiscal_year')->orderBy('end', 'desc')->limit(1)->value('end'));

        while ($tranDate <= $endDate) {
            $fromDate = $tranDate->toDateString();
            $tranDate = $tranDate->addYear();
            $tillDate = $tranDate->toDateString();

            DB::transaction(function () use ($fromDate, $tillDate) {
                DB::table("0_sales_order_details as itm")
                    ->leftJoin("0_sales_orders as ord", function (Builder $query) {
                        $query->whereColumn('itm.trans_type', 'ord.trans_type')
                            ->whereColumn('itm.order_no', 'ord.order_no');
                    })
                    ->where('itm._unit_tax', '<>', '0')
                    ->where('itm.discount_amount', '<>', '0')
                    ->whereRaw('(ord.ord_date >= ? AND ord.ord_date < ?)', [$fromDate, $tillDate])
                    ->update([
                        'itm._discount_taxed' => DB::raw('!(round(itm._unit_tax, 2) = round((itm.unit_price + itm.returnable_amt + itm.extra_srv_chg) * itm.quantity * if(ord._tax_included, 5/105, 5/100), 2))')
                    ]);

                DB::table("0_sales_orders as ord")
                    ->joinSub(
                        DB::table("0_sales_order_details as t")
                            ->leftJoin("0_sales_orders as p", function (Builder $query) {
                                $query->whereColumn('t.trans_type', 'p.trans_type')
                                    ->whereColumn('t.order_no', 'p.order_no');
                            })
                            ->where('t._unit_tax', '<>', '0')
                            ->where('t.discount_amount', '<>', '0')
                            ->whereRaw('(p.ord_date >= ? AND p.ord_date < ?)', [$fromDate, $tillDate])
                            ->groupBy('t.trans_type', 't.order_no')
                            ->select(
                                't.trans_type',
                                't.order_no',
                                DB::raw('sum(t._discount_taxed = 0) = 0 as discount_taxed')
                            ),
                        'itm',
                        function (Builder $query) {
                            $query->whereColumn('itm.trans_type', 'ord.trans_type')
                                ->whereColumn('itm.order_no', 'ord.order_no');
                        }
                    )
                    ->update(['ord._discount_taxed' => DB::raw('itm.discount_taxed')]);

                DB::table("0_sales_orders as ord")
                    ->leftJoin("0_sales_order_details as itm", function (Builder $query) {
                        $query->whereColumn('itm.trans_type', 'ord.trans_type')
                            ->whereColumn('itm.order_no', 'ord.order_no');
                    })
                    ->whereRaw('(ord.ord_date >= ? AND ord.ord_date < ?)', [$fromDate, $tillDate])
                    ->where('ord._discount_taxed', '0')
                    ->update(['itm._discount_taxed' => '0']);

                // --------------------------------------------------------------------------

                DB::table("0_debtor_trans_details as itm")
                    ->leftJoin("0_debtor_trans as ord", function (Builder $query) {
                        $query->whereColumn('itm.debtor_trans_type', 'ord.type')
                            ->whereColumn('itm.debtor_trans_no', 'ord.trans_no');
                    })
                    ->where('itm.quantity', '<>', '0')
                    ->where('itm.unit_tax', '<>', '0')
                    ->whereRaw('(ord.tran_date >= ? AND ord.tran_date < ?)', [$fromDate, $tillDate])
                    ->where('itm.discount_amount', '<>', '0')
                    ->update([
                        'itm.discount_taxed' => DB::raw('!(round(itm.unit_tax, 2) = round((itm.unit_price + itm.returnable_amt + itm.extra_srv_chg) * itm.quantity * if(ord.tax_included, 5/105, 5/100), 2))')
                    ]);

                DB::table("0_debtor_trans as ord")
                    ->joinSub(
                        DB::table("0_debtor_trans_details as t")
                            ->leftJoin("0_debtor_trans as p", function (Builder $query) {
                                $query->whereColumn('t.debtor_trans_type', 'p.type')
                                    ->whereColumn('t.debtor_trans_no', 'p.trans_no');
                            })
                            ->where('t.quantity', '<>', '0')
                            ->where('t.unit_tax', '<>', '0')
                            ->whereRaw('(p.tran_date >= ? AND p.tran_date < ?)', [$fromDate, $tillDate])
                            ->where('t.discount_amount', '<>', '0')
                            ->groupBy('t.debtor_trans_type', 't.debtor_trans_no')
                            ->select(
                                't.debtor_trans_type',
                                't.debtor_trans_no',
                                DB::raw('sum(t.discount_taxed = 0) = 0 as discount_taxed')
                            ),
                        'itm',
                        function (Builder $query) {
                            $query->whereColumn('itm.debtor_trans_type', 'ord.type')
                                ->whereColumn('itm.debtor_trans_no', 'ord.trans_no');
                        }
                    )
                    ->update(['ord.discount_taxed' => DB::raw('itm.discount_taxed')]);

                DB::table("0_debtor_trans as ord")
                    ->leftJoin("0_debtor_trans_details as itm", function (Builder $query) {
                        $query->whereColumn('itm.debtor_trans_type', 'ord.type')
                            ->whereColumn('itm.debtor_trans_no', 'ord.trans_no');
                    })
                    ->whereRaw('(ord.tran_date >= ? AND ord.tran_date < ?)', [$fromDate, $tillDate])
                    ->where('ord.discount_taxed', '0')
                    ->update(['itm.discount_taxed' => '0']);
            });
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
