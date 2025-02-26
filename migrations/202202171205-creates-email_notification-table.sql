CREATE TABLE `0_email_notification` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `debtor_no` int(11) DEFAULT NULL,
  `debtor_email` varchar(255) DEFAULT NULL,
  `sent_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `modified_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
); 