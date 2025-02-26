<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMissingColumnsToSalesOrderRelatedVoidedTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_voided_sales_orders', function (Blueprint $table) {
            $table->dropIndex('trans_type');
            $table->index(['trans_type', 'order_no'], 'trans_type');
            $table->bigInteger('id')->first();
            $table->bigIncrements('_id')->first();
            $table->bigInteger('service_req_id')->after('trans_type')->nullable();
            $table->bigInteger('contract_id')->after('branch_code')->nullable();
            $table->bigInteger('salesman_id')->after('debtor_no')->nullable();
            $table->date('period_from')->after('ord_date')->nullable();
            $table->date('period_till')->after('period_from')->nullable();
            $table->string('contact_person')->after('contact_email')->nullable(false)->default('');
            $table->string('customer_trn')->after('contact_person')->nullable(false)->default('');
            $table->text('display_customer')->after('customer_trn')->nullable(false)->default('');
            $table->json('narrations')->after('display_customer')->nullable(false)->default('[]');
            $table->double('_tax')->after('total')->nullable(false)->default(0);
            $table->boolean('_tax_included')->after('_tax')->nullable(false)->default(0);
            $table->bigInteger('created_by')->after('_tax_included')->nullable(false);
            $table->boolean('updated_by')->after('created_by')->nullable(false);
            $table->dateTime('transacted_at')->after('updated_by')->nullable(false);
            $table->dateTime('updated_at')->after('transacted_at')->nullable(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('0_voided_sales_orders', function (Blueprint $table) {
            $table->dropColumn('_id');
            $table->dropColumn('id');
            $table->dropColumn('service_req_id');
            $table->dropColumn('contract_id');
            $table->dropColumn('salesman_id');
            $table->dropColumn('period_from');
            $table->dropColumn('period_till');
            $table->dropColumn('contact_person');
            $table->dropColumn('customer_trn');
            $table->dropColumn('display_customer');
            $table->dropColumn('narrations');
            $table->dropColumn('_tax');
            $table->dropColumn('_tax_included');
            $table->dropColumn('created_by');
            $table->dropColumn('updated_by');
            $table->dropColumn('transacted_at');
            $table->dropColumn('updated_at');
        });
    }
}
