<?php

use App\Models\EntityGroup;
use App\Models\EntityGroupCategory;
use Illuminate\Database\Migrations\Migration;

class AddDummyGroupToEntityGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        EntityGroup::insert([
            "id" => EntityGroup::DUMMY_GROUP,
            "name" => "Dummy Group",
            "description" => "A Dummy Group to reserve the ids upto 1000",
            "category" => EntityGroupCategory::SYSTEM_RESERVED
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        EntityGroup::whereId(EntityGroup::DUMMY_GROUP)->delete();
    }
}
