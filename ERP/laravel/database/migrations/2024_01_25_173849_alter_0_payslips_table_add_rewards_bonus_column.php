<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Alter0PayslipsTableAddRewardsBonusColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_payslips', function (Blueprint $table) {
            $table->decimal('rewards_bonus', 8, 2)->default('0.00')->nullable(false)->after('violations');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('0_payslips', function (Blueprint $table) {
            $table->dropColumn('rewards_bonus');
        });
    }
}
