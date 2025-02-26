CREATE TABLE `0_shifts` (
  `id` int(4) NOT NULL AUTO_INCREMENT,
  `code` varchar(15) NOT NULL,
  `description` varchar(100) NOT NULL,
  `from` time NOT NULL,
  `till` time NOT NULL,
  `color` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;