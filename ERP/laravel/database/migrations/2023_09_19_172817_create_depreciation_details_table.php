<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDepreciationDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('0_stock_depreciation_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('item_id', 20)->nullable(false);
            $table->date('depreciation_date')->nullable(false);
            $table->double('depreciation_amount', 14, 2)->default(0.00);
            $table->smallInteger('trans_type')->nullable(true);
            $table->integer('trans_no')->nullable(true);
            $table->date('trans_date')->nullable(true);
            $table->text('description')->nullable(true);
            $table->smallInteger('is_active')->default(1)->comment('1-Active');
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
        Schema::dropIfExists('0_stock_depreciation_details');
    }

}