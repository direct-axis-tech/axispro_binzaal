<?php

use App\Models\Sales\CustomerTransaction;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\JoinClause;

class MigrateMissingInvoiceDataToTransTaxDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $taxTypeId = DB::table('0_tax_types')->value('id');
        $taxOutPut = TR_OUTPUT;

        $q = DB::query()
            ->fromSub(
                DB::table('0_refs')
                    ->whereType(CustomerTransaction::INVOICE)
                    ->groupBy('type', 'reference')
                    ->select('type', 'reference')
                    ->selectRaw('max(id) as id'),
                'ref'
            )
            ->leftJoin('0_debtor_trans as dt', function (JoinClause $join) {
                $join->whereColumn('ref.type', 'dt.type')
                    ->whereColumn('ref.id', 'dt.trans_no');
            })
            ->leftJoin('0_trans_tax_details as tr', function (JoinClause $join) {
                $join->whereColumn('dt.type', 'tr.trans_type')
                    ->whereColumn('dt.trans_no', 'tr.trans_no');
            })
            ->leftJoin('0_tax_types as tType', function (JoinClause $join) use ($taxTypeId) {
                $join->where('tType.id', $taxTypeId);
            })
            ->whereNull('tr.id')
            ->select(
                "ref.type as trans_type",
                "ref.id as trans_no",
                "dt.tran_date as tran_date",
                "tType.id as tax_type_id",
                "tType.rate as rate",
                DB::raw("1 as ex_rate"),
                "dt.tax_included as included_in_price",
                DB::raw("0 as net_amount"),
                DB::raw("0 as amount"),
                "ref.reference as memo",
                DB::raw("$taxOutPut as reg_type")
            );

        DB::table('0_trans_tax_details')
            ->insertUsing(
                [
                    "trans_type",
                    "trans_no",
                    "tran_date",
                    "tax_type_id",
                    "rate",
                    "ex_rate",
                    "included_in_price",
                    "net_amount",
                    "amount",
                    "memo",
                    "reg_type"
                ],
                $q
            );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('0_trans_tax_details', function (Blueprint $table) {
            //
        });
    }
}
