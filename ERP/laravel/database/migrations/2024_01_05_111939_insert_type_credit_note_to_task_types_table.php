<?php

use App\Models\TaskType;
use App\Permissions;
use Illuminate\Database\Migrations\Migration;

class InsertTypeCreditNoteToTaskTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        TaskType::insert([
            [
                "id" => TaskType::CREDIT_NOTE,
                "name" => "Credit Note Request",
                "class" => \Axispro\Sales\CreditNoteController::class,
                "type_prefix" => "SCN",
                "module_permission" => Permissions::HEAD_MENU_SALES,
                "uses_fa_code" => true
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        TaskType::whereId(TaskType::CREDIT_NOTE)->delete();
    }
}
