ALTER TABLE `0_payslips`
    ADD `iban_no` varchar(35) NULL DEFAULT NULL AFTER `designation_id`,
    ADD `bank_id` mediumint(3) NULL DEFAULT NULL AFTER `designation_id`,
    ADD `mode_of_pay` char(1) NOT NULL AFTER `designation_id`;
