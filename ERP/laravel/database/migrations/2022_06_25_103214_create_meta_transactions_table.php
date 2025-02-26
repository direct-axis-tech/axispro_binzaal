<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

class CreateMetaTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('0_meta_transactions', function (Blueprint $table) {
            $table->smallInteger('id')->primary();
            $table->string('name');
            $table->string('table');
            $table->string('col_type')->nullable();
            $table->string('col_trans_no');
            $table->string('col_reference');
            $table->string('col_trans_date');
            $table->bigInteger('next_trans_no');
        });

        Artisan::call('db:seed --class=MetaTransactionsSeeder --force');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('0_meta_transactions');
    }
}
