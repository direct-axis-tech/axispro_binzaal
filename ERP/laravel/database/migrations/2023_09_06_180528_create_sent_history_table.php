<?php

use App\Traits\MigratesData;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateSentHistoryTable extends Migration
{
    use MigratesData;
    
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('0_sent_history')) {
            // take a backup of the database table
            Schema::dropIfExists('0_sent_history_backup');
            DB::unprepared('CREATE TABLE `0_sent_history_backup` LIKE `0_sent_history`');
            DB::unprepared('INSERT INTO `0_sent_history_backup` SELECT * FROM `0_sent_history`');
            Schema::dropIfExists('0_sent_history');
        }

        Schema::create('0_sent_history', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->smallInteger('trans_type');
            $table->smallInteger('trans_no');
            $table->string('trans_ref');
            $table->longText('content');
            $table->string('sent_through');
            $table->string('sent_to');
            $table->dateTime('sent_at');
            $table->string('resource_ref')->nullable();
            $table->timestamps();
        });

        if (Schema::hasTable('0_sent_history_backup')) {
            $this->migrateData(function () {
                DB::unprepared("SET sql_mode = ''");
                DB::unprepared(
                    "INSERT INTO `0_sent_history` (trans_type, trans_no, trans_ref, content, sent_through, sent_to, sent_at)
                        SELECT
                            history.trans_type,
                            history.trans_no,
                            `ref`.reference as trans_ref,
                            '' as `content`,
                            history.sent_type as sent_through,
                            history.sent_to,
                            history.sent_at
                        FROM `0_sent_history_backup` as history
                        INNER JOIN 0_refs as `ref` ON
                            `ref`.`type` = history.trans_type
                            AND `ref`.id = history.trans_no
                        WHERE
                            !isnull(nullif(history.sent_type, ''))
                            AND !isnull(nullif(history.sent_to, ''))
                            AND !isnull(nullif(history.sent_at, ''))"
                );
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('0_sent_history');
        DB::unprepared('CREATE TABLE `0_sent_history` LIKE `0_sent_history_backup`');
        DB::unprepared('INSERT INTO `0_sent_history` SELECT * FROM `0_sent_history_backup`');
    }
}
