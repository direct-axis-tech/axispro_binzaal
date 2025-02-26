<?php

/** @var string DB_DATE_FORMAT Date format used in MySQL */
const DB_DATE_FORMAT = 'Y-m-d';

/** @var string DB_DATETIME_FORMAT DateTime format used in MySQL */
const DB_DATETIME_FORMAT = 'Y-m-d H:i:s';

/** @var string UAE_MOBILE_NO_PATTERN The regular expression pattern to match valid UAE mobile number */
const UAE_MOBILE_NO_PATTERN = '/^(\+971|00971|971|0)?((5[024568]|[1234679])\d{7})$/';

/** @var string PF_TASHEEL_CC The Payment Flag used to indicate customer card tasheel transactions in old system */
const PF_TASHEEL_CC = 2;

// Notification Types
const NT_SMS = 'SMS';
const NT_EMAIL = 'EMAIL';

/** @var string[] GCC_COUNTRIES The Countries/States Belonging to GCC */
const GCC_COUNTRIES = [
    'BH' => 'Bahrain',
    'KW' => 'Kuwait',
    'OM' => 'Oman',
    'QA' => 'Qatar',
    'SA' => 'Saudi Arabia',
    'AE' => 'United Arab Emirates'
];

/** @var string[] PAYMENT_METHODS The Payment Methods available in the system */
const PAYMENT_METHODS = [
    'Cash' => 'Cash',
    'CreditCard' => 'Credit Card',
    'BankTransfer' => 'Bank Transfer',
    'Split' => 'Split (Cash + Card)',
    'OnlinePayment' => 'Online Payment',
    'CustomerCard' => 'Customer Card',
    'CenterCard' => 'Center Card'
];

/** @var string[] CENTER_TYPES The type of centers that can be configured in the system */
const CENTER_TYPES = [
    'ADHEED' => 1,
    'AMER' => 2,
    'DED' => 3,
    'DHA' => 4,
    'DOMESTIC_WORKER' => 5,
    'DUBAI_COURT' => 6,
    'EJARI' => 7,
    'OTHER' => 8,
    'RTA' => 9,
    'TADBEER' => 10,
    'TASHEEL' => 11,
    'TAWJEEH' => 12,
    'TYPING' => 13,
];

/*
 |----------------------------------------------------------------------------------
 |	Payment types
 |----------------------------------------------------------------------------------
 */

/** @var int PT_MISC Miscellaneous Payment */
const PT_MISC =  0;

/** @var int PT_WORKORDER No Idea */
const PT_WORKORDER =  1;

/** @var int PT_CUSTOMER Customer Payment */
const PT_CUSTOMER =  2;

/** @var int PT_SUPPLIER Supplier Payment */
const PT_SUPPLIER =  3;

/** @var int PT_QUICKENTRY No Idea */
const PT_QUICKENTRY =  4;

/** @var int PT_DIMESION No Idea */
const PT_DIMESION =  5;

/** @var int PT_EMPLOYEE */
const PT_EMPLOYEE =  6;

/** @var int PT_USER */
const PT_USER =  7;

/** @var int PT_SUBLEDGER */
const PT_SUBLEDGER =  8;

/** @var int PT_SALESMAN */
const PT_SALESMAN =  9;

/*
 |----------------------------------------------------------------------------------
 |	Tax Register Types
 |----------------------------------------------------------------------------------
 */
/** Tax Output - Sales */
const TR_OUTPUT = 0;

/** Tax Input - Purchase */
const TR_INPUT = 1;

/*
|------------------------------
| Item Tax Types
|------------------------------
*/
const ITT_REGULAR = '1';
const ITT_NO_TAX = '2';

/*
 |----------------------------------------------------------------------------------
 |	Front accounting system transactions
 |----------------------------------------------------------------------------------
 */
const ST_LOCTRANSFER = 16;
const ST_INVADJUST = 17;
const ST_PURCHORDER = 18;
const ST_SUPPINVOICE = 20;
const ST_SUPPCREDIT = 21;
const ST_SUPPAYMENT = 22;
const ST_SUPPRECEIVE = 25;
const ST_WORKORDER = 26;
const ST_MANUISSUE = 28;
const ST_MANURECEIVE = 29;
const ST_DIMENSION = 40;
const ST_CUSTOMER = 41;
const ST_SUPPLIER = 42;

/** Error tolerance for floating point comparison */
const FLOAT_COMP_DELTA = 0.004;

/*
 |----------------------------------------------------------------------------------
 |	Subledger Types
 |----------------------------------------------------------------------------------
 */
const SLT_ACCOUNTS_REC = 1;
const SLT_ACCOUNTS_PAY = -1;
const SLT_USR_COMMISSION = 3;
const SLT_STAFF_MISTAKE = 4;
const SLT_EMP_PENSION = 5;
const SLT_VIOLATION_DED = 6;
const SLT_SALARY_ADVANCE = 7;
const SLT_EMP_LOAN = 8;
const SLT_EMP_SALARY_PAY = 9;
const SLT_CUST_COMMISSION = 10;
const SLT_AXISPRO_SUBLEDGER = 11;
const SLT_LEAVE_ACCRUAL = 12;
const SLT_GRATUITY_PAY = 13;
const SLT_SUPP_COMMISSION = 14;
const SLT_EMP_REWARDS = 15;
const SLT_SALESMAN_COMMISSION = 16;

/*
 |----------------------------------------------------------------------------------
 |	Sales Credit Types
 |----------------------------------------------------------------------------------
 */
