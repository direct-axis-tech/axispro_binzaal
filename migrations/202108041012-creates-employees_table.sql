CREATE TABLE `0_employees` (
  `id` bigint(8) NOT NULL AUTO_INCREMENT,
  `emp_ref` varchar(15) NOT NULL,
  `machine_id` varchar(15) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `ar_name` varchar(100) DEFAULT NULL,
  `nationality` char(2) DEFAULT NULL,
  `gender` char(1) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `blood_group` varchar(3) DEFAULT NULL,
  `marital_status` char(1) DEFAULT NULL,
  `email` varchar(50) DEFAULT NULL,
  `mobile_no` varchar(15) DEFAULT NULL,
  `date_of_join` date DEFAULT NULL,
  `mode_of_pay` char(1) DEFAULT NULL,
  `bank_id` mediumint(3) unsigned DEFAULT NULL,
  `iban_no` varchar(35) DEFAULT NULL,
  `file_no` varchar(50) DEFAULT NULL,
  `uid_no` varchar(50) DEFAULT NULL,
  `passport_no` varchar(50) DEFAULT NULL,
  `personal_id_no` varchar(50) DEFAULT NULL,
  `labour_card_no` varchar(50) DEFAULT NULL,
  `emirates_id` varchar(50) DEFAULT NULL,
  `status` tinyint(1) DEFAULT 1,
  `updated_by` smallint(6) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `emp_ref` (`emp_ref`),
  UNIQUE KEY `machine_id` (`machine_id`),
  UNIQUE KEY `iban_no` (`iban_no`),
  UNIQUE KEY `file_no` (`file_no`),
  UNIQUE KEY `uid_no` (`uid_no`),
  UNIQUE KEY `passport_no` (`passport_no`),
  UNIQUE KEY `personal_id_no` (`personal_id_no`),
  UNIQUE KEY `labour_card_no` (`labour_card_no`),
  UNIQUE KEY `emirates_id` (`emirates_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;