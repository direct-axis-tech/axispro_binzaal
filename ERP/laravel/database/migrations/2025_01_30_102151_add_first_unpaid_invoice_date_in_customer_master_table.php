<?php

use App\Jobs\Sales\CalculateCustomerBalanceJob;
use App\Models\Sales\Customer;
use App\Traits\MigratesData;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFirstUnpaidInvoiceDateInCustomerMasterTable extends Migration
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
            $table->date('first_unpaid_invoice_date')->nullable()->after('balance');
        });

        $this->migrateData(function () {
            $customers = Customer::pluck('debtor_no')->all();

            foreach ($customers as $id) {
                (new CalculateCustomerBalanceJob($id))->handle();
            }
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
            $table->dropColumn('first_unpaid_invoice_date');
        });
    }
}
