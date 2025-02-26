<?php
/**********************************************************************
    Direct Axis Technology L.L.C.
	Released under the terms of the GNU General Public License, GPL, 
	as published by the Free Software Foundation, either version 3 
	of the License, or (at your option) any later version.
    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  
    See the License here <http://www.gnu.org/licenses/gpl-3.0.html>.
***********************************************************************/

use App\Models\Accounting\LedgerTransaction;
use App\Models\Hr\PayElement;
use Illuminate\Support\Arr;

$page_security = 'HRM_SETUP';
$path_to_root="..";
include($path_to_root . "/includes/session.inc");

$js = "";
if ($SysPrefs->use_popup_windows && $SysPrefs->use_popup_search)
	$js .= get_js_open_window(900, 500);

page(trans($help_context = "HR Setup"), false, false, "", $js);

include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/includes/data_checks.inc");

include_once($path_to_root . "/admin/db/company_db.inc");

//-------------------------------------------------------------------------------------------------

function can_process()
{

	$mandatory = [
		'dflt_atd_metric_review_status' => 'General HR Default attendance metric review status',
		'default_shift_id' => 'Default Shift',
		'home_country' => 'Home Country',
		'weekends' => 'Weekends',
		'released_holded_salary_el' => 'Released Holded Salary Element',
		'overtime_el' => 'Overtime Element',
		'commission_el' => 'Commission Element',
		'absence_el' => 'Absence Element',
		'designation_hr' => 'HR Executive Position',
		'leaves_el' => 'Leaves Element',
		'weekendsworked_el' => 'Weekends Worked Element',
		'holidaysworked_el' => 'Holidays Worked Element',
		'days_not_worked_el' => 'Days Not Worked Element',
		'housing_alw_el' => 'Housing Alw Element',
		'basic_pay_el' => 'Basic Pay Element',
		'violations_el' => 'Violation Element',
		'latecoming_el' => 'Late Coming Element',
		'earlyleaving_el' => 'Early Leave Element',
		'pension_el' => 'Pension Element',
		'staff_mistake_el' => 'Staff Mistake Element',
		'holded_salary_el' => 'Holded salary Element',
		'loan_recovery_el' => 'Loan Recovery Element',
		'advance_recovery_el' => 'Advance Recovery Element',
		'rewards_bonus_el' => 'Reward / Bonus Element',
	];

	$field = '';
	$field_name = '';

	foreach($mandatory as $key=>$val){
		if(isset($_POST[$key]) && empty($_POST[$key])){
			$field = $val;
			$field_name = $key;
			break;
		}

	}

	if(!empty($field)){
		display_error("The ".$field." field is mandatory");
		set_focus($field_name);
		return false;
	}

	$elements_configs = Arr::only($_POST, [
		'basic_pay_el',
		'housing_alw_el',
		'days_not_worked_el',
		'holidaysworked_el',
		'weekendsworked_el',
		'leaves_el',
		'absence_el',
		'commission_el',
		'overtime_el',
		'released_holded_salary_el',
		'holded_salary_el',
		'staff_mistake_el',
		'pension_el',
		'earlyleaving_el',
		'latecoming_el',
		'violations_el',
		'loan_recovery_el',
		'advance_recovery_el',
		'rewards_bonus_el',
	]);

	$subledger_accounts = subledger_elements();
	foreach ($subledger_accounts as $k) {
		$other_elements = Arr::except($elements_configs, $k);
		if (!empty($_POST[$k]) && in_array($_POST[$k], $other_elements)) {
			display_error("The element for $k must be unique");
			set_focus($k);
			return false;
		}
	}

	$check_num_fields = [
		'shift_spans_midnight' => 'Shift Spans Midnight',
        'auto_journal_deduction_entry' => 'Generate Auto Journal For Deduction Entry',
		'weekend_rate' => 'Weekend Rate',
		'standard_workhours' => 'Standard Work Hours',
		'standard_days' => 'Standard Days',
		'latehour_rate' => 'Late Hour Rate',
		'duplicate_punch_interval' => 'Duplicate punch interval',
		'working_hours_grace_time' => 'Working-Hours grace time',
		'late_in_grace_time' => 'Late-in grace time',
		'early_out_grace_time' => 'Early-out grace time',
		'absent_when_late_in_exceeds_min' => 'Absent when late-in exceeds',
		'absent_when_early_out_exceeds_min' => 'Absent when early-out exceeds',
		'count_missing_punch_as' => 'Count missing punch as',
		'value_count_missing_punch_as' => 'Value: Count missing punch as',
		'overtime_grace_time' => 'Overtime grace time',
		'overtime_algorithm' => 'Overtime algorithm',
		'overtime_round_to' => 'Overtime round-to',
		'overtime_rounding_algorithm' => 'Overtime rounding algorithm',
		'gpssa_employee_share' => 'GPSSA employee share',
		'gpssa_employer_share' => 'GPSSA employer share',
		'earlygoing_rate' => 'Early Going Rate',
		'shift_tolerance' => 'Shift Tolerence',
		'public_holiday_rate' => 'Public Holiday Rate',
		'payroll_cutoff' => 'Payroll Cutoff Date',
		'overtime_rate' => 'Overtime Rate',
		'personal_timeout_calculation_method' => 'Personal Timeout Calculation'
	];

	foreach (GCC_COUNTRIES as $code => $name) {
		$key = 'gpssa_employee_share_'.strtolower($code);
		if ($_POST[$key] !== '' && !check_num($key, 0, 100)) {
			display_error("The GPSSA share of ($name) must be a number and between 0 and 100");
			set_focus($key);
			return false;
		}

		$key = 'gpssa_employer_share_'.strtolower($code);
		if ($_POST[$key] !== '' && !check_num($key, 0, 100)) {
			display_error("The GPSSA Employer share of ($name) must be a number and between 0 and 100");
			set_focus($key);
			return false;
		}
	}

	$field = '';
	$field_name = '';

	foreach($check_num_fields as $key=>$val){
		if(isset($_POST[$key]) && !check_num($key,0)){
			$field = $val;
			$field_name = $key;
			break;
		}

	}

	if(!empty($field)){
		display_error("Minimum ".$field." field should be 0");
		set_focus($field_name);
		return false;
	}

	if (
		!empty($salary_payable = get_post('default_salary_payable_account'))
		&& $salary_payable != pref('hr.default_salary_payable_account')
		&& LedgerTransaction::where('amount', '<>', 0)->whereAccount($salary_payable)->exists()
	) {
		display_error("The salary payable account is already in use");
		set_focus('default_salary_payable_account');
		return false;
	}

	return true;
}

