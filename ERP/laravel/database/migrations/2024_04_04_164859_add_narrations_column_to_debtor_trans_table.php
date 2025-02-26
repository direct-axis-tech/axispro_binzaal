<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\JoinClause;

class AddNarrationsColumnToDebtorTransTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_debtor_trans', function (Blueprint $table) {
            $table->json('narrations')->nullable(false)->default('[]')->after('customer_ref');
        });

        try {
            $narrations = DB::table('0_debtor_trans_details as line')
                ->select(
                    'line.debtor_trans_no',
                    'line.debtor_trans_type',
                    DB::raw("concat('[', group_concat(concat('{'"
                            . ", json_quote('description'), ':', json_quote(ifnull(line.description, '')), ','"
                            . ", json_quote('line_reference'), ':', json_quote(ifnull(line.line_reference, '')), ','"
                            . ", json_quote('transaction_id'), ':', json_quote(ifnull(line.transaction_id, '')), ','"
                            . ", json_quote('application_id'), ':', json_quote(ifnull(line.application_id, '')), ','"
                            . ", json_quote('passport_no'), ':', json_quote(ifnull(line.passport_no, '')), ','"
                            . ", json_quote('narration'), ':', json_quote(ifnull(line.ref_name, ''))"
                    .", '}')), ']') as narrations")
                )
                ->where('line.quantity', '<>', 0)
                ->groupBy('line.debtor_trans_type', 'line.debtor_trans_no');

            DB::table('0_debtor_trans as trans')
            ->leftJoinSub($narrations, 'details', function (JoinClause $join) {
                $join->on('details.debtor_trans_no', 'trans.trans_no')
                    ->whereColumn('details.debtor_trans_type', 'trans.type');
            })
            ->update([
                'trans.narrations' => DB::raw("ifnull(details.narrations, '[]')")
            ]);
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
        Schema::table('0_debtor_trans', function (Blueprint $table) {
            $table->dropColumn('narrations');
        });
    }
}
