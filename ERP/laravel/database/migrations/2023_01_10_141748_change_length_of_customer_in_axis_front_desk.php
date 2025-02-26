<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeLengthOfCustomerInAxisFrontDesk extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_axis_front_desk', function (Blueprint $table) {
            $table->longText('display_customer')->change();
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
            $table->string('display_customer')->change();
        });
    }
}
