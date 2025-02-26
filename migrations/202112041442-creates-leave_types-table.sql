CREATE TABLE `0_leave_types` (
    `id` TINYINT(1) NOT NULL PRIMARY KEY,
    `desc` VARCHAR(60) NOT NULL,
    `inactive` BOOLEAN DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `0_leave_types` VALUES
    (1, 'Annual Leave', 0),
    (2, 'Hajj Leave', 0),
    (3, 'Maternity Leave', 0),
    (4, 'Parental Leave', 0),
    (5, 'Sick Leave', 0),
    (6, 'Unpaid Leave', 0);
