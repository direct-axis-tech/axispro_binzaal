<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\Sales\CustomerTransaction;

class UpdateDueDateInDebtorTransTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('0_debtor_trans')
        ->where(function ($query) {
            $query->whereNull('due_date')
                ->orWhere('due_date', '0000-00-00');
        })
        ->whereIn('type', [CustomerTransaction::INVOICE, CustomerTransaction::DELIVERY])
        ->update([
            'due_date' => DB::raw('tran_date')
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        
    }
}
