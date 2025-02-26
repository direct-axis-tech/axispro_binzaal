ALTER TABLE `0_shifts` ADD duration TIME DEFAULT NULL AFTER `till`;

UPDATE `0_shifts` SET duration = TIMEDIFF(`till`, `from`);

ALTER TABLE `0_shifts` MODIFY duration TIME NOT NULL;