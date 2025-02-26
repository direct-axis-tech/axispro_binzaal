<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\TaskType;

class AddPrefixForTaskType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_task_types', function (Blueprint $table) {
            $table->string('type_prefix')->nullable()->after('name');
        });

        DB::table('0_task_types')->where('id', TaskType::LEAVE_REQUEST)->update(['type_prefix' => 'LR']);
        DB::table('0_task_types')->where('id', TaskType::EMP_DOC_RELEASE_REQ)->update(['type_prefix' => 'DRR']);

        Schema::table('0_task_types', function (Blueprint $table) {
            $table->string('type_prefix')->nullable(false)->change();
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
            $table->dropColumn('type_prefix');
        });
    }
}
