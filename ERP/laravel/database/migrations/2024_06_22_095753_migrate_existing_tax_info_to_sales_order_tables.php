<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

class MigrateExistingTaxInfoToSalesOrderTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $php = PHP_BINARY;
        $appDir = realpath(base_path() . '/../../');

        $output = [];
        $resultCode = null;
        exec("cd $appDir/scripts/ && $php ./migrate_existing_tax_info_to_sales_order_tables.php", $output, $resultCode);

        if ($resultCode !== 0) {
            throw new Exception(implode("\n", $output), $resultCode);
        }
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
