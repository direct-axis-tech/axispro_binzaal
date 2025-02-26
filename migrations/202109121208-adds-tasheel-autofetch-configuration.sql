ALTER TABLE `0_stock_master` ADD UNIQUE KEY (`description`);

INSERT INTO `0_sys_prefs` (`name`,`category`,`type`, `length`, `value`) VALUES
('ts_next_auto_stock_no', 'autofetch.tasheel', 'smallint', '6', '1'),
('ts_auto_stock_category', 'autofetch.tasheel', 'int', '11', '98'),
('ts_auto_stock_sales_acc', 'autofetch.tasheel', 'varchar', '15', '311005'),
('ts_auto_stock_cogs_acc', 'autofetch.tasheel', 'varchar', '15', '411005'),
('ts_auto_govt_bank_acc', 'autofetch.tasheel', 'varchar', '15', '113003'),
('ts_auto_returnable_to', 'autofetch.tasheel', 'varchar', '15', '1210003');
