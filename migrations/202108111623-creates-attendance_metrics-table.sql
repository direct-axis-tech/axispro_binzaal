CREATE TABLE `0_attendance_metrics` (
    `id` BIGINT(8) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `employee_id` BIGINT(8) NOT NULL,
    `date` DATE NOT NULL,
    `type` CHAR(1) NOT NULL COMMENT "O-Overtime, L-Late, S-Short",
    `minutes` SMALLINT(2) NOT NULL,
    `amount` DECIMAL(8,2) DEFAULT 0.00,
    `status` CHAR(1) NOT NULL COMMENT "P-Pending, V-Verified, I-Ignored",
    `reviewed_by` SMALLINT(6) NULL DEFAULT NULL,
    `reviewed_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP(),
    KEY `employee_id` (`employee_id`),
    KEY `date` (`date`),
    KEY `type` (`type`),
    KEY `status` (`status`),
    UNIQUE KEY uniq_metric (`employee_id`, `date`, `type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;