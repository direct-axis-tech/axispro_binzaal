<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class UpdateBankDetailsToDatabase extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('temp_banks', function (Blueprint $table) {
            $table->string('name')->collation('utf8mb4_general_ci');
            $table->string('routing_no')->collation('utf8mb4_general_ci')->primary();
            $table->temporary();
        });

        DB::table('temp_banks')->insert(array(
            array(
                "name" => "Abu Dhabi Commercial Bank",
                "routing_no" => "600310101"
            ),
            array(
                "name" => "Al Ahli Bank Of Kuwait K.S.C.",
                "routing_no" => "200420101"
            ),
            array(
                "name" => "Rafidain Bank",
                "routing_no" => "400510101"
            ),
            array(
                "name" => "Arab African International Bank",
                "routing_no" => "900720101"
            ),
            array(
                "name" => "Al Masraf",
                "routing_no" => "100810101"
            ),
            array(
                "name" => "Bank Melli Iran",
                "routing_no" => "901020101"
            ),
            array(
                "name" => "Bank of Baroda",
                "routing_no" => "801120101"
            ),
            array(
                "name" => "Bank Saderat Iran",
                "routing_no" => "901320124"
            ),
            array(
                "name" => "Banque Banorient France",
                "routing_no" => "1420151"
            ),
            array(
                "name" => "Al Khaliji France S.A.",
                "routing_no" => "201720101"
            ),
            array(
                "name" => "BNP Paribas",
                "routing_no" => "401810101"
            ),
            array(
                "name" => "Citibank NA",
                "routing_no" => "102120101"
            ),
            array(
                "name" => "El Nilein Bank",
                "routing_no" => "2510101"
            ),
            array(
                "name" => "EmiratesNBD Bank PJSC",
                "routing_no" => "202620103"
            ),
            array(
                "name" => "First Abu Dhabi Bank - Erstwhile FGB",
                "routing_no" => "102710102"
            ),
            array(
                "name" => "Habib Bank Limited",
                "routing_no" => "102820111"
            ),
            array(
                "name" => "Habib Bank AG Zurich",
                "routing_no" => "302920101"
            ),
            array(
                "name" => "Investbank PSC",
                "routing_no" => "503030102"
            ),
            array(
                "name" => "Lloyds TSB",
                "routing_no" => "303220101"
            ),
            array(
                "name" => "Mashreqbank",
                "routing_no" => "203320101"
            ),
            array(
                "name" => "First Abu Dhabi Bank",
                "routing_no" => "803510106"
            ),
            array(
                "name" => "National Bank Of Fujairah",
                "routing_no" => "703820101"
            ),
            array(
                "name" => "RAK Bank",
                "routing_no" => "104060106"
            ),
            array(
                "name" => "Sharjah Islamic Bank",
                "routing_no" => "404130101"
            ),
            array(
                "name" => "National Bank Of Umm Al Qaiwain",
                "routing_no" => "104251001"
            ),
            array(
                "name" => "ABUDHABI COMMERCIAL BANK Erstwhile UNB",
                "routing_no" => "704510131"
            ),
            array(
                "name" => "Emirates Investment Bank",
                "routing_no" => "4820101"
            ),
            array(
                "name" => "Deutsche Bank",
                "routing_no" => "204910101"
            ),
            array(
                "name" => "Abu Dhabi Islamic Bank",
                "routing_no" => "405010101"
            ),
            array(
                "name" => "Emirates Islamic Bank PJSC (Erstwhile Dubai Bank)",
                "routing_no" => "5120101"
            ),
            array(
                "name" => "Dubai Islamic Bank - Erstwhile Noor Bank",
                "routing_no" => "905220101"
            ),
            array(
                "name" => "Al Hilal Bank",
                "routing_no" => "105310101"
            ),
            array(
                "name" => "The Saudi National Bank",
                "routing_no" => "605520101"
            ),
            array(
                "name" => "National Bank Of Kuwait",
                "routing_no" => "505620101"
            ),
            array(
                "name" => "Ajman Bank",
                "routing_no" => "805740101"
            ),
            array(
                "name" => "Wio Bank PJSC",
                "routing_no" => "808610001"
            ),
            array(
                "name" => "KEB HANA Bank",
                "routing_no" => "408910101"
            ),
            array(
                "name" => "Bank Of China",
                "routing_no" => "309010188"
            ),
            array(
                "name" => "MCB Bank Limited",
                "routing_no" => "209120101"
            ),
            array(
                "name" => "BOK International Bank",
                "routing_no" => "209410101"
            ),
            array(
                "name" => "ZAND BANK",
                "routing_no" => "809610000"
            ),
            array(
                "name" => "Al Maryah Community Bank",
                "routing_no" => "9710001"
            ),
            array(
                "name" => "Agricultural Bank of China",
                "routing_no" => "709820785"
            ),
            array(
                "name" => "Bank Alfalah Limited",
                "routing_no" => "9920501"
            ),
            array(
                "name" => "Dubai First",
                "routing_no" => "610010101"
            ),
            array(
                "name" => "International Development Bank",
                "routing_no" => "313020001"
            ),
            array(
                "name" => "Ruya Community Islamic Bank LLC",
                "routing_no" => "413240101"
            ),
            array(
                "name" => "BUNA (AMF)",
                "routing_no" => "145110101"
            ),
            array(
                "name" => "DFM PG SETTLEMENT A\/C",
                "routing_no" => "175210101"
            ),
            array(
                "name" => "Fee related to GRC Insurance Industry",
                "routing_no" => "378910101"
            ),
            array(
                "name" => "ABU DHABI SECURITIES EXCHANGE",
                "routing_no" => "579010101"
            ),
            array(
                "name" => "EMIRATES DEVELOPMENT BANK",
                "routing_no" => "680910101"
            ),
            array(
                "name" => "Abu Dhabi Pension Fund",
                "routing_no" => "485610101"
            ),
            array(
                "name" => "Federal Tax Authority",
                "routing_no" => "386810000"
            ),
            array(
                "name" => "Federal Tax Authority  Unregistered Tax Payers",
                "routing_no" => "387310101"
            ),
            array(
                "name" => "INSURANCE SECTOR - FEES AND CHARGES COLLECTIONS",
                "routing_no" => "89210101"
            ),
            array(
                "name" => "Euroclear Bank SA\/NV",
                "routing_no" => "892010101"
            ),
            array(
                "name" => "AP- Intermediary account  Mbill",
                "routing_no" => "792110101"
            ),
            array(
                "name" => "BCCI ABU DHABI",
                "routing_no" => "193010101"
            ),
            array(
                "name" => "Unclaimed Funds Management Account",
                "routing_no" => "798710000"
            ),
            array(
                "name" => "UAE CBDC Clearing and Settlement",
                "routing_no" => "899110101"
            )
        ));

        DB::table('temp_banks as n')
            ->leftJoin('0_banks as o', 'o.routing_no', 'n.routing_no')
            ->whereNotNull('o.routing_no')
            ->update([
                'o.name' => DB::raw('n.name')
            ]);

        DB::table('0_banks')
            ->insertUsing(
                ['name', 'routing_no'],
                DB::table('temp_banks as n')
                    ->leftJoin('0_banks as o', 'o.routing_no', 'n.routing_no')
                    ->select('n.name', 'n.routing_no')
                    ->whereNull('o.routing_no')
            );
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
