CREATE TABLE `0_department_shifts` (
    `id` BIGINT(8) NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `department_id` SMALLINT(2) NOT NULL,
    `shift_id` INT(4) NOT NULL,
    UNIQUE KEY `uniq_shift` (`department_id`, `shift_id`)
);

INSERT INTO `0_department_shifts`
SELECT
    NULL as id,
    dep.id as department_id,
    shift.id as shift_id
FROM `0_departments` dep
CROSS JOIN `0_shifts` shift;