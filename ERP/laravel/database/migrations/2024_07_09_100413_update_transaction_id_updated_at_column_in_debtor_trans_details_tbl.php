<?php

use App\Models\Sales\CustomerTransaction;
use Illuminate\Database\Migrations\Migration;

class UpdateTransactionIdUpdatedAtColumnInDebtorTransDetailsTbl extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared("SET SESSION sql_mode = ''");
        DB::table('0_debtor_trans_details as dtd')
        ->join('0_debtor_trans as dt', function ($join) {
            $join->on('dt.trans_no','dtd.debtor_trans_no')
                 ->whereColumn('dt.type', 'dtd.debtor_trans_type');
        })
        ->where(function ($query) {
            $query->whereNotNull('dtd.transaction_id')
                ->orWhereRaw("dtd.transaction_id != ''");
        })
        ->where('dtd.debtor_trans_type', CustomerTransaction::INVOICE)
        ->where('dtd.quantity', '<>', 0)
        ->where(function ($query) {
            $query->whereNull('dtd.transaction_id_updated_at')
                  ->orWhere('dtd.transaction_id_updated_at', '0000-00-00');
        })
        ->update([
            'dtd.transaction_id_updated_at' => DB::raw('dt.tran_date'),
            'dtd.transaction_id_updated_by' => DB::raw(
                "IF("
                    . "isnull(dtd.transaction_id_updated_by) OR dtd.transaction_id_updated_by = ''"
                    . ", ifnull(nullif(dtd.created_by, ''), dt.created_by)"
                    . ", dtd.transaction_id_updated_by"
                . ")"
            ),
        ]);
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