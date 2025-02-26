<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateIndexOnSubledgerTransInGLTransTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_gl_trans', function (Blueprint $table) {
            $table->index(['person_type_id', 'person_id', 'account', 'tran_date'], 'subledger_transaction');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('0_gl_trans', function (Blueprint $table) {
            $table->dropIndex('subledger_transaction');
        });
    }
}
