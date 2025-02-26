<?php

$path_to_root = "../..";

include($path_to_root . "/includes/db_pager.inc");
include_once $path_to_root . "/includes/session.inc";
include_once $path_to_root . "/hrm/db/employees_db.php";
include_once $path_to_root . "/hrm/db/hold_salary_db.php";

$page_security = 'HRM_HOLDED_EMP_SALARY';

page(trans($help_context = "Employee Holded Salary"), false, false, "", "");
    echo('<h1 class="h3 mb-5">Holded Slaries of Employees</h1>');

    start_form();
        function gl_link($row)
        {
            return '<a class="kt-link" href="../../hrm/employees/holded_emp_salary_release.php?id_no='.$row['id'].'"> Release </a>';
        }

        $sql = get_sql_for_view_holded_salary(null);

        $cols = array(
            trans("ID") =>array('name'=>'id'),
            trans("Empolyee Name.") => array('name'=>'formatted_name'),
            trans("Transaction Date") =>array('name'=>'trans_date','type'=>'date'),
            trans("Amount (Holded)") => array('name'=>'amount', 'align' => 'center'),
            trans("Memo") => array('name'=>'memo'),
            trans("Release") => array('fun'=>'gl_link')
        );

        $table =& new_db_pager('holded_salary', $sql, $cols);
        $table->width = "80%";

        display_db_pager($table);
    end_form();
    
end_page();