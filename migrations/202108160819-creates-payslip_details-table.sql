CREATE TABLE `0_payslip_details` (
    `id` BIGINT(8) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `payslip_id` BIGINT(8) NOT NULL,
    `key` VARCHAR(25) NOT NULL,
    `date` DATE NOT NULL,
    `unit` VARCHAR(10) NOT NULL,
    `measure` DECIMAL(8,2) NOT NULL,
    `amount` DECIMAL(8,2) NOT NULL,
    KEY `payslip_id` (`payslip_id`),
    KEY `column_name` (`key`),
    UNIQUE KEY uniq_detail (`payslip_id`, `key`, `date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;