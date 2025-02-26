ALTER TABLE `0_stock_master`
    ADD split_govt_fee_acc SMALLINT(6) NULL DEFAULT NULL AFTER govt_bank_account,
    ADD split_govt_fee_amt DECIMAL(6,2) NOT NULL DEFAULT 0 AFTER govt_bank_account;

ALTER TABLE `0_debtor_trans_details`
    ADD split_govt_fee_acc SMALLINT(6) NULL DEFAULT NULL AFTER govt_bank_account,
    ADD split_govt_fee_amt DECIMAL(6,2) NOT NULL DEFAULT 0 AFTER govt_bank_account;

ALTER TABLE `0_sales_order_details`
    ADD split_govt_fee_acc SMALLINT(6) NULL DEFAULT NULL AFTER govt_bank_account,
    ADD split_govt_fee_amt DECIMAL(6,2) NOT NULL DEFAULT 0 AFTER govt_bank_account;