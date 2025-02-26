/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

DROP TABLE IF EXISTS `0_kv_empl_attendance`;
CREATE TABLE IF NOT EXISTS `0_kv_empl_attendance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `empl_id` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `code` varchar(5) COLLATE utf8_unicode_ci NOT NULL,
  `shift` int(11) NOT NULL,
  `salary_ded` int(11) NOT NULL,
  `leave_id` int(11) NOT NULL,
  `a_date` date NOT NULL,
  `in_time` time NOT NULL,
  `out_time` time NOT NULL,
  `dimension` int(11) NOT NULL DEFAULT 0,
  `dimension2` int(11) NOT NULL DEFAULT 0,
  `duration` int(11) NOT NULL,
  `ot` int(11) NOT NULL,
  `sot` int(11) NOT NULL,
  `description` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `inactive` tinyint(1) NOT NULL DEFAULT 0,
  `upload_master_id` int(8) NOT NULL DEFAULT 0,
  `manual_sync` enum('1','0') COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `remarks` text COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

 

DROP TABLE IF EXISTS `0_kv_empl_certificate_request`;
CREATE TABLE IF NOT EXISTS `0_kv_empl_certificate_request` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `request_ref_no` varchar(50) DEFAULT NULL,
  `certifcate_name` varchar(50) DEFAULT NULL,
  `bank` varchar(50) DEFAULT NULL,
  `iban` varchar(50) DEFAULT NULL,
  `branch` varchar(50) DEFAULT NULL,
  `empl_id` int(11) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `address_to` varchar(75) DEFAULT NULL,
  `comments` text DEFAULT NULL,
  `req_status` int(11) DEFAULT NULL,
  `level` int(11) DEFAULT NULL,
  `role_id` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `request_date` date DEFAULT NULL,
  `status` int(11) DEFAULT NULL,
  `hr_status` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*!40000 ALTER TABLE `0_kv_empl_certificate_request` DISABLE KEYS */;
/*!40000 ALTER TABLE `0_kv_empl_certificate_request` ENABLE KEYS */;

DROP TABLE IF EXISTS `0_kv_empl_country`;
CREATE TABLE IF NOT EXISTS `0_kv_empl_country` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `iso` varchar(50) DEFAULT NULL,
  `local_name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

 

DROP TABLE IF EXISTS `0_kv_empl_docu_request_details`;
CREATE TABLE IF NOT EXISTS `0_kv_empl_docu_request_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `empl_id` int(11) DEFAULT NULL,
  `doc_type` int(11) DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `doc_req_date` date DEFAULT NULL,
  `document_name` varchar(100) DEFAULT NULL,
  `status` int(11) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `approved_on` datetime DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `active` int(11) DEFAULT NULL,
  `request_forward_to` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*!40000 ALTER TABLE `0_kv_empl_docu_request_details` DISABLE KEYS */;
/*!40000 ALTER TABLE `0_kv_empl_docu_request_details` ENABLE KEYS */;

DROP TABLE IF EXISTS `0_kv_empl_holiday_approved`;
CREATE TABLE IF NOT EXISTS `0_kv_empl_holiday_approved` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `holiday_id` int(11) NOT NULL DEFAULT 0,
  `empl_id` int(11) NOT NULL DEFAULT 0,
  `date` date DEFAULT NULL,
  `pay_option` int(11) DEFAULT NULL,
  `payroll_id` int(11) DEFAULT NULL,
  `created_on` date DEFAULT NULL,
  `created_by` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*!40000 ALTER TABLE `0_kv_empl_holiday_approved` DISABLE KEYS */;
/*!40000 ALTER TABLE `0_kv_empl_holiday_approved` ENABLE KEYS */;

DROP TABLE IF EXISTS `0_kv_empl_late_coming_days`;
CREATE TABLE IF NOT EXISTS `0_kv_empl_late_coming_days` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` date DEFAULT NULL,
  `laps_time` varchar(50) DEFAULT NULL,
  `empl_id` varchar(50) DEFAULT NULL,
  `year` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*!40000 ALTER TABLE `0_kv_empl_late_coming_days` DISABLE KEYS */;
/*!40000 ALTER TABLE `0_kv_empl_late_coming_days` ENABLE KEYS */;

DROP TABLE IF EXISTS `0_kv_empl_leave_applied`;
CREATE TABLE IF NOT EXISTS `0_kv_empl_leave_applied` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `request_ref_no` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `year` int(5) NOT NULL,
  `empl_id` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `leave_type` varchar(5) COLLATE utf8_unicode_ci NOT NULL,
  `reason` text COLLATE utf8_unicode_ci NOT NULL,
  `date` date NOT NULL,
  `t_date` date NOT NULL,
  `role_id` int(11) NOT NULL DEFAULT 0,
  `level` int(11) NOT NULL DEFAULT 0,
  `req_status` int(11) NOT NULL DEFAULT 0,
  `request_date` date DEFAULT NULL,
  `days` int(3) NOT NULL,
  `allowed_paid_leaves` int(3) NOT NULL,
  `Column 15` int(3) NOT NULL,
  `full_day_salary_cut` int(3) NOT NULL,
  `half_day_salary_cut` int(3) NOT NULL,
  `filename` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  `task_assigned` text COLLATE utf8_unicode_ci DEFAULT NULL,
  `leave_days_reduce_cmnt` text COLLATE utf8_unicode_ci DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 0,
  `created_by` int(11) NOT NULL DEFAULT 0,
  `del_status` int(11) NOT NULL DEFAULT 0,
  `half_full_day` int(11) NOT NULL DEFAULT 0,
  `sick_leave_doc` varchar(150) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

 

