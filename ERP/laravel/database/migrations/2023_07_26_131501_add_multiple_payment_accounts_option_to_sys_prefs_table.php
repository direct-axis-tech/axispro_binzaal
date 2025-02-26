<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddMultiplePaymentAccountsOptionToSysPrefsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::transaction(function() {
            DB::table('0_sys_prefs')->whereIn('name', [
                'customer_card_act',
                'dflt_csh_pmt_act',
                'dflt_edirhame_bank_act',
                'dflt_bnk_trnsfr_pmt_act',
                'dflt_online_pmt_act',
                'dflt_credit_card_pmt_act'
            ])->delete();

            DB::table('0_sys_prefs')->insert([
                [
                    'name' => 'center_card_accounts',
                    'category' => 'setup.axispro',
                    'type' => 'smallint',
                    'length' => 6,
                    'value' => ''
                ],
                [
                    'name' => 'customer_card_accounts',
                    'category' => 'setup.axispro',
                    'type' => 'smallint',
                    'length' => 6,
                    'value' => ''
                ],
                [
                    'name' => 'cash_accounts',
                    'category' => 'setup.axispro',
                    'type' => 'smallint',
                    'length' => 6,
                    'value' => ''
                ],
                [
                    'name' => 'credit_card_accounts',
                    'category' => 'setup.axispro',
                    'type' => 'smallint',
                    'length' => 6,
                    'value' => ''
                ],
                [
                    'name' => 'bank_transfer_accounts',
                    'category' => 'setup.axispro',
                    'type' => 'smallint',
                    'length' => 6,
                    'value' => ''
                ],
                [
                    'name' => 'online_payment_accounts',
                    'category' => 'setup.axispro',
                    'type' => 'smallint',
                    'length' => 6,
                    'value' => ''
                ],
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
        DB::transaction(function() {
            DB::table('0_sys_prefs')->whereIn('name', [
                'center_card_accounts',
                'customer_card_accounts',
                'cash_accounts',
                'bank_transfer_accounts',
                'online_payment_accounts',
                'credit_card_accounts'
            ])->delete();

            DB::table('0_sys_prefs')->insert([
                [
                    'name' => 'dflt_csh_pmt_act',
                    'category' => 'setup.axispro',
                    'type' => 'smallint',
                    'length' => 6,
                    'value' => ''
                ],
                [
                    'name' => 'customer_card_act',
                    'category' => 'setup.axispro',
                    'type' => 'smallint',
                    'length' => 6,
                    'value' => ''
                ],
                [
                    'name' => 'dflt_edirhame_bank_act',
                    'category' => 'setup.axispro',
                    'type' => 'smallint',
                    'length' => 6,
                    'value' => ''
                ],
                [
                    'name' => 'dflt_credit_card_pmt_act',
                    'category' => 'setup.axispro',
                    'type' => 'smallint',
                    'length' => 6,
                    'value' => ''
                ],
                [
                    'name' => 'dflt_bnk_trnsfr_pmt_act',
                    'category' => 'setup.axispro',
                    'type' => 'smallint',
                    'length' => 6,
                    'value' => ''
                ],
                [
                    'name' => 'dflt_online_pmt_act',
                    'category' => 'setup.axispro',
                    'type' => 'smallint',
                    'length' => 6,
                    'value' => ''
                ],
            ]);
        });
    }
}
