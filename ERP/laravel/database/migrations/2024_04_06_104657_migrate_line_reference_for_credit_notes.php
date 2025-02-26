<?php

use App\Models\Sales\CustomerTransaction;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;

class MigrateLineReferenceForCreditNotes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('0_debtor_trans_details as credit')
            ->join('0_debtor_trans_details as invoice', function (JoinClause $query) {
                $query->on('invoice.id', 'credit.src_id')
                    ->whereNotNull('invoice.line_reference');
            })
            ->where('credit.debtor_trans_type', CustomerTransaction::CREDIT)
            ->where('credit.quantity', '<>', 0)
            ->whereNull('credit.line_reference')
            ->update(['credit.line_reference' => DB::raw('invoice.line_reference')]);
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