const CT_RETURN = 'Return';
const CT_WRITEOFF = 'WriteOff';


/*
 |----------------------------------------------------------------------------------
 |	Commission Calculation Methods
 |----------------------------------------------------------------------------------
 */
const CCM_AMOUNT = '1';
const CCM_PERCENTAGE = '2';

/*
 |----------------------------------------------------------------------------------
 |	Commission Base Values
 |----------------------------------------------------------------------------------
 */
const CBV_SERVICE_CHG = '1';
const CBV_CUST_COMMISSION = '2';

/*
|-----------------------------------------------------------------------------------
| ATTENDANCE METRIC TYPES
|-----------------------------------------------------------------------------------
*/
const AT_OVERTIME = 'O';
const AT_LATEHOURS = 'L';
const AT_SHORTHOURS = 'S';
const AT_ABSENT = 'A';

/*
|-----------------------------------------------------------------------------------
| Missing Punch Options
|-----------------------------------------------------------------------------------
*/
const MPO_LATE_IN = 1;
const MPO_EARLY_OUT = 2;
const MPO_ABSENT = 3;
const MPO_IGNORE = 4;
const MPO_AUTO_DETECT = 5;

/*
|-----------------------------------------------------------------------------------
| Overtime Algorithms
|-----------------------------------------------------------------------------------
*/
const OA_MANUAL = 1;
const OA_WORK_HOURS = 2;

/*
|-----------------------------------------------------------------------------------
| Overtime Rounding Algorithms
|-----------------------------------------------------------------------------------
*/
const ORA_ROUND_UP_HALF = 1;
const ORA_ROUND_UP_3QTR = 2;

/*
|-----------------------------------------------------------------------------------
| STATUS TYPES
|-----------------------------------------------------------------------------------
*/
const STS_VERIFIED = 'V';
const STS_PENDING = 'P';
const STS_IGNORED = 'I';
const STS_APPROVED = 'A';
const STS_IN_QUEUE = 'Q';

/*
|-----------------------------------------------------------------------------------
| ATTENDANCE STATUS
|-----------------------------------------------------------------------------------
*/
const ATS_PRESENT = 'P';
const ATS_ABSENT = 'A';
const ATS_OFF = 'O';

/*
|-----------------------------------------------------------------------------------
| DUTY STATUS
|-----------------------------------------------------------------------------------
*/
const DS_PRESENT = 'present';
const DS_ABSENT = 'not_present';
const DS_OFF = 'off';
const DS_HOLIDAY = 'holiday';
const DS_ON_LEAVE = 'on_leave';

/*
|-----------------------------------------------------------------------------------
| Mode of Payment
|-----------------------------------------------------------------------------------
*/
const MOP_BANK = 'B';
const MOP_CASH = 'C';

/*
|-----------------------------------------------------------------------------------
| Round Off Algorithms
|-----------------------------------------------------------------------------------
*/
const ROUNDOFF = 1;
const ROUND_UP = 2;
const ROUND_DOWN = 3;

/*
|-----------------------------------------------------------------------------------
| Payment Terms
|-----------------------------------------------------------------------------------
*/
const PMT_TERMS_DELAYED_1 = 4;
const PMT_TERMS_PREPAID = 6;

/*
|-----------------------------------------------------------------------------------
| Attendance Calculation Types
|-----------------------------------------------------------------------------------
*/
const ACT_SHIFT_BASED = 1;
const ACT_WORK_HOURS_BASED = 2;

/*
|-----------------------------------------------------------------------------------
| Timeout Calc Methods
|-----------------------------------------------------------------------------------
*/
const TO_MONTHLY = 1;
const TO_CUTOFF_DATE = 2;

/*
|-----------------------------------------------------------------------------------
| Stock Item Types
|-----------------------------------------------------------------------------------
*/
const STOCK_TYPE_FIXED_ASSET = 'F';
const STOCK_TYPE_MANUFACTURED = 'M';
const STOCK_TYPE_PURCHASED = 'B';
const STOCK_TYPE_SERVICE = 'D';

/*
|-----------------------------------------------------------------------------------
| Costing Methods
|-----------------------------------------------------------------------------------
*/
const COSTING_METHOD_NORMAL = '1';
const COSTING_METHOD_EXPENSE = '2';

/*
|-----------------------------------------------------------------------------------
| Order Invoice Status
|-----------------------------------------------------------------------------------
*/
const OIS_FULLY_INVOICED = 'Fully Invoiced';
const OIS_PARTIALLY_INVOICED = 'Partially Invoiced';
const OIS_NOT_INVOICED = 'Not Invoiced';

/*
|-----------------------------------------------------------------------------------
| Order Completion Status
|-----------------------------------------------------------------------------------
*/
const OCS_PENDING = 'Pending';
const OCS_WORK_IN_PROGRESS = 'Work in Progress';
const OCS_COMPLETED = 'Completed';

/*
|-----------------------------------------------------------------------------------
| Autofetch Item Types
|-----------------------------------------------------------------------------------
*/
const AIT_TASHEEL = 'TASHEEL';
const AIT_IMM_PRE_DXB = 'IMM_PRE_DXB';
const AIT_IMM_POST_DXB = 'IMM_POST_DXB';

/*
|------------------------------
| Sales Types
|------------------------------
*/
const SALES_TYPE_TAX_EXCLUDED = 1;
const SALES_TYPE_TAX_INCLUDED = 2;