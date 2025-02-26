CREATE TABLE `0_designations` (
  `id` smallint(2) NOT NULL AUTO_INCREMENT,
  `name` varchar(30) NOT NULL,
  `inactive` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;