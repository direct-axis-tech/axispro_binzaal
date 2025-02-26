<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class InsertProcessingFeeAndProcessingFeeTaxRateAndExtraChargeForOnlinePaymentToSysPrefsTbl extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

            DB::table('0_sys_prefs')->insert(
                array(
                    ['name' => 'processing_fee_rate','category'=>'setup.company','type'=>'varchar','length'=>11,'value'=>0.0231],
                    ['name' => 'processing_fee_tax_rate','category'=>'setup.company','type'=>'varchar','length'=>11,'value'=>0],
                    ['name' => 'extra_charge_for_online_payment','category'=>'setup.company','type'=>'varchar','length'=>11,'value'=>0],
                )
            );   

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
            DB::transaction(function() {
                DB::table('0_sys_prefs')
                    ->whereIn('name', ['processing_fee_rate', 'processing_fee_tax_rate', 'extra_charge_for_online_payment'])
                    ->delete();
            });

    }
}
