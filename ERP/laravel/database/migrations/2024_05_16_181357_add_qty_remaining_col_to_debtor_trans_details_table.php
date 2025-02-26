<?php

use App\Traits\MigratesData;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddQtyRemainingColToDebtorTransDetailsTable extends Migration
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
            $table->double('qty_remaining')->nullable(false)->default(0)->after('qty_done')->index();
        });

        $this->migrateData(function () {
            DB::table('0_debtor_trans_details')
                ->update([
                    'qty_remaining' => DB::raw('quantity - qty_done')
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
            $table->double('qty_remaining');
        });
    }
}
