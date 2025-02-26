<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAutofetchedTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('0_autofetched_trans', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('system_id');
            $table->string('type');
            $table->string('service_en');
            $table->string('service_ar')->nullable();
            $table->decimal('service_chg');
            $table->decimal('processing_chg')->nullable();
            $table->decimal('total');
            $table->string('transaction_id');
            $table->string('application_id');
            $table->string('company')->nullable();
            $table->string('contact_name')->nullable();
            $table->string('contact_no')->nullable();
            $table->string('web_user');
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
        Schema::dropIfExists('0_autofetched_trans');
    }
}
