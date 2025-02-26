<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ConvertTypeIdKeyToUniqueInCommentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_comments', function (Blueprint $table) {
            $table->unique(['type', 'id']);
            $table->dropIndex('type_and_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('0_comments', function (Blueprint $table) {
            $table->index(['type', 'id'], 'type_and_id');
            $table->dropUnique(['type', 'id']);
        });
    }
}
