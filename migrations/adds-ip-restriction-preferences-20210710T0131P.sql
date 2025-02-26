INSERT INTO `0_sys_prefs` (`name`, `category`, `type`, `length`, `value`) VALUES ('ip_restriction', 'setup.axispro', 'tinyint', 1, '1');
INSERT INTO `0_sys_prefs` (`name`, `category`, `type`, `length`, `value`) VALUES ('org_ip', 'setup.axispro', 'varchar', 255, '217.165.129.172');
ALTER TABLE `0_users` ADD ip_restriction TINYINT(1) NOT NULL DEFAULT 1;