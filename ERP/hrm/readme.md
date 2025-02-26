## Employees
* All the employees can have their job history saved in the `emp_jobs` table
* Currently the job is being inserted only when there is a change in the department, designation
* All the jobs have a `is_current` flag which indicates wether it is the the currently active job
* All jobs can have multiple salary which enables the history of salary increment/decrement
* The salary: similar to the jobs, also have an `is_current` flag which serves the same purpose
* For all the employee the `joining_date`, `current_job`, `current_salary` must be set inorder to process the payroll for her/him correctly

## Time sheet
* A processed version of employee's punch-in and punch-out is being stored on the `attendance` table
* If the employee's attendance needs to be verified, his `machine_id` must be configured correctly
* Previously the `Off` was also recorded using the `attendance` table as a temporary solution. However at present - the valid values are `Present` or `Not Present`
* The ***authorozed*** person can override the punching.
* `Offs` *MUST NOT* be updated from timesheet as it is discarded
* At present, The offs are calculated as follows
    * If the shift is defined as it is `Off` then `Off` ie. weekly off
    * If the shift is not defined and it is default weekend e.g. Friday then `Off`
    * If the shift is not defined and it is a public holiday then `Off`

## Attendance Metrics
* Based on the presence of the employee his metrics are calculated.
* The metrics include the *late coming*, *early_leaving* and general *overtime*

## Shift
* The master shift is stored in `shifts` table
* The employees schedule is stored in `emp_shifts` table
* When schedule is being inserted the offs and public holidays are calculated as defined in the previous section.
* If the employee's `default_shift_id` is defined for the job, it is prefilled for ease of use

## Leaves
* The module is still simple and there is no validation at this point for the limits
* ***Authorized*** people can add leave for a selected employee
* No two leaves are allowed on the same day
* The master leaves is stored on the `leaves` table, However this is constant and not modifiable
* Each employees's leaves are store on two seperate table
    1. `emp_leaves` table, which store the semantic leave. eg. 1<sup>st</sup> leave from 10<sup>th</sup> Jan till 20<sup>th</sup> Jan for 10 days
    2. `emp_leave_details` table, which store the leave per day. eg. 10<sup>th</sup> Jan on his 1<sup>st</sup> leave, 11th Jan on his 1<sup>st</sup> leave etc.

## Commission
* Commissions are calculated as per the system, using Employee Sales report and Adheed Emp Commission Report

## Staff Mistake
* The mistakes are calculated from `debtor_trans` table
* All the staff mistakes are under a highlevel customer configured using `staff_mistake_customer_id` in the `sys_prefs` table
* Unique employee is identified using his `employee_id` under the *staff mistake customer*
* For each employee the mistake amount is the `sum(total)` minus the `sum(allocated)`
* As the staff mistake can be on any date, we are currently not considering the payroll period for staff mistake and the *staff mistake customer* MUST be perfectly maintained to avoid any mistake

## Payroll
* The payroll is calculated from the `cutoff` date. If the `cutoff` is not defined it uses the 1<sup>st</sup> of the month till the last of the month as payroll period
* The payroll once generated is cached in the `payrolls` table for future use until it is processed. So any update to *Shift*, *Attendance*, *Salary*, *Job*, *Leaves* etc. after the payroll is generated is cached, require the payroll to be regenerated - for it to reflect the changes.
* The payroll once generated - can be regenerated using the *Regenerate the Payroll* button
* Once the payroll is processed all the payslips under it is immutable afterwards.

    ### Payslips
    * A payroll consist of multiple payslips.
    * All the payslips are stored in `payslips` table
    * A payslip can be processed individually or by batch.
    * Once a payslip is processed, The *Shift*, *Attendance*, *Salary*, *Job*, *Leaves*, & *Attendance metrices* are all immutable. and no modification is allowed afterwards

    ### Payslip Details
    * The details of each payslip is stored in `payslip_details` table.
    * It includes all the details on a per day basis and amount

    ### Payslip Elements
    * The payslip elements are the constituant amounts which makes up the whole salary
    * it is stored in the `payslip_elements` table.