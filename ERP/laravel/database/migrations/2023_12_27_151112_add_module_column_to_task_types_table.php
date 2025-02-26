<?php

use App\Models\TaskType;
use App\Permissions;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddModuleColumnToTaskTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_task_types', function (Blueprint $table) {
            $table->string('module_permission')->nullable(true);
        });

        TaskType::query()->update(['module_permission' => Permissions::HEAD_MENU_HR]);

        Schema::table('0_task_types', function (Blueprint $table) {
            $table->string('module_permission')->nullable(false)->change();
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
            $table->dropColumn('module_permission');
        });
    }
}