//-------------------------------------------------------------------------------------------------

if (isset($_POST['submit']) && can_process())
{

	$_POST['weekends'] = implode(",",($_POST['weekends']));
	$_POST['overtime_salary_elements'] = implode(",",($_POST['overtime_salary_elements']));
	$_POST['holidays_salary_elements'] = implode(",",($_POST['holidays_salary_elements']));
	
    $updates = array_merge(
        array( 
            'absence_el',
            'basic_pay_el',
            'commission_el',
            'days_not_worked_el',
            'default_shift_id',
            'shift_spans_midnight',
            'auto_journal_deduction_entry',
            'designation_hr',
            'dep_amer',
            'dep_tadbeer',
            'dep_tasheel',
            'dep_tawjeeh',
            'dflt_atd_metric_review_status',
            'earlygoing_rate',
            'earlyleaving_el',
            'gpssa_employee_share',
            'gpssa_employer_share',
            'duplicate_punch_interval',
            'working_hours_grace_time',
            'late_in_grace_time',
            'early_out_grace_time',
            'absent_when_late_in_exceeds_min',
            'absent_when_early_out_exceeds_min',
            'count_missing_punch_as',
            'value_count_missing_punch_as',
            'overtime_grace_time',
            'overtime_algorithm',
            'default_overtime_status',
            'overtime_round_to',
            'overtime_rounding_algorithm',
            'holded_salary_el',
            'holidaysworked_el',
            'home_country',
            'housing_alw_el',
            'latecoming_el',
            'latehour_rate',
            'leaves_el',
            'overtime_el',
            'overtime_rate',
            'pension_el',
            'public_holiday_rate',
            'released_holded_salary_el',
            'shift_tolerance',
            'staff_mistake_customer_id',
            'staff_mistake_el',
            'standard_days',
            'standard_workhours',
            'violations_el',
            'weekend_rate',
            'weekends',
            'weekendsworked_el',
            'advance_recovery_el',
            'loan_recovery_el',
            'default_salary_payable_account',
            'pension_expense_account',
            'overtime_salary_elements',
            'holidays_salary_elements',
            'personal_timeout_calculation_method',
            'rewards_bonus_el'
        ),
        array_map(
            function ($c) { return 'gpssa_employee_share_'.strtolower($c); },
            array_keys(GCC_COUNTRIES)
        ),
        array_map(
            function ($c) { return 'gpssa_employer_share_'.strtolower($c); },
            array_keys(GCC_COUNTRIES)
        )
    );

    if ($_SESSION['wa_current_user']->is_developer_session) {
        $updates[] = 'payroll_cutoff';
    }

	update_company_prefs(get_post($updates));

	display_notification(trans("The HR setup has been updated."));

} /* end of if submit */

