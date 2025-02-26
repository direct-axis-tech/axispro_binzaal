CREATE TABLE `0_emp_contacts` (
  `id` bigint(8) NOT NULL AUTO_INCREMENT,
  `employee_id` bigint(8) NOT NULL,
  `tag` varchar(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `dialing_code` varchar(10) NOT NULL,
  `number` varchar(15) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_id` (`employee_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;