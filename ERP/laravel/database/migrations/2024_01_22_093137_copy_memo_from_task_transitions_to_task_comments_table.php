<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CopyMemoFromTaskTransitionsToTaskCommentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('0_task_transitions', 'memo')) {
            return;
        }
        
        DB::statement("
            INSERT INTO 0_task_comments (transition_id, task_id, comment, commented_by, created_at, updated_at)
            SELECT id, task_id, memo, completed_by, completed_at, completed_at FROM 0_task_transitions WHERE memo != ''
        ");

        DB::statement('ALTER TABLE 0_task_transitions DROP COLUMN memo');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('ALTER TABLE 0_task_transitions ADD COLUMN memo text AFTER completed_by');

        DB::statement(
            'UPDATE 0_task_transitions tt
            SET tt.memo = (
                SELECT tc.comment
                FROM 0_task_comments tc
                WHERE tc.transition_id = tt.id 
                ORDER BY tc.id asc
                LIMIT 1
            )'
        );

        DB::statement('TRUNCATE TABLE 0_task_comments');
    }
}
