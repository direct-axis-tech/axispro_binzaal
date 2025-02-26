<?php
namespace App\Http\Controllers\Hr;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Bank;
use App\Models\Country;
use App\Models\Hr\Company;
use App\Models\Hr\Department;
use App\Models\Hr\Designation;
use App\Models\Hr\Employee;
use App\Models\Hr\EmployeeJob;
use App\Models\Hr\EmployeeSalary;
use App\Models\Hr\PayElement;
use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\View as ViewFacade;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class BulkEmployeeUploadController extends Controller
{
    /**
     * Constructor function for this controller
     * 
     * Shares the common variable with the views
     */
    public function __construct()
    {
        ViewFacade::share('dateFormats', $this->getAvailableDateFormats());
    }

    /**
     * Show the file upload form
     *
     * @return View
     */
    public function index()
    {
        return view('hr.bulkEmployeeUpload');
    }

    public function store(Request $request)
    {
        // Fetch dateFormats and pass them to the view
        $dateFormats = $this->getAvailableDateFormats();

        // Validation rules for date_format
        $request->validate([
            'excel_file' => 'required|file|mimes:txt,csv,xlsx,xls',
            'date_format' => 'required|in:' . implode(',', array_keys($dateFormats))
        ]);

        // Retrieve the selected date format
        $dateFormat = $request->input('date_format');

        // Process the raw sheet
        $data = Excel::toArray([], $request->file('excel_file'))[0];
        if (empty($data)) {
            return back()->with('dataErrors', ["Could not find any data to process"]);
        }

        // Get the payElements and append a code friendly key to them
        $payElements = DB::table('0_pay_elements')->where('is_fixed', true)->get()->keyBy('name')->toArray();
        array_walk($payElements, function (&$v) {
            $v->key = Str::slug($v->name, '_');
        });

        // Define a mapping of Excel headers to database columns
        $headerMapping = array_merge(
            [
                'ID' => 'emp_ref',
                'Attendance Machine ID' => 'machine_id',
                'Full Name (Arb)' => 'ar_name',
                'Full Name (Eng)' => 'name',
                'Preferred Name (Short Name)' => 'preferred_name',
                'Department' => 'department_name',
                'Designation' => 'designation_name',
                'Working Company' => 'working_company_name',
                'Visa Company' => 'visa_company_name',
                'Nationality' => 'country_name',
                'Gender' => 'gender',
                'DOB' => 'date_of_birth',
                'Marital Status' => 'marital_status',
                'Email' => 'email',
                'Mobile No.' => 'mobile_no',
                'Date of join' => 'date_of_join',
                'Payment Mode' => 'mode_of_pay',
                'Bank Name' => 'bank_name',
                'IBAN No.' => 'iban_no',
                'Personal ID No.' => 'personal_id_no',
                'File No.' => 'file_no',
                'UID No.' => 'uid_no',
                'Passport No.' => 'passport_no',
                'Labour Card No.' => 'labour_card_no',
                'Emirates ID' => 'emirates_id',
                'Week Offs' => 'week_offs',
                'Work Hours' => 'work_hours',
                'Has Commission' => 'has_commission',
                'Has Pension' => 'has_pension',
                'Has Overtime' => 'has_overtime',
                'Commence From' => 'commence_from',
                'Require Attendance' => 'require_attendance',
                'Monthly Salary' => 'gross_salary',
            ],
            array_column($payElements, 'key', 'name')
        );
            

        // Validates if all the expected headers are present in the uploaded file
        $headerRow = array_filter(array_shift($data));
        $expectedHeaders = array_keys($headerMapping);
        if (!empty($missingHeaders = array_diff($expectedHeaders, $headerRow))) {
            array_walk($missingHeaders, function (&$v, $k) {
                $v = "\"{$v}\": Expected at cell \"" . Coordinate::stringFromColumnIndex($k + 1) . '1"';
            });
            $msgs = [
                "Missing Columns:\n    " . implode("\n    ", $missingHeaders)
            ];

            if (!empty($extraHeaders = array_diff($headerRow, $expectedHeaders))) {
                array_walk($extraHeaders, function (&$v, $k) {
                    $v = "\"{$v}\": Found at cell \"" . Coordinate::stringFromColumnIndex($k + 1) . '1"';
                });
                $msgs[] = "Unrecognized Columns:\n    " . implode("\n    ", $extraHeaders);
            }

            return back()->with('dataErrors', $msgs);
        }

        // Map the data to an associative array
        $flippedHeaderRow = array_flip($headerRow);
        array_walk($data, function (&$row, $index) use ($headerMapping, $expectedHeaders, $flippedHeaderRow) {
            $mappedRow = [];
            foreach ($expectedHeaders as $h) {
                $value = trim($row[$flippedHeaderRow[$h]]);
                $mappedRow[$headerMapping[$h]] = $value === '' ? null : $value;
            }
            $row = $mappedRow;
        });

        // Prepare inputs
        array_walk($data, function (&$row) {
            $row['week_offs'] = array_values(
                array_unique(
                    array_filter(
                        array_map('trim', explode(',', $row['week_offs']))
                    )
                )
            );
        });

        // Validate the inputs
        $validationResult = $this->validateInputs($data, $dateFormat, $payElements, $headerMapping, $flippedHeaderRow);
        if (!empty($validationResult['errors'])) {
            return back()->with('dataErrors', $validationResult['errors']);
        }

        // Store the data
        $this->storeData($data, $dateFormat, $payElements);

        // Redirect with success message
        return redirect()->route('bulkEmployeeUpload.index')->with('success', 'Excel file uploaded successfully');
    }

    private function validateInputs($data, $dateFormat, $payElements, $headerMappings, $flippedHeaderRow)
    {
        $displayNames = array_flip($headerMappings);
        $exampleDate = Carbon::parse('1975-12-30')->format($dateFormat);
        $dec = user_price_dec();
        $errors = [];

        // Pay elements validations are dynamic, So handle them dynamically
        $payElementsValidations = [];
        foreach ($payElements as $payElement) {
            $payElementsValidations[] = [
                'key' => "data.*.{$payElement->key}",
                'rules' => 'required|numeric',
                'messages' => [
                    'required' => ":attribute: {$displayNames[$payElement->key]} is required, make it 0 if not applicable",
                    'numeric' => ":attribute: {$displayNames[$payElement->key]} is expected to be numeric, make it 0 if not applicable",
                ],
            ];
        }

        // Check for normal errors
        $validator = Validator::make(
            compact('data'),
            array_merge(
                [
                    'data.*.emp_ref' => 'required|regex:/^[0-9a-zA-Z][0-9a-zA-Z]*$/|unique:0_employees,emp_ref|distinct:ignore_case',
                    'data.*.machine_id' => 'required|regex:/^[0-9a-zA-Z][0-9a-zA-Z]*$/|unique:0_employees,machine_id|distinct:ignore_case',
                    'data.*.ar_name' => 'nullable|string|max:100',
                    'data.*.name' => ['required', 'min:3', 'regex:/^[a-zA-Z][a-zA-Z ]+$/', 'max:100'],
                    'data.*.preferred_name' => ['nullable', 'regex:/^[a-zA-Z][a-zA-Z ]+$/', 'max:60'],
                    'data.*.department_name' => 'required|exists:0_departments,name',
                    'data.*.designation_name' => 'required|exists:0_designations,name',
                    'data.*.working_company_name' => 'required|exists:0_companies,name',
                    'data.*.visa_company_name' => ['required', Rule::exists('0_companies', 'name')->whereNotNull('mol_id')],
                    'data.*.country_name' => 'required|exists:0_countries,name',
                    'data.*.gender' => 'required|in:Male,Female,Not Specified',
                    'data.*.date_of_birth' => 'required|date_format:'.$dateFormat,
                    'data.*.marital_status' => 'nullable|in:Rather Not Say,'.implode(',', marital_statuses()),
                    'data.*.email' => 'required|email|unique:0_employees,email|distinct:ignore_case',
                    'data.*.mobile_no' => ['required', 'regex:/^(5[024568]|[1234679])\d{7}$/', 'unique:0_employees,mobile_no', 'distinct:ignore_case'],
                    'data.*.date_of_join' => 'required|date_format:'.$dateFormat,
                    'data.*.mode_of_pay' => 'required|in:Bank,Cash',
                    'data.*.bank_name' => 'required_if:data.*.mode_of_pay,Bank|nullable|exists:0_banks,name',
                    'data.*.iban_no' => ['required_if:data.*.mode_of_pay,Bank', 'nullable', 'regex:/^(AE\d{21}|\d{23})$/', 'unique:0_employees,iban_no', 'distinct:ignore_case'],
                    'data.*.personal_id_no' => ['required_if:data.*.mode_of_pay,Bank', 'nullable', 'regex:/^\d{14}$/', 'unique:0_employees,personal_id_no', 'distinct:ignore_case'],
                    'data.*.file_no' => ['nullable', 'regex:_^[1-7]01/\d{4}/\d/?\d+$_', 'unique:0_employees,file_no', 'distinct:ignore_case'],
                    'data.*.uid_no' => 'nullable|regex:/^\d+$/|distinct:ignore_case|unique:0_employees,uid_no',
                    'data.*.passport_no' => 'nullable|alpha_num|distinct:ignore_case|unique:0_employees,passport_no',
                    'data.*.labour_card_no' => 'nullable|regex:/^\d+$/|distinct:ignore_case|unique:0_employees,labour_card_no',
                    'data.*.emirates_id' => 'nullable|regex:/^784-\d{4}-\d{7}-\d$/|unique:0_employees,emirates_id|distinct:ignore_case',
                    'data.*.week_offs' => 'nullable|array',
                    'data.*.week_offs.*' => 'nullable|in:Mon,Tue,Wed,Thu,Fri,Sat,Sun',
                    'data.*.work_hours' => 'nullable|numeric',
                    'data.*.has_commission' => 'required|in:Yes,No',
                    'data.*.has_pension' => 'required|in:Yes,No',
                    'data.*.has_overtime' => 'required|in:Yes,No',
                    'data.*.commence_from' => 'nullable|date_format:'.$dateFormat,
                    'data.*.require_attendance' => 'required|in:Yes,No',
                    'data.*.gross_salary' => 'required|numeric|min:1',
                ],
                array_column($payElementsValidations, 'rules', 'key')
            ),
            Arr::dot(array_merge(
                [
                    'data.*.emp_ref' => [
                        'required' => ":attribute: {$displayNames['emp_ref']} is required",
                        'regex' => ":attribute: {$displayNames['emp_ref']} must only consists of alphabets and numbers",
                        'unique' => ":attribute: An employee with the same {$displayNames['emp_ref']} :input already exists in the database",
                        'distinct' => ":attribute: Multiple employees with the same {$displayNames['emp_ref']} :input found in this sheet"
                    ],
                    'data.*.machine_id' => [
                        'required' => ":attribute: {$displayNames['machine_id']} is required",
                        'regex' => ":attribute: {$displayNames['machine_id']} must only consists of alphabets and numbers",
                        'unique' => ":attribute: An employee with the same {$displayNames['machine_id']} :input already exists in the database",
                        'distinct' => ":attribute: Multiple employees with the same {$displayNames['machine_id']} :input found in this sheet"
                    ],
                    'data.*.ar_name' => [
                        'string' => ":attribute: {$displayNames['ar_name']} expected to be a string",
                        'max' => ":attribute: {$displayNames['ar_name']} is too long for the database. Limit to :max characters",
                    ],
                    'data.*.name' => [
                        'required' => ":attribute: {$displayNames['name']} is required",
                        'min' => ":attribute: {$displayNames['name']} is too short.",
                        'max' => ":attribute: {$displayNames['name']} is too long for the database. Limit to :max characters",
                        'regex' => ":attribute: {$displayNames['name']} must only consists of alphabets and space"
                    ],
                    'data.*.preferred_name' => [
                        'required' => ":attribute: {$displayNames['preferred_name']} is required",
                        'max' => ":attribute: {$displayNames['preferred_name']} is too long for the database. Limit to :max characters",
                        'regex' => ":attribute: {$displayNames['preferred_name']} must only consists of alphabets and space"
                    ],
                    'data.*.department_name' => [
                        'required' => ":attribute: {$displayNames['department_name']} is required",
                        'exists' => ":attribute: {$displayNames['department_name']} :input could not be found in database",
                    ],
                    'data.*.designation_name' => [
                        'required' => ":attribute: {$displayNames['designation_name']} is required",
                        'exists' => ":attribute: {$displayNames['designation_name']} :input could not be found in database",
                    ],
                    'data.*.working_company_name' => [
                        'required' => ":attribute: {$displayNames['working_company_name']} is required",
                        'exists' => ":attribute: {$displayNames['working_company_name']} :input could not be found in database",
                    ],
                    'data.*.visa_company_name' => [
                        'required' => ":attribute: {$displayNames['visa_company_name']} is required",
                        'exists' => ":attribute: {$displayNames['visa_company_name']} :input with a non empty MOL ID could not be found in database",
                    ],
                    'data.*.country_name' => [
                        'required' => ":attribute: {$displayNames['country_name']} is required",
                        'exists' => ":attribute: {$displayNames['country_name']} :input could not be found in database",
                    ],
                    'data.*.gender' => [
                        'required' => ":attribute: {$displayNames['gender']} is required",
                        'in' => ":attribute: {$displayNames['gender']} :input is not among :values",
                    ],
                    'data.*.date_of_birth' => [
                        'required' => ":attribute: {$displayNames['date_of_birth']} is required",
                        'date_format' => ":attribute: {$displayNames['date_of_birth']} is not confirming to the date format '{$exampleDate}'",
                    ],
                    'data.*.marital_status' => [
                        'in' => ":attribute: {$displayNames['marital_status']} :input is not among :values",
                    ],
                    'data.*.email' => [
                        'required' => ":attribute: {$displayNames['email']} is required",
                        'email' => ":attribute: {$displayNames['email']} must be a valid email address",
                        'unique' => ":attribute: An employee with the same {$displayNames['email']} :input already exists in database",
                        'distinct' => ":attribute: Multiple employee with same {$displayNames['email']} :input found in this sheet"
                    ],
                    'data.*.mobile_no' => [
                        'required' => ":attribute: {$displayNames['mobile_no']} is required",
                        'regex' => ":attribute: {$displayNames['mobile_no']} must be a UAE mobile number without code. (eg. 58XXXXXX9)",
                        'unique' => ":attribute: An employee with the same {$displayNames['mobile_no']} :input already exists in database",
                        'distinct' => ":attribute: Multiple employee with same {$displayNames['mobile_no']} :input found in this sheet"
                    ],
                    'data.*.date_of_join' => [
                        'required' => ":attribute: {$displayNames['date_of_join']} is required",
                        'date_format' => ":attribute: {$displayNames['date_of_join']} :input is not confirming to the date format '{$exampleDate}'",
                    ],
                    'data.*.mode_of_pay' => [
                        'required' => ":attribute: {$displayNames['mode_of_pay']} is required",
                        'in' => ":attribute: {$displayNames['mode_of_pay']} :input is not among :values",
                    ],
                    'data.*.bank_name' => [
                        'required_if' => ":attribute: {$displayNames['bank_name']} is required when {$displayNames['mode_of_pay']} is :value",
                        'exists' => ":attribute: {$displayNames['bank_name']} :input could not be found in database",
                    ],
                    'data.*.iban_no' => [
                        'required_if' => ":attribute: {$displayNames['iban_no']} is required when {$displayNames['mode_of_pay']} is :value",
                        'regex' => ":attribute: {$displayNames['iban_no']} does not seems valid",
                        'unique' => ":attribute: An employee with the same {$displayNames['iban_no']} :input already exists in the database",
                        'distinct' => ":attribute: Multiple {$displayNames['iban_no']} :input found with the same value in this sheet"
                    ],
                    'data.*.personal_id_no' => [
                        'required_if' => ":attribute: {$displayNames['personal_id_no']} is required when {$displayNames['mode_of_pay']} is :value",
                        'regex' => ":attribute: {$displayNames['personal_id_no']} does not seems valid",
                        'unique' => ":attribute: An employee with the same {$displayNames['personal_id_no']} :input already exists in the database",
                        'distinct' => ":attribute: Multiple {$displayNames['personal_id_no']} :input found with the same value in this sheet"
                    ],
                    'data.*.file_no' => [
                        'regex' => ":attribute: {$displayNames['file_no']} does not seems valid",
                        'unique' => ":attribute: An employee with the same {$displayNames['file_no']} :input already exists in the database",
                        'distinct' => ":attribute: Multiple {$displayNames['file_no']} :input found with the same value in this sheet"
                    ],
                    'data.*.uid_no' => [
                        'regex' => ":attribute: {$displayNames['uid_no']} does not seems valid",
                        'unique' => ":attribute: An employee with the same {$displayNames['uid_no']} :input already exists in the database",
                        'distinct' => ":attribute: Multiple {$displayNames['uid_no']} :input found with the same value in this sheet"
                    ],
                    'data.*.passport_no' => [
                        'alpha_num' => ":attribute: {$displayNames['passport_no']} does not seems valid",
                        'unique' => ":attribute: An employee with the same {$displayNames['passport_no']} :input already exists in the database",
                        'distinct' => ":attribute: Multiple {$displayNames['passport_no']} :input found with the same value in this sheet"
                    ],
                    'data.*.labour_card_no' => [
                        'regex' => ":attribute: {$displayNames['labour_card_no']} does not seems valid",
                        'unique' => ":attribute: An employee with the same {$displayNames['labour_card_no']} :input already exists in the database",
                        'distinct' => ":attribute: Multiple {$displayNames['labour_card_no']} :input found with the same value in this sheet"
                    ],
                    'data.*.emirates_id' => [
                        'regex' => ":attribute: {$displayNames['emirates_id']} does not seems valid",
                        'unique' => ":attribute: An employee with the same {$displayNames['emirates_id']} :input already exists in the database",
                        'distinct' => ":attribute: Multiple {$displayNames['emirates_id']} :input found with the same value in this sheet"
                    ],
                    'data.*.week_offs' => [
                        'required' => ":attribute: {$displayNames['week_offs']} is required",
                        '*.required' => ":attribute: {$displayNames['week_offs']} is required",
                        '*.in' => ":attribute: {$displayNames['week_offs']} :input is not among :values",
                    ],
                    'data.*.work_hours' => [
                        'required' => ":attribute: {$displayNames['work_hours']} is required",
                        'numeric' => ":attribute: {$displayNames['work_hours']} is expected to be numeric",
                    ],
                    'data.*.has_commission' => [
                        'required' => ":attribute: {$displayNames['has_commission']} is required",
                        'in' => ":attribute: {$displayNames['has_commission']} must be one of :values",
                    ],
                    'data.*.has_pension' => [
                        'required' => ":attribute: {$displayNames['has_pension']} is required",
                        'in' => ":attribute: {$displayNames['has_pension']} must be one of :values",
                    ],
                    'data.*.has_overtime' => [
                        'required' => ":attribute: {$displayNames['has_overtime']} is required",
                        'in' => ":attribute: {$displayNames['has_overtime']} must be one of :values",
                    ],
                    'data.*.require_attendance' => [
                        'required' => ":attribute: {$displayNames['require_attendance']} is required",
                        'in' => ":attribute: {$displayNames['require_attendance']} must be one of :values",
                    ],
                    'data.*.commence_from' => [
                        'required' => ":attribute: {$displayNames['commence_from']} is required",
                        'date_format' => ":attribute: {$displayNames['commence_from']} :input is not confirming to the date format '{$exampleDate}'",
                    ],
                    'data.*.gross_salary' => [
                        'required' => ":attribute: {$displayNames['gross_salary']} is required",
                        'min' => ":attribute: Nobody should work without a salary",
                        'numeric' => ":attribute: {$displayNames['gross_salary']} is expected to be numeric",
                    ],
                ],
                array_column($payElementsValidations, 'messages', 'key')
            ))
        );

        // Return if there are errors
        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                [$key, $message] = explode(': ', $error);
                $rowIndex = 2 + (int)preg_replace('/data\.([0-9]+)\..*/', "$1", $key);
                $column = preg_replace('/data\.[0-9]+\.(\w+).*/', "$1", $key);
                $cell = Coordinate::stringFromColumnIndex($flippedHeaderRow[$displayNames[$column]] + 1) . '' . $rowIndex;
                $errors[] = "At {$cell}: \t{$message}"; 
            }
            return compact('errors');
        }

        // Check for logical errors
        foreach ($data as $rowIndex => $row) {
            $rowIndex += 2;
            $payElementKeys = array_column($payElements, 'key');

            if (round2(array_sum(Arr::only($row, $payElementKeys)), $dec) != round2($row['gross_salary'], $dec)) {
                $errors[] = "At Row {$rowIndex}: The total of" . implode(', ', Arr::only($displayNames, $payElementKeys)) . " does not add up to the {$displayNames['gross_salary']}";
            }
        }

        return compact('errors');
    }


    private function storeData($data, $dateFormat, $payElements) {
        DB::transaction(function () use ($data, $dateFormat, $payElements) {
            $departments = Department::select('id')
                ->selectRaw('lower(`name`) as name')
                ->get()
                ->pluck('id', 'name')
                ->toArray();
            $designations = Designation::select('id')
                ->selectRaw('lower(`name`) as name')
                ->get()
                ->pluck('id', 'name')
                ->toArray();
            $companies = Company::select('id')
                ->selectRaw('lower(`name`) as name')
                ->get()
                ->pluck('id', 'name')
                ->toArray();
            $countries = Country::select('code')
                ->selectRaw('lower(`name`) as name')
                ->get()
                ->pluck('code', 'name')
                ->toArray();
            $banks = Bank::select('id')
                ->selectRaw('lower(`name`) as name')
                ->get()
                ->pluck('id', 'name')
                ->toArray();
            $genders = array_flip(genders());
            $maritalStatuses = array_flip(marital_statuses());

            // First save the employees and map their ids
            foreach($data as $i => $row) {
                $data[$i]['employee'] = Employee::create(array_merge(
                    Arr::only($row, [
                        'emp_ref',
                        'machine_id',
                        'ar_name',
                        'name',
                        'preferred_name',
                        'email',
                        'mobile_no',
                        'iban_no',
                        'personal_id_no',
                        'file_no',
                        'uid_no',
                        'passport_no',
                        'labour_card_no',
                        'emirates_id',
                    ]),
                    [
                        'nationality' => $countries[strtolower($row['country_name'])],
                        'gender' => $genders[$row['gender']] ?? null,
                        'date_of_birth' => DateTime::createFromFormat($dateFormat, $row['date_of_birth'])->format(DB_DATE_FORMAT),
                        'marital_status' => $maritalStatuses[$row['marital_status']] ?? null,
                        'date_of_join' => DateTime::createFromFormat($dateFormat, $row['date_of_join'])->format(DB_DATE_FORMAT),
                        'mode_of_pay' => substr($row['mode_of_pay'], 0, 1),
                        'bank_id' => $banks[strtolower($row['bank_name'])] ?? null
                    ]
                ));
            }

            // Insert all the jobs and salaries
            $employeeJobs = [];
            $employeeSalaries = [];
            foreach ($data as $row) {
                $employeeJobs[] = [
                    'employee_id' => $row['employee']->id,
                    'working_company_id' => $companies[strtolower($row['working_company_name'])],
                    'visa_company_id' => $companies[strtolower($row['visa_company_name'])],
                    'designation_id' => $designations[strtolower($row['designation_name'])],
                    'department_id' => $departments[strtolower($row['department_name'])],
                    'commence_from' => $row['commence_from']
                        ? DateTime::createFromFormat($dateFormat, $row['commence_from'])->format(DB_DATE_FORMAT)
                        : $row['employee']->date_of_join,
                    'week_offs' => json_encode($row['week_offs']),
                    'work_hours' => $row['work_hours'],
                    'has_commission' => $row['has_commission'] == 'Yes',
                    'has_pension' => $row['has_pension'] == 'Yes',
                    'has_overtime' => $row['has_overtime'] == 'Yes',
                    'require_attendance' => $row['require_attendance'] == 'Yes',
                    'created_at' => $row['employee']->created_at->format(DB_DATETIME_FORMAT),
                    'updated_at' => $row['employee']->created_at->format(DB_DATETIME_FORMAT),
                ];
                $employeeSalaries[] = [
                    'employee_id' => $row['employee']->id,
                    'from' => $row['employee']->date_of_join,
                    'gross_salary' => $row['gross_salary']
                ];
            }
            EmployeeJob::insert($employeeJobs);
            EmployeeSalary::insert($employeeSalaries);

            // Insert the salary details
            $employeeSalaries = EmployeeSalary::all()->keyBy('employee_id');
            $employeeSalaryDetails = [];
            foreach ($data as $row) {
                foreach ($payElements as $payElement) {
                    $employeeSalaryDetails[] = [
                        'salary_id' => $employeeSalaries[$row['employee']->id]->id,
                        'pay_element_id' => $payElement->id,
                        'amount' => $row[$payElement->key]
                    ];
                }
            }
            DB::table('0_emp_salary_details')->insert($employeeSalaryDetails);
        });
    }

    /**
     * Returns all the supported/recognized date formats
     *
     * @return array
     */
    private function getAvailableDateFormats()
    {
        return [
            "d/m/Y" => 'dd/mm/yyyy   -   30/12/1975',
            'm/d/Y' => 'mm/dd/yyyy   -   12/30/1975',
            'Y/m/d' => 'yyyy/mm/dd   -   1975/12/30',
            'd/m/y' => 'dd/md/yy     -   30/12/75',
            'm/d/y' => 'mm/dd/yy     -   12/30/75',
            'y/m/d' => 'yy/mm/dd     -   75/12/30',
            'd/M/Y' => 'dd/mmm/yyyy  -   30/Dec/1975',
            'M/d/Y' => 'mmm/dd/yyyy  -   Dec/30/1975',
            'Y/M/d' => 'yyyy/mmm/dd  -   1975/Dec/30',
            'd/M/y' => 'dd/mmm/yy    -   30/Dec/75',
            'M/d/y' => 'mmm/dd/yy    -   Dec/30/75',
            'y/M/d' => 'yy/mmm/dd    -   75/Dec/30',
            'd/F/Y' => 'dd/mmmm/yyyy -   30/December/1975',
            'F/d/Y' => 'mmmm/dd/yyyy -   December/30/1975',
            'Y/F/d' => 'yyyy/mmmm/dd -   1975/December/30',
            'd/F/y' => 'dd/mmmm/yy   -   30/December/75',
            'F/d/y' => 'mmmm/dd/yy   -   December/30/75',
            'y/F/d' => 'yy/mmmm/dd   -   75/December/30',
            'd m Y' => 'dd mm yyyy   -   30 12 1975',
            'm d Y' => 'mm dd yyyy   -   12 30 1975',
            'Y m d' => 'yyyy mm dd   -   1975 12 30',
            'd m y' => 'dd md yy     -   30 12 75',
            'm d y' => 'mm dd yy     -   12 30 75',
            'y m d' => 'yy mm dd     -   75 12 30',
            'd M Y' => 'dd mmm yyyy  -   30 Dec 1975',
            'M d Y' => 'mmm dd yyyy  -   Dec 30 1975',
            'Y M d' => 'yyyy mmm dd  -   1975 Dec 30',
            'd M y' => 'dd mmm yy    -   30 Dec 75',
            'M d y' => 'mmm dd yy    -   Dec 30 75',
            'y M d' => 'yy mmm dd    -   75 Dec 30',
            'd F Y' => 'dd mmmm yyyy -   30 December 1975',
            'F d Y' => 'mmmm dd yyyy -   December 30 1975',
            'Y F d' => 'yyyy mmmm dd -   1975 December 30',
            'd F y' => 'dd mmmm yy   -   30 December 75',
            'F d y' => 'mmmm dd yy   -   December 30 75',
            'y F d' => 'yy mmmm dd   -   75 December 30',
            'd-m-Y' => 'dd-mm-yyyy   -   30-12-1975',
            'm-d-Y' => 'mm-dd-yyyy   -   12-30-1975',
            'Y-m-d' => 'yyyy-mm-dd   -   1975-12-30',
            'd-m-y' => 'dd-md-yy     -   30-12-75',
            'm-d-y' => 'mm-dd-yy     -   12-30-75',
            'y-m-d' => 'yy-mm-dd     -   75-12-30',
            'd-M-Y' => 'dd-mmm-yyyy  -   30-Dec-1975',
            'M-d-Y' => 'mmm-dd-yyyy  -   Dec-30-1975',
            'Y-M-d' => 'yyyy-mmm-dd  -   1975-Dec-30',
            'd-M-y' => 'dd-mmm-yy    -   30-Dec-75',
            'M-d-y' => 'mmm-dd-yy    -   Dec-30-75',
            'y-M-d' => 'yy-mmm-dd    -   75-Dec-30',
            'd-F-Y' => 'dd-mmmm-yyyy -   30-December-1975',
            'F-d-Y' => 'mmmm-dd-yyyy -   December-30-1975',
            'Y-F-d' => 'yyyy-mmmm-dd -   1975-December-30',
            'd-F-y' => 'dd-mmmm-yy   -   30-December-75',
            'F-d-y' => 'mmmm-dd-yy   -   December-30-75',
            'y-F-d' => 'yy-mmmm-dd   -   75-December-30',
        ];
    }
    
}