<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMetaReferencesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('0_meta_references', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->mediumInteger('type')->nullable(false);
            $table->string('template')->nullable(false);
            $table->bigInteger('next_seq');
            $table->unique(['type', 'template']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('0_meta_references');
    }
}
