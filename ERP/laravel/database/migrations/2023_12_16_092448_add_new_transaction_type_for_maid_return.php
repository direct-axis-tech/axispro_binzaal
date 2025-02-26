<?php

use App\Models\Inventory\StockMove;
use Illuminate\Database\Migrations\Migration;

class AddNewTransactionTypeForMaidReturn extends Migration
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
                    'id' => StockMove::STOCK_RETURN,
                    'name' => 'Maid Return',
                    'table' => '0_stock_moves',
                    'col_type' => 'type',
                    'col_trans_no' => 'trans_no',
                    'col_reference' => 'reference',
                    'col_trans_date' => 'tran_date',
                    'next_trans_no' => 0
                ],
            ]);
    
            DB::table('0_reflines')->insert([
                [
                    "trans_type" => StockMove::STOCK_RETURN,
                    "prefix" => '',
                    "pattern" => 'MR{YY}{0001}',
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
        DB::table('0_reflines')->where('trans_type', StockMove::STOCK_RETURN)->delete();
        DB::table('0_meta_transactions')->where('id', StockMove::STOCK_RETURN)->delete();
        DB::table('0_meta_references')->where('type', StockMove::STOCK_RETURN)->delete();
    }
}
