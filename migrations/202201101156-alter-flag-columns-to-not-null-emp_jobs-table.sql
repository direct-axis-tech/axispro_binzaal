ALTER TABLE `0_emp_jobs`
    MODIFY `has_commission` tinyint(1) NOT NULL DEFAULT 1,
    MODIFY `has_pension` tinyint(1) NOT NULL DEFAULT 0,
    MODIFY `has_overtime` tinyint(1) NOT NULL DEFAULT 1,
    MODIFY `require_attendance` tinyint(1) NOT NULL DEFAULT 1;