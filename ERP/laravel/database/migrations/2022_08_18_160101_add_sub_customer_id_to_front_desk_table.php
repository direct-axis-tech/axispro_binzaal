<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSubCustomerIdToFrontDeskTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_axis_front_desk', function (Blueprint $table) {
            $table->bigInteger('sub_customer_id')->nullable()->after('customer_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('0_axis_front_desk', function (Blueprint $table) {
            $table->dropColumn('sub_customer_id');
        });
    }
}
