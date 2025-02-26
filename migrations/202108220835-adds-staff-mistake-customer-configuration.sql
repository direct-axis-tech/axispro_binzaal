INSERT INTO `0_sys_prefs` (`name`,`category`,`type`, `length`, `value`) VALUES
('staff_mistake_customer_id', 'setup.hr', 'smallint', '6', '998');
ALTER TABLE `0_debtor_trans` ADD `mistook_staff_id` BIGINT NULL DEFAULT NULL AFTER `debtor_no`;