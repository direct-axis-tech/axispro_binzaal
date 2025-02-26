ALTER TABLE `0_cash_handover_requests` ADD `type` SMALLINT(6) unsigned DEFAULT 0 NOT NULL AFTER id;
ALTER TABLE `0_cash_handover_requests` ADD trans_no INT(11) unsigned NULL AFTER `type`;