<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSalaryDetailsAndMolIdColumnToLabours extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_labours', function (Blueprint $table) {
            $table->decimal('basic_salary', 10, 2)->after('salary')->nullable();
            $table->decimal('other_allowance', 10, 2)->after('basic_salary')->nullable();
            $table->decimal('food_allowance', 10, 2)->after('other_allowance')->nullable();
            $table->decimal('transportation_allowance', 10, 2)->after('food_allowance')->nullable();
            $table->bigInteger('mol_id')->after('transportation_allowance')->nullable();
            $table->date('date_of_joining')->after('application_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('0_labours', function (Blueprint $table) {
            $table->dropColumn(['basic_salary', 'other_allowance', 'food_allowance', 'transportation_allowance','mol_id','date_of_joining']);
        });
    }
}
