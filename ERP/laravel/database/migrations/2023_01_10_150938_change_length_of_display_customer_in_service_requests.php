<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeLengthOfDisplayCustomerInServiceRequests extends Migration
{

    public function __construct()
   {
    DB::getDoctrineSchemaManager()->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');
   }
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_service_requests', function (Blueprint $table) {
            $table->longText('display_customer')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('0_service_requests', function (Blueprint $table) {
            $table->string('display_customer')->change();
        });
    }
}
