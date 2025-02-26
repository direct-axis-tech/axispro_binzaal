ALTER TABLE `0_emp_salaries`
    ADD `employee_id` BIGINT(8) NULL DEFAULT NULL AFTER `job_id`;

UPDATE `0_emp_salaries` salary
LEFT JOIN `0_emp_jobs` job ON job.`id` = salary.`job_id`
SET salary.`employee_id` = job.`employee_id`;

ALTER TABLE `0_emp_salaries`
    MODIFY `employee_id` BIGINT(8) NOT NULL,
    DROP INDEX job_id,
    DROP KEY current_salary;

ALTER TABLE `0_emp_salaries`
    ADD UNIQUE KEY current_salary (`employee_id`, `is_current`),
    DROP COLUMN job_id;