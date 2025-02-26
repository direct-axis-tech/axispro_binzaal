<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\Task;

class AddReferenceForTask extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('0_reflines')->insert([
            [
                "trans_type" => Task::TASK,
                "prefix" => '',
                "pattern" => '{SUB}{YY}{0001}',
                "description" => '',
                "default" => 1,
                "inactive" => 0
            ]
        ]);

        DB::table('0_meta_transactions')->insert([
            [
                'id' => Task::TASK,
                'name' => 'Task',
                'table' => '0_tasks',
                'col_type' => '',
                'col_trans_no' => 'id',
                'col_reference' => 'reference',
                'col_trans_date' => 'created_date',
                'next_trans_no' => 0
            ]
        ]);

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('0_reflines')->whereIn('trans_type', [Task::TASK])->delete();
        DB::table('0_meta_transactions')->whereIn('id', [Task::TASK])->delete();
    }
}
