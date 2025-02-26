INSERT INTO `0_sys_prefs` (`name`,`category`,`type`, `length`, `value`) VALUES
('days_not_worked_el', 'setup.hr', 'smallint', '2', '17');

AlTER TABLE
    `0_payslips`
MODIFY COLUMN `work_hours` DECIMAL(4,2) NOT NULL,
MODIFY COLUMN days_absent DECIMAL(4,2) NOT NULL DEFAULT 0,
ADD `days_not_worked` TINYINT(1) NOT NULL DEFAULT 0 AFTER `work_hours`;