<?php

use App\Models\Accounting\JournalTransaction;
use Illuminate\Database\Migrations\Migration;

class AddNewTransactionTypeForPayroll extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::transaction(function () {
            DB::table('0_meta_transactions')->insert([
                [
                    'id' => JournalTransaction::PAYROLL,
                    'name' => 'Payroll',
                    'table' => '0_journal',
                    'col_type' => 'type',
                    'col_trans_no' => 'trans_no',
                    'col_reference' => 'reference',
                    'col_trans_date' => 'tran_date',
                    'next_trans_no' => 0
                ],
            ]);
    
            DB::table('0_reflines')->insert([
                [
                    "trans_type" => JournalTransaction::PAYROLL,
                    "prefix" => '',
                    "pattern" => 'PRL{YY}{001}',
                    "description" => '',
                    "default" => 1,
                    "inactive" => 0
                ],
            ]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('0_reflines')->whereIn('trans_type', [JournalTransaction::PAYROLL])->delete();
        DB::table('0_meta_transactions')->whereIn('id', [JournalTransaction::PAYROLL])->delete();
        DB::table('0_meta_references')->whereIn('type', [JournalTransaction::PAYROLL])->delete();
    }
}
