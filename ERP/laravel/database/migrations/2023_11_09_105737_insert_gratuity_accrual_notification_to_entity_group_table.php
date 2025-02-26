<?php

use App\Models\EntityGroup;
use App\Models\EntityGroupCategory;
use Illuminate\Database\Migrations\Migration;

class InsertGratuityAccrualNotificationToEntityGroupTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        EntityGroup::insert([
            "id" => EntityGroup::GRATUITY_ACCRUAL_NOTIFICATION,
            "name" => "Gratuity Accrual Notification",
            "description" => "Group where the gratuity accrual notifications would be sent to",
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
        EntityGroup::whereId(EntityGroup::GRATUITY_ACCRUAL_NOTIFICATION)->delete();
    }
}
