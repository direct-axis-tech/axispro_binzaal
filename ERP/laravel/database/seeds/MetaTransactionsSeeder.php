<?php

use App\Models\Accounting\BankTransaction;
use App\Models\Accounting\JournalTransaction;
use App\Models\Sales\CustomerTransaction;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MetaTransactionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('0_meta_transactions')->insert([
            [
                'id' => JournalTransaction::JOURNAL,
                'name' => 'Journal Entry',
                'table' => '0_journal',
                'col_type' => 'type',
                'col_trans_no' => 'trans_no',
                'col_reference' => 'reference',
                'col_trans_date' => 'tran_date',
                'next_trans_no' => 0
            ],
            [
                'id' => BankTransaction::CREDIT,
                'name' => 'Payment Voucher',
                'table' => '0_bank_trans',
                'col_type' => 'type',
                'col_trans_no' => 'trans_no',
                'col_reference' => 'ref',
                'col_trans_date' => 'trans_date',
                'next_trans_no' => 0
            ],
            [
                'id' => BankTransaction::DEBIT,
                'name' => 'Receipt Voucher',
                'table' => '0_bank_trans',
                'col_type' => 'type',
                'col_trans_no' => 'trans_no',
                'col_reference' => 'ref',
                'col_trans_date' => 'trans_date',
                'next_trans_no' => 0
            ],
            [
                'id' => BankTransaction::TRANSFER,
                'name' => 'Bank Transfer',
                'table' => '0_bank_trans',
                'col_type' => 'type',
                'col_trans_no' => 'trans_no',
                'col_reference' => 'ref',
                'col_trans_date' => 'trans_date',
                'next_trans_no' => 0
            ],
            [
                'id' => CustomerTransaction::INVOICE,
                'name' => 'Sales Invoice',
                'table' => '0_debtor_trans',
                'col_type' => 'type',
                'col_trans_no' => 'trans_no',
                'col_reference' => 'reference',
                'col_trans_date' => 'tran_date',
                'next_trans_no' => 0
            ],
            [
                'id' => CustomerTransaction::CREDIT,
                'name' => 'Credit Note',
                'table' => '0_debtor_trans',
                'col_type' => 'type',
                'col_trans_no' => 'trans_no',
                'col_reference' => 'reference',
                'col_trans_date' => 'tran_date',
                'next_trans_no' => 0
            ],
            [
                'id' => CustomerTransaction::PAYMENT,
                'name' => 'Customer Payment',
                'table' => '0_debtor_trans',
                'col_type' => 'type',
                'col_trans_no' => 'trans_no',
                'col_reference' => 'reference',
                'col_trans_date' => 'tran_date',
                'next_trans_no' => 0
            ],
            [
                'id' => CustomerTransaction::DELIVERY,
                'name' => 'Delivery Note',
                'table' => '0_debtor_trans',
                'col_type' => 'type',
                'col_trans_no' => 'trans_no',
                'col_reference' => 'reference',
                'col_trans_date' => 'tran_date',
                'next_trans_no' => 0
            ],
            [
                'id' => 16,
                'name' => 'Location Transfer',
                'table' => '0_stock_moves',
                'col_type' => 'type',
                'col_trans_no' => 'trans_no',
                'col_reference' => 'reference',
                'col_trans_date' => 'tran_date',
                'next_trans_no' => 0
            ],
            [
                'id' => 17,
                'name' => 'Inventory Adjustment',
                'table' => '0_stock_moves',
                'col_type' => 'type',
                'col_trans_no' => 'trans_no',
                'col_reference' => 'reference',
                'col_trans_date' => 'tran_date',
                'next_trans_no' => 0
            ],
            [
                'id' => 18,
                'name' => 'Purchase Order',
                'table' => '0_purch_orders',
                'col_type' => null,
                'col_trans_no' => 'order_no',
                'col_reference' => 'reference',
                'col_trans_date' => 'ord_date',
                'next_trans_no' => 0
            ],
            [
                'id' => 20,
                'name' => 'Supplier Invoice',
                'table' => '0_supp_trans',
                'col_type' => 'type',
                'col_trans_no' => 'trans_no',
                'col_reference' => 'reference',
                'col_trans_date' => 'tran_date',
                'next_trans_no' => 0
            ],
            [
                'id' => 21,
                'name' => 'Debit Note',
                'table' => '0_supp_trans',
                'col_type' => 'type',
                'col_trans_no' => 'trans_no',
                'col_reference' => 'reference',
                'col_trans_date' => 'tran_date',
                'next_trans_no' => 0
            ],
            [
                'id' => 22,
                'name' => 'Supplier Payment',
                'table' => '0_supp_trans',
                'col_type' => 'type',
                'col_trans_no' => 'trans_no',
                'col_reference' => 'reference',
                'col_trans_date' => 'tran_date',
                'next_trans_no' => 0
            ],
            [
                'id' => 25,
                'name' => 'Goods Recieved Note',
                'table' => '0_grn_batch',
                'col_type' => null,
                'col_trans_no' => 'id',
                'col_reference' => 'reference',
                'col_trans_date' => 'delivery_date',
                'next_trans_no' => 0
            ],
            [
                'id' => 26,
                'name' => 'Work Order',
                'table' => '0_workorders',
                'col_type' => null,
                'col_trans_no' => 'id',
                'col_reference' => 'wo_ref',
                'col_trans_date' => 'released_date',
                'next_trans_no' => 0
            ],
            [
                'id' => 28,
                'name' => 'Work Order Issue',
                'table' => '0_wo_issues',
                'col_type' => null,
                'col_trans_no' => 'issue_no',
                'col_reference' => 'reference',
                'col_trans_date' => 'issue_date',
                'next_trans_no' => 0
            ],
            [
                'id' => 29,
                'name' => 'Work Order Receive',
                'table' => '0_wo_manufacture',
                'col_type' => null,
                'col_trans_no' => 'id',
                'col_reference' => 'reference',
                'col_trans_date' => 'date_',
                'next_trans_no' => 0
            ],
            [
                'id' => 30,
                'name' => 'Sales Order',
                'table' => '0_sales_orders',
                'col_type' => 'trans_type',
                'col_trans_no' => 'order_no',
                'col_reference' => 'reference',
                'col_trans_date' => 'ord_date',
                'next_trans_no' => 0
            ],
            [
                'id' => 31,
                'name' => 'Service Order',
                'table' => '0_service_orders',
                'col_type' => null,
                'col_trans_no' => 'order_no',
                'col_reference' => 'cust_ref',
                'col_trans_date' => 'date',
                'next_trans_no' => 0
            ],
            [
                'id' => 32,
                'name' => 'Sales Quote',
                'table' => '0_sales_orders',
                'col_type' => 'trans_type',
                'col_trans_no' => 'order_no',
                'col_reference' => 'reference',
                'col_trans_date' => 'ord_date',
                'next_trans_no' => 0
            ],
            [
                'id' => 40,
                'name' => 'Dimension',
                'table' => '0_dimensions',
                'col_type' => null,
                'col_trans_no' => 'id',
                'col_reference' => 'reference',
                'col_trans_date' => 'date_',
                'next_trans_no' => 0
            ],
            [
                'id' => JournalTransaction::COST_UPDATE,
                'name' => 'Cost Update',
                'table' => '0_journal',
                'col_type' => 'type',
                'col_trans_no' => 'trans_no',
                'col_reference' => 'reference',
                'col_trans_date' => 'tran_date',
                'next_trans_no' => 0
            ],
        ]);
    }
}
