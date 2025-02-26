<?php

use App\Models\EntityGroup;
use App\Models\EntityGroupCategory;
use Illuminate\Database\Migrations\Migration;

class InsertLeaveAccrualNotificationToEntityGroupTbl extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        EntityGroup::insert([
            "id" => EntityGroup::LEAVE_ACCRUAL_NOTIFICATION,
            "name" => "Leave Accrual Notification",
            "description" => "Group where the leave accrual notifications would be sent to",
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
        EntityGroup::whereId(EntityGroup::LEAVE_ACCRUAL_NOTIFICATION)->delete();
    }
}

