<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Sales\SalesOrderDetail;

class AddEntriesToMetaTransactionAndReflinesForLineItemsReference extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('0_meta_transactions')->insert([
            [
                'id' => SalesOrderDetail::ORDER_LINE_ITEM,
                'name' => 'Sales Order Line Item',
                'table' => '0_sales_order_details',
                'col_trans_no' => 'id',
                'col_reference' => 'line_reference',
                'col_trans_date' => '',
                'next_trans_no' => 0
            ]
        ]);

        DB::table('0_reflines')->insert([
            [
                "trans_type" => SalesOrderDetail::ORDER_LINE_ITEM,
                "prefix" => '',
                "pattern" => 'TR{YY}{00001}',
                "description" => '',
                "default" => 1,
                "inactive" => 0
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('0_meta_transactions')->where('id', SalesOrderDetail::ORDER_LINE_ITEM)->delete();
        DB::table('0_meta_references')->where('type', SalesOrderDetail::ORDER_LINE_ITEM)->delete();
        DB::table('0_reflines')->where('trans_type', SalesOrderDetail::ORDER_LINE_ITEM)->delete();
    }
}
