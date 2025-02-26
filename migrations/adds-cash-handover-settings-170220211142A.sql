INSERT INTO `0_sys_prefs` (`name`,`category`,`type`,`length`,`value`)
	VALUES ('cash_handover_round_off_adj_act','glsetup.general','varchar',15,'3210001');

ALTER TABLE `0_cash_handover_requests` CHANGE gl_balance cash_in_hand decimal(8,2) NOT NULL;

ALTER TABLE `0_users` ADD cash_handover_dr_act VARCHAR(15) NULL DEFAULT NULL;

UPDATE `0_users` SET cash_handover_dr_act = '1110010';
