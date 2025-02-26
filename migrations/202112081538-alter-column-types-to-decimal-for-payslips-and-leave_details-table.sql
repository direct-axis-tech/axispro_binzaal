ALTER TABLE `0_payslips` MODIFY COLUMN days_absent decimal(4,2) DEFAULT 0 NULL;
ALTER TABLE `0_emp_leave_details` MODIFY COLUMN days decimal(6,2) NOT NULL;