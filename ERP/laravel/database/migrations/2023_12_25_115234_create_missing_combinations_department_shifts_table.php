<?php

use App\Models\Hr\DepartmentShift;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\JoinClause;

class CreateMissingCombinationsDepartmentShiftsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DepartmentShift::insertUsing(
            ['department_id', 'shift_id'],
            DB::query()
                ->select(
                    'dep.id as department_id',
                    'shift.id as shift_id'
                )
                ->from('0_shifts as shift')
                ->crossJoin('0_departments as dep')
                ->leftJoin('0_department_shifts as depShift', function (JoinClause $join) {
                    $join->whereColumn('depShift.department_id', 'dep.id')
                        ->whereColumn('depShift.shift_id', 'shift.id');
                })
                ->whereNull('depShift.id')
        );
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