//-------------------------------------------------------------------------------------------------

start_form();

start_outer_table(TABLESTYLE2);

table_section(1);

$myrow = get_company_prefs();

// $_POST['allow_negative_stock'] = $myrow['allow_negative_stock'];
// $_POST['po_over_receive'] = percent_format($myrow['po_over_receive']);
// $_POST['po_over_charge'] = percent_format($myrow['po_over_charge']);
// $_POST['default_credit_limit'] = price_format($myrow['default_credit_limit']);

$_POST['dflt_atd_metric_review_status'] = $myrow['dflt_atd_metric_review_status'];
$_POST['dep_tawjeeh'] = $myrow['dep_tawjeeh'];
$_POST['dep_tasheel'] = $myrow['dep_tasheel'];
$_POST['dep_tadbeer'] = $myrow['dep_tadbeer'];
$_POST['designation_hr'] = $myrow['designation_hr'];
$_POST['dep_amer'] = $myrow['dep_amer'];
$_POST['default_shift_id'] = $myrow['default_shift_id'];
$_POST['shift_spans_midnight'] = $myrow['shift_spans_midnight'];
$_POST['auto_journal_deduction_entry'] = $myrow['auto_journal_deduction_entry'];
$_POST['days_not_worked_el'] = $myrow['days_not_worked_el'];
$_POST['commission_el'] = $myrow['commission_el'];
$_POST['basic_pay_el'] = $myrow['basic_pay_el'];
$_POST['absence_el'] = $myrow['absence_el'];

