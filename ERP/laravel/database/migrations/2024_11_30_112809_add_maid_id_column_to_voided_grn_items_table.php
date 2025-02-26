<?php

use App\Traits\MigratesData;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMaidIdColumnToVoidedGrnItemsTable extends Migration
{
    use MigratesData;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_voided_grn_items', function (Blueprint $table) {
            $table->bigInteger('maid_id')->nullable()->after('item_code');
        });

        $this->migrateData(function () {
            DB::table('0_voided_grn_items as gri')
                ->leftJoin('0_purch_order_details as poi', 'poi.po_detail_item', 'gri.po_detail_item')
                ->update([
                    'gri.maid_id' => DB::raw('nullif(poi.maid_id, 0)')
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
        Schema::table('0_voided_grn_items', function (Blueprint $table) {
            $table->dropColumn('maid_id');
        });
    }
}
