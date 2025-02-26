<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmployeeTransactionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('0_emp_trans', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('employee_id');
            $table->smallInteger('trans_type');
            $table->smallInteger('year');
            $table->smallInteger('month');
            $table->date('trans_date');
            $table->decimal('amount', 8, 2);
            $table->bigInteger('ref_id')
                ->nullable()
                ->default(null);
            $table->longText('memo')
                ->nullable()    
                ->default(null);
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
        Schema::dropIfExists('0_emp_trans');
    }
}