$_POST['latehour_rate'] = $myrow['latehour_rate'];
$_POST['latecoming_el'] = $myrow['latecoming_el'];
$_POST['housing_alw_el'] = $myrow['housing_alw_el'];
$_POST['home_country'] = $myrow['home_country'];
$_POST['holidaysworked_el'] = $myrow['holidaysworked_el'];
$_POST['holded_salary_el'] = $myrow['holded_salary_el'];
$_POST['duplicate_punch_interval'] = $myrow['duplicate_punch_interval'];
$_POST['working_hours_grace_time'] = $myrow['working_hours_grace_time'];
$_POST['late_in_grace_time'] = $myrow['late_in_grace_time'];
$_POST['early_out_grace_time'] = $myrow['early_out_grace_time'];
$_POST['absent_when_late_in_exceeds_min'] = $myrow['absent_when_late_in_exceeds_min'];
$_POST['absent_when_early_out_exceeds_min'] = $myrow['absent_when_early_out_exceeds_min'];
$_POST['count_missing_punch_as'] = $myrow['count_missing_punch_as'];
$_POST['value_count_missing_punch_as'] = $myrow['value_count_missing_punch_as'];
$_POST['overtime_grace_time'] = $myrow['overtime_grace_time'];
$_POST['overtime_algorithm'] = $myrow['overtime_algorithm'];
$_POST['default_overtime_status'] = $myrow['default_overtime_status'];
$_POST['overtime_round_to'] = $myrow['overtime_round_to'];
$_POST['overtime_rounding_algorithm'] = $myrow['overtime_rounding_algorithm'];
$_POST['gpssa_employee_share'] = $myrow['gpssa_employee_share'];
foreach (array_keys(GCC_COUNTRIES) as $code) {
	$_POST['gpssa_employee_share_'.strtolower($code)] = $myrow['gpssa_employee_share_'.strtolower($code)];
}
$_POST['gpssa_employer_share'] = $myrow['gpssa_employer_share'];
foreach (array_keys(GCC_COUNTRIES) as $code) {
	$_POST['gpssa_employer_share_'.strtolower($code)] = $myrow['gpssa_employer_share_'.strtolower($code)];
}
$_POST['earlyleaving_el'] = $myrow['earlyleaving_el'];
$_POST['earlygoing_rate'] = $myrow['earlygoing_rate'];

$_POST['staff_mistake_el'] = $myrow['staff_mistake_el'];
$_POST['staff_mistake_customer_id'] = $myrow['staff_mistake_customer_id'];
$_POST['shift_tolerance'] = $myrow['shift_tolerance'];
$_POST['released_holded_salary_el'] = $myrow['released_holded_salary_el'];
$_POST['public_holiday_rate'] = $myrow['public_holiday_rate'];
$_POST['pension_el'] = $myrow['pension_el'];
$_POST['payroll_cutoff'] = $myrow['payroll_cutoff'];
$_POST['overtime_rate'] = $myrow['overtime_rate'];
$_POST['overtime_el'] = $myrow['overtime_el'];
$_POST['leaves_el'] = $myrow['leaves_el'];
$_POST['advance_recovery_el'] = $myrow['advance_recovery_el'];
$_POST['loan_recovery_el'] = $myrow['loan_recovery_el'];

$_POST['weekendsworked_el'] = $myrow['weekendsworked_el'];
$_POST['weekends'] = explode(",",$myrow['weekends']);
$_POST['weekend_rate'] = $myrow['weekend_rate'];
$_POST['violations_el'] = $myrow['violations_el'];
$_POST['standard_workhours'] = $myrow['standard_workhours'];
$_POST['standard_days'] = $myrow['standard_days'];
$_POST['default_salary_payable_account'] = $myrow['default_salary_payable_account'];
$_POST['pension_expense_account'] = $myrow['pension_expense_account'];
$_POST['overtime_salary_elements'] = explode(",", $myrow['overtime_salary_elements']);
$_POST['holidays_salary_elements'] = explode(",", $myrow['holidays_salary_elements']);
$_POST['personal_timeout_calculation_method'] = $myrow['personal_timeout_calculation_method'];
$_POST['rewards_bonus_el'] = $myrow['rewards_bonus_el'];

//---------------


table_section_title(trans("General HR"));

array_selector_row (trans("Default attendance metric review status:"), 'dflt_atd_metric_review_status', null, $GLOBALS['attendance_review_status']);
shifts_list_row(trans("Default Shift :"), 'default_shift_id', null, true, null);
check_row(trans("Shift Spans Midnight :"), 'shift_spans_midnight');
countries_list_row(trans("Home Country :"), 'home_country', null, true, null, 1);
array_selector_row (trans("Weekends:"), 'weekends', null,  [
	"1"=>"Monday",
	"2"=>"Tuesday",
	"3"=>"Wednesday",
	"4"=>"Thursday",
	"5"=>"Friday",
	"6"=>"Saturday",
	"7"=>"Sunday"
],['multi'=>true]);
customer_list_row(trans("Staff Mistake Customer:"), 'staff_mistake_customer_id', null, 'None', false, false, true);
text_row(trans("Overtime Rate:"), 'overtime_rate', null, 6, 6, '', "","%");
payelements_list_row(trans("Overtime Salary Elements"), 'overtime_salary_elements', null, false, null, 1, false, true, null, null, $is_fixed = 1);

