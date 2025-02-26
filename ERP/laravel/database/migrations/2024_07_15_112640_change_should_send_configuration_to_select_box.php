<?php

use App\Models\Sales\CustomerTransaction;
use App\Traits\MigratesData;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeShouldSendConfigurationToSelectBox extends Migration
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
            $table->string('should_send_email')->nullable()->change();
            $table->string('should_send_sms')->nullable()->change();
        });

        $invoiceType = CustomerTransaction::INVOICE;
        DB::table('0_debtors_master')->update([
            'should_send_email' => DB::raw("if(should_send_email, $invoiceType, NULL)"),
            'should_send_sms' => DB::raw("if(should_send_sms, $invoiceType, NULL)"),
        ]);
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
