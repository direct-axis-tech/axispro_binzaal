<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAmcExpiryAcknowledgmentColumnsToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_users', function (Blueprint $table) {
            $table->dateTime('amc_expiry_ack_due_at')->nullable()->default(null);
            $table->dateTime('amc_expiry_ack_at')->nullable()->default(null);
            $table->integer('amc_expiry_times_ack')->nullable(false)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('0_users', function (Blueprint $table) {
            $table->dropColumn('amc_expiry_ack_due_at', 'amc_expiry_ack_at', 'amc_expiry_times_ack');
        });
    }
}
