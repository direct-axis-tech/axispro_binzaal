<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNewBankAccounts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('0_banks')->insert([
            ['name' => 'CENTRAL BANK OF UAE', 'routing_no' => '800110101'],
            ['name' => 'BANK OF SHARJAH', 'routing_no' => '401230101'],
            ['name' => 'BLOM BANK FRANCE', 'routing_no' => '1420151'],
            ['name' => 'BANQUE MISR', 'routing_no' => '1510102'],
            ['name' => 'CREDIT AGRICOLE CORPORATE AND INVESTMENT BANK', 'routing_no' => '301620101'],
            ['name' => 'EMIRATES NBD BANK PJSC', 'routing_no' => '202620103'],
            ['name' => 'NATIONAL BANK OF RAS AL-KHAIMAH', 'routing_no' => '104060106'],
            ['name' => 'INDUSTRIAL AND COMMERCIAL BANK OF CHINA', 'routing_no' => '804310101'],
            ['name' => 'ISLAMIC FINANCE COMPANY', 'routing_no' => '8310101'],
            ['name' => 'GULF INTERNATIONAL BANK', 'routing_no' => '509210001'],
            ['name' => 'INTESA SANPAOLO', 'routing_no' => '309314334'],
            ['name' => 'AL MARYAH COMMUNITY BANK LLC', 'routing_no' => '9710001'],
            ['name' => 'SIRAJ FINANCE PJSC', 'routing_no' => '711310001'],
            ['name' => 'GPSSA - PENSION CONTRIBUTIONS AND ', 'routing_no' => '985110101'],
        ]);

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
