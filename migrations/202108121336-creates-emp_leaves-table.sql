CREATE TABLE `0_emp_leaves` (
    `id` BIGINT(8) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `employee_id` BIGINT(8) NOT NULL,
    `date` DATE NOT NULL,
    `leave_id` TINYINT(1) NOT NULL,
    `is_taken` TINYINT(1) DEFAULT 0,
    `approved_by` SMALLINT(6) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP(),
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP(),
    KEY `employee_id` (`employee_id`),
    KEY `date` (`date`),
    KEY `leave_id` (`leave_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;