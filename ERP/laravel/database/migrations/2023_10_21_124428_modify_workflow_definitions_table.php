<?php

use App\Models\Entity;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyWorkflowDefinitionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_workflow_definitions', function (Blueprint $table) {
            $table->integer('entity_type_id')->after('assigned_group_id')->nullable(false)->default(Entity::GROUP);
            $table->renameColumn('assigned_group_id', 'entity_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('0_workflow_definitions', function (Blueprint $table) {
            $table->renameColumn('entity_id', 'assigned_group_id');
            $table->dropColumn('entity_type_id');
        });
    }
}
