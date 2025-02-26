CREATE TABLE `0_payslip_elements` (
    `id` BIGINT(8) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `payslip_id` BIGINT(8) NOT NULL,
    `pay_element_id` SMALLINT(2) NOT NULL,
    `amount` DECIMAL(8,2) NOT NULL,
    KEY `payslip_id` (`payslip_id`),
    KEY `pay_element_id` (`pay_element_id`),
    UNIQUE KEY `uniq_payslip_elem` (`payslip_id`, `pay_element_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;