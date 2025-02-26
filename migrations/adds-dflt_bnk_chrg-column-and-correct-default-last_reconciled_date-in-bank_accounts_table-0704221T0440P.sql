ALTER TABLE `0_bank_accounts` MODIFY COLUMN last_reconciled_date timestamp DEFAULT '1971-01-01 00:00:01' NOT NULL;
ALTER TABLE `0_bank_accounts` ADD dflt_bank_chrg DECIMAL(5,2) DEFAULT 0.00 NOT NULL;