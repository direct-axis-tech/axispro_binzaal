CREATE TABLE `0_category_groups` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `desc` VARCHAR(65) NOT NULL
);

ALTER TABLE `0_stock_category` ADD `group_id` INT NOT NULL DEFAULT -1 AFTER `category_id`;

ALTER TABLE `0_stock_category` ADD INDEX `group_id` (`group_id`);