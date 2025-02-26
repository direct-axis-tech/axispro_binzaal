CREATE TABLE 0_leaves (
    `id` TINYINT(1) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `code` VARCHAR(15) NOT NULL,
    `type` TINYINT(1) NULL,
    `name` VARCHAR(50) NOT NULL,
    `inactive` BOOLEAN DEFAULT 0,
    UNIQUE KEY `code` (`code`),
    UNIQUE KEY `type` (`type`),
    KEY `inactive` (`inactive`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;