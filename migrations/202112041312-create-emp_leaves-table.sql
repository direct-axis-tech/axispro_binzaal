CREATE TABLE `0_emp_leaves` (
    `id` BIGINT(8) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `employee_id` BIGINT(8) NOT NULL,
    `leave_type_id` SMALLINT(2) NOT NULL,
    `days` FLOAT NOT NULL,
    `from` DATE NOT NULL,
    `till` DATE NOT NULL,
    `requested_on` DATE NOT NULL,
    `status` CHAR(1) NOT NULL,
    `reviewed_on` DATE NULL DEFAULT NULL,
    `reviewed_by` SMALLINT(6) NULL DEFAULT NULL,
    `memo` VARCHAR(255) NOT NULL DEFAULT '',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP(),
    UNIQUE KEY `uniq_leave_request` (`employee_id`, `from`, `till`),
    KEY `leave_type` (`leave_type_id`),
    KEY `status` (`status`),
    KEY `reviewed_by` (`reviewed_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;