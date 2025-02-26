CREATE TABLE `0_departments` (
  `id` smallint(2) NOT NULL AUTO_INCREMENT,
  `name` varchar(30) NOT NULL,
  `hod_id` bigint(20) unsigned DEFAULT NULL,
  `inactive` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;