ALTER TABLE `0_stock_master`
    ADD returnable_to VARCHAR(15) NULL DEFAULT NULL AFTER govt_bank_account,
    ADD returnable_amt DECIMAL(6,2) NOT NULL DEFAULT 0 AFTER govt_bank_account;

ALTER TABLE `0_debtor_trans_details`
    ADD returnable_to VARCHAR(15) NULL DEFAULT NULL AFTER govt_bank_account,
    ADD returnable_amt DECIMAL(6,2) NOT NULL DEFAULT 0 AFTER govt_bank_account;

ALTER TABLE `0_sales_order_details`
    ADD returnable_to VARCHAR(15) NULL DEFAULT NULL AFTER govt_bank_account,
    ADD returnable_amt DECIMAL(6,2) NOT NULL DEFAULT 0 AFTER govt_bank_account;