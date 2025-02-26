ALTER TABLE `0_employees`
    ADD `status_updated_at` TIMESTAMP NULL DEFAULT NULL AFTER `status`,
    ADD `status_updated_by` SMALLINT(6) NULL DEFAULT NULL  AFTER `status`,
    ADD `cancellation_reason` VARCHAR(255) NULL DEFAULT NULL AFTER `status`;