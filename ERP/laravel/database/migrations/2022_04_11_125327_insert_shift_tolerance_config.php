<?php

use App\Models\System\Preference;
use Illuminate\Database\Migrations\Migration;

class InsertShiftToleranceConfig extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $pref = new Preference([
            'name' => 'shift_tolerance',
            'category' => 'setup.hr',
            'type' => 'int',
            'length' => '1',
            'value' => '4'
        ]);

        $pref->save();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Preference::where('name', 'shift_tolerance')->delete();
    }
}
