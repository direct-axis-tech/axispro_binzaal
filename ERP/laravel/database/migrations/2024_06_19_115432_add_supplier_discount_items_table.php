<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSupplierDiscountItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('0_supplier_discount_items', function(Blueprint $table)
        {
            $table->increments('id');
            $table->integer('supplier_id')->nullable(false);
            $table->integer('category_id')->nullable(false);
            $table->double('commission')->nullable(false)->default(0);
            $table->integer('comm_calc_method')->nullable(false)->default(CCM_AMOUNT);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('0_supplier_discount_items');
    }
}
