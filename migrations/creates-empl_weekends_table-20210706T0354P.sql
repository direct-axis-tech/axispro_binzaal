CREATE TABLE `0_empl_weekends` (
    id INT(4) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    empl_id INT(11) NOT NULL,
    weekoff CHAR(3) NOT NULL,
    UNIQUE `emp_weekoff`(empl_id, weekoff)
);