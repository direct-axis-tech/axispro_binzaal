CREATE TABLE IF NOT EXISTS 0_logs (
    `id` BIGINT(20) UNSIGNED AUTO_INCREMENT NOT NULL PRIMARY KEY,
    `channel` VARCHAR(50), 
    `level` TINYINT(2) UNSIGNED, 
    `message` LONGTEXT, 
    `timestamp` TIMESTAMP,
    `ip` VARCHAR(45),
    `user` SMALLINT(6),
    `session` VARCHAR(33),
    `context` JSON,
    `extra` JSON,
    INDEX(`channel`) USING HASH, 
    INDEX(`level`) USING HASH, 
    INDEX(`timestamp`) USING BTREE,
    INDEX(`user`) USING HASH
)