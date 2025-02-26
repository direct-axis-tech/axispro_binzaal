<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmpDocExpiryReminderGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('0_entity_groups')->insert([
            'id' => 1,
            'name' => 'EmpDoc Expiry Reminder',
            'description' => 'Employees who will receive reminder notifications for expiring documents'
        ]);
    }
}
