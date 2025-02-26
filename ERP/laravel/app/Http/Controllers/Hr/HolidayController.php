<?php

namespace App\Http\Controllers\Hr;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Hr\Holiday;
use App\Models\Calendar;
use App\Models\Hr\EmployeeShift;
use App\Permissions;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Yajra\DataTables\QueryDataTable;

class HolidayController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        abort_unless(
            authUser()->hasPermission(Permissions::HRM_MANAGE_HOLIDAY),
            403
        );

        return view('hr.holidays');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        abort_unless(
            authUser()->hasPermission(Permissions::HRM_MANAGE_HOLIDAY),
            403
        );

        $inputs = $this->getValidatedInputs($request);

        $result = DB::transaction(function () use ($inputs) {
            $this->validateTimeSensitiveInputs($inputs);

            Calendar::whereBetween('date', [$inputs['start_date'], $inputs['end_date']])
                ->update([
                    'is_holiday' => true,
                    'holiday_name' => $inputs['name']
                ]);

            if ($inputs['update_shifts'] != 0) {
                EmployeeShift::whereBetween('date', [$inputs['start_date'], $inputs['end_date']])
                    ->update([
                        'shift_id'   => null,
                        'updated_by' => authUser()->id
                    ]);
            }
            (new Holiday($this->columns($inputs)))->save();

            return response()->json(['message' => 'Holiday Added Successfully'], 201);
        });

        return $result;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Hr\Holiday $holiday
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Holiday $holiday)
    {
        abort_unless(authUser()->hasPermission(Permissions::HRM_MANAGE_HOLIDAY), 403);

        $inputs = $this->getValidatedInputs($request);

        $result = DB::transaction(function () use ($inputs, $holiday) {
            $this->validateTimeSensitiveInputs($inputs, $holiday->getKey());

            $this->reverseCalendarUpdates($holiday);

            $holiday->update($this->columns($inputs));

            Calendar::whereBetween('date', [$inputs['start_date'], $inputs['end_date']])
                ->update([
                    'is_holiday' => true,
                    'holiday_name' => $inputs['name']
                ]);

            if ($inputs['update_shifts'] != 0) {
                EmployeeShift::whereBetween('date', [$inputs['start_date'], $inputs['end_date']])
                    ->update([
                        'shift_id'   => null,
                        'updated_by' => authUser()->id
                    ]);
            }
                
            return response()->json(['message' => 'Holiday Updated Successfully']);
        });

        return $result;

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Hr\Holiday $holiday
     * @return \Illuminate\Http\Response
     */
    public function destroy(Holiday $holiday)
    {
        abort_unless(authUser()->hasPermission(Permissions::HRM_MANAGE_HOLIDAY), 403);

        $result = DB::transaction(function () use ($holiday) {
            $this->reverseCalendarUpdates($holiday);
    
            $holiday->update(['inactive' => true]);
    
            return response()->json(['message' => 'Holiday Deleted Successfully']);
        });

        return $result;
    }

    /**
     * Validates the request and return the inputs
     *
     * @param Request $request
     * @return array
     */
    public function getValidatedInputs(Request $request)
    {
        $inputs = $request->validate([
            'name' => 'required',
            'num_of_days' => 'required|integer|min:1',
            'start_date' => 'required|date_format:' . dateformat(),
            'update_shifts' => 'required|in:0,1'
        ]);
    
        $inputs['start_date'] = date2sql($inputs['start_date']);
        $inputs['end_date'] = Carbon::parse($inputs['start_date'])
            ->addDays($inputs['num_of_days'] - 1)
            ->toDateString();

        return $inputs;
    }

    /**
     * Returns only the columns that can be updated or inserted
     *
     * @param array $inputs
     * @return array
     */
    public function columns($inputs)
    {
        return Arr::only($inputs, [
            'name',
            'num_of_days',
            'start_date',
            'end_date'
        ]);
    }

    /**
     * Validates time sensitive data
     *
     * @param array $inputs
     * @param string $ignoreId
     * @return void
     */
    public function validateTimeSensitiveInputs($inputs, $ignoreId = null)
    {
        // Check if the same date range already exists in the Holiday table
        $conflictingHoliday = Holiday::active()
            ->whereRaw(
                '('
                    .     '(? between `start_date` and `end_date`)'
                    . ' or (? between `start_date` and `end_date`)'
                    . ' or (`start_date` >= ? and `end_date` <= ?)'
                . ')',
                [
                    $inputs['start_date'],
                    $inputs['end_date'],
                    $inputs['start_date'], $inputs['end_date'],
                ]
            );

        if ($ignoreId) {
            $conflictingHoliday->where('id', '!=', $ignoreId);
        }

        abort_if(
            $conflictingHoliday->exists(),
            422,
            'A holiday with the same date range already exists'
        );

        // Check For Payroll Processed For The Date Range
        abort_if(
            isPayslipProcessed(null, $inputs['start_date'], $inputs['end_date']),
            422,
            'Payroll has already been generated for the selected date range'
        );
    }

    /**
     * Reverse the updates to calendar for the given holiday
     *
     * @param Holiday $holiday
     * @return void
     */
    public function reverseCalendarUpdates(Holiday $holiday)
    {
        // Check if old date falls under the payroll period
        abort_if(
            isPayslipProcessed(null, $holiday->start_date, $holiday->end_date),
            422,
            "Payroll has already been generated for the selected date range"
        );

        // Update the Calendar table to mark the holiday as not a holiday
        Calendar::whereBetween('date', [$holiday->start_date, $holiday->end_date])
            ->update([
                'is_holiday' => false,
                'holiday_name' => null
            ]);
    }

    /**
     * Returns the dataTable api for this resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function dataTable()
    {
        abort_unless(
            authUser()->hasPermission(Permissions::HRM_MANAGE_HOLIDAY), 
            403
        );

        $mysqlDateFormat = getDateFormatForMySQL();
        $builder = Holiday::active()
            ->select(
                '*',
                DB::raw("date_format(`start_date`, '{$mysqlDateFormat}') as formatted_start_date"),
                DB::raw("date_format(`end_date`, '{$mysqlDateFormat}') as formatted_end_date")
            )
            ->orderBy('start_date');

        $dataTable = (new QueryDataTable(DB::query()->fromSub($builder, 't')))
            ->orderColumn('formatted_start_date', 'start_date $1')
            ->orderColumn('formatted_end_date', 'end_date $1');

        return $dataTable->toJson();
    }
}