DROP TABLE IF EXISTS `0_kv_empl_mail_send_log`;
CREATE TABLE IF NOT EXISTS `0_kv_empl_mail_send_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `empl_id` varchar(50) NOT NULL DEFAULT '0',
  `type` varchar(50) NOT NULL DEFAULT '0',
  `date` date DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

 

DROP TABLE IF EXISTS `0_kv_empl_payroll_details`;
CREATE TABLE IF NOT EXISTS `0_kv_empl_payroll_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `payslip_id` int(11) NOT NULL DEFAULT 0,
  `gl_trans_id` int(11) NOT NULL DEFAULT 0,
  `trans_date` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `empl_id` int(11) NOT NULL DEFAULT 0,
  `leave_days` int(11) NOT NULL DEFAULT 0,
  `ot_hours` double NOT NULL DEFAULT 0,
  `ot_amount` double NOT NULL DEFAULT 0,
  `pf_amount` double NOT NULL DEFAULT 0,
  `days_worked` int(11) NOT NULL DEFAULT 0,
  `weakend` int(11) NOT NULL DEFAULT 0,
  `salary_amount` double NOT NULL DEFAULT 0,
  `net_salary_payable` double NOT NULL DEFAULT 0,
  `processed_salary` double NOT NULL DEFAULT 0,
  `created_on` datetime NOT NULL,
  `created_by` int(11) NOT NULL,
  `salary_process_date` datetime NOT NULL,
  `processed_by` int(11) NOT NULL DEFAULT 0,
  `memo` text COLLATE utf8_unicode_ci DEFAULT NULL,
  `transaction_type` text COLLATE utf8_unicode_ci DEFAULT NULL,
  `tot_salary_deduction` double DEFAULT NULL,
  `commission` double DEFAULT NULL,
  `net_commission` double DEFAULT NULL,
  `loan_amount` double DEFAULT NULL,
  `advance_amount` double DEFAULT NULL,
  `absent_hours` double DEFAULT NULL,
  `absent_ded_amount_hrs` double DEFAULT NULL,
  `leave_absent_deduction` double DEFAULT NULL,
  `payroll_porcessed` int(11) DEFAULT NULL,
  `tot_salary_payable` double DEFAULT NULL,
  `anual_leave_salary` double DEFAULT NULL,
  `late_coming_deduction_minutes` double DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*!40000 ALTER TABLE `0_kv_empl_payroll_details` DISABLE KEYS */;
/*!40000 ALTER TABLE `0_kv_empl_payroll_details` ENABLE KEYS */;

DROP TABLE IF EXISTS `0_kv_empl_payroll_elements`;
CREATE TABLE IF NOT EXISTS `0_kv_empl_payroll_elements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `payslip_detail_id` int(11) NOT NULL DEFAULT 0,
  `pay_element` varchar(75) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `amount` varchar(75) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `war_ded_desc` text COLLATE utf8_unicode_ci DEFAULT NULL,
  `loan_ded_desc` text COLLATE utf8_unicode_ci DEFAULT NULL,
  `holiday_ids` text COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*!40000 ALTER TABLE `0_kv_empl_payroll_elements` DISABLE KEYS */;
/*!40000 ALTER TABLE `0_kv_empl_payroll_elements` ENABLE KEYS */;

DROP TABLE IF EXISTS `0_kv_empl_payroll_master`;
CREATE TABLE IF NOT EXISTS `0_kv_empl_payroll_master` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `payslip_id` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `pay_year` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `pay_month` int(11) DEFAULT NULL,
  `dept_id` int(11) DEFAULT NULL,
  `payslip_status` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `payroll_from_date` date DEFAULT NULL,
  `payroll_to_date` date DEFAULT NULL,
  `created_on` datetime NOT NULL,
  `created_by` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*!40000 ALTER TABLE `0_kv_empl_payroll_master` DISABLE KEYS */;
/*!40000 ALTER TABLE `0_kv_empl_payroll_master` ENABLE KEYS */;

DROP TABLE IF EXISTS `0_kv_empl_shiftdetails`;
CREATE TABLE IF NOT EXISTS `0_kv_empl_shiftdetails` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shift_id` int(11) DEFAULT NULL,
  `dept_id` int(11) DEFAULT NULL,
  `empl_id` int(11) DEFAULT NULL,
  `from` date DEFAULT NULL,
  `to` date DEFAULT NULL,
  `s_date` date DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `unassign_date` datetime DEFAULT NULL,
  `unassign_by` int(11) DEFAULT NULL,
  `assign_status` int(11) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

 

DROP TABLE IF EXISTS `0_kv_empl_shifts`;
CREATE TABLE IF NOT EXISTS `0_kv_empl_shifts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `description` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  `BeginTime` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `EndTime` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `dimension` int(11) NOT NULL,
  `inactive` tinyint(1) NOT NULL DEFAULT 0,
  `shift_color` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

 

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
