<?php

namespace App\Jobs\Hr;

use App\Models\Hr\EmployeeShift;
use App\Models\Hr\Shift;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Carbon\CarbonImmutable;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use stdClass;

class GenerateAttendanceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Name of the temporary table to store intermediate processed data
     *
     * @var string TEMP_TABLE_NAME
     */
    const TEMP_TABLE_NAME = 'temp_attendance_store';

    /**
     * Date format used for storing to database
     *
     * @var string DATE_FORMAT
     */
    const DATE_FORMAT = 'Y-m-d';

    /**
     * Datetime format used for calculations
     *
     * @var string DATETIME_FORMAT
     */
    const DATETIME_FORMAT = 'Y-m-d H:i:s';

    /**
     * Indicates whether the shift spans the midnight
     *
     * @var boolean
     */
    private $shiftSpansMidnight = true;

    /**
     * Determines if the job is already initialized
     *
     * @var boolean
     */
    private $isInitialized = false;

    /**
     * @var string|null
     */
    private $from;

    /**
     * @var string|null
     */
    private $till;

    /**
     * @var array
     */
    private $filters;

    /**
     * Collection of master shift data
     *
     * @var Collection
     */
    private $masterShifts;

    /**
     * The default shift of the company
     *
     * @var int
     */
    private $companyDefaultShiftId;

    /**
     * The interval in minutes in which a punch is considered duplicate
     *
     * @var int
     */
    private $duplicatePunchInterval;

    /**
     * The currently processing employee inside the loop
     *
     * @var std_class
     */
    private $employee;

    /**
     * The default shift of the employee inside the loop
     *
     * @var Shift
     */
    private $defaultShift;
    
    /**
     * All the defined shifts of the employee inside the loop
     *
     * @var EmployeeShift[]|SupportCollection
     */
    private $shifts;

    /**
     * All the punchings of the employee inside the loop
     *
     * @var SupportCollection
     */
    private $punchings;

    /**
     * The tolerance between employee's shift (in hr)
     *
     * This value is used to mark an employee as absent or missing punch.
     * An employee's punchings are considered valid: only if it falls between the defined
     * shift timing +/- the tolerance value
     *
     * @var int
     */
    private $tolerance;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 300;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($from = null, $till = null, $filters = [])
    {
        $this->from = $from ?: ($this->lastRanUntil() ?: '1971-01-01');
        $this->till = $till ?: date(self::DATE_FORMAT);
        $this->filters = $filters;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->initialize();

        $period = CarbonPeriod::since($this->from)->day()->until($this->till);
        $employees = $this->getEmployees($period);

        foreach ($employees as $employee) {
            $this->generate($employee, $period);
        }

        $this->tearDown();
    }

    public function fixDirty()
    {
        $this->initialize();

        foreach ($this->getDirtyRecords() as $records) {
            $employee = (object)Arr::only(reset($records), ['id', 'machine_id', 'default_shift_id']);
            $this->generate($employee, array_column($records, 'date'));
        }

        $this->tearDown();
    }

    /**
     * Generate attendance for the given employee for the given period
     *
     * @param object $employee
     * @param CarbonPeriod|string[] $period
     * @return void
     */
    public function generate($employee, $period)
    {
        $this->punchings = $this->getPunchings($employee->machine_id, $period);
        
        if ($this->punchings->isEmpty()) {
            return;
        }

        $attendances = [];
        $this->employee = $employee;
        $this->shifts = $this->getEmployeeShifts($employee->id, $period);
        $this->defaultShift = $this->masterShifts->get(
            $employee->default_shift_id ?? $this->companyDefaultShiftId
        );

        foreach ($period as $oDate) {
            $punchin = $punchout = $punchin2 = $punchout2 = null;
            $oDate = new CarbonImmutable($oDate);
            $empShift = $this->getEmployeeShift($oDate->toDateString(), true);
            $punches = collect();

            // merge previous date punches
            $previousDate = $oDate->subDay()->toDateString();
            if ($firstBoundaryOfCurrentShift = $this->getPunchBoundaries('first', $empShift)[0] ?? null) {
                if ($previousDate == $firstBoundaryOfCurrentShift->toDateString()) {
                    $punches = $punches->merge($this->punchings->get($previousDate));
                }
            }
            
            // merge current date punches
            $punches = $punches->merge($this->punchings->get($oDate->toDateString()));

            // merge next date punches
            $nextDate = $oDate->addDay()->toDateString();
            if ($lastBoundaryOfCurrentShift = $this->getPunchBoundaries('last', $empShift)[1] ?? null) {
                if ($nextDate == $lastBoundaryOfCurrentShift->toDateString()) {
                    $punches = $punches->merge($this->punchings->get($nextDate));
                }
            }

            // filter out the punches that belongs to previous day's shift
            if (isset(($previousDayShift = $this->getEmployeeShift($previousDate))->shift)) {
                $lastBoundaryOfPreviousShift = $this->getPunchBoundaries('last', $previousDayShift)[1];
                $punches = $punches->filter(function ($punch) use ($lastBoundaryOfPreviousShift) {
                    return Carbon::parse($punch->authdatetime) > $lastBoundaryOfPreviousShift;
                });
            }

            // filter out the punches that belongs to next day's shift
            if (isset(($nextDayShift = $this->getEmployeeShift($nextDate))->shift)) {
                $firstBoundaryOfNextShift = $this->getPunchBoundaries('first', $nextDayShift)[0];
                $punches = $punches->filter(function ($punch) use ($firstBoundaryOfNextShift) {
                    return Carbon::parse($punch->authdatetime) < $firstBoundaryOfNextShift;
                });
            }

            if ($punches->isEmpty()) {
                continue;
            }

            $checkBoundaries = (
                $empShift->is_split_shift
                || $firstBoundaryOfCurrentShift->toDateString() != $empShift->date
                || $lastBoundaryOfCurrentShift->toDateString() != $empShift->date
            );

            $punchin = $this->guessPunchin($punches, $empShift, $checkBoundaries);
            $punchout = $this->guessPunchout($punches, $empShift, $checkBoundaries);
            $duration = $this->calculateDuration($punchin, $punchout);

            if ($punchin && $punchout && $duration == '00:00:00') {
                [$punchin, $punchout] = $this->guessMissingPunch($punchin, $empShift);
            }
            
            $punchin2 = $this->guessPunchin2($punches, $empShift);
            $punchout2 = $this->guessPunchout2($punches, $empShift);
            $duration2 = $this->calculateDuration($punchin2, $punchout2);

            if (!array_filter([$punchin, $punchout, $punchin2, $punchout2])) {
                continue;
            }

            $attendances[] = [
                'machine_id' => $employee->machine_id,
                'date'  => $empShift->date,
                'based_on_shift_id' => $empShift->shift_id,
                'status' => 'P',
                'punchin' => $punchin->authtime ?? null,
                'punchin_stamp' => $punchin->authdatetime ?? null,
                'punchout' => $punchout->authtime ?? null,
                'punchout_stamp' => $punchout->authdatetime ?? null,
                'duration' => $duration,
                'punchin2' => $punchin2->authtime ?? null,
                'punchin2_stamp' => $punchin2->authdatetime ?? null,
                'punchout2' => $punchout2->authtime ?? null,
                'punchout2_stamp' => $punchout2->authdatetime ?? null,
                'duration2' => $duration2
            ];
        }
        $this->persist($attendances, $employee->machine_id, $period);
    }

    /**
     * Apply the date period filter to the given builder
     *
     * @param Builder $builder
     * @param string $column
     * @param CarbonPeriod|string[] $period
     * @param bool $includeAdjacentDates
     * @return Builder
     */
    private function applyDatePeriod($builder, $column, $period, $includeAdjacentDates = true)
    {
        if ($includeAdjacentDates && $period instanceof CarbonPeriod) {
            return $builder->whereBetween($column, [
                $period->start->toImmutable()->subDay()->toDateString(),
                $period->end->toImmutable()->addDay()->toDateString(),
            ]);
        }
        
        if (!$includeAdjacentDates && $period instanceof CarbonPeriod) {
            return $builder->whereBetween($column, [
                $period->start->toDateString(),
                $period->end->toDateString(),
            ]);
        }
        
        $dates = is_array($period) ? $period : [];
        if  ($includeAdjacentDates) {
            foreach ($period as $date) {
                $oDate = new CarbonImmutable($date);
                $dates[] = $oDate->subDay()->toDateString();
                $dates[] = $oDate->addDay()->toDateString();
            }
        }

        return $builder->whereIn($column, array_unique($dates));
    }

    /**
     * Fetch the dirty attendance records from the database
     *
     * @return array[]
     */
    public function getDirtyRecords()
    {
        $builder = DB::query()
            ->select(
                'emp.id',
                'attd.machine_id',
                'job.default_shift_id',
                'attd.date'
            )
            ->from('0_attendance as attd')
            ->join('0_employees as emp', 'emp.machine_id', '=', 'attd.machine_id')
            ->leftJoin('0_emp_jobs AS job', function(JoinClause $join) {
                $join->on('job.employee_id', 'emp.id')
                    ->where('job.is_current', 1);
            })
            ->join('0_emp_shifts as shift', function(JoinClause $join) {
                $join->on('shift.employee_id', '=', 'emp.id')
                    ->whereColumn('attd.date', '=', 'shift.date')
                    ->where(function(Builder $query) {
                        $query->whereNull('attd.based_on_shift_id')
                            ->orWhereRaw('('
                                . "`attd`.`based_on_shift_id` != ifnull(`job`.`default_shift_id`, {$this->companyDefaultShiftId})"
                                . ' and  isnull(`shift`.`shift_id`)'
                            . ')')
                            ->orWhereColumn('attd.based_on_shift_id', '!=', 'shift.shift_id');
                    });
            })
            ->leftJoin('0_payslips as pslip', function(JoinClause $join) {
                $join->on('pslip.employee_id', '=', 'emp.id')
                    ->whereRaw('`attd`.`date` between `pslip`.`from` and `pslip`.`till`')
                    ->where('pslip.is_processed', '1');
            })
            ->whereNull('pslip.id')
            ->whereNull('attd.reviewed_at')
            ->whereBetween('attd.date', [$this->from, $this->till]);

        if (!empty($this->filters['department_id'])) {
            $builder->where('job.department_id', $this->filters['department_id']);
        }

        if (!empty($this->filters['working_company_id'])) {
            $builder->where('job.working_company_id', $this->filters['working_company_id']);
        }

        // group by machine id
        $dirtyRecords = [];
        foreach (getResultAsArray($builder) as $r) {
            $dirtyRecords[$r['machine_id']][] = $r;
        }

        return $dirtyRecords;
    }

    /**
     * Retrieves all the punchings for the specified employee
     *
     * @param string $machineId
     * @param CarbonPeriod|string[] $period
     * @return SupportCollection
     */
    private function getPunchings($machineId, $period)
    {
        $builder = DB::table('0_empl_punchinouts AS punch')
            ->leftJoin('0_empl_punchinouts AS dup', function (JoinClause $join) {
                $join->on('punch.id', '>', 'dup.id')
                    ->whereColumn('punch.empid', '=', 'dup.empid')
                    ->whereColumn('punch.authdate', '=', 'dup.authdate')
                    ->whereRaw("TIMEDIFF(`punch`.`authtime`, `dup`.`authtime`) BETWEEN '00:00:00' AND '00:{$this->duplicatePunchInterval}:00'");
            })
            ->select(['punch.empid', 'punch.authdatetime', 'punch.authdate', 'punch.authtime'])
            ->whereNull('dup.id')
            ->where('punch.empid', $machineId)
            ->orderBy('punch.authdatetime');

        $this->applyDatePeriod($builder, 'punch.authdate', $period);

        $collection = $builder->get()->groupBy('authdate');

        return $collection;
    }

    /**
     * Returns the list of employee_id keyed by their machine_id
     *
     * @param CarbonPeriod|string[] $period
     * @return SupportCollection
     */
    private function getEmployees($period)
    {
        $builder = DB::query()
            ->select(
                'emp.id',
                'punch.empid as machine_id',
                'job.default_shift_id'
            )
            ->from('0_empl_punchinouts AS punch')
            ->leftJoin('0_employees AS emp', 'punch.empid', 'emp.machine_id')
            ->leftJoin('0_emp_jobs AS job', function(JoinClause $join) {
                $join->on('job.employee_id', 'emp.id')
                    ->where('job.is_current', 1);
            })
            ->groupBy('punch.empid');

        $this->applyDatePeriod($builder, 'punch.authdate', $period);

        if (!empty($this->filters['department_id'])) {
            $builder->where('job.department_id', $this->filters['department_id']);
        }

        if (!empty($this->filters['working_company_id'])) {
            $builder->where('job.working_company_id', $this->filters['working_company_id']);
        }

        return $builder->get()->keyBy('machine_id');
    }

    /**
     * Returns all the shifts that are assigned to the specified employee
     *
     * @param null|int $employeeId
     * @param CarbonPeriod|string[] $period
     * @return SupportCollection
     */
    private function getEmployeeShifts($employeeId, $period)
    {
        if ($employeeId == null) {
            return collect();
        }

        $builder = EmployeeShift::where('employee_id', $employeeId);
        $this->applyDatePeriod($builder, 'date', $period);
        return $builder->get()->keyBy('date');
    }

    /**
     * Guess the employee's first punchin from the day's punches
     *
     * @param SupportCollection $punches
     * @param EmployeeShift $empShift
     * @param boolean $checkBoundaries
     * @return stdClass|null
     */
    private function guessPunchin($punches, $empShift, $checkBoundaries = true)
    {
        if (!$checkBoundaries) {
            return $punches->first();
        }

        return $this->firstPunchBetweenBoundaries(
            $punches,
            ...$this->getPunchBoundaries('first_punchin', $empShift)
        );
    }

    /**
     * Guess the employee's first punchout from the day's punches
     *
     * @param SupportCollection $punches
     * @param EmployeeShift $empShift
     * @param boolean $checkBoundaries
     * @return stdClass|null
     */
    private function guessPunchout($punches, $empShift, $checkBoundaries = true)
    {
        $punches = $punches->reverse();

        if (!$checkBoundaries) {
            return $punches->first();
        }

        return $this->firstPunchBetweenBoundaries(
            $punches,
            ...$this->getPunchBoundaries('first_punchout', $empShift)
        );
    }

    /**
     * Guess the employee's second punchin from the day's punches
     *
     * @param SupportCollection $punches
     * @param EmployeeShift $empShift
     * @return stdClass|null
     */
    private function guessPunchin2($punches, $empShift)
    {
        if (!$empShift->is_split_shift) {
            return null;
        }

        return $this->firstPunchBetweenBoundaries(
            $punches,
            ...$this->getPunchBoundaries('second_punchin', $empShift)
        );
    }

    /**
     * Guess the employee's second punchout from the day's punches
     *
     * @param SupportCollection $punches
     * @param EmployeeShift $empShift
     * @return stdClass|null
     */
    private function guessPunchout2($punches, $empShift)
    {
        if (!$empShift->is_split_shift) {
            return null;
        }

        return $this->firstPunchBetweenBoundaries(
            $punches->reverse(),
            ...$this->getPunchBoundaries('second_punchout', $empShift)
        );
    }
    
    /**
     * Guess the employee's missing punch
     *
     * @param stdClass $punch
     * @param EmployeeShift $empShift
     * @return string|null
     */
    private function guessMissingPunch($punch, $empShift)
    {
        $punch_ = CarbonImmutable::parse($punch->authtime);
        $shiftFrom = CarbonImmutable::parse($empShift->shift->from);
        $shiftTill = CarbonImmutable::parse($empShift->shift->till);

        $setTo = $shiftFrom->diffInSeconds($punch_) < $shiftTill->diffInSeconds($punch_) ? 'punchin' : 'punchout';
        $unsetFrom = $setTo == 'punchin' ? 'punchout' : 'punchin';

        $$setTo = $punch;
        $$unsetFrom = null;

        return [$punchin, $punchout];
    }

    /**
     * Get the boundaries for the punch
     *
     * @param "first_punchin"|"first_punchout"|"second_punchin"|"second_punchout"|"first"|"last" $type
     * @param EmployeeShift $empShift
     * @return null|CarbonImmutable[]
     */
    private function getPunchBoundaries($type, $empShift)
    {
        if (!$empShift->shift) {
            return null;
        }

        $toleranceOne = $this->tolerance;
        $toleranceTwo = $empShift->is_split_shift
            ? ($empShift->ends_at->diffInMinutes($empShift->starts_at2) / 2)
            : $this->tolerance;
        
        $halfOfFirstShift = ($empShift->ends_at->diffInMinutes($empShift->starts_at) / 2);
        $halfOfSecondShift = $empShift->is_split_shift
            ? ($empShift->ends_at2->diffInMinutes($empShift->starts_at2) / 2)
            : null;

        if ($type == "last") {
            $type = $empShift->is_split_shift ? "second_punchout" : "first_punchout";
        }

        switch ($type) {
            case "first":
            case "first_punchin":
                return [
                    $empShift->starts_at->subMinute($toleranceOne),
                    $empShift->starts_at->addMinutes($halfOfFirstShift)
                ];

            case "first_punchout":
                return [
                    $empShift->ends_at->subMinute($halfOfFirstShift),
                    $empShift->ends_at->addMinutes($toleranceTwo)
                ];

            case "second_punchin":
                return !$empShift->is_split_shift
                    ? null
                    : [
                        $empShift->starts_at2->subMinute($toleranceTwo),
                        $empShift->starts_at2->addMinutes($halfOfSecondShift)
                    ];

            case "second_punchout":
                return !$empShift->is_split_shift
                    ? null
                    : [
                        $empShift->ends_at2->subMinute($halfOfSecondShift),
                        $empShift->ends_at2->addMinutes($toleranceOne)
                    ];
        }
    }

    /**
     * Returns the first punch that is closest to the base time with regards to the tolerance
     *
     * @param SupportCollection $punches
     * @param CarbonImmutable $basis
     * @return stdClass|null
     */
    private function firstPunchBetweenBoundaries($punches, $floor, $ceil) {
        foreach ($punches as $punch) {
            $punchTime = new CarbonImmutable($punch->authdatetime);

            if ($punchTime->isBetween($floor, $ceil)) {
                return $punch;
            }
        }

        return null;
    }

    /**
     * Calculates the work duration between the punchin and punchout
     *
     * @param stdClass|null $punchin
     * @param stdClass|null $punchout
     * @return string
     */
    private function calculateDuration($punchin, $punchout)
    {
        if (!$punchin || !$punchout) {
            return '00:00:00';
        }

        $punchin = new CarbonImmutable($punchin->authtime);
        $punchout = new CarbonImmutable($punchout->authtime);

        // If the punchout time is less than punchin time it means the shift
        // overlaps with the next day so add one day
        if ($punchout->isBefore($punchin)) {
            $punchout = $punchout->addDay();
        }

        $duration = $punchin->diff($punchout);

        return $duration->format('%H:%I:%S');
    }

    /**
     * Get the employee's shift instance
     *
     * @param string $date
     * @param boolean $replaceOff
     * @return EmployeeShift
     */
    private function getEmployeeShift($date, $replaceOff = false)
    {
        $empShift = $this->shifts[$date] ?? null;

        if (
            empty($empShift)
            || (
                $empShift->id
                && empty($empShift->shift_id)
                && $replaceOff
            )
        ) {
            $empShift = EmployeeShift::make([
                'employee_id' => $this->employee->id ?? null,
                'date' => $date,
                'shift_id' => $this->defaultShift->id
            ]);
        }

        $empShift->setRelation(
            'shift',
            $empShift->shift_id
                ? $this->masterShifts->get($empShift->shift_id)
                : null
        );

        return $empShift;
    }

    /**
     * Update or insert the attendances in database
     *
     * @param array $attendances
     * @param string $machineId The employees ID in the attendance machine
     * @param CarbonPeriod|string[] $period
     * @return void
     */
    private function persist($attendances = [], $machineId, $period)
    {
        if (empty($attendances)) {
            return;
        }

        $this->clearTemporaryTable();

        // Insert data into temporary processing table
        DB::table(self::TEMP_TABLE_NAME)->insert($attendances);

        // Update if exists
        $this->updateIfExists();

        // Delete if not in this generation of attendances
        $this->deleteExcess($machineId, $period);

        // Insert if not already exists
        $this->insertIfNotExists();
    }

    /**
     * Creates a temporary table to store the processed information
     *
     * @return void
     */
    private function createTemporaryTable()
    {
        if (Schema::hasTable(self::TEMP_TABLE_NAME)) return;

        Schema::create(self::TEMP_TABLE_NAME, function(Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8';
            $table->collation = 'utf8_unicode_ci';
            $table->temporary();
            $table->string('machine_id');
            $table->date('date');
            $table->smallInteger('based_on_shift_id')->nullable();
            $table->char('status', 1);
            $table->time('punchin')->nullable();
            $table->dateTime('punchin_stamp')->nullable();
            $table->time('punchout')->nullable();
            $table->dateTime('punchout_stamp')->nullable();
            $table->time('duration')->nullable();
            $table->time('punchin2')->nullable();
            $table->dateTime('punchin2_stamp')->nullable();
            $table->time('punchout2')->nullable();
            $table->dateTime('punchout2_stamp')->nullable();
            $table->time('duration2')->nullable();
            $table->primary(['machine_id', 'date']);
        });
    }

    /**
     * Clear the contents of the temporary table
     *
     * @return void
     */
    private function clearTemporaryTable()
    {
        DB::table(self::TEMP_TABLE_NAME)->truncate();
    }

    /**
     * Drop the temporary table
     *
     * @return void
     */
    private function dropTemporaryTable()
    {
        Schema::dropIfExists(self::TEMP_TABLE_NAME);
    }

    /**
     * Initialize this job
     *
     * @return void
     */
    private function initialize()
    {
        if ($this->isInitialized) return;

        // Preload the master shifts because its only less than a 100 shifts,
        // it won't take up that much memory
        $this->masterShifts = Shift::all()->keyBy('id');

        // Create a temporary table to hold the intermediate attendance data.
        $this->createTemporaryTable();
        
        $this->tolerance = pref('hr.shift_tolerance', 1) * 60;
        $this->shiftSpansMidnight = boolval(pref('hr.shift_spans_midnight'));
        $this->companyDefaultShiftId = pref('hr.default_shift_id');
        $this->duplicatePunchInterval = str_pad(pref('hr.duplicate_punch_interval', 5), 2, "0", STR_PAD_LEFT);

        $this->isInitialized = true;
    }

    /**
     * Teardown this job
     *
     * @return void
     */
    private function tearDown()
    {
        $this->dropTemporaryTable();
        $this->isInitialized = false;
    }

    /**
     * Update the attendance if it already exists
     *
     * @return void
     */
    private function updateIfExists()
    {
        $currDateTime = date(self::DATETIME_FORMAT);

        $columns = [
            'based_on_shift_id',
            'punchin',
            'punchin_stamp',
            'punchout',
            'punchout_stamp',
            'duration',
            'punchin2',
            'punchin2_stamp',
            'punchout2',
            'punchout2_stamp',
            'duration2'
        ];
        $builder = DB::table('0_attendance as attd')
            ->leftJoin('0_employees as emp', 'emp.machine_id', '=', 'attd.machine_id')
            ->join(self::TEMP_TABLE_NAME . ' as temp', function(JoinClause $join) {
                $join->on('attd.machine_id', '=', 'temp.machine_id')
                    ->on('attd.date', '=', 'temp.date');
            })
            ->leftJoin('0_payslips as pslip', function(JoinClause $join) {
                $join->on('pslip.employee_id', '=', 'emp.id')
                    ->whereRaw('`attd`.`date` between `pslip`.`from` and `pslip`.`till`')
                    ->where('pslip.is_processed', '1');
            })
            ->whereNull('pslip.id')
            ->whereNull('attd.reviewed_at')
            ->where(function(Builder $query) use ($columns) {
                foreach ($columns as $column) {
                    $query->orWhereRaw("`attd`.`$column` != `temp`.`$column`")
                        ->orWhereRaw("(isnull(`attd`.`$column`) and !isnull(`temp`.`$column`))")
                        ->orWhereRaw("(!isnull(`attd`.`$column`) and isnull(`temp`.`$column`))");
                }
            });

        $updates = [];
        foreach ($columns as $column) {
            $updates["attd.$column"] = DB::raw("`temp`.`$column`");
        }
        $updates['attd.updated_at'] = DB::raw("'{$currDateTime}'");
        $builder->update($updates);
    }

    /**
     * Delete the attendances if it is not in this generation
     *
     * @param string $machineId The employee's ID in the attendance machine
     * @param CarbonPeriod|string[] $period
     * @return void
     */
    private function deleteExcess($machineId, $period)
    {
        if (empty($machineId)) return;

        $builder = DB::table('0_attendance as attd')
            ->leftJoin('0_employees as emp', 'emp.machine_id', '=', 'attd.machine_id')
            ->leftJoin(self::TEMP_TABLE_NAME . ' as temp', function(JoinClause $join) {
                $join->on('attd.machine_id', '=', 'temp.machine_id')
                    ->on('attd.date', '=', 'temp.date');
            })
            ->leftJoin('0_payslips as pslip', function(JoinClause $join) {
                $join->on('pslip.employee_id', '=', 'emp.id')
                    ->whereRaw('`attd`.`date` between `pslip`.`from` and `pslip`.`till`')
                    ->where('pslip.is_processed', '1');
            })
            ->select(['attd.id'])
            ->whereNull('pslip.id')
            ->whereNull('temp.machine_id')
            ->whereNull('attd.reviewed_at')
            ->where('attd.machine_id', '=', $machineId);

        $this->applyDatePeriod($builder, 'attd.date', $period, false);

        $idsToDelete = $builder->get()->pluck('id')->toArray();

        if (!empty($idsToDelete)) {
            DB::table('0_attendance')->whereIn('id', $idsToDelete)->delete();
        }
    }

    private function insertIfNotExists()
    {
        $currDateTime = date(self::DATETIME_FORMAT);

        $selects = DB::table(self::TEMP_TABLE_NAME . ' as temp')
            ->leftJoin('0_employees as emp', 'emp.machine_id', '=', 'temp.machine_id')
            ->leftJoin('0_attendance as attd', function(JoinClause $join) {
                $join->on('attd.machine_id', '=', 'temp.machine_id')
                    ->on('attd.date', '=', 'temp.date');
            })
            ->leftJoin('0_payslips as pslip', function(JoinClause $join) {
                $join->on('pslip.employee_id', '=', 'emp.id')
                    ->whereRaw('`temp`.`date` between `pslip`.`from` and `pslip`.`till`')
                    ->where('pslip.is_processed', '1');
            })
            ->select([
                'temp.machine_id',
                'temp.date',
                'temp.based_on_shift_id',
                'temp.status',
                'temp.punchin',
                'temp.punchin_stamp',
                'temp.punchout',
                'temp.punchout_stamp',
                'temp.duration',
                'temp.punchin2',
                'temp.punchin2_stamp',
                'temp.punchout2',
                'temp.punchout2_stamp',
                'temp.duration2',
                DB::raw("'{$currDateTime}' AS created_at")
            ])
            ->whereNull('pslip.id')
            ->whereNull('attd.id');

        DB::table('0_attendance')->insertUsing(
            [
                'machine_id', 
                'date', 
                'based_on_shift_id', 
                'status', 
                'punchin', 
                'punchin_stamp', 
                'punchout', 
                'punchout_stamp', 
                'duration', 
                'punchin2', 
                'punchin2_stamp', 
                'punchout2', 
                'punchout2_stamp', 
                'duration2', 
                'created_at'
            ],
            $selects
        );
    }

    private function lastRanUntil()
    {
        return DB::table('0_attendance')
            ->select(['date'])
            ->whereNull('reviewed_at')
            ->orderBy('date', 'desc')
            ->take(1)
            ->value('date');
    }
}
