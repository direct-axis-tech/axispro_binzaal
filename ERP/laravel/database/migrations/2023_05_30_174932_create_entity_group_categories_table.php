<?php

use App\Models\EntityGroupCategory;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEntityGroupCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('0_entity_group_categories', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->string('description');
        });

        EntityGroupCategory::insert([
            ["id" => EntityGroupCategory::SYSTEM_RESERVED, "description" => "System Reserved"],
            ["id" => EntityGroupCategory::WORK_FLOW_RELATED, "description" => "Workflow Related"]
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('0_entity_group_categories');
    }
}
