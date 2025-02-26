<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddGovtFeeColumnToPurchDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_purch_data', function (Blueprint $table) {
            $table->double('govt_fee')->default(0)->after('stock_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('0_purch_data', function (Blueprint $table) {
            $table->dropColumn('govt_fee');
        });
    }
}