ALTER TABLE `0_service_request_items` ADD pf_amount DECIMAL(8,3) DEFAULT 0.00 NOT NULL;

-- Reverse
-- ALTER TABLE `0_service_request_items` DROP pf_amount;