<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\Hr\DepartmentShift;
use App\Models\Hr\Shift;
use App\Permissions;
use Carbon\CarbonImmutable;
use DateTimeZone;
use DB;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Yajra\DataTables\QueryDataTable;

class ShiftController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $shifts = Shift::orderBy('id', 'DESC')->paginate(20);
        return view('hr.shifts', ['shifts' => $shifts]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        abort_unless(authUser()->hasPermission(Permissions::HRM_MANAGE_SHIFT), 403);

        $inputs = $request->validate($this->getValidationRules());
        
        $shift = Shift::create(array_merge($inputs, $this->calculateDurations($inputs)));

        DepartmentShift::insertUsing(
            ['department_id', 'shift_id'],
            DB::table('0_departments as dep')
                ->crossJoin('0_shifts as shift', function (Builder $query) use ($shift) {
                    $query->where('shift.id', $shift->id);
                })
                ->addSelect('dep.id as department_id')
                ->addSelect('shift.id as shift_id')
        );
        
        return response()->json([
            'message' => 'Shift Saved Successfully',
            'shift' => $shift
        ], 201);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Hr\Shift $shift
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Shift $shift)
    {
        abort_unless(authUser()->hasPermission(Permissions::HRM_MANAGE_SHIFT), 403);

        $inputs = Arr::except(
            $request->validate($this->getValidationRules($shift->id)),
            ['_method']
        );

        abort_if($shift->is_used, 422, 'This shift is already assigned for employees');

        $shift->update(array_merge($inputs, $this->calculateDurations($inputs)));

        return response()->json(['message' => 'Shift Updated Successfully']);
    }

    /**
     * Returns the validation rules for adding and editing
     *
     * @param string $ignoreId The id to ignore when checking for uniqueness
     * @return array
     */
    protected function getValidationRules($ignoreId = null)
    {
        return [
            'code' => ['required', 'string', Rule::unique('0_shifts')->ignore($ignoreId)],
            'description' => 'required|string',
            'color' => ['required', 'regex:/^#(?!(000000|ffffff))[0-9a-fA-F]{6}$/'],
            'from' => 'required|date_format:H:i',
            'till' => 'required|date_format:H:i',
            'from2' => 'nullable|date_format:H:i',
            'till2' => 'bail|required_with:from2|nullable|date_format:H:i',
        ];
    }

    /**
     * calculates the actual durations
     *
     * @param array $inputs
     * @return array
     */
    protected function calculateDurations($inputs)
    {
        $tz = new DateTimeZone('UTC');
        $zeroTime = CarbonImmutable::createFromFormat('!H:i:s', '00:00:00', $tz);
        $from = CarbonImmutable::createFromFormat('!H:i', $inputs['from'], $tz);
        $till = CarbonImmutable::createFromFormat('!H:i', $inputs['till'], $tz);
        Shift::fixDatesInOrder($from, $till);
        $totalDuration = $duration = $zeroTime->add($from->diff($till));

        $from2 = $till2 = $duration2 = null;
        if (!empty($inputs['from2'])) {
            $from2 = CarbonImmutable::createFromFormat('!H:i', $inputs['from2'], $tz);
            $till2 = CarbonImmutable::createFromFormat('!H:i', $inputs['till2'], $tz);
            Shift::fixDatesInOrder($till, $from2, $till2);
            $diff = $from2->diff($till2);
            $duration2 = $zeroTime->add($diff);
            $totalDuration = $totalDuration->add($diff);
        }

        $formatDuration = function ($dur) use ($zeroTime) {
            return str_pad($zeroTime->diffInHours($dur), 2, "0", STR_PAD_LEFT).$dur->format(':i:s');
        };

        return [
            'from' => $from->format('H:i:s'),
            'till' => $till->format('H:i:s'),
            'duration' => $formatDuration($duration),
            'from2' => $from2 ? $from2->format('H:i:s') : null,
            'till2' => $till2 ? $till2->format('H:i:s') : null,
            'duration2' => $duration2 ? $formatDuration($duration2) : null,
            'total_duration' => $formatDuration($totalDuration)
        ];
    }

    public function dataTable(Request $request)
    {
        abort_unless($request->user()->hasPermission(Permissions::HRM_MANAGE_SHIFT), 403);

        $builder = DB::table('0_shifts as shift')
            ->leftJoin('0_sys_prefs as default_shift', function (JoinClause $join) {
                $join->where('name', 'default_shift_id');
            })
            ->select("shift.*")
            ->selectRaw("time_format(shift.from, '%h:%i %p') as formatted_from")
            ->selectRaw("time_format(shift.till, '%h:%i %p') as formatted_till")
            ->selectRaw("time_format(shift.from2, '%h:%i %p') as formatted_from2")
            ->selectRaw("time_format(shift.till2, '%h:%i %p') as formatted_till2")
            ->selectRaw("time_format(shift.total_duration, '%H hr %i min') as formatted_total_duration")
            ->selectRaw(
                "("
                    . "shift.id = default_shift.value"
                    . " or exists(select 1 from `0_attendance` atd where atd.based_on_shift_id = shift.id limit 1)"
                    . " or exists(select 1 from `0_emp_shifts` es where es.shift_id = shift.id limit 1)"  
                .") as is_used"
            );

        $dataTable = new QueryDataTable(DB::query()->fromSub($builder, 't'));
        
        return $dataTable->toJson();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Hr\Shift $shift
     * @return \Illuminate\Http\Response
     */
    public function destroy(Shift $shift)
    {
        abort_unless(authUser()->hasPermission(Permissions::HRM_MANAGE_SHIFT), 403);
        abort_if($shift->is_used, 422, 'This shift is already assigned for employees');
        
        DepartmentShift::whereShiftId($shift->id)->delete();
        $shift->delete();
        
        return response()->json(['message' => 'Shift Deleted Successfully']);
    }

    public function getSuggestedColors()
    {
        $colors = array_flip([
            "#ff0040", "#ff0080", "#ff00bf", "#ff00ff", "#bf00ff",
            "#7f00ff", "#4000ff", "#0000ff", "#0040ff", "#007fff",
            "#00bfff", "#00ffff", "#00ffbf", "#00ff80", "#00ff40",
            "#00ff00", "#40ff00", "#80ff00", "#bfff00", "#ffff00",
            "#ffbf00", "#ff8000", "#ff4000", "#ff0000", "#e6194c",
            "#e61980", "#e619b2", "#e619e5", "#b319e6", "#7f19e6",
            "#4d19e6", "#1919e6", "#194ce6", "#197fe6", "#19b3e6",
            "#19e5e6", "#19e6b3", "#19e680", "#19e64d", "#19e619",
            "#4ce619", "#80e619", "#b3e619", "#e5e619", "#e6b319",
            "#e68019", "#e64d19", "#e61919", "#cc3359", "#cc3380",
            "#cc33a6", "#cc33cc", "#a633cc", "#7f33cc", "#5933cc",
            "#3333cc", "#3359cc", "#337fcc", "#33a6cc", "#33cccc",
            "#33cca6", "#33cc80", "#33cc59", "#33cc33", "#59cc33",
            "#80cc33", "#a6cc33", "#cccc33", "#cca633", "#cc8033",
            "#cc5933", "#cc3333", "#800020", "#800040", "#800060",
            "#80007f", "#600080", "#400080", "#200080", "#000080",
            "#002080", "#004080", "#006080", "#007f80", "#008060",
            "#008040", "#008020", "#008000", "#208000", "#408000",
            "#608000", "#7f8000", "#806000", "#804000", "#802000",
            "#800000", "#730d26", "#730d40", "#730d59", "#730d73",
            "#590d73", "#400d73", "#260d73", "#0d0d73", "#0d2673",
            "#0d4073", "#0d5973", "#0d7373", "#0d7359", "#0d7340",
            "#0d7326", "#0d730d", "#26730d", "#40730d", "#59730d",
            "#73730d", "#73590d", "#73400d", "#73260d", "#730d0d",
            "#66192d", "#661940", "#661953", "#661966", "#531966",
            "#401966", "#2d1966", "#191966", "#192d66", "#194066",
            "#195366", "#196666", "#196653", "#196640", "#19662d",
            "#196619", "#2d6619", "#406619", "#536619", "#666619",
            "#665319","#664019","#662d19","#661919"
        ]);

        $usedColors = Shift::pluck('color', 'color')->toArray();

        return array_keys(array_diff_key($colors, $usedColors));
    }
}
