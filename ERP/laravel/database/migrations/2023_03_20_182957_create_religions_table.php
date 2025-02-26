<?php

use App\Models\Religion;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReligionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('0_religions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
        });

        Religion::insert([
            ['name' => 'Christianity'],
            ['name' => 'Islam'],
            ['name' => 'Hinduism'],
            ['name' => 'Buddhism'],
            ['name' => 'Shinto'],
            ['name' => 'Taoism'],
            ['name' => 'Vodou'],
            ['name' => 'Sikhism'],
            ['name' => 'Judaism'],
            ['name' => 'Spiritism'],
            ['name' => 'Korean shamanism'],
            ['name' => 'Caodaism'],
            ['name' => 'Confucianism'],
            ['name' => 'Jainism'],
            ['name' => 'Cheondoism'],
            ['name' => 'Hoahaoism'],
            ['name' => 'Tenriism'],
            ['name' => 'Druze'],
            ['name' => 'Other']
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('0_religions');
    }
}
