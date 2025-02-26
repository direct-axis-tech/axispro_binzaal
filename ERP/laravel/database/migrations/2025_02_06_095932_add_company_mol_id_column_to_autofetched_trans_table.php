<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCompanyMolIdColumnToAutofetchedTransTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_autofetched_trans', function (Blueprint $table) {
            $table->string('company_mol_id', 255)->after('company')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('0_autofetched_trans', function (Blueprint $table) {
            $table->dropColumn('company_mol_id');
        });
    }
}