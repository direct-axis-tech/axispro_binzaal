ALTER TABLE `0_emp_jobs` ADD end_date DATE NULL DEFAULT NULL AFTER `commence_from`;
ALTER TABLE `0_emp_jobs`
    ADD KEY end_date (end_date),
    ADD KEY commence_from (commence_from);