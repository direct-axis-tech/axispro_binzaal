ALTER TABLE `0_employees`
    ADD `cancel_remarks` varchar(255) NULL AFTER `emirates_id`,
    ADD `cancel_approved_by` smallint(6) NULL AFTER `emirates_id`,
    ADD `cancel_leaving_on` date NULL AFTER `emirates_id`,
    ADD `cancel_requested_on` date NULL AFTER `emirates_id`;