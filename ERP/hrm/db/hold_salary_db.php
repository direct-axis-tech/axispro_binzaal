<?php
    
    use App\Models\Hr\EmployeeTransaction; 

    /**
     * Save the employee hold salary into the database
     *
     * @param array $inputs
     * @return void
     */
    function saveEmployeeHoldSalary($inputs)
    {
        $empTrans = new EmployeeTransaction([
            "employee_id" => $inputs['employee_id'],
            "trans_type" => $inputs['trans_type'],
            "year" => $inputs['year'],
            "month" => $inputs['month'],
            "trans_date" => $inputs['trans_date'],
            "amount" => $inputs['hold_salary_amount'],
            "memo" => $inputs['memo'],
            "created_at" => $inputs['created_at'],
            "updated_at" => $inputs['updated_at']
        ]);

        $empTrans->save();
    }

        /**
     * Save/Release holded salary of employee into the database
     *
     * @param array $inputs
     * @return void
     */
    function releaseEmployeeHoldedSalary($inputs)
    {
        $empTrans = new EmployeeTransaction([
            "employee_id" => $inputs['employee_id'],
            "trans_type" => $inputs['trans_type'],
            "year" => $inputs['year'],
            "month" => $inputs['month'],
            "trans_date" => $inputs['trans_date'],
            "amount" => $inputs['amount'],
            "ref_id" => $inputs['id'],
            "created_at" => $inputs['created_at'],
            "updated_at" => $inputs['updated_at']
        ]);

        $empTrans->save();
    }

    /**
     * View/Get employees holded salary from database
     *
     * @param array $inputs
     * @return string
     */
    function get_sql_for_view_holded_salary($filters)
    {
        $where = "";
        $trans_type = ET_HOLDED_SALARY;
     
        if (!empty($filters['id'])) {
            $where .= " AND trans.id = '{$filters['id']}'";
        }

        return $sql = ("SELECT 
            trans.id,
			trans.employee_id,
            trans.trans_type,
			CONCAT(emp.emp_ref, ' - ', emp.name) formatted_name,
			trans.trans_date,
			trans.`year`,
			trans.`month`,
			trans.amount,
			trans.ref_id,
			trans.memo,
			SUM(alloc.amount) partially_released
		FROM 
            0_emp_trans trans
			LEFT JOIN 0_employees emp ON emp.id = trans.employee_id
			LEFT JOIN 0_emp_trans alloc ON trans.id = alloc.ref_id
        WHERE
            trans.trans_type = {$trans_type}
			AND trans.amount > 0 {$where}
		GROUP BY 
            trans.id 
		HAVING 
            SUM(alloc.id IS NULL) > 0 OR (-1 * SUM(alloc.amount) < trans.amount)"
        );
    }