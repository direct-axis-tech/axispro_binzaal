<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddExtraColumnsToEmployeePunchinouts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_empl_punchinouts', function (Blueprint $table) {
            $table->string('ref_id')->nullable()->after('id');
            $table->string('loc')->nullable()->default('1')->after('ref_id');

            $table->unique(['loc', 'ref_id']);
        });

        DB::table('0_empl_punchinouts')->update(['ref_id' => DB::raw('id')]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('0_empl_punchinouts', function (Blueprint $table) {
            $table->dropColumn('loc', 'ref_id');
        });
    }
}
