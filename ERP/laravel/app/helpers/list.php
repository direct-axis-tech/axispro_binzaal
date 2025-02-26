<?php

use App\Models\Hr\Employee;
use App\Models\Hr\PayElement;
use App\Models\Inventory\StockCategory;
use App\Models\Labour\Contract;

/**
 * List of payment statuses
 *
 * @return array
 */
function payment_statuses()
{
    return [
        1 => 'Fully Paid',
        2 => 'Not Paid',
        3 => 'Partially Paid'
    ];
}

/**
 * List of payment cart types
 *
 * @return array
 */
function card_types()
{
    return [
        'Cash' => 'Cash',
        'CenterCard' => 'Center Card',
        'CustomerCard' => 'Customer Card'
    ];
}

/**
 * List of payment methods
 *
 * @return array
 */
function payment_methods()
{
    return [
        'Cash' => 'Cash',
        'CreditCard' => 'Credit Card',
        'BankTransfer' => 'Bank Transfer'
    ];
}

/**
 * List of transaction statuses
 *
 * @return array
 */
function transaction_statuses()
{
    return [
        1 => 'Completed',
        2 => 'Not Completed'
    ];
}

/**
 * List of marital statuses
 *
 * @return array
 */
function marital_statuses()
{
    return [
        'S' => 'Single',
        'M' => 'Married',
        'W' => 'Widowed',
        'D' => 'Divorced'
    ];
}

/**
 * List of genders
 *
 * @return array
 */
function genders()
{
    return [
        'M' => 'Male',
        'F' => 'Female',
    ];
}

/**
 * List of skills available in labour
 *
 * @return array
 */
function labour_skills($lang = 'en')
{
    $skills = [
        'en' => [
            '1' => 'Baby Care',
            '2' => 'Cleaning',
            '3' => 'Cooking',
            '4' => 'Washing',
            '5' => 'Ironing'
        ],
        'ar' => [
            '1' => 'العناية بالطفل',
            '2' => 'تنظيف',                   
            '3' => 'طبخ',
            '4' => 'غسل',
            '5' => 'كي الملابس'
        ]
    ];

    return $skills[$lang] ?? $skills['en'];
}

/**
 * List of labour types
 *
 * @return array
 */
function labour_types($lang = 'en')
{
    $types = [
        'en' =>  [
            '1' => 'House maid',
            '2' => 'Cleaning staff',
        ],
        'ar' => [
            '1' => 'خادمة المنزل',
            '2' => 'عمال نظافة',
        ]
    ];

    return $types[$lang] ?? $types['en'];
}

/**
 * List of labour job types
 *
 * @return array
 */
function labour_job_types()
{
    return [
        '1' => 'Full Time',
        '2' => 'Part Time'
    ];
}

/**
 * List of labour types
 *
 * @return array
 */
function labour_categories()
{
    return [
        '1' => 'Office Maid',
        '2' => 'Partner Maid'
    ];
}

/**
 * List of labour contract types
 *
 * @return array
 */
function labour_contract_types()
{
    return [
        Contract::CONTRACT => 'Normal',
        Contract::TEMPORARY_CONTRACT => 'Trial'
    ];
}

/**
 * List of labour invoice categories
 *
 * @return array
 */
function labour_invoice_categories()
{
    return [
        StockCategory::DWD_PACKAGEONE => "Package 1",
        StockCategory::DWD_PACKAGETWO => "Package 2",
        StockCategory::DWD_PACKAGETHREE => "Package 3",
        StockCategory::DWD_PACKAGEFOUR => "Package 4",
    ];
}

function maid_status(){
    return [
        'Available' => 'Available',
        'cancellation under processes' => 'cancellation under processes',
        'Deactivate' => 'Deactivate',
        'EWAA' => 'EWAA',
        'New Hire ( Outside)' => 'New Hire ( Outside)',
        'On Hold' => 'On Hold',
        'Run away' => 'Run away',
        'vacation' => 'vacation',
        'Visa Under Processes' => 'Visa Under Processes',
        'Working' => 'Working'
    ];
}

