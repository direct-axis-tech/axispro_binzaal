<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

class CreateDocumentTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('0_document_types', function (Blueprint $table) {
            $table->smallInteger('id')->primary();
            $table->smallInteger('entity_type');
            $table->string('name');
            $table->decimal('notify_before')->nullable();
            $table->string('notify_before_unit', 25)->nullable();
        });

        Artisan::call('db:seed --class=DocumentTypesSeeder --force');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('0_document_types');
    }
}
