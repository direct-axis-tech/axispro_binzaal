ALTER TABLE `0_cash_handover_requests`
    ADD `handovered_on` DATE NULL DEFAULT NULL AFTER `status`,
    ADD `source_ref` varchar(60) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' AFTER `trans_no`;
    
ALTER TABLE `0_debtor_trans` MODIFY `credit_card_charge` double NOT NULL DEFAULT 0;