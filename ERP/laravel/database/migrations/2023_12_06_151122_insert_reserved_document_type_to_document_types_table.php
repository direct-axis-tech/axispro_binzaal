<?php

use App\Models\DocumentType;
use App\Models\Entity;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class InsertReservedDocumentTypeToDocumentTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DocumentType::insert([
            [
                'id' => DocumentType::SPECIAL_RESERVED,
                'entity_type' => Entity::SPECIAL_GROUP,
                'name' => 'Special Reserved',
                'notify_before' => null,
                'notify_before_unit' => null,
                'is_reserved' => true
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DocumentType::whereId(DocumentType::SPECIAL_RESERVED)->delete();
    }
}
