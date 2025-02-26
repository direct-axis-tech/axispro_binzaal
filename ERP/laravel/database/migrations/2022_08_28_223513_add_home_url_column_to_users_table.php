<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddHomeUrlColumnToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_users', function (Blueprint $table) {
            $table->string('home_url')->nullable();
            $table->boolean('is_fixed_home')->default(false);
        });

        DB::statement(
            'UPDATE `0_users` SET'
                . ' `home_url` = ('
                    . ' CASE'
                        . ' WHEN `startup_tab` = "sales" THEN :sales'
                        . ' WHEN `startup_tab` = "purchase" THEN :purchase'
                        . ' WHEN `startup_tab` = "hr" THEN :hr'
                        . ' WHEN `startup_tab` = "dashboard" THEN :dashboard'
                        . ' WHEN `startup_tab` = "settings" THEN :settings'
                    . ' END'
                . ' ),'
                . '`is_fixed_home` = IF(`id` = 292, 1, 0)',
            [
                "sales" => '/?application=sales',
                "purchase" => '/?application=purchase',
                "hr" => '/?application=hr',
                "settings" => '/?application=settings',
                "dashboard" => '/v3/dashboard'
            ]
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('0_users', function (Blueprint $table) {
            $table->dropColumn('home_url');
            $table->dropColumn('is_fixed_home');
        });
    }
}
