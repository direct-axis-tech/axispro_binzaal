CREATE TABLE 0_payrolls (
    `id` INT(4) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `year` SMALLINT(2) NOT NULL,
    `month` TINYINT(1) NOT NULL,
    `from` DATE NOT NULL,
    `till` DATE NOT NULL,
    `work_days` TINYINT(1) NOT NULL,
    `is_processed` BOOLEAN NOT NULL DEFAULT 0,
    `processed_by` SMALLINT(6) NULL,
    UNIQUE KEY `pay_month` (`year`, `month`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;