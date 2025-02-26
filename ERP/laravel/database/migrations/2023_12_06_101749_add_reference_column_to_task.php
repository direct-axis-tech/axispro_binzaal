<?php

use App\Models\MetaReference;
use App\Models\System\User;
use App\Models\Task;
use App\Models\TaskType;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddReferenceColumnToTask extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_tasks', function (Blueprint $table) {
            $table->string('reference')->nullable()->default(null)->after('task_type');
            $table->date('created_date')->nullable()->after('task_type');
        });

        Auth::login(new User(["id" => User::SYSTEM_USER]));
        try {
            DB::transaction(function () {
                Task::oldest()->get()->each(function (Task $task) {
                    DB::table('0_tasks')->whereId($task->id)->update([
                        'reference' => MetaReference::getNext(
                            Task::TASK,
                            null,
                            [
                                'type_prefix' => TaskType::find($task->task_type)->type_prefix,
                                'date' => Carbon::parse($task->created_at)->format(dateformat())
                            ],
                            true
                        ),
                        'created_date' => Carbon::parse($task->created_at)->toDateString()
                    ]);
                });
            });
        }

        catch (Throwable $e) {
            $this->down();
        }

        Schema::table('0_tasks', function (Blueprint $table) {
            $table->string('reference')->nullable(false)->change();
            $table->date('created_date')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('0_tasks', function (Blueprint $table) {
            $table->dropColumn('reference');
            $table->dropColumn('created_date');
        });
    }
}
