<?php

namespace App\Models\Hr;

use DateInterval;
use DatePeriod;
use DateTime;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class EmployeeLeaveDetail extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = '0_emp_leave_details';

    /**
     * The attributes that are guarded from mass assigning.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();
 
        static::addGlobalScope('active', function (Builder $builder) {
            $builder->where('is_cancelled', 0);
        });
    }

    /**
     * The leave associated with this detail
     *
     * @return void
     */
    public function leave() {
        return $this->belongsTo(\App\Models\Hr\EmployeeLeave::class);
    }

    /**
     * Save the given leave's details into the database
     *
     * @param int $leaveId
     * @return int
     */
    public static function generateFromLeaveId($leaveId) {
        $leave = EmployeeLeave::find($leaveId)->toArray();

        $details = [];
        $begin = DateTime::createFromFormat(DB_DATE_FORMAT, $leave['from'])->modify('midnight');
        $end = DateTime::createFromFormat(DB_DATE_FORMAT, $leave['till'])->modify('noon');
        $interval = new DateInterval('P1D');
        $dateRange = new DatePeriod($begin, $interval, $end);
        $leaveTotal = $leave['days'] < 1 ? $leave['days'] : 1;

        foreach ($dateRange as $dt) {
            $details[] = [
                "leave_id" => $leave['id'],
                "employee_id" => $leave['employee_id'],
                "leave_type_id" => $leave['leave_type_id'],
                "category_id" => $leave['category_id'],
                "type" => EmployeeLeave::DEBIT,
                "days" => $leaveTotal,
                "date" => $dt->format(DB_DATE_FORMAT)
            ];
        }

        $query = DB::table('0_emp_leave_details');
        
        return $query->getConnection()->affectingStatement(
            $query->getGrammar()->compileInsert($query, $details),
            Arr::flatten($details, 1)
        );
    }

    public static function getEmployeeLeaveRecords($employeeId, $leaveTypeId, $fromDate, $toDate, $type) {
        $result = DB::table('0_emp_leave_details as leaveDetail')
            ->selectRaw('SUM(leaveDetail.days) as leave_count')
            ->leftJoin('0_emp_leaves as leave', 'leave.id', '=', 'leaveDetail.leave_id')
            ->where([
                ["leaveDetail.leave_type_id", '=', $leaveTypeId],
                ["leaveDetail.employee_id", '=', $employeeId],
                ["leaveDetail.date", '>=', $fromDate],
                ["leaveDetail.date", '<', $toDate],
                ["leaveDetail.is_cancelled", '=', 0],
                ["leaveDetail.type", '=', $type],
                ["leave.status", '=', STS_APPROVED],
            ])
            ->whereIn('leave.category_id', [EmployeeLeave::CATEGORY_NORMAL, EmployeeLeave::CATEGORY_ADJUSTMENT])
            ->first();
    
        return $result->leave_count ?? 0;
    }

    public static function generateAdjustmentFromLeaveId($leaveId, $inputs = array())
    {
        $leave = EmployeeLeave::find($leaveId)->toArray();
        $adjustment = [
            "leave_id" => $leave['id'],
            "employee_id" => $leave['employee_id'],
            "leave_type_id" => $leave['leave_type_id'],
            "category_id" => $leave['category_id'],
            "type" => $inputs['adjustment_type'],
            "days" => $leave['days'],
            "date" => $leave['requested_on']
        ];

        DB::table('0_emp_leave_details')->insert($adjustment);
    }


}