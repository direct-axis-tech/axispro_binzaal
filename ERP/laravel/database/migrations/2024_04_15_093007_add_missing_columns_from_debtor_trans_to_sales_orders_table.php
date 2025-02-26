<?php

use App\Models\Sales\CustomerTransaction;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\JoinClause;

class AddMissingColumnsFromDebtorTransToSalesOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_sales_orders', function (Blueprint $table) {
            $table->json('narrations')->nullable(false)->default('[]')->after('contact_email');
            $table->text('display_customer')->nullable(false)->default('')->after('contact_email');
            $table->string('customer_trn')->nullable(false)->default('')->after('contact_email');
            $table->string('contact_person')->nullable(false)->default('')->after('contact_email');
        });

        try {
            DB::table('0_sales_orders as so')
                ->join('0_debtor_trans as dt', function (JoinClause $join) {
                    $join->on('so.order_no', 'dt.order_')
                        ->where('dt.type', CustomerTransaction::INVOICE);
                })
                ->update([
                    'so.contact_person' => DB::raw('dt.contact_person'),
                    'so.customer_trn' => DB::raw('dt.customer_trn'),
                    'so.display_customer' => DB::raw('dt.display_customer'),
                    'so.narrations' => DB::raw('dt.narrations'),
                ]);
        }

        catch (Throwable $e) {
            $this->down();
            throw $e;
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('0_sales_orders', function (Blueprint $table) {
            $table->dropColumn(
                'narrations',
                'display_customer',
                'customer_trn',
                'contact_person',
            );
        });
    }
}
