<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRequestTypeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('0_request_types', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('request_type', 255)->nullable(false);
            $table->string('remarks', 255)->nullable()->default(null);
            $table->smallInteger('created_by');
            $table->boolean('inactive')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('0_request_types');
    }
}