($_SESSION['wa_current_user']->is_developer_session)
    ? text_row(trans("Payroll Cutoff Date:"), 'payroll_cutoff', null, 6, 6, '', "")
    : hidden('payroll_cutoff');

text_row(trans("Public Holiday Rate:"), 'public_holiday_rate', null, 6, 6, '', "","%");
payelements_list_row(trans("Holiday Salary Elements"), 'holidays_salary_elements', null, false, null, 1, false, true, null, null, $is_fixed = 1);
text_row(trans("Shift Tolerence:"), 'shift_tolerance', null, 6, 6, '', "",trans("Hours"));
text_row(trans("Early Going Rate:"), 'earlygoing_rate', null, 6, 6, '', "","%");
text_row(trans("Duplicate punch interval:"), 'duplicate_punch_interval', null, 6, 6, '', "","Minutes");
text_row(trans("Working-Hours grace time:"), 'working_hours_grace_time', null, 6, 6, '', "","Minutes");
text_row(trans("Late-in grace time:"), 'late_in_grace_time', null, 6, 6, '', "","Minutes");
text_row(trans("Early-out grace time:"), 'early_out_grace_time', null, 6, 6, '', "","Minutes");
text_row(trans("Absent when late-in exceeds:"), 'absent_when_late_in_exceeds_min', null, 6, 6, '', "","Minutes");
text_row(trans("Absent when early-out exceeds:"), 'absent_when_early_out_exceeds_min', null, 6, 6, '', "","Minutes");

start_row();
label_cell(trans('Count missing-punch as:'), 'class="label"');
echo '<td>';
echo array_selector('count_missing_punch_as', null, missing_punch_options());
echo text_input('value_count_missing_punch_as', get_post('value_count_missing_punch_as'), 6, 6) . ' Fixed or %';
$GLOBALS['Ajax']->addUpdate('value_count_missing_punch_as', 'value_count_missing_punch_as', get_post('value_count_missing_punch_as'));
echo '</td>';
end_row();

text_row(trans("Overtime grace time:"), 'overtime_grace_time', null, 6, 6, '', "", "Minutes");
array_selector_row(trans("Overtime algorithm:"), 'overtime_algorithm', null, overtime_algorithms());
array_selector_row(trans("Default overtime status:"), 'default_overtime_status', null, $GLOBALS['attendance_review_status']);
text_row(trans("Round overtime to nearest:"), 'overtime_round_to', null, 6, 6, '', "", "Hr");
array_selector_row(trans("Overtime rounding algorithm:"), 'overtime_rounding_algorithm', null, overtime_rounding_algorithms());

text_row(trans("Late Hour Rate:"), 'latehour_rate', null, 6, 6, '', "","%");
text_row(trans("Standard Days:"), 'standard_days', null, 6, 6, '', "", trans("days"));
text_row(trans("Standard Work Hours:"), 'standard_workhours', null, 6, 6, '', "", trans("Hours"));
text_row(trans("Weekend Rate:"), 'weekend_rate', null, 6, 6, '', "","%");

designations_list_row(trans('HR Executive Position'), 'designation_hr');
gl_all_accounts_list_row(trans("Default. Salary Payable Account:"), 'default_salary_payable_account', null, false, false, '-- select --');
gl_all_accounts_list_row(trans("Pension Expense Account:"), 'pension_expense_account', null, false, false, '-- select --');
array_selector_row(trans("Personal Timeout Calculation:"), 'personal_timeout_calculation_method', null, personal_timeout_calculation_methods());
check_row(trans("Generate Auto Journal For Deduction Entry :"), 'auto_journal_deduction_entry');

table_section_title(trans("Departments"));

