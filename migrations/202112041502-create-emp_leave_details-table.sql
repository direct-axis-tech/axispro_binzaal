CREATE TABLE `0_emp_leave_details` (
    `id` BIGINT(8) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `leave_id` BIGINT(8) NOT NULL,
    `employee_id` BIGINT(8) NOT NULL,
    `leave_type_id` SMALLINT(2) NOT NULL,
    `type` SMALLINT(1) NOT NULL DEFAULT -1,
    `days` FLOAT NOT NULL,
    `date` DATE NOT NULL,
    `is_cancelled` BOOLEAN NOT NULL DEFAULT 0,
    UNIQUE KEY `uniq_leave` (`employee_id`, `leave_type_id`, `date`, `type`),
    KEY `leave_type` (`leave_type_id`),
    KEY `date` (`date`),
    KEY `type` (`type`),
    KEY `leave_id` (`leave_id`),
    KEY `is_cancelled` (`is_cancelled`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;