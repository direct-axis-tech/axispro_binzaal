<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUsesFaCodeFlagToTaskTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_task_types', function (Blueprint $table) {
            $table->boolean('uses_fa_code')->nullable(false)->default(false)->after('module_permission');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('0_task_types', function (Blueprint $table) {
            $table->dropColumn('uses_fa_code');
        });
    }
}
