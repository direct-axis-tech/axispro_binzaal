<?php

use App\Jobs\Sales\AggregateCustomerBalancesJob;
use App\Traits\MigratesData;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class StoreLastUnpaidInvoiceDateInCustomerBalancesTable extends Migration
{
    use MigratesData;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_customer_balances', function (Blueprint $table) {
            $table->date('first_unpaid_invoice_date')->nullable()->after('running_last_invoiced_date');
            $table->date('running_first_unpaid_invoice_date')->nullable()->after('first_unpaid_invoice_date');
        });

        $this->migrateData(function () {
            (new AggregateCustomerBalancesJob())->handle();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('0_customer_balances', function (Blueprint $table) {
            $table->dropColumn('first_unpaid_invoice_date', 'running_first_unpaid_invoice_date');
        });
    }
}
