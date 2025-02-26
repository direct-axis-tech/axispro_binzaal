<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddReceivableCommissionColumnsToTable0Dimensions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_dimensions', function (Blueprint $table) {
            $table->tinyInteger('is_receivable_commission_amt_editable')->default(0)->after('is_returnable_amt_editable');
            $table->tinyInteger('is_receivable_commission_act_editable')->default(0)->after('is_receivable_commission_amt_editable');
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
            $table->dropColumn('is_receivable_commission_amt_editable');
            $table->dropColumn('is_receivable_commission_act_editable');
        });
    }
}
