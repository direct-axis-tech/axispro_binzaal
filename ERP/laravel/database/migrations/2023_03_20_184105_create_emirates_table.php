<?php

use App\Models\Emirate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmiratesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('0_emirates', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
        });

        Emirate::insert([
            ['name' => 'Abu Dhabi'],
            ['name' => 'Ajman'],
            ['name' => 'Dubai'],
            ['name' => 'Fujairah'],
            ['name' => 'Ras Al Khaimah'],
            ['name' => 'Sharjah'],
            ['name' => 'Umm Al Quwain']
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('0_emirates');
    }
}
