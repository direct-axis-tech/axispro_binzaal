<?php

use Illuminate\Database\Migrations\Migration;

class ChangeEmailTemplateToBase64Encoded extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $template = DB::table('0_sys_prefs')->where('name', 'email_template')->value('value');
        DB::table('0_sys_prefs')->where('name', 'email_template')->update(['value' => base64_encode($template)]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $template = DB::table('0_sys_prefs')->where('name', 'email_template')->value('value');
        DB::table('0_sys_prefs')->where('name', 'email_template')->update(['value' => base64_decode($template)]);
    }
}
