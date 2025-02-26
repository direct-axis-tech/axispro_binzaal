<?php

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\JoinClause;

class MigrateMissingReferencesToGlTransTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('t_dtd', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('type');
            $table->bigInteger('trans_no');
            $table->date('tran_date')->index();
            $table->integer('_seq_line');
            $table->integer('_seq_gov');
            $table->boolean('has_gov');
            $table->integer('_seq_returnable');
            $table->boolean('has_returnable');
            $table->integer('_seq_split');
            $table->boolean('has_split');
            $table->integer('_seq_bnk_chg');
            $table->boolean('has_bnk_chg');
            $table->integer('_seq_bnk_chg_vat');
            $table->boolean('has_bnk_chg_vat');
            $table->integer('_seq_pf');
            $table->boolean('has_pf');
            $table->string('transaction_id')->nullable();
            $table->string('application_id')->nullable();
            $table->string('line_reference')->nullable();
            $table->index(['type', 'trans_no']);
            $table->temporary();
        });

        Schema::create('t_gl', function (Blueprint $table) {
            $table->bigIncrements('counter');
            $table->bigInteger('type');
            $table->bigInteger('trans_no');
            $table->date('tran_date');
            $table->string('memo_');
            $table->integer('_seq');
            $table->index(['type', 'trans_no', 'memo_']);
            $table->temporary();
        });

        DB::table('t_dtd')->insertUsing(
            [
                'id',
                'type',
                'trans_no',
                'tran_date',
                '_seq_line',
                '_seq_gov',
                'has_gov',
                '_seq_returnable',
                'has_returnable',
                '_seq_split',
                'has_split',
                '_seq_bnk_chg',
                'has_bnk_chg',
                '_seq_bnk_chg_vat',
                'has_bnk_chg_vat',
                '_seq_pf',
                'has_pf',
                'transaction_id',
                'application_id',
                'line_reference'
            ],
            DB::table('0_debtor_trans_details as dtd')
            ->leftJoin('0_debtor_trans as dt', function (JoinClause $join) {
                $join->whereColumn('dtd.debtor_trans_type', 'dt.type')
                    ->whereColumn('dtd.debtor_trans_no', 'dt.trans_no');
            })
            ->select(
                'dtd.id',
                'dtd.debtor_trans_type AS type',
                'dtd.debtor_trans_no AS trans_no',
                'dt.tran_date',
                DB::raw('COUNT(dtd.id) OVER (PARTITION BY dtd.debtor_trans_type, dtd.debtor_trans_no ORDER BY dtd.id ROWS BETWEEN UNBOUNDED PRECEDING AND CURRENT ROW) AS _seq_line'),
                DB::raw('COUNT(dtd.id) OVER (PARTITION BY dtd.debtor_trans_type, dtd.debtor_trans_no, (round(dtd.govt_fee - dtd.split_govt_fee_amt - dtd.returnable_amt, 2) > 0) ORDER BY dtd.id ROWS BETWEEN UNBOUNDED PRECEDING AND CURRENT ROW) AS _seq_gov'),
                DB::raw('(round(dtd.govt_fee - dtd.split_govt_fee_amt - dtd.returnable_amt, 2) > 0) AS has_gov'),
                DB::raw('COUNT(dtd.id) OVER (PARTITION BY dtd.debtor_trans_type, dtd.debtor_trans_no, (round(dtd.returnable_amt, 2) > 0) ORDER BY dtd.id ROWS BETWEEN UNBOUNDED PRECEDING AND CURRENT ROW) AS _seq_returnable'),
                DB::raw('(round(dtd.returnable_amt, 2) > 0) AS has_returnable'),
                DB::raw('COUNT(dtd.id) OVER (PARTITION BY dtd.debtor_trans_type, dtd.debtor_trans_no, (round(dtd.split_govt_fee_amt, 2) > 0) ORDER BY dtd.id ROWS BETWEEN UNBOUNDED PRECEDING AND CURRENT ROW) AS _seq_split'),
                DB::raw('(round(dtd.split_govt_fee_amt, 2) > 0) AS has_split'),
                DB::raw('COUNT(dtd.id) OVER (PARTITION BY dtd.debtor_trans_type, dtd.debtor_trans_no, (round(dtd.bank_service_charge, 2) > 0) ORDER BY dtd.id ROWS BETWEEN UNBOUNDED PRECEDING AND CURRENT ROW) AS _seq_bnk_chg'),
                DB::raw('(round(dtd.bank_service_charge, 2) > 0) AS has_bnk_chg'),
                DB::raw('COUNT(dtd.id) OVER (PARTITION BY dtd.debtor_trans_type, dtd.debtor_trans_no, (round(dtd.bank_service_charge_vat, 2) > 0) ORDER BY dtd.id ROWS BETWEEN UNBOUNDED PRECEDING AND CURRENT ROW) AS _seq_bnk_chg_vat'),
                DB::raw('(round(dtd.bank_service_charge_vat, 2) > 0) AS has_bnk_chg_vat'),
                DB::raw('COUNT(dtd.id) OVER (PARTITION BY dtd.debtor_trans_type, dtd.debtor_trans_no, (round(dtd.pf_amount, 2) > 0) ORDER BY dtd.id ROWS BETWEEN UNBOUNDED PRECEDING AND CURRENT ROW) AS _seq_pf'),
                DB::raw('(round(dtd.pf_amount, 2) > 0) AS has_pf'),
                'dtd.transaction_id',
                'dtd.application_id',
                'dtd.line_reference'
            )
            ->where('dtd.quantity', '<>', 0)
            ->where('dtd.debtor_trans_type', 10)
            ->whereRaw('round(dtd.govt_fee + dtd.bank_service_charge + dtd.bank_service_charge_vat + dtd.pf_amount, 2) <> 0')
        );

        DB::table('t_gl')->insertUsing(
            [
                'counter',
                'type',
                'trans_no',
                'tran_date',
                'memo_',
                '_seq'
            ],
            DB::table('0_gl_trans as gt')
            ->select(
                'gt.counter',
                'gt.type',
                'gt.type_no as trans_no',
                'gt.tran_date',
                'gt.memo_',
                DB::raw('COUNT(gt.counter) OVER (PARTITION BY gt.type, gt.type_no, gt.memo_ ORDER BY gt.counter ROWS BETWEEN UNBOUNDED PRECEDING AND CURRENT ROW) AS _seq')
            )
            ->where('gt.amount', '<>', 0)
            ->whereIn('gt.memo_', ['Govt.Fee', 'Govt. Fee', 'Bank service charge', 'VAT for Bank service charge', 'Service charge', 'Returnable Benefits'])
        );

        
        $tranDate = CarbonImmutable::parse(DB::table('t_dtd')->min('tran_date'));
        $endDate = CarbonImmutable::parse(DB::table('t_dtd')->max('tran_date'));

        while ($tranDate <= $endDate) {
            $fromDate = $tranDate->toDateString();
            $tranDate = $tranDate->addYear();
            $tillDate = $tranDate->toDateString();

            DB::transaction(function () use ($fromDate, $tillDate) {
                DB::statement(
                    "UPDATE t_dtd AS _l
                    LEFT JOIN t_gl AS _g ON
                        _g.type = _l.type
                        AND _g.trans_no = _l.trans_no
                        AND (
                            CASE
                                WHEN _g.memo_ = 'Govt.Fee' 						THEN (_l.has_gov AND _l._seq_gov = _g._seq)
                                WHEN _g.memo_ = 'Govt. Fee' 					THEN (_l.has_split AND _l._seq_split = _g._seq)
                                WHEN _g.memo_ = 'Bank service charge' 			THEN (_l.has_bnk_chg AND _l._seq_bnk_chg = _g._seq)
                                WHEN _g.memo_ = 'VAT for Bank service charge'   THEN (_l.has_bnk_chg_vat AND _l._seq_bnk_chg_vat = _g._seq)
                                WHEN _g.memo_ = 'Service charge' 				THEN (_l.has_pf AND _l._seq_pf = _g._seq)
                                WHEN _g.memo_ = 'Returnable Benefits' 			THEN (_l.has_returnable AND _l._seq_returnable = _g._seq)
                            END
                        )
                    LEFT JOIN 0_gl_trans AS gl ON gl.counter = _g.counter
                    SET gl.transaction_id = COALESCE(
                            NULLIF(NULLIF(gl.transaction_id, 'N/A'), ''),
                            NULLIF(NULLIF(_l.transaction_id, 'N/A'), ''),
                            gl.transaction_id
                        ), 
                        gl.application_id = COALESCE(
                            NULLIF(NULLIF(gl.application_id, 'N/A'), ''),
                            NULLIF(NULLIF(_l.application_id, 'N/A'), ''),
                            gl.application_id
                        ),
                        gl.line_reference = COALESCE(
                            NULLIF(gl.line_reference, ''),
                            NULLIF(_l.line_reference, ''),
                            gl.line_reference
                        )
                    WHERE _l.tran_date >= ? AND _l.tran_date < ?",
                    [$fromDate, $tillDate]
                );
            });
        }

        Schema::dropIfExists('t_dtd');
        Schema::dropIfExists('t_gl');
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
