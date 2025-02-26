CREATE TABLE `0_emp_salary_details` (
  `id` bigint(8) NOT NULL AUTO_INCREMENT,
  `salary_id` bigint(8) NOT NULL,
  `pay_element_id` smallint(2) NOT NULL,
  `amount` decimal(8,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `salary_id` (`salary_id`),
  KEY `pay_element_id` (`pay_element_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;