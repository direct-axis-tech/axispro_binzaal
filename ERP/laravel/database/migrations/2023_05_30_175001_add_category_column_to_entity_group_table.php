<?php

use App\Models\EntityGroup;
use App\Models\EntityGroupCategory;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCategoryColumnToEntityGroupTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_entity_groups', function (Blueprint $table) {
            $table->bigInteger('category');
        });

        EntityGroup::whereIn('id', [
            EntityGroup::EMP_DOC_EXPIRY_REMINDER,
        ])->update(['category' => EntityGroupCategory::SYSTEM_RESERVED]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('0_entity_groups', function (Blueprint $table) {
            $table->dropColumn('category');
        });
    }
}
