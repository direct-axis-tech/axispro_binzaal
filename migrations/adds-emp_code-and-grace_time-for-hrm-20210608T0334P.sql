ALTER TABLE `0_kv_empl_info` ADD emp_code varchar(20) NULL AFTER empl_id;
INSERT INTO `0_sys_prefs` (name,category,`type`,`length`, value)
	VALUES ('payroll_grace_time','setup.axispro','int',11, 10);

-- Reverse
-- ALTER TABLE `0_kv_empl_info` DROP emp_code;
-- DELETE FROM `0_sys_prefs` WHERE `name` = 'payroll_grace_time';