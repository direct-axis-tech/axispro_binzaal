<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEnableCardTypeColumnInDimensionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_dimensions', function (Blueprint $table) {
            $table->boolean('enable_govt_fee_pmt_method')->nullable(false)->default(0)->after('enabled_payment_methods');
            $table->boolean('require_govt_fee_pmt_method')->nullable(false)->default(0)->after('enable_govt_fee_pmt_method');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('0_dimensions', function (Blueprint $table) {
            $table->dropColumn('enable_govt_fee_pmt_method', 'require_govt_fee_pmt_method');
        });
    }
}
