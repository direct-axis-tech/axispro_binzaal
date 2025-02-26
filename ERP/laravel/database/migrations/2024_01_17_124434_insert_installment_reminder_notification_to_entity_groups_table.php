<?php

use App\Models\EntityGroup;
use App\Models\EntityGroupCategory;
use Illuminate\Database\Migrations\Migration;

class InsertInstallmentReminderNotificationToEntityGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        EntityGroup::insert([
            "id" => EntityGroup::INSTALLMENT_REMINDER_NOTIFICATION,
            "name" => "Installment Reminder Notification",
            "description" => "Group where the installment reminder notifications would be sent to",
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
        EntityGroup::whereId(EntityGroup::INSTALLMENT_REMINDER_NOTIFICATION)->delete();
    }
}