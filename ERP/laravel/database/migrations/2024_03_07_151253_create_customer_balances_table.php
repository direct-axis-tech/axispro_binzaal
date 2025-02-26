<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCustomerBalancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('0_customer_balances', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('previous_id')->nullable()->index();
            $table->integer('debtor_no')->nullable();
            $table->string('key')->index();
            $table->date('from_date')->index();
            $table->date('till_date');
            $table->double('debit', 14, 4)->default(0.00);
            $table->double('credit', 14, 4)->default(0.00);
            $table->double('balance', 14, 4)->default(0.00);
            $table->double('running_debit', 14, 4)->default(0.00);
            $table->double('running_credit', 14, 4)->default(0.00);
            $table->double('running_balance', 14, 4)->default(0.00);
            $table->double('alloc_alloc', 14, 4)->default(0.00);
            $table->double('alloc_due', 14, 4)->default(0.00);
            $table->double('alloc_outstanding', 14, 4)->default(0.00);
            $table->double('alloc_balance', 14, 4)->default(0.00);
            $table->double('alloc_running_alloc', 14, 4)->default(0.00);
            $table->double('alloc_running_due', 14, 4)->default(0.00);
            $table->double('alloc_running_outstanding', 14, 4)->default(0.00);
            $table->double('alloc_running_balance', 14, 4)->default(0.00);
            $table->double('alloc_date_alloc', 14, 4)->default(0.00);
            $table->double('alloc_date_due', 14, 4)->default(0.00);
            $table->double('alloc_date_outstanding', 14, 4)->default(0.00);
            $table->double('alloc_date_balance', 14, 4)->default(0.00);
            $table->double('alloc_date_running_alloc', 14, 4)->default(0.00);
            $table->double('alloc_date_running_due', 14, 4)->default(0.00);
            $table->double('alloc_date_running_outstanding', 14, 4)->default(0.00);
            $table->double('alloc_date_running_balance', 14, 4)->default(0.00);
            $table->date('last_payment_date')->nullable();
            $table->date('last_invoiced_date')->nullable();
            $table->date('running_last_payment_date')->nullable();
            $table->date('running_last_invoiced_date')->nullable();
            $table->index(['debtor_no', 'key']);
            $table->index(['from_date', 'till_date']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('0_customer_balances');
    }
}
