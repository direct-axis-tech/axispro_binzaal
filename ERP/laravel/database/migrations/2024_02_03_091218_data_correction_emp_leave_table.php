<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DataCorrectionEmpLeaveTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('0_emp_leaves')
            ->where('status', 'A')
            ->whereNull('reviewed_on')
            ->whereNull('reviewed_by')
            ->update([
                'reviewed_on' => DB::raw('created_at'),
                'reviewed_by' => DB::raw('created_by')
            ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
