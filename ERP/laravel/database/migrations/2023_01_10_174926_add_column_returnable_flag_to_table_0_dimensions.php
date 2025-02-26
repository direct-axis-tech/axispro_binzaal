<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnReturnableFlagToTable0Dimensions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_dimensions', function (Blueprint $table) {
            $table->boolean('is_returnable_amt_editable')->default(false)->after('type_');
            $table->boolean('is_returnable_act_editable')->default(false)->after('type_');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('0_dimensions', function (Blueprint $table) {
            $table->dropColumn('is_returnable_amt_editable');
            $table->dropColumn('is_returnable_act_editable');
        });
    }
}
