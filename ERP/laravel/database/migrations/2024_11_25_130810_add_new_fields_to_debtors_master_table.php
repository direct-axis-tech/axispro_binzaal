<?php

use App\Traits\MigratesData;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNewFieldsToDebtorsMasterTable extends Migration
{
    use MigratesData;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_debtors_master', function (Blueprint $table) {
            $table->dateTime('modified_at')->nullable();
            $table->integer('created_by');
            $table->integer('modified_by')->nullable();
        });

        $this->migrateData(function () {
            DB::table('0_debtors_master')->update([
                'created_at' => DB::raw('DATE_ADD(created_at, INTERVAL 4 HOUR)')
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
        Schema::table('0_debtors_master', function (Blueprint $table) {
            $table->dropColumn('modified_at')->nullable();
            $table->dropColumn('created_by');
            $table->dropColumn('modified_by')->nullable();
        });
    }
}
