INSERT INTO `0_sys_prefs` (`name`,`category`,`type`, `length`, `value`) VALUES
('collect_processing_chg_frm_cust', 'setup.company', 'bool', '1', '1');

ALTER TABLE `0_debtor_trans` ADD processing_fee double NOT NULL DEFAULT 0 AFTER ov_discount;