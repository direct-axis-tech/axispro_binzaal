<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMissingColumnsToVoidedSalesOrderDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_voided_sales_order_details', function (Blueprint $table) {
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            $indexesFound = $sm->listTableIndexes('0_voided_sales_order_details');
            if (array_key_exists('primary', $indexesFound)) {
                $table->dropPrimary('primary');
            }

            if (array_key_exists('primary_id', $indexesFound)) {
                $table->dropIndex('primary_id');
            }
        });

        Schema::table('0_voided_sales_order_details', function (Blueprint $table) {
            $table->bigIncrements('_id')->first();
            $table->index(['id'], 'primary_id');
            $table->string('item_code')->nullable()->after('trans_type');
            $table->string('kit_ref')->nullable()->after('item_code');
            $table->string('line_reference')->nullable()->after('kit_ref');
            $table->bigInteger('srv_req_line_id')->nullable()->after('line_reference');
            $table->double('qty_not_sent')->nullable(false)->default(0)->after('qty_sent');
            $table->double('qty_expensed')->nullable(false)->default(0)->after('qty_not_sent');
            $table->double('_unit_tax')->nullable(false)->default(0)->after('unit_price');
            $table->string('passport_no')->nullable()->after('application_id');
            $table->double('split_govt_fee_amt')->nullable(false)->default(0)->after('govt_bank_account');
            $table->string('split_govt_fee_acc')->nullable()->after('split_govt_fee_amt');
            $table->double('returnable_amt')->nullable(false)->default(0)->after('split_govt_fee_acc');
            $table->string('returnable_to')->nullable()->after('returnable_amt');
            $table->double('receivable_commission_amount')->nullable(false)->default(0)->after('returnable_to');
            $table->string('receivable_commission_account')->nullable()->after('receivable_commission_amount');
            $table->double('extra_srv_chg')->nullable(false)->default(0)->after('ref_name');
            $table->double('user_commission')->nullable(false)->default(0)->after('extra_srv_chg');
            $table->double('customer_commission')->nullable(false)->default(0)->after('user_commission');
            $table->double('cust_comm_center_share')->nullable(false)->default(0)->after('customer_commission');
            $table->double('cust_comm_emp_share')->nullable(false)->default(0)->after('cust_comm_center_share');
            $table->double('customer_commission2')->nullable(false)->default(0)->after('cust_comm_emp_share');
            $table->double('assignee_id')->nullable()->after('customer_commission2');
            $table->double('created_by')->nullable()->after('assignee_id');
            $table->double('transaction_id_updated_at')->nullable()->after('created_by');
            $table->double('transaction_id_updated_by')->nullable()->after('transaction_id_updated_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('0_voided_sales_order_details', function (Blueprint $table) {
            $table->dropColumn('_id');
            $table->dropColumn('item_code');
            $table->dropColumn('kit_ref');
            $table->dropColumn('line_reference');
            $table->dropColumn('srv_req_line_id');
            $table->dropColumn('qty_not_sent');
            $table->dropColumn('qty_expensed');
            $table->dropColumn('_unit_tax');
            $table->dropColumn('passport_no');
            $table->dropColumn('split_govt_fee_amt');
            $table->dropColumn('split_govt_fee_acc');
            $table->dropColumn('returnable_amt');
            $table->dropColumn('returnable_to');
            $table->dropColumn('receivable_commission_amount');
            $table->dropColumn('receivable_commission_account');
            $table->dropColumn('extra_srv_chg');
            $table->dropColumn('user_commission');
            $table->dropColumn('customer_commission');
            $table->dropColumn('cust_comm_center_share');
            $table->dropColumn('cust_comm_emp_share');
            $table->dropColumn('customer_commission2');
            $table->dropColumn('assignee_id');
            $table->dropColumn('created_by');
            $table->dropColumn('transaction_id_updated_at');
            $table->dropColumn('transaction_id_updated_by');
        });
    }
}
