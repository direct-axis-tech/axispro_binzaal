ALTER TABLE `0_cash_handover_requests` ADD `gl_balance` DECIMAL(8,2) NOT NULL AFTER `cash_acc_code`;
ALTER TABLE `0_cash_handover_requests` ADD `total_to_pay` DECIMAL(8,2) NOT NULL AFTER `gl_balance`;
ALTER TABLE `0_cash_handover_requests` ADD `adj` DECIMAL(2,2) NOT NULL AFTER `total_to_pay`;
ALTER TABLE `0_cash_handover_requests` ADD `balance` DECIMAL(8,2) NOT NULL AFTER `adj`;
ALTER TABLE `0_cash_handover_requests` MODIFY `amount` decimal(8,2);