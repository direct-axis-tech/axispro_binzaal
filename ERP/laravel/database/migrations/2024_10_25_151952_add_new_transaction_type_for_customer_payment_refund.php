<?php

use App\Models\Sales\CustomerTransaction;
use Illuminate\Database\Migrations\Migration;

class AddNewTransactionTypeForCustomerPaymentRefund extends Migration
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
                    'id' => CustomerTransaction::REFUND,
                    'name' => 'Customer Payment Refund',
                    'table' => '0_debtor_trans',
                    'col_type' => 'type',
                    'col_trans_no' => 'trans_no',
                    'col_reference' => 'reference',
                    'col_trans_date' => 'tran_date',
                    'next_trans_no' => 0
                ],
            ]);
    
            DB::table('0_reflines')->insert([
                [
                    "trans_type" => CustomerTransaction::REFUND,
                    "prefix" => '',
                    "pattern" => 'CPR{YY}{0001}',
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
        DB::table('0_reflines')->where('trans_type', CustomerTransaction::REFUND)->delete();
        DB::table('0_meta_transactions')->where('id', CustomerTransaction::REFUND)->delete();
        DB::table('0_meta_references')->where('type', CustomerTransaction::REFUND)->delete();
    }
}
