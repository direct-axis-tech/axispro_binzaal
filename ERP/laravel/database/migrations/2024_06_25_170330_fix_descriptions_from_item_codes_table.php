<?php

use Illuminate\Database\Migrations\Migration;

require_once __DIR__ . '/../../utils/session_helpers.php';

class FixDescriptionsFromItemCodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $itemCodes = DB::table('0_item_codes')->pluck('description', 'id');
        foreach ($itemCodes as $id => $desc) {
            DB::table('0_item_codes')
                ->where('id', $id)
                ->update([
                    'description' => trim(html_specials_decode($desc), " \n\r\t\v\0'")
                ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
