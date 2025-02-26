<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLineReferenceColumnToBankTransTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_bank_trans', function (Blueprint $table) {
            $table->string('line_reference')->nullable()->after('amount')->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('0_bank_trans', function (Blueprint $table) {
            $table->dropColumn('line_reference');
        });
    }
}
