ALTER TABLE 0_emp_salaries
    ADD `from` DATE NULL DEFAULT NULL AFTER job_id,
    ADD `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER is_current;

UPDATE 0_emp_salaries sal
LEFT JOIN 0_emp_jobs job ON job.id = sal.job_id
SET sal.`from` = job.commence_from;

ALTER TABLE 0_emp_salaries MODIFY `from` DATE NOT NULL;