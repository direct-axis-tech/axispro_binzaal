<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEnableEmployeeColumnInInvoicePageToDimensionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_dimensions', function (Blueprint $table) {
            $table->boolean('enable_assignee_col')->nullable(false)->default(0);
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
            $table->dropColumn('enable_assignee_col');
        });
    }
}
