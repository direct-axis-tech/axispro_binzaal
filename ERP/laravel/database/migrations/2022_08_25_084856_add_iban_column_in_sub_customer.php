<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIbanColumnInSubCustomer extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_sub_customers', function (Blueprint $table) {
            $table->string('iban')->nullable()->default(null)->after('trn');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('0_sub_customers', function (Blueprint $table) {
            $table->dropColumn('iban');
        });
    }
}
