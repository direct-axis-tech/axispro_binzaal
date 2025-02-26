<?php

use App\Traits\MigratesData;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsCustomerCardActColumnToDebtorTransDetailsTable extends Migration
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
            $table->boolean('is_customer_card_act')->nullable(false)->default(0)->after('govt_bank_account');
        });

        $this->migrateData(function () {
            DB::unprepared("SET SESSION sql_mode = ''");

            DB::table('0_debtor_trans_details as dtd')
                ->leftJoin('0_debtor_trans as dt', function ($join) {
                    $join->on('dt.type', 'dtd.debtor_trans_type')
                        ->whereColumn('dt.trans_no', 'dtd.debtor_trans_no');
                })
                ->where('dt.payment_method', 'CustomerCard')
                ->update(['dtd.is_customer_card_act' => 1]);
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
            $table->dropColumn('is_customer_card_act');
        });
    }
}
