<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTransNoToRewardDeductionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_emp_reward_deductions', function (Blueprint $table) {
            $table->integer('trans_no')->nullable()->default(null)->index()->after('document_date');
            $table->string('reference')->nullable()->default('')->after('trans_no');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('0_emp_reward_deductions', function (Blueprint $table) {
            $table->dropColumn('trans_no', 'reference');
        });
    }
}
