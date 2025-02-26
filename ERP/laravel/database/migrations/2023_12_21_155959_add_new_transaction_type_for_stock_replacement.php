<?php

use App\Models\Inventory\StockReplacement;
use Illuminate\Database\Migrations\Migration;

class AddNewTransactionTypeForStockReplacement extends Migration
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
                    'id' => StockReplacement::STOCK_REPLACEMENT,
                    'name' => 'Maid Replacement',
                    'table' => '0_stock_replacement',
                    'col_type' => 'type',
                    'col_trans_no' => 'trans_no',
                    'col_reference' => 'reference',
                    'col_trans_date' => 'tran_date',
                    'next_trans_no' => 0
                ],
            ]);
    
            DB::table('0_reflines')->insert([
                [
                    "trans_type" => StockReplacement::STOCK_REPLACEMENT,
                    "prefix" => '',
                    "pattern" => 'MP{YY}{0001}',
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
        DB::table('0_reflines')->where('trans_type', StockReplacement::STOCK_REPLACEMENT)->delete();
        DB::table('0_meta_transactions')->where('id', StockReplacement::STOCK_REPLACEMENT)->delete();
        DB::table('0_meta_references')->where('type', StockReplacement::STOCK_REPLACEMENT)->delete();
    }
}
