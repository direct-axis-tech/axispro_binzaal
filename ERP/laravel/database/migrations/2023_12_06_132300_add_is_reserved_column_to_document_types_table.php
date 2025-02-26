<?php

use App\Models\DocumentType;
use App\Models\Labour\Labour;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsReservedColumnToDocumentTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_document_types', function (Blueprint $table) {
            $table->boolean('is_reserved')->nullable(false)->default(false);
        });

        DocumentType::query()
            ->whereIn('id', [
                    Labour::DOC_TYPE_PASSPORT,
                    Labour::DOC_TYPE_VISA,
                    Labour::DOC_TYPE_LABOUR_CARD,
                    Labour::DOC_TYPE_PASSPORT_SIZE_PHOTO,
                    Labour::DOC_TYPE_FULL_BODY_PHOTO,
                    DocumentType::EMP_PASSPORT,
                ])
            ->update(['is_reserved' => true]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('0_document_types', function (Blueprint $table) {
            $table->dropColumn('is_reserved');
        });
    }
}
