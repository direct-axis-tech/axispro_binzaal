<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\EntityGroup;
use App\Models\EntityGroupCategory;

class AddLabourExpenseRecognitionNotificationToEntityGroupsTbl extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        EntityGroup::insert([
            "id" => EntityGroup::LBR_EXPENSE_RECOGNITION_NOTIFICATION,
            "name" => "Labour Expense Recognition Notification",
            "description" => "Group where the labour expense recognition notifications would be sent to",
            "category" => EntityGroupCategory::SYSTEM_RESERVED
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        EntityGroup::whereId(EntityGroup::LBR_EXPENSE_RECOGNITION_NOTIFICATION)->delete();
    }
}
