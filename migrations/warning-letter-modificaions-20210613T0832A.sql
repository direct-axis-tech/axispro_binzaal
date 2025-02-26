CREATE TABLE 0_warning_categories (
    `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name` varchar(50) NOT NULL UNIQUE,
    `inactive` tinyint(1) NOT NULL DEFAULT 0
);

CREATE TABLE 0_warning_grades (
    `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name` varchar(25) NOT NULL UNIQUE,
    `inactive` tinyint(1) NOT NULL DEFAULT 0
);