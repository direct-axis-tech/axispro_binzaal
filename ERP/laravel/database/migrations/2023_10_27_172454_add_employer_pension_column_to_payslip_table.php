<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEmployerPensionColumnToPayslipTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_payslips', function (Blueprint $table) {
            $table->decimal('pension_employer_share', 8, 2)
                ->nullable(false)
                ->default(0.00)
                ->after('days_absent');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('0_payslips', function (Blueprint $table) {
            $table->dropColumn('pension_employer_share');
        });
    }
}