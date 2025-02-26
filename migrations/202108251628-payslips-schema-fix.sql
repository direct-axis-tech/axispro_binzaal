ALTER TABLE `0_payslips`
MODIFY holidays_worked decimal(4,2) DEFAULT 0 NOT NULL, 
MODIFY weekends_worked decimal(4,2) DEFAULT 0 NOT NULL;