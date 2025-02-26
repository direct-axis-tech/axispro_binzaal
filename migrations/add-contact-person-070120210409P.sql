ALTER TABLE `0_axis_front_desk` 
    ADD `contact_person` VARCHAR(80) NOT NULL AFTER `display_customer`;
ALTER TABLE `0_service_requests` 
    ADD `contact_person` VARCHAR(80) NOT NULL AFTER `display_customer`;
ALTER TABLE `0_debtor_trans` 
    ADD `contact_person` VARCHAR(80) NOT NULL AFTER `display_customer`;
ALTER TABLE `0_debtors_master` 
    ADD `contact_person` VARCHAR(80) NULL AFTER `is_employee`, 
    ADD `iban_no` VARCHAR(34) NULL AFTER `contact_person`;