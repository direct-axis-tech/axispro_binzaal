<?php

use App\Models\Accounting\BankTransaction;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class PatchTaxTransDetailsTableForPaymentVoucher extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('0_trans_tax_details')
            ->where('tran_date', '>=', '2022-08-01')
            ->where('trans_type', BankTransaction::CREDIT)
            ->whereNull('reg_type')
            ->update(['reg_type'=> TR_INPUT]);
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
