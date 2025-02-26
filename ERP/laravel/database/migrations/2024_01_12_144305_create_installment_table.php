<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInstallmentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('0_contract_installments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('contract_id')->index();
            $table->smallInteger('person_type_id');
            $table->bigInteger('person_id');
            $table->date('trans_date');
            $table->decimal('total_amount', 14);
            $table->integer('no_installment');
            $table->integer('interval');
            $table->string('interval_unit', 25);
            $table->decimal('installment_amount', 14);
            $table->date('start_date');
            $table->bigInteger('bank_id');
            $table->string('payee_name');
            $table->string('initial_cheque_no', 40);
            $table->smallInteger('created_by');
            $table->smallInteger('updated_by')->nullable();
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
        Schema::dropIfExists('0_contract_installments');
    }
}