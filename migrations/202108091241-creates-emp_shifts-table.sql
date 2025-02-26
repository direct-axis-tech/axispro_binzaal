CREATE TABLE `0_emp_shifts` (
    `id` BIGINT(8) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `employee_id` BIGINT(8) NOT NULL,
    `shift_id` INT(4) NULL,
    `date` DATE NOT NULL,
    `created_by` SMALLINT(6) NOT NULL,
    `updated_by` SMALLINT(6) NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
    `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP(),
    KEY `employee_id` (`employee_id`),
    KEY `shift_id` (`shift_id`),
    KEY `date` (`date`),
    UNIQUE KEY `uniq_shift` (`employee_id`, `date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;