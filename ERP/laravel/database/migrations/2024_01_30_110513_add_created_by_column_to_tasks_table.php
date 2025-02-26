<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCreatedByColumnToTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_tasks', function (Blueprint $table) {
            $table->smallInteger('created_by')->nullable()->after('created_date');
        });

        DB::table('0_tasks')->update(['created_by' => DB::raw('initiated_by')]);

        Schema::table('0_tasks', function (Blueprint $table) {
            $table->smallInteger('created_by')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('0_tasks', function (Blueprint $table) {
            $table->dropColumn('created_by');
        });
    }
}
