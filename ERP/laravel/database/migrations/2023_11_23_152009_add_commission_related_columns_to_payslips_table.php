<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCommissionRelatedColumnsToPayslipsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_payslips', function (Blueprint $table) {
            $table->decimal('commission_earned', 14, 2)->nullable(false)->default('0.00')->after('pension_employer_share');
            $table->decimal('expense_offset', 14, 2)->nullable(false)->default('0.00')->after('commission_earned');
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
            $table->dropColumn('commission_earned', 'expense_offset');
        });
    }
}
