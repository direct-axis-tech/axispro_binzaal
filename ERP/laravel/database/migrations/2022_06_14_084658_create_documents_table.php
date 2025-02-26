<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('0_documents', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->smallInteger('entity_type');
            $table->bigInteger('entity_id');
            $table->smallInteger('document_type');
            $table->string('reference')->nullable();
            $table->date('issued_on');
            $table->date('expires_on')->nullable();
            $table->string('file');
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
        Schema::dropIfExists('0_documents');
    }
}
