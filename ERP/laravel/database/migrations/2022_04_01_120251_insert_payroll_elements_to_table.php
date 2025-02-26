<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class InsertPayrollElementsToTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::transaction(function() {
            DB::table('0_pay_elements')->insert([
                [
                    'name' => 'Holded Salary',
                    'type' => '-1',
                    'is_fixed' => '0',
                    'inactive' => '0'
                ],
                [
                    'name' => 'Released Holded Salary',
                    'type' => '1',
                    'is_fixed' => '0',
                    'inactive' => '0'
                ]
            ]);
            $holderSalaryId = DB::table('0_pay_elements')->where('name', 'Holded Salary')->first()->id;
            $releasedHoldedSalaryId = DB::table('0_pay_elements')->where('name', 'Released Holded Salary')->first()->id;
    
            DB::table('0_sys_prefs')->insert([
                [
                    "name" => "holded_salary_el",
                    "category" => "setup.hr",
                    "type" => "decimal",
                    "length" => 0,
                    "value" => $holderSalaryId
                ],
                [
                    "name" => "released_holded_salary_el",
                    "category" => "setup.hr",
                    "type" => "decimal",
                    "length" => 0,
                    "value" => $releasedHoldedSalaryId
                ]
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
            DB::table('0_pay_elements')->whereIn('name', ['Holded Salary', 'Released Holded Salary'])->delete();
            DB::table('0_sys_prefs')->whereIn('name', ['holded_salary_el', 'released_holded_salary_el'])->delete();
        });
    }
}