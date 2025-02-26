CREATE TABLE `0_pay_elements` (
  `id` smallint(2) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `for` tinyint(1) DEFAULT NULL,
  `type` tinyint(1) NOT NULL COMMENT '1:Earning,-1:Dedcution',
  `is_fixed` tinyint(1) NOT NULL DEFAULT 0,
  `inactive` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_type` (`for`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- TYPES Examples
-- 1: Basic Salary
-- 2: Over Time
-- 3: Pension
-- 4: Commission
-- 5: Loan
-- 6: Violation
-- 7: Advance Recovery