departments_list_row(trans("Amer Department"), 'dep_amer', null, true, null, 1);
departments_list_row(trans("Tadbeer Department"), 'dep_tadbeer', null, true, null, 1);
departments_list_row(trans("Tasheel Department"), 'dep_tasheel', null, true, null, 1);
departments_list_row(trans("Tawjeeh Department"), 'dep_tawjeeh', null, true, null, 1);


table_section(2);

hidden('gpssa_employee_share', 0);
foreach (GCC_COUNTRIES as $code => $name) {
	hidden('gpssa_employee_share_'.strtolower($code), 0);
}

hidden('gpssa_employer_share', 0);
foreach (GCC_COUNTRIES as $code => $name) {
	hidden('gpssa_employer_share_'.strtolower($code), 0);
}

table_section_title(trans("Elements"));

payelements_list_row(trans("Basic Pay Element"), 'basic_pay_el', null, true, null, 1, false, false, null, PayElement::TYPE_ALLOWANCE);
payelements_list_row(trans("Housing Alw Element"), 'housing_alw_el', null, true, null, 1, false, false, null, PayElement::TYPE_ALLOWANCE);
payelements_list_row(trans("Days Not Worked Element"), 'days_not_worked_el', null, true, null, 1, false, false, null, PayElement::TYPE_DEDUCTION);
payelements_list_row(trans("Holidays Worked Element"), 'holidaysworked_el', null, true, null, 1, false, false, null, PayElement::TYPE_ALLOWANCE);
payelements_list_row(trans("Weekends Worked Element"), 'weekendsworked_el', null, true, null, 1, false, false, null, PayElement::TYPE_ALLOWANCE);
payelements_list_row(trans("Leaves Element"), 'leaves_el', null, true, null, 1, false, false, null, PayElement::TYPE_DEDUCTION);
payelements_list_row(trans("Absence Element"), 'absence_el', null, true, null, 1, false, false, null, PayElement::TYPE_DEDUCTION);
payelements_list_row(trans("Commission Element"), 'commission_el', null, true, null, 1, false, false, null, PayElement::TYPE_ALLOWANCE);
payelements_list_row(trans("Overtime Element"), 'overtime_el', null, true, null, 1, false, false, null, PayElement::TYPE_ALLOWANCE);
payelements_list_row(trans("Released Holded Salary Element"), 'released_holded_salary_el', null, true, null, 1, false, false, null, PayElement::TYPE_ALLOWANCE);
payelements_list_row(trans("Holded salary Element"), 'holded_salary_el', null, true, null, 1, false, false, null, PayElement::TYPE_DEDUCTION);
payelements_list_row(trans("Staff Mistake Element"), 'staff_mistake_el', null, true, null, 1, false, false, null, PayElement::TYPE_DEDUCTION);
payelements_list_row(trans("Pension Element"), 'pension_el', null, true, null, 1, false, false, null, PayElement::TYPE_DEDUCTION);
payelements_list_row(trans("Early Leave Element"), 'earlyleaving_el', null, true, null, 1, false, false, null, PayElement::TYPE_DEDUCTION);
payelements_list_row(trans("Late Coming Element"), 'latecoming_el', null, true, null, 1, false, false, null, PayElement::TYPE_DEDUCTION);
payelements_list_row(trans("Violation Element"), 'violations_el', null, true, null, 1, false, false, null, PayElement::TYPE_DEDUCTION);
payelements_list_row(trans("Advance Recovery Element"), 'advance_recovery_el', null, true, null, 1, false, false, null, PayElement::TYPE_DEDUCTION);
payelements_list_row(trans("Loan Recovery Element"), 'loan_recovery_el', null, true, null, 1, false, false, null, PayElement::TYPE_DEDUCTION);
payelements_list_row(trans("Rewards / Bonus Element"), 'rewards_bonus_el', null, true, null, 1, false, false, null, PayElement::TYPE_ALLOWANCE);


// percent_row(trans("Invoice Over-Charge Allowance:"), 'po_over_charge');



end_outer_table(1);

submit_center('submit', trans("Update"), true, '', 'default');

end_form(2);

//-------------------------------------------------------------------------------------------------

end_page();

