<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMaidDeliveryRelatedColumnsToLabourContractsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_labour_contracts', function (Blueprint $table) {
            $table->date('maid_delivered_at')->nullable()->after('memo');
            $table->date('maid_expected_by')->nullable()->after('memo');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('0_labour_contracts', function (Blueprint $table) {
            $table->dropColumn('maid_delivered_at');
            $table->dropColumn('maid_expected_by');
        });
    }
}
