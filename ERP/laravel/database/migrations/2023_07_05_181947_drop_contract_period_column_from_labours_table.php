<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropContractPeriodColumnFromLaboursTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_labours', function (Blueprint $table) {
            $table->dropColumn('contract_period');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('0_labours', function (Blueprint $table) {
            $table->text('contract_period')->nullable()->after('locations');
        });
    }
}
