<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DocumentTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('0_document_types')->insert([
            ['id' => 1, 'entity_type' => 2, 'name' => 'Passport', 'notify_before' => 6, 'notify_before_unit' => 'month'],
            ['id' => 2, 'entity_type' => 2, 'name' => 'Visa', 'notify_before' => 2, 'notify_before_unit' => 'month'],
            ['id' => 3, 'entity_type' => 2, 'name' => 'Employee Insurance', 'notify_before' => 2, 'notify_before_unit' => 'month'],
            ['id' => 4, 'entity_type' => 2, 'name' => 'Labour Card', 'notify_before' => 2, 'notify_before_unit' => 'month'],
            ['id' => 5, 'entity_type' => 2, 'name' => 'Emirates ID', 'notify_before' => 2, 'notify_before_unit' => 'month'],
            ['id' => 6, 'entity_type' => 2, 'name' => 'Employment Contract', 'notify_before' => 2, 'notify_before_unit' => 'month'],
        ]);
    }
}
