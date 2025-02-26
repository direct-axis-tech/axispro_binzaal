<?php

use App\Models\DocumentType;
use App\Models\Entity;
use App\Models\Labour\Labour;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsRequiredFieldToDocumentTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_document_types', function (Blueprint $table) {
            $table->boolean('is_required')->nullable(false)->default('0');
        });

        DocumentType::whereEntityType(Entity::LABOUR)
            ->whereIn('id', [Labour::DOC_TYPE_PASSPORT, Labour::DOC_TYPE_PASSPORT_SIZE_PHOTO])
            ->update(['is_required' => true]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('0_document_types', function (Blueprint $table) {
            $table->dropColumn('is_required');
        });
    }
}