function missing_punch_options()
{
    return [
        MPO_LATE_IN => 'Late-in',
        MPO_EARLY_OUT => 'Early-out',
        MPO_ABSENT => 'Absent',
        MPO_AUTO_DETECT => 'Auto',
        MPO_IGNORE => 'Ignore'
    ];
}

function overtime_algorithms()
{
    return [
        OA_MANUAL => "Calculated Manually/Don't Calculate",
        OA_WORK_HOURS => 'Based on work hours',
    ];
}

function overtime_rounding_algorithms()
{
    return [
        ORA_ROUND_UP_HALF => 'Round up if 1/2 way there or more',
        ORA_ROUND_UP_3QTR => 'Round up if 3/4 way there or more',
    ];
}

function pay_element_types()
{
    return [
        PayElement::TYPE_ALLOWANCE => 'Allowance',
        PayElement::TYPE_DEDUCTION => 'Deduction'
    ];
}

function education_levels()
{
    return [
        'ELEMENTARY' => 'ELEMENTARY',
        'HIGH SCHOOL' => 'HIGH SCHOOL',
        'DIPLOMA' => 'DIPLOMA',
        'POST GRADUATE' => 'POST GRADUATE',
        'SHORT COURSE CERTIFICATE' => 'SHORT COURSE CERTIFICATE',
        'BACHELOR' => 'BACHELOR',
        'MASTERS' => 'MASTERS'
    ];
}

function round_off_algorithms()
{
    return [
        ROUNDOFF   => 'Normal',
        ROUND_UP   => 'Round Up',
        ROUND_DOWN => 'Round Down'
    ];
}

function language_proficiencies($lang = 'en')
{
    $prof = [
        "en" => [
            'Basic' => 'Basic',
            'Average' => 'Average',
            'Above Average' => 'Above Average',
            'Professional' => 'Professional',
            'Master' => 'Master'
        ],
        "ar" => [
            'Basic' => 'أساسي',
            'Average' => 'متوسط',
            'Above Average' => 'فوق المتوسطة',
            'Professional' => 'المهنية',
            'Master' => 'الماجستير'
        ]
    ];

    return $prof[$lang] ?? $prof['en'];
}

function notify_before_units()
{
    return [
        'month' => 'Months',
        'week' => 'Weeks',
        'day' => 'Days'
    ];
}

function get_employee_attendance_types()
{
    return [
        ACT_SHIFT_BASED => 'Shift Based',
        ACT_WORK_HOURS_BASED => 'Working Hours Based',
    ];
}

function personal_timeout_calculation_methods()
{
    return [
        TO_MONTHLY => "Monthly",
        TO_CUTOFF_DATE => 'Cutoff Date',
    ];
}


function installment_interval_units()
{
    return [
        'month' => 'Month/s',
        'week' => 'Week/s',
        'day' => 'Day/s'
    ];;
}

function employment_statuses()
{
    return [
        Employee::ES_ALL => 'All',
        Employee::ES_ACTIVE => 'Active',
        Employee::ES_RESIGNED => 'Resigned',
        Employee::ES_TERMINATED => 'Terminated',
        Employee::ES_RETIRED => 'Retired',
    ];
}

function commission_calculation_methods()
{
    return [
        CCM_AMOUNT => 'Amount Based',
        CCM_PERCENTAGE => 'Percentage Based'
    ];
}

function commission_base_values()
{
    return [
        CBV_SERVICE_CHG => 'Service Charge',
        CBV_CUST_COMMISSION => 'Customer Commission'
    ];
}

function subledger_elements() {
    return [
		'commission_el',
		'staff_mistake_el',
		'pension_el',
		'violations_el',
		'advance_recovery_el',
		'loan_recovery_el',
		'rewards_bonus_el'
	];
}