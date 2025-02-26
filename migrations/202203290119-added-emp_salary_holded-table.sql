CREATE TABLE `0_emp_salary_holded` (
  `id` bigint(8) NOT NULL AUTO_INCREMENT,
  `employee_id` int(4) NOT NULL,
  `for_month` date DEFAULT NULL,
  `amount` decimal(8,2) DEFAULT 0.00,
  `memo` varchar(500) DEFAULT NULL,
  `is_released` tinyint(1) DEFAULT 0,
  `released_date` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `modified_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
);