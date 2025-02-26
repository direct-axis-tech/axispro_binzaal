<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPrepAmountAndTransDateToLabourContractsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_labour_contracts', function (Blueprint $table) {
            $table->date('order_date')->after('reference')->nullable();
            $table->decimal('prep_amount', 14, 2)->after('amount')->nullable(false)->default(0);
        });

        DB::table('0_labour_contracts')->update([
            'order_date' => DB::raw('maid_expected_by')
        ]);

        Schema::table('0_labour_contracts', function (Blueprint $table) {
            $table->date('order_date')->nullable(false)->change();
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
            $table->dropColumn('order_date', 'prep_amount');
        });
    }
}
