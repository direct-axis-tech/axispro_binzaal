START TRANSACTION;

ALTER TABLE `0_voided_supp_allocations` DROP INDEX person_id, ADD `stamp` timestamp, ADD `updated_by` SMALLINT(6) NOT NULL DEFAULT -2;
ALTER TABLE `0_voided_cust_allocations` DROP INDEX id, DROP INDEX person_id, ADD `_id` BIGINT UNSIGNED auto_increment NOT NULL PRIMARY KEY FIRST, ADD `stamp` timestamp, ADD `updated_by` SMALLINT(6) NOT NULL DEFAULT -2;
ALTER TABLE `0_cust_allocations` ADD stamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP NOT NULL, ADD `updated_by` SMALLINT(6) NOT NULL DEFAULT -1;
ALTER TABLE `0_supp_allocations` ALTER date_alloc SET DEFAULT '1970-01-02 00:00:01', ADD stamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP NOT NULL, ADD `updated_by` SMALLINT(6) NOT NULL DEFAULT -1;

UPDATE `0_cust_allocations` SET stamp = '1970-01-02 00:00:01';
UPDATE `0_supp_allocations` SET stamp = '1970-01-02 00:00:01';
UPDATE `0_voided_cust_allocations` SET stamp = '1970-01-02 00:00:00';
UPDATE `0_voided_supp_allocations` SET stamp = '1970-01-02 00:00:00';

ALTER TABLE `0_voided_supp_allocations` ALTER updated_by DROP DEFAULT;
ALTER TABLE `0_voided_cust_allocations` ALTER updated_by DROP DEFAULT;
ALTER TABLE `0_supp_allocations` ALTER updated_by DROP DEFAULT;
ALTER TABLE `0_cust_allocations` ALTER updated_by DROP DEFAULT;

COMMIT;

-- Reverse
-- ALTER TABLE `0_voided_cust_allocations` DROP PRIMARY KEY, DROP COLUMN _id, ADD UNIQUE KEY id (id), ADD UNIQUE KEY person_id (person_id, `trans_type_from`,`trans_no_from`,`trans_type_to`,`trans_no_to`), DROP COLUMN stamp, DROP COLUMN updated_by;
-- ALTER TABLE `0_voided_supp_allocations` ADD UNIQUE KEY person_id (person_id, `trans_type_from`,`trans_no_from`,`trans_type_to`,`trans_no_to`), DROP COLUMN stamp, DROP COLUMN updated_by;
-- ALTER TABLE `0_cust_allocations` DROP COLUMN stamp, DROP COLUMN updated_by;
-- ALTER TABLE `0_supp_allocations` DROP COLUMN stamp, DROP COLUMN updated_by;