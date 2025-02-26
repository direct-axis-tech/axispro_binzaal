<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOtherFeeInfoJsonColumnToVoidedDebtorTransDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_voided_debtor_trans_details', function (Blueprint $table) {
            $table->text('other_fee_info_json')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('0_voided_debtor_trans_details', function (Blueprint $table) {
            $table->dropColumn('other_fee_info_json');
        });
    }
}
