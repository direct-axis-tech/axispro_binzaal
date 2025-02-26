<?php

use App\Models\Entity;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Mpdf\Tag\Article;

class CreateEntityGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('0_entity_groups');
        
        Schema::create('0_entity_groups', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('description');
            $table->timestamps();
        });

        Artisan::call('db:seed --class=EmpDocExpiryReminderGroupSeeder --force');

        // First 1000 Groups are reserved for the system
        DB::statement("ALTER TABLE `0_entity_groups` AUTO_INCREMENT = 1001");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('0_entity_groups');
    }
}
