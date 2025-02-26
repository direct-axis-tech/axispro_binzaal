CREATE TABLE `0_emp_salaries` (
  `id` bigint(8) NOT NULL AUTO_INCREMENT,
  `job_id` bigint(8) NOT NULL,
  `gross_salary` decimal(8,2) NOT NULL,
  `is_current` tinyint(1) NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `current_salary` (`job_id`,`is_current`),
  KEY `job_id` (`job_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;