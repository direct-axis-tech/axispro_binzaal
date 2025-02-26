<?php

use App\Models\CalendarEventType;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInstallmentDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('0_contract_installment_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('installment_id')->nullable()->index();
            $table->integer('installment_number');
            $table->date('due_date');
            $table->string('payee_name');
            $table->integer('bank_id')->nullable();
            $table->string('cheque_no', 40);
            $table->decimal('amount', 14);
            $table->string('invoice_ref')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('0_contract_installment_details');

        DB::table('0_calendar_events')->where('type_id', CalendarEventType::INSTALLMENT_REMINDER)->delete();
    }
}