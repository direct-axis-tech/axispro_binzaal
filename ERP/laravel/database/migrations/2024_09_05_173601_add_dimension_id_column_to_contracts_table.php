<?php

use App\Models\Accounting\Dimension;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDimensionIdColumnToContractsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_labour_contracts', function (Blueprint $table) {
            $table->integer('dimension_id')->nullable(false)->after('labour_id');
        });

        DB::table('0_labour_contracts')
            ->update(['dimension_id' => Dimension::whereCenterType(CENTER_TYPES['DOMESTIC_WORKER'])->value('id')]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('0_labour_contracts', function (Blueprint $table) {
            $table->dropColumn('dimension_id');
        });
    }
}
