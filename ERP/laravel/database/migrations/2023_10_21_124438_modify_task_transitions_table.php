<?php

use App\Models\Entity;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyTaskTransitionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_task_transitions', function (Blueprint $table) {
            $table->integer('assigned_entity_type_id')->after('assigned_group_id')->nullable(false)->default(Entity::GROUP);
            $table->renameColumn('assigned_group_id', 'assigned_entity_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('0_task_transitions', function (Blueprint $table) {
            $table->renameColumn('assigned_entity_id', 'assigned_group_id');
            $table->dropColumn('assigned_entity_type_id');
        });
    }
}
