ALTER TABLE `0_debtors_master` 
    ADD cr_lmt_notice_lvl DECIMAL(16,2) DEFAULT 9999999999 NOT NULL AFTER credit_limit,
    ADD cr_lmt_warning_lvl DECIMAL(16,2) DEFAULT 9999999999 NOT NULL;

INSERT INTO `0_sys_prefs` (`name`,category,`type`,`length`,`value`)
	VALUES 
	('dflt_cr_lmt_notice_lvl','setup.customer','int',11, 9999999999),
	('dflt_cr_lmt_warning_lvl','setup.customer','int',11, 9999999999);