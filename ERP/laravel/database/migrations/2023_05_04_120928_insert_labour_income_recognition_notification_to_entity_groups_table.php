<?php

use App\Models\EntityGroup;
use Illuminate\Database\Migrations\Migration;

class InsertLabourIncomeRecognitionNotificationToEntityGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        EntityGroup::insert([
            "id" => EntityGroup::LBR_INCOME_RECOGNITION_NOTIFICATION,
            "name" => "Labour Income Recognition Notification",
            "description" => "Group where the labour income recognition notifications would be sent to"
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        EntityGroup::whereId(EntityGroup::LBR_INCOME_RECOGNITION_NOTIFICATION)->delete();
    }
}
