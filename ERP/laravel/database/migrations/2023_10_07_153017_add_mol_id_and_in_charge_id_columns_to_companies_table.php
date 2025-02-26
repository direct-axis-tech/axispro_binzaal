<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMolIdAndInChargeIdColumnsToCompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_companies', function (Blueprint $table) {
            $table->string('mol_id', 255)->nullable();
            $table->bigInteger('in_charge_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('0_companies', function (Blueprint $table) {
            $table->dropColumn('mol_id', 'in_charge_id');
        });
    }
}
