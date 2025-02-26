<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveOldNotificationTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('0_notification_users');
        Schema::dropIfExists('0_notifications');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('0_notifications', function(Blueprint $table) {
            $table->bigInteger('id')->primary();
            $table->string('description')->nullable();
            $table->string('link')->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->boolean('read')->default(false);
            $table->bigInteger('created_by');
        });

        Schema::create('0_notification_users', function(Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('notification_id');
            $table->bigInteger('user_id');
            $table->boolean('read_status')->default(false);
            $table->timestamp('read_at')->nullable()->default(null);
            $table->foreign('notification_id')
                ->references('id')->on('0_notifications')
                ->onDelete('cascade');
        });
    }
}
