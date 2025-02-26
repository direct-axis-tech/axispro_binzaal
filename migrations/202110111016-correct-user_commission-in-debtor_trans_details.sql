UPDATE `0_debtor_trans_details` SET user_commission = 0 WHERE user_commission IS NULL;
ALTER TABLE `0_debtor_trans_details` MODIFY `user_commission` DOUBLE NOT NULL DEFAULT 0;