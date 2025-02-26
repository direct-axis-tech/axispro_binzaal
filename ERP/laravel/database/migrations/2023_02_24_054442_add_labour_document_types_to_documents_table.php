<?php

use App\Models\DocumentType;
use App\Models\Entity;
use Illuminate\Database\Migrations\Migration;

class AddLabourDocumentTypesToDocumentsTable extends Migration
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
                'id'                 => 7,
                'entity_type'        => Entity::LABOUR,
                'name'               => 'Passport',
                'notify_before'      => '6',
                'notify_before_unit' => 'month'
            ],
            [
                'id'                 => 8,
                'entity_type'        => Entity::LABOUR,
                'name'               => 'Visa',
                'notify_before'      => '6',
                'notify_before_unit' => 'month'
            ],
            [
                'id'                 => 9,
                'entity_type'        => Entity::LABOUR,
                'name'               => 'Labour Card',
                'notify_before'      => '6',
                'notify_before_unit' => 'month'
            ],
            [
                'id'                 => 10,
                'entity_type'        => Entity::LABOUR,
                'name'               => 'Passport Size Photo',
                'notify_before'      => 6,
                'notify_before_unit' => 'month'
            ],
            [
                'id'                 => 11,
                'entity_type'        => Entity::LABOUR,
                'name'               => 'Full Size Photo',
                'notify_before'      => 6,
                'notify_before_unit' => 'month'
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DocumentType::where('entity_type', Entity::LABOUR)->delete();
    }
}
