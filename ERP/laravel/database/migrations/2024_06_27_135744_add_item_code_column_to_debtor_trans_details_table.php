<?php

use App\Traits\MigratesData;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddItemCodeColumnToDebtorTransDetailsTable extends Migration
{
    use MigratesData;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_debtor_trans_details', function (Blueprint $table) {
            $table->string('item_code')->nullable(false)->after('debtor_trans_type');
            $table->string('kit_ref')->nullable(false)->default(1)->after('item_code');
        });

        $this->migrateData(function () {
            DB::table('0_debtor_trans_details')
                ->update([
                    'item_code' => DB::raw('stock_id')
                ]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('0_debtor_trans_details', function (Blueprint $table) {
            $table->dropColumn('item_code', 'kit_ref');
        });
    }
}
