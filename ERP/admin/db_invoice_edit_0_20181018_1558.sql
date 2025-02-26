# MySQL dump of database 'db_invoice_edit' on host 'localhost'
# Backup Date and Time: 2018-10-18 15:58
# Built by Axis Pro 2.4.4
# http://directaxistech.com
# Company: V1
# User: Administrator

# Compatibility: 2.4.1


SET NAMES utf8;


### Structure of table `0_areas` ###

DROP TABLE IF EXISTS `0_areas`;

CREATE TABLE `0_areas` (
  `area_code` int(11) NOT NULL AUTO_INCREMENT,
  `description` varchar(60) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `inactive` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`area_code`),
  UNIQUE KEY `description` (`description`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

### Data of table `0_areas` ###

INSERT INTO `0_areas` VALUES
('1', 'Global', '0'),
('2', '', '0'),
('3', '0', '0'),
('4', ' Europe', '0');

### Structure of table `0_attachments` ###

DROP TABLE IF EXISTS `0_attachments`;

CREATE TABLE `0_attachments` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `description` varchar(60) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `type_no` int(11) NOT NULL DEFAULT '0',
  `trans_no` int(11) NOT NULL DEFAULT '0',
  `unique_name` varchar(60) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `tran_date` date NOT NULL DEFAULT '0000-00-00',
  `filename` varchar(60) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `filesize` int(11) NOT NULL DEFAULT '0',
  `filetype` varchar(60) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `type_no` (`type_no`,`trans_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

### Data of table `0_attachments` ###


### Structure of table `0_attendance` ###

DROP TABLE IF EXISTS `0_attendance`;

CREATE TABLE `0_attendance` (
  `emp_id` int(11) NOT NULL,
  `overtime_id` int(11) NOT NULL,
  `hours_no` float NOT NULL DEFAULT '0',
  `rate` float NOT NULL DEFAULT '1',
  `att_date` date NOT NULL,
  PRIMARY KEY (`emp_id`,`overtime_id`,`att_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

### Data of table `0_attendance` ###

INSERT INTO `0_attendance` VALUES
('1', '0', '1', '1', '2018-08-02'),
('2', '0', '1', '1', '2018-08-02'),
('4', '0', '1', '1', '2018-08-02'),
('5', '0', '1', '1', '2018-08-02'),
('6', '0', '1', '1', '2018-08-02'),
('7', '0', '1', '1', '2018-08-02'),
('8', '0', '1', '1', '2018-08-02'),
('9', '0', '1', '1', '2018-08-02'),
('10', '0', '1', '1', '2018-08-02'),
('11', '0', '1', '1', '2018-08-02'),
('12', '0', '1', '1', '2018-08-02'),
('14', '0', '1', '1', '2018-08-02'),
('15', '0', '1', '1', '2018-08-02'),
('16', '0', '1', '1', '2018-08-02'),
('17', '0', '1', '1', '2018-08-02'),
('18', '0', '1', '1', '2018-08-02'),
('19', '0', '1', '1', '2018-08-02'),
('20', '0', '1', '1', '2018-08-02'),
('21', '0', '1', '1', '2018-08-02');

### Structure of table `0_audit_trail` ###

DROP TABLE IF EXISTS `0_audit_trail`;

CREATE TABLE `0_audit_trail` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` smallint(6) unsigned NOT NULL DEFAULT '0',
  `trans_no` int(11) unsigned NOT NULL DEFAULT '0',
  `user` smallint(6) unsigned NOT NULL DEFAULT '0',
  `stamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `description` varchar(60) COLLATE utf8_unicode_ci DEFAULT NULL,
  `fiscal_year` int(11) NOT NULL DEFAULT '0',
  `gl_date` date NOT NULL DEFAULT '0000-00-00',
  `gl_seq` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `Seq` (`fiscal_year`,`gl_date`,`gl_seq`),
  KEY `Type_and_Number` (`type`,`trans_no`)
) ENGINE=InnoDB AUTO_INCREMENT=1169 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

### Data of table `0_audit_trail` ###

INSERT INTO `0_audit_trail` VALUES
('1140', '30', '1', '1', '2018-10-17 10:04:11', NULL, '2', '2018-10-17', '0'),
('1141', '13', '1', '1', '2018-10-17 10:04:11', NULL, '2', '2018-10-17', '0'),
('1142', '10', '1', '1', '2018-10-17 16:27:41', NULL, '2', '2018-10-17', NULL),
('1143', '12', '1', '1', '2018-10-17 10:08:22', NULL, '2', '2018-10-17', '0'),
('1144', '30', '2', '1', '2018-10-17 10:43:33', NULL, '2', '2018-10-17', '0'),
('1145', '13', '2', '1', '2018-10-17 10:43:33', NULL, '2', '2018-10-17', '0'),
('1146', '10', '2', '1', '2018-10-17 16:30:48', NULL, '2', '2018-10-17', NULL),
('1147', '0', '1', '1', '2018-10-17 11:34:14', NULL, '2', '2018-10-17', '0'),
('1148', '30', '3', '1', '2018-10-17 12:45:55', NULL, '2', '2018-10-17', NULL),
('1149', '13', '3', '1', '2018-10-17 12:45:55', NULL, '2', '2018-10-17', NULL),
('1150', '10', '3', '1', '2018-10-17 12:45:55', NULL, '2', '2018-10-17', NULL),
('1151', '30', '4', '1', '2018-10-17 12:45:04', NULL, '2', '2018-10-17', '0'),
('1152', '13', '4', '1', '2018-10-17 12:45:04', NULL, '2', '2018-10-17', '0'),
('1153', '10', '4', '1', '2018-10-17 12:45:04', NULL, '2', '2018-10-17', '0'),
('1154', '30', '5', '1', '2018-10-17 12:45:17', NULL, '2', '2018-10-17', '0'),
('1155', '13', '5', '1', '2018-10-17 12:45:17', NULL, '2', '2018-10-17', '0'),
('1156', '10', '5', '1', '2018-10-17 12:45:17', NULL, '2', '2018-10-17', '0'),
('1157', '12', '2', '1', '2018-10-17 12:45:34', NULL, '2', '2018-10-17', '0'),
('1158', '30', '6', '1', '2018-10-17 12:45:54', NULL, '2', '2018-10-17', '0'),
('1159', '13', '6', '1', '2018-10-17 12:45:54', NULL, '2', '2018-10-17', '0'),
('1160', '30', '3', '1', '2018-10-17 12:45:55', 'Deleted.', '2', '2018-10-17', '0'),
('1161', '13', '3', '1', '2018-10-17 12:45:55', 'Voided.', '2', '2018-10-17', '0'),
('1162', '10', '3', '1', '2018-10-17 12:45:55', 'Voided.\nEDITED_INVOICE', '2', '2018-10-17', '0'),
('1163', '10', '6', '1', '2018-10-17 12:45:55', NULL, '2', '2018-10-17', '0'),
('1164', '10', '1', '1', '2018-10-17 16:27:41', 'Updated.', '2', '2018-10-17', '0'),
('1165', '10', '2', '1', '2018-10-17 16:30:48', 'Updated.', '2', '2018-10-17', '0'),
('1166', '30', '7', '1', '2018-10-18 14:57:29', NULL, '2', '2018-10-18', '0'),
('1167', '13', '7', '1', '2018-10-18 14:57:29', NULL, '2', '2018-10-18', '0'),
('1168', '10', '7', '1', '2018-10-18 14:57:29', NULL, '2', '2018-10-18', '0');

### Structure of table `0_axis_front_desk` ###

DROP TABLE IF EXISTS `0_axis_front_desk`;

CREATE TABLE `0_axis_front_desk` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) DEFAULT '0',
  `token` varchar(250) COLLATE utf8_unicode_ci DEFAULT NULL,
  `display_customer` varchar(250) COLLATE utf8_unicode_ci DEFAULT NULL,
  `customer_mobile` varchar(250) COLLATE utf8_unicode_ci DEFAULT NULL,
  `customer_email` varchar(250) COLLATE utf8_unicode_ci DEFAULT NULL,
  `customer_trn` varchar(250) COLLATE utf8_unicode_ci DEFAULT NULL,
  `customer_ref` varchar(250) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

### Data of table `0_axis_front_desk` ###

INSERT INTO `0_axis_front_desk` VALUES
('21', '19', 'x1', NULL, '0544706704', 'test@bipin.com', 'BIPINTRN', '45545', NULL, '2018-10-07 11:27:02', '2018-10-07 14:38:03');

### Structure of table `0_bank_accounts` ###

DROP TABLE IF EXISTS `0_bank_accounts`;

CREATE TABLE `0_bank_accounts` (
  `id` smallint(6) NOT NULL AUTO_INCREMENT,
  `account_code` varchar(15) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `account_type` smallint(6) NOT NULL DEFAULT '0',
  `bank_account_name` varchar(60) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `bank_account_number` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `bank_name` varchar(60) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `bank_address` tinytext COLLATE utf8_unicode_ci,
  `bank_curr_code` char(3) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `dflt_curr_act` tinyint(1) NOT NULL DEFAULT '0',
  `bank_charge_act` varchar(15) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `last_reconciled_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ending_reconcile_balance` double NOT NULL DEFAULT '0',
  `inactive` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `bank_account_name` (`bank_account_name`),
  KEY `bank_account_number` (`bank_account_number`),
  KEY `account_code` (`account_code`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

### Data of table `0_bank_accounts` ###

INSERT INTO `0_bank_accounts` VALUES
('2', '1065', '3', 'Petty Cash account', 'N/A', 'N/A', NULL, 'AED', '0', '5690', '2018-06-27 20:00:00', '0', '0'),
('4', '1111', '3', 'NOQODI', '', '', NULL, 'AED', '0', '5690', '2018-06-22 20:00:00', '0', '0'),
('5', '123456', '3', 'ADCB BANK', '', '', NULL, 'AED', '0', '5690', '0000-00-00 00:00:00', '0', '0'),
('6', '1112', '1', 'Commercial Bank of Dubai', '', 'Commercial Bank of Dubai', NULL, 'AED', '0', '5690', '0000-00-00 00:00:00', '0', '0'),
('7', '1050', '3', 'Cashier 1', '', '', NULL, 'AED', '1', '5690', '0000-00-00 00:00:00', '0', '0'),
('8', '1113', '1', 'FAB PJSC', '', 'First Abu Dhabi Bank PJSC', NULL, 'AED', '0', '5690', '0000-00-00 00:00:00', '0', '0'),
('9', '1110', '3', 'E-Dirhams', '', 'E-Dirhams', NULL, 'AED', '0', '5690', '0000-00-00 00:00:00', '0', '0'),
('10', '1112', '2', 'VISA AND MASTERCARD', '', 'MASHREQ', NULL, 'AED', '0', '5690', '0000-00-00 00:00:00', '0', '0'),
('12', '11121', '0', 'RAK BANK', '', '', NULL, 'AED', '0', '5690', '0000-00-00 00:00:00', '0', '0'),
('13', '245', '3', 'CASH SAMSHU', '', '', NULL, 'AED', '0', '245', '0000-00-00 00:00:00', '0', '0');

### Structure of table `0_bank_trans` ###

DROP TABLE IF EXISTS `0_bank_trans`;

CREATE TABLE `0_bank_trans` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` smallint(6) DEFAULT NULL,
  `trans_no` int(11) DEFAULT NULL,
  `bank_act` varchar(15) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `ref` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  `trans_date` date NOT NULL DEFAULT '0000-00-00',
  `amount` double DEFAULT NULL,
  `dimension_id` int(11) NOT NULL DEFAULT '0',
  `dimension2_id` int(11) NOT NULL DEFAULT '0',
  `person_type_id` int(11) NOT NULL DEFAULT '0',
  `person_id` tinyblob,
  `reconciled` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `bank_act` (`bank_act`,`ref`),
  KEY `type` (`type`,`trans_no`),
  KEY `bank_act_2` (`bank_act`,`reconciled`),
  KEY `bank_act_3` (`bank_act`,`trans_date`)
) ENGINE=InnoDB AUTO_INCREMENT=90 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

### Data of table `0_bank_trans` ###

INSERT INTO `0_bank_trans` VALUES
('88', '12', '1', '5', '001/2018', '2018-10-17', '1000', '0', '0', '2', '1', NULL),
('89', '12', '2', '5', '002/2018', '2018-10-17', '1806.64', '0', '0', '2', '19', NULL);

### Structure of table `0_bom` ###

DROP TABLE IF EXISTS `0_bom`;

CREATE TABLE `0_bom` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent` char(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `component` char(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `workcentre_added` int(11) NOT NULL DEFAULT '0',
  `loc_code` char(5) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `quantity` double NOT NULL DEFAULT '1',
  PRIMARY KEY (`parent`,`component`,`workcentre_added`,`loc_code`),
  KEY `component` (`component`),
  KEY `id` (`id`),
  KEY `loc_code` (`loc_code`),
  KEY `parent` (`parent`,`loc_code`),
  KEY `workcentre_added` (`workcentre_added`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

### Data of table `0_bom` ###

INSERT INTO `0_bom` VALUES
('1', '201', '101', '1', 'DEF', '1'),
('2', '201', '102', '1', 'DEF', '1'),
('3', '201', '103', '1', 'DEF', '1'),
('4', '201', '301', '1', 'DEF', '1');

### Structure of table `0_budget_trans` ###

DROP TABLE IF EXISTS `0_budget_trans`;

CREATE TABLE `0_budget_trans` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tran_date` date NOT NULL DEFAULT '0000-00-00',
  `account` varchar(15) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `memo_` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `amount` double NOT NULL DEFAULT '0',
  `dimension_id` int(11) DEFAULT '0',
  `dimension2_id` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `Account` (`account`,`tran_date`,`dimension_id`,`dimension2_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

### Data of table `0_budget_trans` ###


### Structure of table `0_chart_class` ###

DROP TABLE IF EXISTS `0_chart_class`;

CREATE TABLE `0_chart_class` (
  `cid` varchar(3) COLLATE utf8_unicode_ci NOT NULL,
  `class_name` varchar(60) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `ctype` tinyint(1) NOT NULL DEFAULT '0',
  `inactive` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`cid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

### Data of table `0_chart_class` ###

INSERT INTO `0_chart_class` VALUES
('1', 'Assets', '1', '0'),
('2', 'Liabilities', '2', '0'),
('3', 'Income', '4', '0'),
('4', 'Costs', '6', '0'),
('5', 'Expense', '6', '0');

### Structure of table `0_chart_master` ###

DROP TABLE IF EXISTS `0_chart_master`;

CREATE TABLE `0_chart_master` (
  `account_code` varchar(15) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `account_code2` varchar(15) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `account_name` varchar(60) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `account_type` varchar(10) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `inactive` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`account_code`),
  KEY `account_name` (`account_name`),
  KEY `accounts_by_type` (`account_type`,`account_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

### Data of table `0_chart_master` ###

INSERT INTO `0_chart_master` VALUES
('1050', '', 'Cashier 1', '1', '0'),
('1051', '', 'Cashier 2', '1', '0'),
('1060', '', 'Current Account', '1', '0'),
('1065', '', 'Petty Cash - Accounts', '1', '0'),
('1066', '', 'Petty Cash - Cashier 1', '1', '0'),
('1067', '', 'Petty Cash - Cashier 2', '1', '0'),
('1110', '', 'E-Dirhams', '15', '0'),
('1111', '', 'NOQODI', '1', '0'),
('1112', '', 'CBD ATTIJARI AL ISLAMI', '1', '0'),
('11121', '', 'RAK', '1', '0'),
('1113', '', 'FAB PJSC', '1', '0'),
('1114', '', 'CBD IMMIGRATION', '10', '0'),
('1115', '', 'MASHREQ ', '1', '0'),
('1116', '', 'FGB', '1', '0'),
('1200', '', 'Accounts Receivables', '1', '0'),
('123456', '', 'ADCBGL', '1', '0'),
('1300', '', 'Prepaid Rent', '1', '0'),
('1510', '', 'Inventory', '2', '0'),
('1520', '', 'Stocks of Raw Materials', '2', '0'),
('1530', '', 'Stocks of Work In Progress', '2', '0'),
('1540', '', 'Stocks of Finished Goods', '2', '0'),
('1550', '', 'Goods Received Clearing account', '2', '0'),
('1820', '', 'Office Furniture &amp; Equipment', '3', '0'),
('1825', '', 'Accum. Dep. - Furn. &amp; Equip.', '3', '0'),
('1830', '', 'Interior &amp; Decoration', '3', '0'),
('1835', '', 'Accum. Dep. - Interior &amp; Decoration', '3', '0'),
('1840', '', 'Vehicle', '3', '0'),
('1845', '', 'Accum. Dep. - Vehicle', '3', '0'),
('1850', '', 'Softwares, Applications &amp; Programs', '3', '0'),
('2100', '', 'Accounts Payable', '4', '0'),
('2105', '', 'Deferred Income', '4', '0'),
('2110', '', 'Accrued Income Tax - Federal', '4', '0'),
('2120', '', 'Accrued Income Tax - State', '4', '0'),
('2130', '', 'Accrued Franchise Tax', '4', '0'),
('2140', '', 'Accrued Real &amp; Personal Prop Tax', '4', '0'),
('2150', '', 'Sales Tax', '4', '0'),
('2160', '', 'Pension Payable', '4', '0'),
('2210', '', 'Accrued Wages', '4', '0'),
('2220', '', 'Accrued Comp Time', '4', '0'),
('2230', '', 'Accrued Holiday Pay', '4', '0'),
('2240', '', 'Accrued Vacation Pay', '4', '0'),
('2320', '', 'Accr. Benefits - Stock Purchase', '4', '0'),
('2340', '', 'Accr. Benefits - Payroll Taxes', '4', '0'),
('2350', '', 'Accr. Benefits - Credit Union', '4', '0'),
('2360', '', 'Accr. Benefits - Savings Bond', '4', '0'),
('2370', '', 'Accr. Benefits - Garnish', '4', '0'),
('2380', '', 'Accr. Benefits - Charity Cont.', '4', '0'),
('2390', '', 'abdelhamid typing', '4', '1'),
('245', '', 'CASH SAMSHU', '1', '0'),
('2620', '', 'Bank Loans', '5', '0'),
('2630', '', 'Notes Payable', '5', '0'),
('2680', '', 'Loans from Shareholders', '5', '0'),
('3350', '', 'Capital Mr. Saeed Khalfan', '6', '0'),
('3590', '', 'Retained Earnings - prior years', '7', '0'),
('4010', '', 'Sales', '8', '0'),
('4011', '', 'SALES-IMMIGRATION', '8', '0'),
('4012', '', 'SALES-EMIRATES ID', '8', '0'),
('4013', '', 'SALES-MEDICAL', '8', '0'),
('4014', '', 'SALES-INSURANCE', '8', '0'),
('4015', '', 'SALES-OTHER SERVICES', '8', '0'),
('4016', '', 'SALES-PRO SERVICES', '8', '0'),
('4017', '', 'SALES-BANK GUARANTEE', '8', '0'),
('4020', '', 'Sales Return', '8', '0'),
('4321', '', 'TASHEEL - COGS', '10', '0'),
('4420', '', 'Other Income', '9', '0'),
('4430', '', 'Shipping &amp; Handling', '9', '0'),
('4440', '', 'Interest', '9', '0'),
('4450', '', 'Foreign Exchange Gain', '9', '0'),
('4500', '', 'Prompt Payment Discounts', '9', '0'),
('4510', '', 'Discounts on Sales', '9', '0'),
('5010', '', 'Cost of Goods Sold - Retail', '10', '0'),
('5020', '', 'Material Usage Varaiance', '10', '0'),
('5030', '', 'Consumable Materials', '10', '0'),
('5040', '', 'Purchase price Variance', '10', '0'),
('5050', '', 'Purchases of materials', '10', '0'),
('5060', '', 'Discounts Received', '10', '0'),
('5070', '', 'typing outside', '10', '1'),
('5100', '', 'Freight', '10', '0'),
('5360', '', 'Capital Mr. Aiman Hassan', '6', '0'),
('5410', '', 'Wages &amp; Salaries', '11', '0'),
('5420', '', 'Wages - Overtime', '11', '0'),
('5430', '', 'Benefits - Pension', '11', '0'),
('5440', '', 'Benefits - Transportation Allowance', '11', '0'),
('5450', '', 'Benefits - Accomodation', '11', '0'),
('5460', '', 'Benefits - Travel Ticket', '11', '0'),
('5470', '', 'Benefits - General Benefits', '11', '0'),
('5510', '', 'Inc Tax Exp - Federal', '11', '1'),
('5520', '', 'Inc Tax Exp - State', '11', '1'),
('5530', '', 'Taxes - Real Estate', '11', '1'),
('5540', '', 'Taxes - Personal Property', '11', '1'),
('5550', '', 'Taxes - Franchise', '11', '1'),
('5560', '', 'Taxes - Foreign Withholding', '11', '1'),
('5600', '', 'Direct Expenses', '12', '0'),
('5610', '', 'Accounting &amp; Legal', '12', '0'),
('5615', '', 'Advertising &amp; Promotions', '12', '0'),
('5620', '', 'Bad Debts', '12', '0'),
('5625', '', 'Commission', '12', '0'),
('5630', '5630', 'Clothing &amp; Uniform', '12', '0'),
('5640', '', 'Fuel &amp; Gasoline', '12', '0'),
('5650', '', 'Depreciation Expense', '12', '0'),
('5660', '', 'Amortization Expense', '12', '0'),
('5685', '', 'Insurance', '12', '0'),
('5690', '', 'Interest &amp; Bank Charges', '12', '0'),
('5700', '', 'Office Supplies', '12', '0'),
('5705', '', 'Parking &amp; Toll Fees', '12', '0'),
('5760', '', 'Rent', '12', '0'),
('5765', '', 'Repair &amp; Maintenance', '12', '0'),
('5770', '', 'Representation &amp; Entertainment', '12', '0'),
('5780', '', 'Telephone &amp; Internet', '12', '0'),
('5785', '', 'Transportation &amp; Travel ', '12', '0'),
('5790', '', 'Membership &amp; Subscription', '12', '0'),
('5795', '', 'Registrations &amp; Test Fees', '12', '0'),
('5800', '', 'Taxes, Licenses &amp; Government Fees', '12', '0'),
('5805', '', 'Professional and Legal Fees', '12', '0'),
('5810', '', 'Foreign Exchange Loss', '12', '0'),
('5815', '', 'Pantry Expenses', '12', '0'),
('5820', '', 'Printing &amp; Photocopying', '12', '0'),
('5830', '', 'Visa, Medical, Labour &amp; EID ', '12', '0'),
('5840', '', 'Miscellaneous', '12', '0'),
('9990', '', 'Year Profit/Loss', '12', '0');

### Structure of table `0_chart_types` ###

DROP TABLE IF EXISTS `0_chart_types`;

CREATE TABLE `0_chart_types` (
  `id` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(60) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `class_id` varchar(3) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `parent` varchar(10) COLLATE utf8_unicode_ci NOT NULL DEFAULT '-1',
  `inactive` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `name` (`name`),
  KEY `class_id` (`class_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

### Data of table `0_chart_types` ###

INSERT INTO `0_chart_types` VALUES
('1', 'Current Assets', '1', '', '0'),
('10', 'Cost of Goods Sold', '4', '', '0'),
('11', 'Payroll Expenses', '4', '', '0'),
('12', 'General &amp; Administrative expenses', '4', '', '0'),
('13', 'Salary', '5', '', '0'),
('14', 'Duties and Taxes', '2', '', '0'),
('15', 'Payment Cards', '1', '1', '0'),
('2', 'Inventory Assets', '1', '', '0'),
('3', 'Capital Assets', '1', '', '0'),
('4', 'Current Liabilities', '2', '', '0'),
('5', 'Long Term Liabilities', '2', '', '0'),
('6', 'Share Capital', '2', '', '0'),
('7', 'Retained Earnings', '2', '', '0'),
('8', 'Sales Revenue', '3', '', '0'),
('9', 'Other Revenue', '3', '', '0');

### Structure of table `0_comments` ###

DROP TABLE IF EXISTS `0_comments`;

CREATE TABLE `0_comments` (
  `type` int(11) NOT NULL DEFAULT '0',
  `id` int(11) NOT NULL DEFAULT '0',
  `date_` date DEFAULT '0000-00-00',
  `memo_` tinytext COLLATE utf8_unicode_ci,
  KEY `type_and_id` (`type`,`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

### Data of table `0_comments` ###


### Structure of table `0_credit_status` ###

DROP TABLE IF EXISTS `0_credit_status`;

CREATE TABLE `0_credit_status` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reason_description` char(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `dissallow_invoices` tinyint(1) NOT NULL DEFAULT '0',
  `inactive` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `reason_description` (`reason_description`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

### Data of table `0_credit_status` ###

INSERT INTO `0_credit_status` VALUES
('1', 'Good History', '0', '0'),
('3', 'No more work until payment received', '1', '0'),
('4', 'In liquidation', '1', '0');

### Structure of table `0_crm_categories` ###

DROP TABLE IF EXISTS `0_crm_categories`;

CREATE TABLE `0_crm_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'pure technical key',
  `type` varchar(20) COLLATE utf8_unicode_ci NOT NULL COMMENT 'contact type e.g. customer',
  `action` varchar(20) COLLATE utf8_unicode_ci NOT NULL COMMENT 'detailed usage e.g. department',
  `name` varchar(30) COLLATE utf8_unicode_ci NOT NULL COMMENT 'for category selector',
  `description` tinytext COLLATE utf8_unicode_ci NOT NULL COMMENT 'usage description',
  `system` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'nonzero for core system usage',
  `inactive` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `type` (`type`,`action`),
  UNIQUE KEY `type_2` (`type`,`name`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

### Data of table `0_crm_categories` ###

INSERT INTO `0_crm_categories` VALUES
('1', 'cust_branch', 'general', 'General', 'General contact data for customer branch (overrides company setting)', '1', '0'),
('2', 'cust_branch', 'invoice', 'Invoices', 'Invoice posting (overrides company setting)', '1', '0'),
('3', 'cust_branch', 'order', 'Orders', 'Order confirmation (overrides company setting)', '1', '0'),
('4', 'cust_branch', 'delivery', 'Deliveries', 'Delivery coordination (overrides company setting)', '1', '0'),
('5', 'customer', 'general', 'General', 'General contact data for customer', '1', '0'),
('6', 'customer', 'order', 'Orders', 'Order confirmation', '1', '0'),
('7', 'customer', 'delivery', 'Deliveries', 'Delivery coordination', '1', '0'),
('8', 'customer', 'invoice', 'Invoices', 'Invoice posting', '1', '0'),
('9', 'supplier', 'general', 'General', 'General contact data for supplier', '1', '0'),
('10', 'supplier', 'order', 'Orders', 'Order confirmation', '1', '0'),
('11', 'supplier', 'delivery', 'Deliveries', 'Delivery coordination', '1', '0'),
('12', 'supplier', 'invoice', 'Invoices', 'Invoice posting', '1', '0');

### Structure of table `0_crm_contacts` ###

DROP TABLE IF EXISTS `0_crm_contacts`;

CREATE TABLE `0_crm_contacts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `person_id` int(11) NOT NULL DEFAULT '0' COMMENT 'foreign key to crm_contacts',
  `type` varchar(20) COLLATE utf8_unicode_ci NOT NULL COMMENT 'foreign key to crm_categories',
  `action` varchar(20) COLLATE utf8_unicode_ci NOT NULL COMMENT 'foreign key to crm_categories',
  `entity_id` varchar(11) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'entity id in related class table',
  PRIMARY KEY (`id`),
  KEY `type` (`type`,`action`)
) ENGINE=InnoDB AUTO_INCREMENT=117 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

### Data of table `0_crm_contacts` ###

INSERT INTO `0_crm_contacts` VALUES
('4', '2', 'supplier', 'general', '2'),
('5', '3', 'cust_branch', 'general', '1'),
('8', '1', 'supplier', 'general', '1'),
('9', '4', 'cust_branch', 'general', '2'),
('11', '5', 'cust_branch', 'general', '3'),
('13', '6', 'cust_branch', 'general', '4'),
('15', '7', 'cust_branch', 'general', '5'),
('17', '8', 'cust_branch', 'general', '6'),
('22', '10', 'cust_branch', 'general', '7'),
('24', '11', 'cust_branch', 'general', '8'),
('26', '12', 'cust_branch', 'general', '9'),
('28', '13', 'cust_branch', 'general', '10'),
('30', '14', 'cust_branch', 'general', '11'),
('32', '15', 'cust_branch', 'general', '12'),
('34', '16', 'cust_branch', 'general', '13'),
('36', '17', 'cust_branch', 'general', '14'),
('38', '18', 'cust_branch', 'general', '15'),
('40', '19', 'cust_branch', 'general', '16'),
('42', '20', 'cust_branch', 'general', '17'),
('43', '21', 'cust_branch', 'general', '18'),
('45', '22', 'cust_branch', 'general', '19'),
('47', '23', 'cust_branch', 'general', '20'),
('49', '24', 'cust_branch', 'general', '21'),
('51', '25', 'cust_branch', 'general', '22'),
('53', '26', 'cust_branch', 'general', '23'),
('55', '27', 'cust_branch', 'general', '24'),
('57', '28', 'cust_branch', 'general', '25'),
('59', '29', 'cust_branch', 'general', '26'),
('60', '29', 'customer', 'general', '16'),
('61', '30', 'cust_branch', 'general', '27'),
('62', '30', 'customer', 'general', '17'),
('63', '31', 'cust_branch', 'general', '28'),
('65', '32', 'cust_branch', 'general', '29'),
('66', '32', 'customer', 'general', '19'),
('67', '33', 'cust_branch', 'general', '30'),
('68', '33', 'customer', 'general', '20'),
('69', '34', 'cust_branch', 'general', '1'),
('70', '34', 'customer', 'general', '1'),
('73', '36', 'cust_branch', 'general', '3'),
('75', '37', 'cust_branch', 'general', '4'),
('77', '38', 'cust_branch', 'general', '5'),
('79', '39', 'cust_branch', 'general', '6'),
('81', '40', 'cust_branch', 'general', '7'),
('84', '42', 'cust_branch', 'general', '8'),
('86', '35', 'cust_branch', 'general', '2'),
('87', '43', 'cust_branch', 'general', '9'),
('89', '44', 'cust_branch', 'general', '10'),
('91', '45', 'cust_branch', 'general', '11'),
('93', '46', 'cust_branch', 'general', '12'),
('95', '47', 'cust_branch', 'general', '13'),
('97', '48', 'supplier', 'general', '1'),
('98', '49', 'supplier', 'general', '2'),
('99', '50', 'cust_branch', 'general', '14'),
('101', '51', 'cust_branch', 'general', '15'),
('103', '52', 'cust_branch', 'general', '16'),
('104', '52', 'customer', 'general', '16'),
('105', '155', 'cust_branch', 'general', '19'),
('106', '155', 'customer', 'general', '19'),
('107', '156', 'cust_branch', 'general', '20'),
('108', '156', 'customer', 'general', '20'),
('109', '157', 'cust_branch', 'general', '21'),
('110', '157', 'customer', 'general', '21'),
('111', '158', 'cust_branch', 'general', '22'),
('112', '158', 'customer', 'general', '22'),
('113', '159', 'cust_branch', 'general', '23'),
('114', '159', 'customer', 'general', '23'),
('115', '160', 'cust_branch', 'general', '24'),
('116', '160', 'customer', 'general', '24');

### Structure of table `0_crm_persons` ###

DROP TABLE IF EXISTS `0_crm_persons`;

CREATE TABLE `0_crm_persons` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ref` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `name2` varchar(60) COLLATE utf8_unicode_ci DEFAULT NULL,
  `address` tinytext COLLATE utf8_unicode_ci,
  `phone` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
  `phone2` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
  `fax` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
  `email` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `lang` char(5) COLLATE utf8_unicode_ci DEFAULT NULL,
  `notes` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `inactive` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `ref` (`ref`)
) ENGINE=InnoDB AUTO_INCREMENT=161 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

### Data of table `0_crm_persons` ###

INSERT INTO `0_crm_persons` VALUES
('8', 'Walk-in Customer', 'Walk-in Customer', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '0'),
('52', 'AL GURG', 'AL GURG', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '0'),
('151', '', 'Customer 1', NULL, NULL, NULL, NULL, NULL, NULL, NULL, ' ', '0'),
('152', '', 'Customer 2', NULL, NULL, NULL, NULL, NULL, NULL, NULL, ' ', '0'),
('153', '', 'Customer 3', NULL, NULL, NULL, NULL, NULL, NULL, NULL, ' ', '0'),
('154', '', 'Customer 4', NULL, NULL, NULL, NULL, NULL, NULL, NULL, ' ', '0'),
('155', 'adsdasd', 'adsdasd', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '0'),
('156', 'ddddddd', 'ddddddd', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '0'),
('157', 'gggggggg', 'gggggggg', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '0'),
('158', 'rrrrrr', 'rrrrrr', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '0'),
('159', 'Unais', 'Unais', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '0'),
('160', 'Rahman', 'Rahman', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '0');

### Structure of table `0_currencies` ###

DROP TABLE IF EXISTS `0_currencies`;

CREATE TABLE `0_currencies` (
  `currency` varchar(60) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `curr_abrev` char(3) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `curr_symbol` varchar(10) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `country` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `hundreds_name` varchar(15) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `auto_update` tinyint(1) NOT NULL DEFAULT '1',
  `inactive` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`curr_abrev`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

### Data of table `0_currencies` ###

INSERT INTO `0_currencies` VALUES
('', '', '', '', '', '0', '0'),
('AED', 'AED', '$', 'United Arab Emirates', 'Fils', '1', '0');

### Structure of table `0_cust_allocations` ###

DROP TABLE IF EXISTS `0_cust_allocations`;

CREATE TABLE `0_cust_allocations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `person_id` int(11) DEFAULT NULL,
  `amt` double unsigned DEFAULT NULL,
  `date_alloc` date NOT NULL DEFAULT '0000-00-00',
  `trans_no_from` int(11) DEFAULT NULL,
  `trans_type_from` int(11) DEFAULT NULL,
  `trans_no_to` int(11) DEFAULT NULL,
  `trans_type_to` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `trans_type_from` (`person_id`,`trans_type_from`,`trans_no_from`,`trans_type_to`,`trans_no_to`),
  KEY `From` (`trans_type_from`,`trans_no_from`),
  KEY `To` (`trans_type_to`,`trans_no_to`)
) ENGINE=InnoDB AUTO_INCREMENT=118 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

### Data of table `0_cust_allocations` ###

INSERT INTO `0_cust_allocations` VALUES
('114', '19', '171.5', '2018-10-17', '2', '12', '6', '10'),
('115', '19', '95.49', '2018-10-17', '2', '12', '4', '10'),
('116', '19', '1539.65', '2018-10-17', '2', '12', '5', '10'),
('117', '1', '103', '2018-10-18', '0', '12', '7', '10');

### Structure of table `0_cust_branch` ###

DROP TABLE IF EXISTS `0_cust_branch`;

CREATE TABLE `0_cust_branch` (
  `branch_code` int(11) NOT NULL AUTO_INCREMENT,
  `debtor_no` int(11) NOT NULL DEFAULT '0',
  `br_name` varchar(60) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `branch_ref` varchar(30) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `br_address` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `area` int(11) DEFAULT NULL,
  `salesman` int(11) NOT NULL DEFAULT '0',
  `default_location` varchar(5) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `tax_group_id` int(11) DEFAULT NULL,
  `sales_account` varchar(15) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `sales_discount_account` varchar(15) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `receivables_account` varchar(15) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `payment_discount_account` varchar(15) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `default_ship_via` int(11) NOT NULL DEFAULT '1',
  `br_post_address` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `group_no` int(11) NOT NULL DEFAULT '0',
  `notes` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `bank_account` varchar(60) COLLATE utf8_unicode_ci DEFAULT NULL,
  `inactive` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`branch_code`,`debtor_no`),
  KEY `branch_ref` (`branch_ref`),
  KEY `group_no` (`group_no`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

### Data of table `0_cust_branch` ###

INSERT INTO `0_cust_branch` VALUES
('1', '1', 'Walk-in Customer', 'Walk-in Customer', '', '1', '3', 'DEF', '1', '', '4510', '1200', '4500', '1', '', '0', '', NULL, '0'),
('16', '16', 'AL GURG', 'AL GURG', '', '1', '3', 'DEF', '1', '', '4510', '1200', '4500', '1', '', '0', '', NULL, '0'),
('17', '17', 'Navas', 'Navas', '', '1', '3', 'DEF', '1', '', '4510', '1200', '4500', '1', '', '0', '', NULL, '0'),
('18', '18', 'Bipin', 'Bipin', '', '1', '3', 'DEF', '1', '', '4510', '1200', '4500', '1', '', '0', '', NULL, '0'),
('19', '19', 'adsdasd', 'adsdasd', '', '2', '3', 'DEF', '1', '', '4510', '1200', '4500', '1', '', '0', '', NULL, '0'),
('20', '20', 'ddddddd', 'ddddddd', '', '2', '3', 'DEF', '1', '', '4510', '1200', '4500', '1', '', '0', '', NULL, '0'),
('21', '21', 'gggggggg', 'gggggggg', '', '2', '3', 'DEF', '1', '', '4510', '1200', '4500', '1', '', '0', '', NULL, '0'),
('22', '22', 'rrrrrr', 'rrrrrr', '', '2', '3', 'DEF', '1', '', '4510', '1200', '4500', '1', '', '0', '', NULL, '0'),
('23', '23', 'Unais', 'Unais', '', '2', '3', 'DEF', '1', '', '4510', '1200', '4500', '1', '', '0', '', NULL, '0'),
('24', '24', 'Rahman', 'Rahman', '', '2', '3', 'DEF', '1', '', '4510', '1200', '4500', '1', '', '0', '', NULL, '0');

### Structure of table `0_dashboard_reminders` ###

DROP TABLE IF EXISTS `0_dashboard_reminders`;

CREATE TABLE `0_dashboard_reminders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_id` int(11) NOT NULL,
  `next_date` date NOT NULL,
  `description` text,
  `frequency` varchar(20) NOT NULL,
  `param` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

### Data of table `0_dashboard_reminders` ###


### Structure of table `0_dashboard_widgets` ###

DROP TABLE IF EXISTS `0_dashboard_widgets`;

CREATE TABLE `0_dashboard_widgets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `app` varchar(50) NOT NULL,
  `column_id` int(11) NOT NULL,
  `sort_no` int(11) NOT NULL,
  `collapsed` tinyint(1) NOT NULL,
  `widget` varchar(100) NOT NULL,
  `description` varchar(100) NOT NULL,
  `param` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8 ;

### Data of table `0_dashboard_widgets` ###

INSERT INTO `0_dashboard_widgets` VALUES
('1', '1', 'AP', '1', '1', '0', 'weeklysales', 'Top 10 Sales Weeks', '{&quot;top&quot;:&quot;10&quot;,&quot;orderby&quot;:&quot;Gross Sales&quot;,&quot;orderby_seq&quot;:&quot;desc&quot;,&quot;graph_type&quot;:&quot;Table&quot;,&quot;data_filter&quot;:&quot;dm.payment_terms = -1&quot;}'),
('2', '1', 'AP', '1', '0', '0', 'weeklysales', 'Weekly Sales', '{&quot;top&quot;:&quot;&quot;,&quot;orderby&quot;:&quot;Week End&quot;,&quot;orderby_seq&quot;:&quot;asc&quot;,&quot;graph_type&quot;:&quot;LineChart&quot;,&quot;data_filter&quot;:&quot;&quot;}'),
('3', '1', 'AP', '2', '1', '0', 'weeklysales', 'Lowest weeks sales', '{&quot;top&quot;:&quot;10&quot;,&quot;orderby&quot;:&quot;Gross Sales&quot;,&quot;orderby_seq&quot;:&quot;asc&quot;,&quot;graph_type&quot;:&quot;Table&quot;,&quot;data_filter&quot;:&quot;&quot;}'),
('6', '1', 'AP', '2', '0', '0', 'dailysales', 'Daily Sales', '{&quot;top&quot;:&quot;10&quot;,&quot;data_filter&quot;:&quot;dm.payment_terms = -1&quot;,&quot;graph_type&quot;:&quot;LineChart&quot;}'),
('7', '1', 'orders', '1', '0', '0', 'customers', 'Top 10 Customers', '{&quot;top&quot;:&quot;10&quot;,&quot;data_filter&quot;:&quot;&quot;,&quot;graph_type&quot;:&quot;Table&quot;}'),
('9', '1', 'orders', '1', '1', '0', 'salesinvoices', 'Overdue invoices', '{&quot;data_filter&quot;:&quot;&quot;}'),
('10', '1', 'AP', '1', '0', '0', 'suppliers', 'Top 10 Suppliers', '{&quot;top&quot;:&quot;&quot;,&quot;data_filter&quot;:&quot;&quot;,&quot;graph_type&quot;:&quot;Table&quot;}'),
('11', '1', 'AP', '2', '0', '0', 'suppliers', 'Top 10 Suppliers', '{&quot;top&quot;:&quot;&quot;,&quot;data_filter&quot;:&quot;&quot;,&quot;graph_type&quot;:&quot;ColumnChart&quot;}'),
('12', '1', 'GL', '2', '1', '0', 'glreturn', 'Return', '{&quot;graph_type&quot;:&quot;Table&quot;,&quot;data_filter&quot;:&quot;&quot;}'),
('13', '1', 'GL', '2', '0', '0', 'glreturn', 'Return', '{&quot;graph_type&quot;:&quot;PieChart&quot;,&quot;data_filter&quot;:&quot;&quot;}'),
('14', '1', 'stock', '1', '0', '0', 'items', 'Top 10 Items', '{&quot;top&quot;:&quot;&quot;,&quot;item_type&quot;:&quot;stock&quot;,&quot;graph_type&quot;:&quot;Table&quot;,&quot;data_filter&quot;:&quot;&quot;}'),
('15', '1', 'stock', '2', '0', '0', 'items', 'Top 10 Items', '{&quot;top&quot;:&quot;&quot;,&quot;item_type&quot;:&quot;stock&quot;,&quot;graph_type&quot;:&quot;PieChart&quot;,&quot;data_filter&quot;:&quot;&quot;}'),
('16', '1', 'manuf', '1', '0', '0', 'items', 'Top 10 items', '{&quot;top&quot;:&quot;&quot;,&quot;item_type&quot;:&quot;manuf&quot;,&quot;graph_type&quot;:&quot;Table&quot;,&quot;data_filter&quot;:&quot;&quot;}'),
('17', '1', 'manuf', '2', '0', '0', 'items', 'Top 10 Items', '{&quot;top&quot;:&quot;&quot;,&quot;item_type&quot;:&quot;manuf&quot;,&quot;graph_type&quot;:&quot;PieChart&quot;,&quot;data_filter&quot;:&quot;&quot;}'),
('18', '1', 'orders', '2', '0', '0', 'customers', 'Top 10 Customers', '{&quot;top&quot;:&quot;&quot;,&quot;data_filter&quot;:&quot;&quot;,&quot;graph_type&quot;:&quot;ColumnChart&quot;}'),
('19', '1', 'GL', '2', '2', '0', 'bankbalances', 'Bank Balances', '{&quot;data_filter&quot;:&quot;&quot;}'),
('20', '1', 'GL', '1', '1', '0', 'dailybankbalances', 'Daily Current Account Balance', '{&quot;days_past&quot;:&quot;&quot;,&quot;days_future&quot;:&quot;&quot;,&quot;bank_act&quot;:&quot;0&quot;,&quot;graph_type&quot;:&quot;ColumnChart&quot;}'),
('21', '1', 'GL', '1', '0', '0', 'banktransactions', 'Current Account Transactions', '{&quot;days_past&quot;:&quot;15&quot;,&quot;days_future&quot;:&quot;15&quot;,&quot;bank_act&quot;:&quot;0&quot;,&quot;data_filter&quot;:&quot;&quot;}'),
('22', '2', 'AP', '1', '1', '0', 'weeklysales', 'Top 10 Sales Weeks', '{&quot;top&quot;:&quot;10&quot;,&quot;orderby&quot;:&quot;Gross Sales&quot;,&quot;orderby_seq&quot;:&quot;desc&quot;,&quot;graph_type&quot;:&quot;Table&quot;,&quot;data_filter&quot;:&quot;dm.payment_terms = -1&quot;}'),
('23', '2', 'AP', '1', '0', '0', 'weeklysales', 'Weekly Sales', '{&quot;top&quot;:&quot;&quot;,&quot;orderby&quot;:&quot;Week End&quot;,&quot;orderby_seq&quot;:&quot;asc&quot;,&quot;graph_type&quot;:&quot;LineChart&quot;,&quot;data_filter&quot;:&quot;&quot;}'),
('24', '2', 'AP', '2', '1', '0', 'weeklysales', 'Lowest weeks sales', '{&quot;top&quot;:&quot;10&quot;,&quot;orderby&quot;:&quot;Gross Sales&quot;,&quot;orderby_seq&quot;:&quot;asc&quot;,&quot;graph_type&quot;:&quot;Table&quot;,&quot;data_filter&quot;:&quot;&quot;}'),
('25', '2', 'AP', '2', '0', '0', 'dailysales', 'Daily Sales', '{&quot;top&quot;:&quot;10&quot;,&quot;data_filter&quot;:&quot;dm.payment_terms = -1&quot;,&quot;graph_type&quot;:&quot;LineChart&quot;}'),
('26', '2', 'orders', '1', '0', '0', 'customers', 'Top 10 Customers', '{&quot;top&quot;:&quot;10&quot;,&quot;data_filter&quot;:&quot;&quot;,&quot;graph_type&quot;:&quot;Table&quot;}'),
('27', '2', 'orders', '1', '1', '0', 'salesinvoices', 'Overdue invoices', '{&quot;data_filter&quot;:&quot;&quot;}'),
('28', '2', 'AP', '1', '0', '0', 'suppliers', 'Top 10 Suppliers', '{&quot;top&quot;:&quot;&quot;,&quot;data_filter&quot;:&quot;&quot;,&quot;graph_type&quot;:&quot;Table&quot;}'),
('29', '2', 'AP', '2', '0', '0', 'suppliers', 'Top 10 Suppliers', '{&quot;top&quot;:&quot;&quot;,&quot;data_filter&quot;:&quot;&quot;,&quot;graph_type&quot;:&quot;ColumnChart&quot;}'),
('30', '2', 'GL', '2', '1', '0', 'glreturn', 'Return', '{&quot;graph_type&quot;:&quot;Table&quot;,&quot;data_filter&quot;:&quot;&quot;}'),
('31', '2', 'GL', '2', '0', '0', 'glreturn', 'Return', '{&quot;graph_type&quot;:&quot;PieChart&quot;,&quot;data_filter&quot;:&quot;&quot;}'),
('32', '2', 'stock', '1', '0', '0', 'items', 'Top 10 Items', '{&quot;top&quot;:&quot;&quot;,&quot;item_type&quot;:&quot;stock&quot;,&quot;graph_type&quot;:&quot;Table&quot;,&quot;data_filter&quot;:&quot;&quot;}'),
('33', '2', 'stock', '2', '0', '0', 'items', 'Top 10 Items', '{&quot;top&quot;:&quot;&quot;,&quot;item_type&quot;:&quot;stock&quot;,&quot;graph_type&quot;:&quot;PieChart&quot;,&quot;data_filter&quot;:&quot;&quot;}'),
('34', '2', 'manuf', '1', '0', '0', 'items', 'Top 10 items', '{&quot;top&quot;:&quot;&quot;,&quot;item_type&quot;:&quot;manuf&quot;,&quot;graph_type&quot;:&quot;Table&quot;,&quot;data_filter&quot;:&quot;&quot;}'),
('35', '2', 'manuf', '2', '0', '0', 'items', 'Top 10 Items', '{&quot;top&quot;:&quot;&quot;,&quot;item_type&quot;:&quot;manuf&quot;,&quot;graph_type&quot;:&quot;PieChart&quot;,&quot;data_filter&quot;:&quot;&quot;}'),
('36', '2', 'orders', '2', '0', '0', 'customers', 'Top 10 Customers', '{&quot;top&quot;:&quot;&quot;,&quot;data_filter&quot;:&quot;&quot;,&quot;graph_type&quot;:&quot;ColumnChart&quot;}'),
('37', '2', 'GL', '2', '2', '0', 'bankbalances', 'Bank Balances', '{&quot;data_filter&quot;:&quot;&quot;}'),
('38', '2', 'GL', '1', '1', '0', 'dailybankbalances', 'Daily Current Account Balance', '{&quot;days_past&quot;:&quot;&quot;,&quot;days_future&quot;:&quot;&quot;,&quot;bank_act&quot;:&quot;0&quot;,&quot;graph_type&quot;:&quot;ColumnChart&quot;}'),
('39', '2', 'GL', '1', '0', '0', 'banktransactions', 'Current Account Transactions', '{&quot;days_past&quot;:&quot;15&quot;,&quot;days_future&quot;:&quot;15&quot;,&quot;bank_act&quot;:&quot;0&quot;,&quot;data_filter&quot;:&quot;&quot;}');

### Structure of table `0_debtor_trans` ###

DROP TABLE IF EXISTS `0_debtor_trans`;

CREATE TABLE `0_debtor_trans` (
  `trans_no` int(11) unsigned NOT NULL DEFAULT '0',
  `type` smallint(6) unsigned NOT NULL DEFAULT '0',
  `version` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `debtor_no` int(11) unsigned NOT NULL,
  `branch_code` int(11) NOT NULL DEFAULT '-1',
  `tran_date` date NOT NULL DEFAULT '0000-00-00',
  `due_date` date NOT NULL DEFAULT '0000-00-00',
  `reference` varchar(60) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `barcode` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `tpe` int(11) NOT NULL DEFAULT '0',
  `order_` int(11) NOT NULL DEFAULT '0',
  `ov_amount` double NOT NULL DEFAULT '0',
  `ov_gst` double NOT NULL DEFAULT '0',
  `ov_freight` double NOT NULL DEFAULT '0',
  `ov_freight_tax` double NOT NULL DEFAULT '0',
  `ov_discount` double NOT NULL DEFAULT '0',
  `alloc` double NOT NULL DEFAULT '0',
  `prep_amount` double NOT NULL DEFAULT '0',
  `rate` double NOT NULL DEFAULT '1',
  `ship_via` int(11) DEFAULT NULL,
  `dimension_id` int(11) NOT NULL DEFAULT '0',
  `dimension2_id` int(11) NOT NULL DEFAULT '0',
  `payment_terms` int(11) DEFAULT NULL,
  `tax_included` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `display_customer` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `customer_trn` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `customer_mobile` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `customer_email` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `customer_ref` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `payment_method` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `credit_card_charge` varchar(255) COLLATE utf8_unicode_ci DEFAULT '0',
  `payment_flag` int(11) DEFAULT '0' COMMENT '0-Amer,1-Tasheel Edirham card, 2-Tasheel Customer Card',
  PRIMARY KEY (`type`,`trans_no`,`debtor_no`),
  KEY `debtor_no` (`debtor_no`,`branch_code`),
  KEY `tran_date` (`tran_date`),
  KEY `order_` (`order_`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

### Data of table `0_debtor_trans` ###

INSERT INTO `0_debtor_trans` VALUES
('1', '0', '0', '1', '1', '2018-10-17', '0000-00-00', '001/2018', NULL, '0', '0', '5000', '0', '0', '0', '0', '0', '0', '1', NULL, '0', '0', NULL, '0', NULL, NULL, NULL, NULL, NULL, NULL, '0', '0'),
('1', '10', '0', '1', '1', '2018-10-17', '2018-10-18', '000001', '103511359595', '1', '1', '2589.9', '4', '0', '0', '0', '0', '0', '1', '0', '0', '0', '4', '0', 'Walk-in Customer', NULL, NULL, NULL, NULL, NULL, '', '0'),
('2', '10', '0', '1', '1', '2018-10-17', '2018-10-18', '000002', '819668691124', '1', '2', '99.5', '0.5', '0', '0', '0', '0', '0', '1', '0', '0', '0', '4', '0', 'Walk-in Customer', NULL, NULL, NULL, NULL, NULL, '', '0'),
('3', '10', '2', '19', '19', '2018-10-17', '2018-10-17', '000003', '906827313353', '2', '3', '0', '0', '0', '0', '0', '0', '0', '1', '1', '0', '0', '5', '0', 'adsdasd', NULL, NULL, NULL, NULL, NULL, '', '0'),
('4', '10', '0', '19', '19', '2018-10-17', '2018-10-17', '000004', '884764726269', '2', '4', '93.14', '2.35', '0', '0', '0', '95.49', '0', '1', '1', '0', '0', '5', '0', 'adsdasd', NULL, NULL, NULL, NULL, NULL, '', '0'),
('5', '10', '0', '19', '19', '2018-10-17', '2018-10-17', '000005', '189257408081', '2', '5', '1535.65', '4', '0', '0', '0', '1539.65', '0', '1', '1', '0', '0', '5', '0', 'adsdasd', NULL, NULL, NULL, NULL, NULL, '', '0'),
('6', '10', '0', '19', '19', '2018-10-17', '2018-11-16', '000003', '006610586117', '2', '6', '170', '1.5', '0', '0', '0', '171.5', '0', '1', '1', '0', '0', '5', '0', 'adsdasd', NULL, NULL, NULL, NULL, NULL, '', '0'),
('7', '10', '0', '1', '1', '2018-10-18', '2018-10-19', '000006', '581385768824', '1', '7', '103', '0', '0', '0', '0', '103', '0', '1', '1', '0', '0', '4', '0', 'Walk-in Customer', NULL, NULL, NULL, NULL, NULL, '', '2'),
('1', '12', '0', '1', '1', '2018-10-17', '0000-00-00', '001/2018', NULL, '0', '0', '1000', '0', '0', '0', '0', '0', '0', '1', '0', '0', '0', NULL, '0', NULL, NULL, NULL, NULL, NULL, 'Cash', '0', '0'),
('2', '12', '0', '19', '19', '2018-10-17', '0000-00-00', '002/2018', NULL, '0', '0', '1806.64', '0', '0', '0', '0', '1806.64', '0', '1', '0', '0', '0', NULL, '0', NULL, NULL, NULL, NULL, NULL, 'Cash', '0', '0'),
('1', '13', '2', '1', '1', '2018-10-17', '2018-10-18', 'auto', '103511359595', '1', '1', '2589.9', '4', '0', '0', '0', '0', '0', '1', '1', '0', '0', '4', '0', 'Walk-in Customer', NULL, NULL, NULL, NULL, NULL, '', '0'),
('2', '13', '2', '1', '1', '2018-10-17', '2018-10-18', 'auto', '819668691124', '1', '2', '99.5', '0.5', '0', '0', '0', '0', '0', '1', '1', '0', '0', '4', '0', 'Walk-in Customer', NULL, NULL, NULL, NULL, NULL, '', '0'),
('3', '13', '2', '19', '19', '2018-10-17', '2018-10-17', 'auto', '906827313353', '2', '3', '0', '0', '0', '0', '0', '0', '0', '1', '1', '0', '0', '5', '0', 'adsdasd', NULL, NULL, NULL, NULL, NULL, '', '0'),
('4', '13', '1', '19', '19', '2018-10-17', '2018-10-17', 'auto', '884764726269', '2', '4', '93.14', '2.35', '0', '0', '0', '0', '0', '1', '1', '0', '0', '5', '0', 'adsdasd', NULL, NULL, NULL, NULL, NULL, '', '0'),
('5', '13', '1', '19', '19', '2018-10-17', '2018-10-17', 'auto', '189257408081', '2', '5', '1535.65', '4', '0', '0', '0', '0', '0', '1', '1', '0', '0', '5', '0', 'adsdasd', NULL, NULL, NULL, NULL, NULL, '', '0'),
('6', '13', '1', '19', '19', '2018-10-17', '2018-11-16', 'auto', '006610586117', '2', '6', '170', '1.5', '0', '0', '0', '0', '0', '1', '1', '0', '0', '5', '0', 'adsdasd', NULL, NULL, NULL, NULL, NULL, '', '0'),
('7', '13', '1', '1', '1', '2018-10-18', '2018-10-19', 'auto', '581385768824', '1', '7', '103', '0', '0', '0', '0', '0', '0', '1', '1', '0', '0', '4', '0', 'Walk-in Customer', NULL, NULL, NULL, NULL, NULL, '', '0');

### Structure of table `0_debtor_trans_details` ###

DROP TABLE IF EXISTS `0_debtor_trans_details`;

CREATE TABLE `0_debtor_trans_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `debtor_trans_no` int(11) DEFAULT NULL,
  `debtor_trans_type` int(11) DEFAULT NULL,
  `stock_id` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `description` tinytext COLLATE utf8_unicode_ci,
  `unit_price` double NOT NULL DEFAULT '0',
  `unit_tax` double NOT NULL DEFAULT '0',
  `quantity` double NOT NULL DEFAULT '0',
  `discount_percent` double NOT NULL DEFAULT '0',
  `discount_amount` double DEFAULT '0',
  `standard_cost` double NOT NULL DEFAULT '0',
  `qty_done` double NOT NULL DEFAULT '0',
  `src_id` int(11) NOT NULL,
  `govt_fee` double NOT NULL DEFAULT '0',
  `bank_service_charge` double NOT NULL DEFAULT '0',
  `bank_service_charge_vat` double NOT NULL DEFAULT '0',
  `pf_amount` double NOT NULL DEFAULT '0',
  `transaction_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `user_commission` double DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `Transaction` (`debtor_trans_type`,`debtor_trans_no`),
  KEY `src_id` (`src_id`)
) ENGINE=InnoDB AUTO_INCREMENT=626 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

### Data of table `0_debtor_trans_details` ###

INSERT INTO `0_debtor_trans_details` VALUES
('612', '1', '13', '108', 'Entry Permit - New - Long Term Visit - Single Entry - Leisure (Inside) - 	أذونات الدخول - جديد - زيارة طويلة - سفرة واحدة - ترفيه	', '80', '4', '1', '0', '0', '0', '1', '349', '0', '0', '0', '0', NULL, '45', '1', '1', '2018-10-17 10:04:11', '2018-10-17 10:04:11'),
('613', '1', '10', '108', 'Entry Permit - New - Long Term Visit - Single Entry - Leisure (Inside) - 	أذونات الدخول - جديد - زيارة طويلة - سفرة واحدة - ترفيه	', '80', '4', '1', '0', '0', '0', '0', '612', '2506.75', '3.15', '0', '0', '20198120180014568', '45', '1', '1', '2018-10-17 10:04:11', '2018-10-17 16:27:41'),
('614', '2', '13', 'EIDRE', 'EID RESCANNING - مسح نواقص للهوية', '10', '0.5', '1', '0', '0', '0', '1', '350', '0', '0', '0', '0', NULL, '0', '1', '1', '2018-10-17 10:43:33', '2018-10-17 10:43:34'),
('615', '2', '10', 'EIDRE', 'EID RESCANNING - مسح نواقص للهوية', '10', '0.5', '1', '0', '0', '0', '0', '614', '89.5', '0', '0', '0', '1234', '0', '1', '1', '2018-10-17 10:43:34', '2018-10-17 16:30:48'),
('616', '3', '13', 'EID5', 'EMIRATES ID FORM/ FIVE YEARS (GCC) - طلب الهوية الإماراتية/ 5 سنوات', '0', '0', '0', '0', '0', '0', '1', '0', '0', '0', '0', '2.63', NULL, '0', '1', '1', '2018-10-17 12:44:49', '2018-10-17 12:45:55'),
('617', '3', '10', 'EID5', 'EMIRATES ID FORM/ FIVE YEARS (GCC) - طلب الهوية الإماراتية/ 5 سنوات', '0', '0', '0', '0', '0', '0', '0', '0', '140', '0', '0', '2.63', NULL, '0', '1', '1', '2018-10-17 12:44:49', '2018-10-17 12:45:55'),
('618', '4', '13', '102', 'Entry Permit - Cancel - Work (Company) - 	أذونات الدخول - إلغاء - عمل	', '47', '2.35', '1', '0.213', '0', '0', '1', '352', '0', '0', '0', '0', NULL, '45', '1', '1', '2018-10-17 12:45:04', '2018-10-17 12:45:04'),
('619', '4', '10', '102', 'Entry Permit - Cancel - Work (Company) - 	أذونات الدخول - إلغاء - عمل	', '47', '2.35', '1', '0.213', '10', '0', '0', '618', '53', '3.15', '0', '0', NULL, '45', '1', '1', '2018-10-17 12:45:04', '2018-10-17 12:45:04'),
('620', '5', '13', '105', 'Entry Permit - Extend - Short Term Visit - On Arrival (Inside) - 	أذونات الدخول - تمديد - تأشيرة زيارة قصيرة - عند الوصول	', '80', '4', '1', '0.125', '0', '0', '1', '353', '0', '0', '0', '0', NULL, '45', '1', '1', '2018-10-17 12:45:17', '2018-10-17 12:45:17'),
('621', '5', '10', '105', 'Entry Permit - Extend - Short Term Visit - On Arrival (Inside) - 	أذونات الدخول - تمديد - تأشيرة زيارة قصيرة - عند الوصول	', '80', '4', '1', '0.125', '10', '0', '0', '620', '1462.5', '3.15', '0', '0', NULL, '45', '1', '1', '2018-10-17 12:45:17', '2018-10-17 12:45:17'),
('622', '6', '13', 'EID5', 'EMIRATES ID FORM/ FIVE YEARS (GCC) - طلب الهوية الإماراتية/ 5 سنوات', '30', '1.5', '1', '0', '0', '0', '1', '354', '0', '0', '0', '2.63', NULL, '0', '1', '1', '2018-10-17 12:45:54', '2018-10-17 12:45:55'),
('623', '6', '10', 'EID5', 'EMIRATES ID FORM/ FIVE YEARS (GCC) - طلب الهوية الإماراتية/ 5 سنوات', '30', '1.5', '1', '0', '0', '0', '0', '622', '140', '0', '0', '2.63', NULL, '0', '1', '1', '2018-10-17 12:45:55', '2018-10-17 12:45:55'),
('624', '7', '13', '11083', 'Absconding - Electronic - 	بلاغ - إلكتروني	', '80', '0', '1', '0', '0', '0', '1', '355', '0', '0', '0', '0', NULL, '0', '1', '1', '2018-10-18 14:57:29', '2018-10-18 14:57:29'),
('625', '7', '10', '11083', 'Absconding - Electronic - 	بلاغ - إلكتروني	', '80', '0', '1', '0', '0', '0', '0', '624', '23', '0', '0', '0', NULL, '0', '1', '1', '2018-10-18 14:57:29', '2018-10-18 14:57:29');

### Structure of table `0_debtors_master` ###

DROP TABLE IF EXISTS `0_debtors_master`;

CREATE TABLE `0_debtors_master` (
  `debtor_no` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `debtor_ref` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `address` tinytext COLLATE utf8_unicode_ci,
  `tax_id` varchar(55) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `curr_code` char(3) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `sales_type` int(11) NOT NULL DEFAULT '1',
  `dimension_id` int(11) NOT NULL DEFAULT '0',
  `dimension2_id` int(11) NOT NULL DEFAULT '0',
  `credit_status` int(11) NOT NULL DEFAULT '0',
  `payment_terms` int(11) DEFAULT NULL,
  `discount` double NOT NULL DEFAULT '0',
  `pymt_discount` double NOT NULL DEFAULT '0',
  `credit_limit` float NOT NULL DEFAULT '1000',
  `notes` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `inactive` tinyint(1) NOT NULL DEFAULT '0',
  `activated_till` timestamp NULL DEFAULT NULL,
  `mobile` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `debtor_email` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `show_discount` int(11) DEFAULT '0',
  PRIMARY KEY (`debtor_no`),
  UNIQUE KEY `debtor_ref` (`debtor_ref`),
  KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

### Data of table `0_debtors_master` ###

INSERT INTO `0_debtors_master` VALUES
('1', 'Walk-in Customer', 'Walk-in Customer', NULL, '', 'AED', '1', '0', '0', '1', '4', '0', '0', '50000', '', '0', '2034-12-31 20:00:00', NULL, NULL, '0'),
('16', 'AL GURG', 'AL GURG', NULL, '', 'AED', '2', '0', '0', '1', '0', '0', '0', '1000', '', '0', '0000-00-00 00:00:00', NULL, NULL, '1'),
('17', 'Navas', 'Navas', NULL, '', 'AED', '1', '0', '0', '1', '0', '0', '0', '1000', '', '0', '0000-00-00 00:00:00', NULL, NULL, '0'),
('18', 'Bipin', 'Bipin', NULL, '', 'AED', '1', '0', '0', '1', '0', '0', '0', '1000', '', '0', '0000-00-00 00:00:00', NULL, NULL, '1'),
('19', 'adsdasd', 'adsdasd', NULL, '', 'AED', '2', '0', '0', '1', '0', '0', '0', '1000', '', '0', '0000-00-00 00:00:00', NULL, NULL, '1'),
('20', 'ddddddd', 'ddddddd', NULL, '', 'AED', '2', '0', '0', '1', '0', '0', '0', '1000', '', '0', '0000-00-00 00:00:00', NULL, NULL, '1'),
('21', 'gggggggg', 'gggggggg', NULL, '', 'AED', '2', '0', '0', '1', '0', '0', '0', '1000', '', '0', '0000-00-00 00:00:00', NULL, NULL, '1'),
('22', 'rrrrrr', 'rrrrrr', NULL, '', 'AED', '2', '0', '0', '1', '0', '0', '0', '1000', '', '0', NULL, NULL, NULL, '1'),
('23', 'Unais', 'Unais', NULL, '', 'AED', '1', '0', '0', '1', '0', '0', '0', '1000', '', '0', NULL, NULL, NULL, '0'),
('24', 'Rahman', 'Rahman', NULL, '', 'AED', '1', '0', '0', '1', '0', '0', '0', '1000', '', '0', NULL, NULL, NULL, '0');

### Structure of table `0_department` ###

DROP TABLE IF EXISTS `0_department`;

CREATE TABLE `0_department` (
  `dept_id` int(11) NOT NULL AUTO_INCREMENT,
  `dept_name` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `inactive` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`dept_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

### Data of table `0_department` ###

INSERT INTO `0_department` VALUES
('1', 'MANAGEMENT ', '0'),
('2', 'ADMINISTRATION AND ACCOUNTING', '0'),
('3', 'COUNTER AND SALES', '0'),
('4', 'IT AND SUPPORT', '0'),
('5', 'RECEPTION AND CASHIER', '0');

### Structure of table `0_dimensions` ###

DROP TABLE IF EXISTS `0_dimensions`;

CREATE TABLE `0_dimensions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reference` varchar(60) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `name` varchar(60) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `type_` tinyint(1) NOT NULL DEFAULT '1',
  `closed` tinyint(1) NOT NULL DEFAULT '0',
  `date_` date NOT NULL DEFAULT '0000-00-00',
  `due_date` date NOT NULL DEFAULT '0000-00-00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `reference` (`reference`),
  KEY `date_` (`date_`),
  KEY `due_date` (`due_date`),
  KEY `type_` (`type_`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

### Data of table `0_dimensions` ###

INSERT INTO `0_dimensions` VALUES
('1', '001/2017', 'Cost Centre', '1', '0', '2017-05-05', '2017-05-25');

### Structure of table `0_discount_trans` ###

DROP TABLE IF EXISTS `0_discount_trans`;

CREATE TABLE `0_discount_trans` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `debtor_no` int(11) DEFAULT '0',
  `trans_no` int(11) DEFAULT '0',
  `invoice_ref` varchar(50) COLLATE utf8_unicode_ci DEFAULT '0',
  `tran_date` date DEFAULT NULL,
  `disc_amount` double DEFAULT '0',
  `user_id` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

### Data of table `0_discount_trans` ###

INSERT INTO `0_discount_trans` VALUES
('1', '1', '4', '000003', '2018-10-15', '10', '1');

### Structure of table `0_employee` ###

DROP TABLE IF EXISTS `0_employee`;

CREATE TABLE `0_employee` (
  `emp_id` int(11) NOT NULL AUTO_INCREMENT,
  `emp_first_name` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `emp_last_name` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `gender` tinyint(1) NOT NULL DEFAULT '0',
  `emp_address` tinytext COLLATE utf8_unicode_ci,
  `emp_mobile` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
  `emp_email` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `emp_birthdate` date NOT NULL,
  `emp_notes` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `emp_hiredate` date DEFAULT NULL,
  `department_id` int(11) NOT NULL,
  `salary_scale_id` int(11) NOT NULL DEFAULT '0',
  `emp_releasedate` date DEFAULT NULL,
  `inactive` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`emp_id`),
  KEY `salary_scale_id` (`salary_scale_id`),
  KEY `department_id` (`department_id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

### Data of table `0_employee` ###

INSERT INTO `0_employee` VALUES
('1', 'AIMAN HASSAN MOHAMED ', 'IBRAHIM', '1', NULL, '0506242867', 'aiman1994@hotmail.com', '1967-08-16', '', '2018-07-01', '1', '1', '0000-00-00', '0'),
('2', 'KHALID HASHIM DARWISH ', 'IBRAHIM', '1', NULL, NULL, NULL, '1975-02-06', '', '2018-07-08', '1', '2', '0000-00-00', '0'),
('4', 'AHMED ELSAYED AHMED ', 'ATIA', '1', NULL, '0568708209', 'ahmed.elsayed91@yahoo.com', '1965-01-15', '', '2018-07-08', '2', '5', '0000-00-00', '0'),
('5', 'AHMAD MOHAMMAD ESSA MOOSA', ' ALBALOOSHI', '1', NULL, NULL, NULL, '2005-07-21', '', '2018-07-08', '3', '8', '0000-00-00', '0'),
('6', 'ANOOD MOHAMMAD ALI AHMAD ', 'THANI', '0', NULL, NULL, NULL, '2005-07-21', '', '2018-07-08', '3', '8', '0000-00-00', '0'),
('7', 'FATMA ALI MOHAMMAD YOUSUF', 'YOUSUF', '0', NULL, NULL, NULL, '2005-07-21', '', '2018-07-08', '3', '8', '0000-00-00', '0'),
('8', 'MASTORH JUMA BILAL ALSALAFA ', 'ALNOOBI', '0', NULL, NULL, NULL, '2005-07-21', '', '2018-07-08', '3', '8', '0000-00-00', '0'),
('9', 'MARWAN MOHAMED ALAWI ABDELQAWI ', 'ALHARTHI', '1', NULL, NULL, NULL, '2005-07-21', '', '2018-07-08', '3', '8', '0000-00-00', '0'),
('10', 'AMIRA SALIM ABDELLATIF MOHAMED ', 'MUSTAFA', '0', NULL, NULL, NULL, '2000-07-23', '', '2018-07-08', '3', '8', '0000-00-00', '0'),
('11', 'MAHRA HUSSAIN GHULOOM ALI ', 'ALMUALLEMI', '0', NULL, NULL, NULL, '2005-07-21', '', '2018-07-08', '3', '8', '0000-00-00', '0'),
('12', 'SHABEERALI MADATHIL BEERANKUTTY ', 'MADATHIL', '1', NULL, NULL, NULL, '2005-07-21', '', '2018-07-01', '4', '11', '0000-00-00', '0'),
('13', 'AZARUDEEN THAJUDEEN ', 'THAJUDEEN', '1', NULL, NULL, NULL, '2005-07-21', '', '2018-07-01', '4', '11', '0000-00-00', '0'),
('14', 'NAIMAT ULLA KHAN RASOOL ', 'KHAN', '1', NULL, NULL, NULL, '2005-07-21', '', '2018-07-01', '4', '12', '0000-00-00', '0'),
('15', 'MARIA LILIBETH BULAGA ', 'AMORA', '0', NULL, NULL, NULL, '2005-07-21', '', '2018-07-01', '3', '13', '0000-00-00', '0'),
('16', 'SARA MOHAMMED ZAKARIA ', 'ALI', '0', NULL, NULL, NULL, '2005-07-21', '', '2018-07-08', '5', '13', '0000-00-00', '0'),
('17', 'MOHAMED KUTHBUDEEN MOHAMED IQBAL MOHAMED ', 'IQBAL', '1', NULL, NULL, NULL, '2005-07-21', '', '2018-07-05', '4', '14', '0000-00-00', '0'),
('18', 'ESMAIL AKBAR ALI AKBAR ', 'ALI', '1', NULL, NULL, NULL, '2005-07-21', '', '0000-00-00', '4', '14', '0000-00-00', '0'),
('19', 'HASSAN NASEF ABDELKHALEK ABDELFATAH ', 'ELWAKEL', '1', NULL, NULL, NULL, '2005-07-21', '', '0000-00-00', '5', '14', '0000-00-00', '0'),
('20', 'MOSTAFA GABER ABDELAZIZ ', 'ZIDAN', '1', NULL, NULL, NULL, '2005-07-21', '', '0000-00-00', '5', '15', '0000-00-00', '0'),
('21', 'DIANA LYN REYMUNDO ', 'SUMANG', '0', NULL, NULL, NULL, '2005-07-21', '', '2018-07-08', '5', '15', '0000-00-00', '0');

### Structure of table `0_employee_trans` ###

DROP TABLE IF EXISTS `0_employee_trans`;

CREATE TABLE `0_employee_trans` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `trans_no` int(11) NOT NULL DEFAULT '0',
  `payslip_no` int(11) NOT NULL,
  `pay_date` date NOT NULL,
  `to_the_order_of` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `pay_amount` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

### Data of table `0_employee_trans` ###


### Structure of table `0_exchange_rates` ###

DROP TABLE IF EXISTS `0_exchange_rates`;

CREATE TABLE `0_exchange_rates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `curr_code` char(3) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `rate_buy` double NOT NULL DEFAULT '0',
  `rate_sell` double NOT NULL DEFAULT '0',
  `date_` date NOT NULL DEFAULT '0000-00-00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `curr_code` (`curr_code`,`date_`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

### Data of table `0_exchange_rates` ###


### Structure of table `0_fiscal_year` ###

DROP TABLE IF EXISTS `0_fiscal_year`;

CREATE TABLE `0_fiscal_year` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `begin` date DEFAULT '0000-00-00',
  `end` date DEFAULT '0000-00-00',
  `closed` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `begin` (`begin`),
  UNIQUE KEY `end` (`end`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

### Data of table `0_fiscal_year` ###

INSERT INTO `0_fiscal_year` VALUES
('1', '2016-01-01', '2016-12-31', '1'),
('2', '2017-01-01', '2019-12-31', '0'),
('3', '2020-01-01', '2020-12-31', '0');

### Structure of table `0_gl_trans` ###

DROP TABLE IF EXISTS `0_gl_trans`;

CREATE TABLE `0_gl_trans` (
  `counter` int(11) NOT NULL AUTO_INCREMENT,
  `type` smallint(6) NOT NULL DEFAULT '0',
  `type_no` int(11) NOT NULL DEFAULT '0',
  `tran_date` date NOT NULL DEFAULT '0000-00-00',
  `account` varchar(15) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `memo_` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `amount` double NOT NULL DEFAULT '0',
  `transaction_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `dimension_id` int(11) NOT NULL DEFAULT '0',
  `dimension2_id` int(11) NOT NULL DEFAULT '0',
  `person_type_id` int(11) DEFAULT NULL,
  `person_id` tinyblob,
  `reconciled` date DEFAULT NULL,
  PRIMARY KEY (`counter`),
  KEY `Type_and_Number` (`type`,`type_no`),
  KEY `dimension_id` (`dimension_id`),
  KEY `dimension2_id` (`dimension2_id`),
  KEY `tran_date` (`tran_date`),
  KEY `account_and_tran_date` (`account`,`tran_date`)
) ENGINE=InnoDB AUTO_INCREMENT=1829 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

### Data of table `0_gl_trans` ###

INSERT INTO `0_gl_trans` VALUES
('1770', '10', '1', '2018-10-17', '4011', '', '0', NULL, '0', '0', NULL, NULL, NULL),
('1771', '10', '1', '2018-10-17', '1111', 'Govt.Fee', '0', 'N/A', '0', '0', NULL, NULL, NULL),
('1772', '10', '1', '2018-10-17', '1111', 'Bank service charge', '0', 'N/A', '0', '0', NULL, NULL, NULL),
('1773', '10', '1', '2018-10-17', '5010', '', '0', NULL, '0', '0', NULL, NULL, NULL),
('1774', '10', '1', '2018-10-17', '1200', '', '0', NULL, '0', '0', '2', '1', NULL),
('1775', '10', '1', '2018-10-17', '2150', '', '0', NULL, '0', '0', NULL, NULL, NULL),
('1776', '12', '1', '2018-10-17', '123456', '', '1000', 'N/A', '0', '0', NULL, NULL, NULL),
('1777', '12', '1', '2018-10-17', '1200', '', '-1000', NULL, '0', '0', '2', '1', NULL),
('1778', '10', '2', '2018-10-17', '4012', '', '0', NULL, '0', '0', NULL, NULL, NULL),
('1779', '10', '2', '2018-10-17', '1112', 'Govt.Fee', '0', 'N/A', '0', '0', NULL, NULL, NULL),
('1780', '10', '2', '2018-10-17', '5010', '', '0', NULL, '0', '0', NULL, NULL, NULL),
('1781', '10', '2', '2018-10-17', '1200', '', '0', NULL, '0', '0', '2', '1', NULL),
('1782', '10', '2', '2018-10-17', '2150', '', '0', NULL, '0', '0', NULL, NULL, NULL),
('1783', '0', '1', '2018-10-17', '1200', '', '5000', 'N/A', '0', '0', '2', '1', NULL),
('1784', '0', '1', '2018-10-17', '1830', '', '-5000', 'N/A', '0', '0', NULL, NULL, NULL),
('1785', '10', '3', '2018-10-17', '4012', '', '0', NULL, '0', '0', NULL, NULL, NULL),
('1786', '10', '3', '2018-10-17', '1112', 'Govt.Fee', '0', 'N/A', '0', '0', NULL, NULL, NULL),
('1787', '10', '3', '2018-10-17', '1112', 'Amer service charge', '0', 'N/A', '0', '0', NULL, NULL, NULL),
('1788', '10', '3', '2018-10-17', '5010', '', '0', NULL, '0', '0', NULL, NULL, NULL),
('1789', '10', '3', '2018-10-17', '1200', '', '0', NULL, '0', '0', '2', '19', NULL),
('1790', '10', '3', '2018-10-17', '2150', '', '0', NULL, '0', '0', NULL, NULL, NULL),
('1791', '10', '4', '2018-10-17', '4011', '', '-103.15', NULL, '0', '0', NULL, NULL, NULL),
('1792', '10', '4', '2018-10-17', '1111', 'Govt.Fee', '-53', 'N/A', '0', '0', NULL, NULL, NULL),
('1793', '10', '4', '2018-10-17', '1111', 'Bank service charge', '-3.15', 'N/A', '0', '0', NULL, NULL, NULL),
('1794', '10', '4', '2018-10-17', '5010', '', '56.15', NULL, '0', '0', NULL, NULL, NULL),
('1795', '10', '4', '2018-10-17', '4510', '', '10.01', NULL, '0', '0', NULL, NULL, NULL),
('1796', '10', '4', '2018-10-17', '1200', '', '95.49', NULL, '0', '0', '2', '19', NULL),
('1797', '10', '4', '2018-10-17', '2150', '', '-2.35', NULL, '0', '0', NULL, NULL, NULL),
('1798', '10', '5', '2018-10-17', '4011', '', '-1545.65', NULL, '0', '0', NULL, NULL, NULL),
('1799', '10', '5', '2018-10-17', '1111', 'Govt.Fee', '-1462.5', 'N/A', '0', '0', NULL, NULL, NULL),
('1800', '10', '5', '2018-10-17', '1111', 'Bank service charge', '-3.15', 'N/A', '0', '0', NULL, NULL, NULL),
('1801', '10', '5', '2018-10-17', '5010', '', '1465.65', NULL, '0', '0', NULL, NULL, NULL),
('1802', '10', '5', '2018-10-17', '4510', '', '10', NULL, '0', '0', NULL, NULL, NULL),
('1803', '10', '5', '2018-10-17', '1200', '', '1539.65', NULL, '0', '0', '2', '19', NULL),
('1804', '10', '5', '2018-10-17', '2150', '', '-4', NULL, '0', '0', NULL, NULL, NULL),
('1805', '12', '2', '2018-10-17', '123456', '', '1806.64', 'N/A', '0', '0', NULL, NULL, NULL),
('1806', '12', '2', '2018-10-17', '1200', '', '-1806.64', NULL, '0', '0', '2', '19', NULL),
('1807', '10', '6', '2018-10-17', '4012', '', '-170', NULL, '0', '0', NULL, NULL, NULL),
('1808', '10', '6', '2018-10-17', '1112', 'Govt.Fee', '-140', 'N/A', '0', '0', NULL, NULL, NULL),
('1809', '10', '6', '2018-10-17', '1112', 'Amer service charge', '-2.63', 'N/A', '0', '0', NULL, NULL, NULL),
('1810', '10', '6', '2018-10-17', '5010', '', '142.63', NULL, '0', '0', NULL, NULL, NULL),
('1811', '10', '6', '2018-10-17', '1200', '', '171.5', NULL, '0', '0', '2', '19', NULL),
('1812', '10', '6', '2018-10-17', '2150', '', '-1.5', NULL, '0', '0', NULL, NULL, NULL),
('1813', '10', '1', '2018-10-17', '4011', '', '-2589.9', NULL, '0', '0', NULL, NULL, NULL),
('1814', '10', '1', '2018-10-17', '1111', 'Govt.Fee', '-2506.75', '20198120180014568', '0', '0', NULL, NULL, NULL),
('1815', '10', '1', '2018-10-17', '1111', 'Bank service charge', '-3.15', '20198120180014568', '0', '0', NULL, NULL, NULL),
('1816', '10', '1', '2018-10-17', '5010', '', '2509.9', NULL, '0', '0', NULL, NULL, NULL),
('1817', '10', '1', '2018-10-17', '1200', '', '2593.9', NULL, '0', '0', '2', '1', NULL),
('1818', '10', '1', '2018-10-17', '2150', '', '-4', NULL, '0', '0', NULL, NULL, NULL),
('1819', '10', '2', '2018-10-17', '4012', '', '-99.5', NULL, '0', '0', NULL, NULL, NULL),
('1820', '10', '2', '2018-10-17', '1112', 'Govt.Fee', '-89.5', '1234', '0', '0', NULL, NULL, '2018-10-17'),
('1821', '10', '2', '2018-10-17', '5010', '', '89.5', NULL, '0', '0', NULL, NULL, NULL),
('1822', '10', '2', '2018-10-17', '1200', '', '100', NULL, '0', '0', '2', '1', NULL),
('1823', '10', '2', '2018-10-17', '2150', '', '-0.5', NULL, '0', '0', NULL, NULL, NULL),
('1824', '10', '7', '2018-10-18', '4018', '', '-103', NULL, '0', '0', NULL, NULL, NULL),
('1825', '10', '7', '2018-10-18', '4321', 'TASHEEL CHARGES', '23', 'N/A', '0', '0', NULL, NULL, NULL),
('1826', '10', '7', '2018-10-18', '1116', 'Service Charge', '80', NULL, '0', '0', NULL, NULL, NULL),
('1827', '10', '7', '2018-10-18', '1200', '', '103', NULL, '0', '0', '2', '1', NULL),
('1828', '10', '7', '2018-10-18', '1200', 'Customer Card Payment', '-103', NULL, '0', '0', '2', '1', NULL);

### Structure of table `0_grn_batch` ###

DROP TABLE IF EXISTS `0_grn_batch`;

CREATE TABLE `0_grn_batch` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `supplier_id` int(11) NOT NULL DEFAULT '0',
  `purch_order_no` int(11) DEFAULT NULL,
  `reference` varchar(60) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `delivery_date` date NOT NULL DEFAULT '0000-00-00',
  `loc_code` varchar(5) COLLATE utf8_unicode_ci DEFAULT NULL,
  `rate` double DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `delivery_date` (`delivery_date`),
  KEY `purch_order_no` (`purch_order_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

### Data of table `0_grn_batch` ###


### Structure of table `0_grn_items` ###

DROP TABLE IF EXISTS `0_grn_items`;

CREATE TABLE `0_grn_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `grn_batch_id` int(11) DEFAULT NULL,
  `po_detail_item` int(11) NOT NULL DEFAULT '0',
  `item_code` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `description` tinytext COLLATE utf8_unicode_ci,
  `qty_recd` double NOT NULL DEFAULT '0',
  `quantity_inv` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `grn_batch_id` (`grn_batch_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

### Data of table `0_grn_items` ###


### Structure of table `0_groups` ###

DROP TABLE IF EXISTS `0_groups`;

CREATE TABLE `0_groups` (
  `id` smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  `description` varchar(60) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `inactive` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `description` (`description`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

### Data of table `0_groups` ###

INSERT INTO `0_groups` VALUES
('1', 'Small', '0'),
('2', 'Medium', '0'),
('3', 'Large', '0');

### Structure of table `0_item_codes` ###

DROP TABLE IF EXISTS `0_item_codes`;

CREATE TABLE `0_item_codes` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `item_code` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `stock_id` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(200) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `category_id` smallint(6) unsigned NOT NULL,
  `quantity` double NOT NULL DEFAULT '1',
  `is_foreign` tinyint(1) NOT NULL DEFAULT '0',
  `inactive` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `stock_id` (`stock_id`,`item_code`),
  KEY `item_code` (`item_code`)
) ENGINE=InnoDB AUTO_INCREMENT=977 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

### Data of table `0_item_codes` ###

INSERT INTO `0_item_codes` VALUES
('462', 'EID1', 'EID1', 'EMIRATES ID FORM/ ONE YEAR', '7', '1', '0', '0'),
('463', 'EID2', 'EID2', 'EMIRATES ID FORM/ TWO YEARS', '7', '1', '0', '0'),
('464', 'EID3', 'EID3', 'EMIRATES ID FORM/ THREE YEARS', '7', '1', '0', '0'),
('465', 'EID5', 'EID5', 'EMIRATES ID FORM/ FIVE YEARS (GCC)', '7', '1', '0', '0'),
('466', 'EIDREPL', 'EIDREPL', 'EMIRATES ID FORM/ REPLACEMENT', '7', '1', '0', '0'),
('467', 'EIDFINE', 'EIDFINE', 'FINE IN EMIRATES ID', '8', '1', '0', '0'),
('468', 'EIDRE', 'EIDRE', 'EID RESCANNING', '7', '1', '0', '0'),
('469', 'EID10', 'EID10', 'EMIRATES ID FOR CITIZEN FOR 10 YEARS', '7', '1', '0', '0'),
('470', 'TOPUP', 'TOPUP', 'EMIRATES ID FEE TOP UP', '7', '1', '0', '0'),
('471', '125', '125', 'Entry Permit - New - Sponsor Registration (OPEN FILE  )', '6', '1', '0', '0'),
('472', '227', '227', 'DEPOSIT', '8', '1', '0', '0'),
('473', '272', '272', 'IMMIGRATION FINE', '8', '1', '0', '0'),
('474', '5000', '5000', 'INSURANCE', '4', '1', '0', '0'),
('475', '801', '801', '24 HOURS MEDICAL SERVANT', '5', '1', '0', '0'),
('476', '803', '803', '48 HOURS MEDICAL', '5', '1', '0', '0'),
('477', '804', '804', '48 HOURS MEDICAL SERVANT', '5', '1', '0', '0'),
('478', '805', '805', 'MEDICAL VIP', '5', '1', '0', '0'),
('479', '806', '806', 'NORMAL MEDICAL', '5', '1', '0', '0'),
('480', '807', '807', 'NORMAL MEDICAL SERVANT', '5', '1', '0', '0'),
('482', '701', '701', 'PRO SERVICES', '8', '1', '0', '0'),
('483', '101', '101', 'Entry Permit - Cancel - Residence (Family)', '6', '1', '0', '0'),
('484', '102', '102', 'Entry Permit - Cancel - Work (Company)', '6', '1', '0', '0'),
('485', '105', '105', 'Entry Permit - Extend - Short Term Visit - On Arrival (Inside)', '6', '1', '0', '0'),
('486', '106', '106', 'Entry Permit - Extend - Short Term Visit - On Arrival (Outside)', '6', '1', '0', '0'),
('487', '108', '108', 'Entry Permit - New - Long Term Visit - Single Entry - Leisure (Inside)', '6', '1', '0', '0'),
('488', '109', '109', 'Entry Permit - New - Long Term Visit - Single Entry - Leisure (Outside)', '6', '1', '0', '0'),
('489', '110', '110', 'Entry Permit - New - Residence - Children /wife- Resident Sponsor Working In Private Sector or Free Zone (Inside)', '6', '1', '0', '0'),
('490', '111', '111', 'Entry Permit - New - Residence - Children /wife- Resident Sponsor Working In Private Sector or Free Zone (Outside)', '6', '1', '0', '0'),
('491', '116', '116', 'Entry Permit - New - Residence - Children/wife - Investor or Partner Sponsor (Inside)', '6', '1', '0', '0'),
('492', '128', '128', 'Entry Permit - New - Residence - Children/wife - Investor or Partner Sponsor (Outside)', '6', '1', '0', '0'),
('493', '130', '130', 'Entry Permit - New - Residence - Children/Wife - Resident Sponsor Working In Government (Inside)', '6', '1', '0', '0'),
('494', '141', '141', 'Entry Permit - New - Residence - Children/Wife - Resident Sponsor Working In Government (Outside)', '6', '1', '0', '0'),
('495', '142', '142', 'Entry Permit - New - Residence - Parents - Investor or Partner Sponsor (Inside)', '6', '1', '0', '0'),
('496', '155', '155', 'Entry Permit - New - Residence - Parents - Investor or Partner Sponsor (Outside)', '6', '1', '0', '0'),
('497', '156', '156', 'Entry Permit - New - Residence - Parents - National Sponsor (Inside)', '6', '1', '0', '0'),
('498', '157', '157', 'Entry Permit - New - Residence - Parents - National Sponsor (Outside)', '6', '1', '0', '0'),
('499', '158', '158', 'Entry Permit - New - Residence - Parents - Resident Sponsor Working In Private Sector or Free Zone (Inside)', '6', '1', '0', '0'),
('500', '159', '159', 'Entry Permit - New - Residence - Parents - Resident Sponsor Working In Private Sector or Free Zone (Outside)', '6', '1', '0', '0'),
('501', '163', '163', 'Entry Permit - New - Residence - Wife - National Sponsor (Inside)', '6', '1', '0', '0'),
('502', '170', '170', 'Entry Permit - New - Residence - Wife - National Sponsor (Outside)', '6', '1', '0', '0'),
('503', '171', '171', 'Entry Permit - New - Residence - Wife/ Children - Investor or Partner Sponsor (Inside)', '6', '1', '0', '0'),
('504', '172', '172', 'Entry Permit - New - Residence - Wife/ Children - Investor or Partner Sponsor (Outside)', '6', '1', '0', '0'),
('505', '175', '175', 'Entry Permit - New - Short Term Visit - Single Entry - Leisure', '6', '1', '0', '0'),
('506', '179', '179', 'Entry Permit - New - Work - Investor - partner (Inside)', '6', '1', '0', '0'),
('507', '183', '183', 'Entry Permit - New - Work - Investor - partner (Outside)', '6', '1', '0', '0'),
('508', '190', '190', 'Entry Permit - New - Work - Private Sector or Free Zone( Inside)', '6', '1', '0', '0'),
('509', '196', '196', 'Entry Permit - New - Work - Private Sector or Free Zone( Outside)', '6', '1', '0', '0'),
('510', '202', '202', 'Residence - Cancel - Cancel Residence  Inside (company)', '6', '1', '0', '0'),
('511', '203', '203', 'Residence - Cancel - Cancel Residence  Inside (Family)', '6', '1', '0', '0'),
('512', '212', '212', 'Residence - Cancel - Cancel Residence outside  (company)', '6', '1', '0', '0'),
('513', '221', '221', 'Residence - Cancel - Cancel Residence-outside (Family)', '6', '1', '0', '0'),
('514', '223', '223', 'Residence - Cancel - Cancel Visa After Entering (family)', '6', '1', '0', '0'),
('515', '225', '225', 'Residence - Data Modification - Nationality Change (Normal)', '6', '1', '0', '0'),
('516', '226', '226', 'Residence - Data Modification - Nationality Change (Urgent)', '6', '1', '0', '0'),
('517', '229', '229', 'Residence - Data Modification - Update Personal Information (Urgent)', '6', '1', '0', '0'),
('518', '231', '231', 'Residence - Data Modification - Update Personal Information (Normal)', '6', '1', '0', '0'),
('519', '235', '235', 'Residence - New - Investor or Partner (Urgent)', '6', '1', '0', '0'),
('520', '238', '238', 'Residence - New - Investor or Partner (Normal)', '6', '1', '0', '0'),
('521', '239', '239', 'Residence - New - New Born Baby - Investor or Partner Sponsor (Normal)', '6', '1', '0', '0'),
('522', '241', '241', 'Residence - New - New Born Baby - Investor or Partner Sponsor (Urgent)', '6', '1', '0', '0'),
('523', '242', '242', 'Residence - New - New Born Baby - Resident Sponsor Working In Government (Normal)', '6', '1', '0', '0'),
('524', '243', '243', 'Residence - New - New Born Baby - Resident Sponsor Working In Government (Urgent)', '6', '1', '0', '0'),
('525', '248', '248', 'Residence - New - New Born Baby - Resident Sponsor Working In Private Sector or Free Zone 2 Year (Normal)', '6', '1', '0', '0'),
('526', '249', '249', 'Residence - New - New Born Baby - Resident Sponsor Working In Private Sector or Free Zone 2 Year (Urgent)', '6', '1', '0', '0'),
('527', '252', '252', 'Residence - New - New Born Baby - Resident Sponsor Working In Private Sector or Free Zone 3 Year (Urgent)', '6', '1', '0', '0'),
('528', '254', '254', 'Residence - New - New Born Baby - Resident Sponsor Working In Private Sector or Free Zone 3 Year (Normal)', '6', '1', '0', '0'),
('529', '257', '257', 'Residence - New - Parents - Investor or Partner Sponsor (Urgent)', '6', '1', '0', '0'),
('530', '259', '259', 'Residence - New - Parents - Investor or Partner Sponsor (Normal)', '6', '1', '0', '0'),
('531', '261', '261', 'Residence - New - Parents - Resident Sponsor Working In Government (Normal)', '6', '1', '0', '0'),
('532', '264', '264', 'Residence - New - Parents - Resident Sponsor Working In Government (Urgent)', '6', '1', '0', '0'),
('533', '265', '265', 'Residence - New - Parents - Resident Sponsor Working In Private Sector or Free Zone (Normal)', '6', '1', '0', '0'),
('534', '266', '266', 'Residence - New - Parents - Resident Sponsor Working In Private Sector or Free Zone (Urgent)', '6', '1', '0', '0'),
('535', '267', '267', 'Residence - New - Son Older Than 21 Years Old - Resident Sponsor Working In Private Sector or Free Zone (Normal)', '6', '1', '0', '0'),
('536', '268', '268', 'Residence - New - Son Older Than 21 Years Old - Resident Sponsor Working In Private Sector or Free Zone (Urgent)', '6', '1', '0', '0'),
('537', '269', '269', 'Residence - New - Wife And Children - Investor or Partner Sponsor (Normal)', '6', '1', '0', '0'),
('538', '270', '270', 'Residence - New - Wife And Children - Investor or Partner Sponsor (Urgent)', '6', '1', '0', '0'),
('539', '273', '273', 'Residence - New - Wife And Children - Resident Sponsor Working In Government (Normal)', '6', '1', '0', '0'),
('540', '280', '280', 'Residence - New - Wife And Children - Resident Sponsor Working In Government (Urgent)', '6', '1', '0', '0'),
('541', '282', '282', 'Residence - New - Work - Private Sector or Free Zone', '6', '1', '0', '0'),
('542', '283', '283', 'Residence - Renew - Investor or Partner (Urgent)', '6', '1', '0', '0'),
('543', '284', '284', 'Residence - Renew - Investor or Partner (Normal)', '6', '1', '0', '0'),
('544', '285', '285', 'Residence - Renew - Parents - Investor or Partner Sponsor (Normal)', '6', '1', '0', '0'),
('545', '288', '288', 'Residence - Renew - Parents - Investor or Partner Sponsor (Urgent)', '6', '1', '0', '0'),
('546', '290', '290', 'Residence - Renew - Parents - National Sponsor (Normal)', '6', '1', '0', '0'),
('547', '303', '303', 'Residence - Renew - Parents - National Sponsor (urgent)', '6', '1', '0', '0'),
('548', '311', '311', 'Residence - Renew - Parents - Resident Sponsor Working In Government (Normal)', '6', '1', '0', '0'),
('549', '320', '320', 'Residence - Renew - Parents - Resident Sponsor Working In Government (Urgent)', '6', '1', '0', '0'),
('550', '501', '501', 'Residence - Renew - Parents - Resident Sponsor Working In Private Sector or Free Zone (Normal)', '6', '1', '0', '0'),
('551', '502', '502', 'Residence - Renew - Parents - Resident Sponsor Working In Private Sector or Free Zone (Urgent)', '6', '1', '0', '0'),
('552', '503', '503', 'Residence - Renew - Son Older Than 21 Years Old - Investor or Partner Sponsor', '6', '1', '0', '0'),
('553', '504', '504', 'Residence - Renew - Son Older Than 21 Years Old - Resident Sponsor Working In Government', '6', '1', '0', '0'),
('554', '505', '505', 'Residence - Renew - Son Older Than 21 Years Old - Resident Sponsor Working In Private Sector or Free Zone', '6', '1', '0', '0'),
('555', '506', '506', 'Residence - Renew - Wife And Children - Investor or Partner Sponsor (Normal)', '6', '1', '0', '0'),
('556', '507', '507', 'Residence - Renew - Wife And Children - Investor or Partner Sponsor (Urgent)', '6', '1', '0', '0'),
('557', '508', '508', 'Residence - Renew - Wife And Children - National Sponsor (Normal)', '6', '1', '0', '0'),
('558', '509', '509', 'Residence - Renew - Wife And Children - National Sponsor (urgent)', '6', '1', '0', '0'),
('559', '510', '510', 'Residence - Renew - Wife And Children - Resident Sponsor Working In Government (Urgent)', '6', '1', '0', '0'),
('560', '511', '511', 'Residence - Renew - Wife And Children - Resident Sponsor Working In Government (Normal)', '6', '1', '0', '0'),
('561', '512', '512', 'Residence - Renew - Wife And Children - Resident Sponsor Working In Private Sector or Free Zone 2 year (Normal)', '6', '1', '0', '0'),
('562', '513', '513', 'Residence - Renew - Wife And Children - Resident Sponsor Working In Private Sector or Free Zone 2 Year(Urgent)', '6', '1', '0', '0'),
('563', '514', '514', 'Residence - Renew - Wife And Children - Resident Sponsor Working In Private Sector or Free Zone 3 Year(Urgent)', '6', '1', '0', '0'),
('564', '515', '515', 'Residence - Renew - Wife And Children - Resident Sponsor Working In Private Sector or Free Zone 3 Year(Normal)', '6', '1', '0', '0'),
('565', '516', '516', 'Residence - Renew - Work - Government (Urgent)', '6', '1', '0', '0'),
('566', '517', '517', 'Residence - Renew - Work - Government (Normal)', '6', '1', '0', '0'),
('567', '518', '518', 'Residence - Renew - Work - Private Sector or Free Zone (Urgent)', '6', '1', '0', '0'),
('568', '519', '519', 'Residence - Renew - Work - Private Sector or Free Zone (Normal)', '6', '1', '0', '0'),
('569', '520', '520', 'Residence - Sponsorship Transfer - Transfer Plus Residence - Family - Resident Sponsor Working In Private Sector or Free Zone - From Same Emirate', '6', '1', '0', '0'),
('570', '521', '521', 'Residence - Sponsorship Transfer - Transfer Plus Residence - Investor or Partner? - From Same Emirate', '6', '1', '0', '0'),
('571', '522', '522', 'Residence - Sponsorship Transfer - Transfer Plus Residence - Work - Private Sector or Free Zone - From Same Emirate', '6', '1', '0', '0'),
('572', '523', '523', 'Residence - Transfer Residence To New Passport - Due To Passport Lost', '6', '1', '0', '0'),
('573', '524', '524', 'Residence - Transfer Residence To New Passport - From Another Passport', '6', '1', '0', '0'),
('574', '525', '525', 'Services - Change Status - (Family)', '6', '1', '0', '0'),
('575', '526', '526', 'Services - Change Status -(Company)', '6', '1', '0', '0'),
('576', '527', '527', 'Services - FIS - Temporary Closure - Absconding ', '6', '1', '0', '0'),
('577', '528', '528', 'Services - FIS - Temporary Closure - Absconding ', '6', '1', '0', '0'),
('578', '529', '529', 'Services - Work Permit - Work - MOL (Inside)', '6', '1', '0', '0'),
('579', '530', '530', 'Services - Work Permit - Work - MOL (Outside)', '6', '1', '0', '0'),
('580', '300', '300', 'Entry Permit / New / Job Seekers', '6', '1', '0', '0'),
('581', '305', '305', 'Residence / Sponsorship Transfer / Transfer Plus Residence - Work - Government - From Same Emirate (Urgent)', '6', '1', '0', '0'),
('582', '316', '316', 'Residence - Cancel - Cancel Visa After Entering (Company)', '6', '1', '0', '0'),
('583', '317', '317', 'Residence / New / Wife And Children - Resident Sponsor Working In Private Sector or Free Zone (Normal)', '6', '1', '0', '0'),
('584', '319', '319', 'Residence / New / Wife And Children - Resident Sponsor Working In Private Sector or Free Zone (Urgent))', '6', '1', '0', '0'),
('585', '326', '326', 'Residence / New / Wife And Children - Resident Sponsor Working In Private Sector or Free Zone', '6', '1', '0', '0'),
('586', '322', '322', 'Residence / New / Wife And Children - Resident Sponsor Working In Private Sector or Free Zone  (Urgent)	', '6', '1', '0', '0'),
('587', '325', '325', 'Residence - New - Work - Private Sector or Free Zone (urgent) ', '6', '1', '0', '0'),
('588', '328', '328', 'Residence - New - Wife And Children - Resident Sponsor Working In Government 1 year  (Normal)', '6', '1', '0', '0'),
('589', '330', '330', 'Residence / Renew / Wife And Children - Resident Sponsor Working In Private Sector or Free Zone 1 year (Nomal)', '6', '1', '0', '0'),
('590', '358', '358', 'Residence / Renew / Wife And Children - Resident Sponsor Working In Private Sector or Free Zone 1 year (Urgentl) ', '6', '1', '0', '0'),
('591', '329', '329', 'Residence / New / New Born Baby - National Sponsor', '6', '1', '0', '0'),
('592', '344', '344', 'Residence / New / Wife And Children - Resident Sponsor Working In Private Sector or Free Zone', '6', '1', '0', '0'),
('598', '3012', '3012', 'Entry Permit / New / Job Seekers', '6', '1', '0', '0'),
('607', 'M24', 'M24', '24 HOURS MEDICAL', '5', '1', '0', '0'),
('610', 'CBD', 'CBD', 'Long Term Visit', '7', '1', '0', '0'),
('611', 'ED2', 'ED2', 'EMIRATES ID FORM/ TWOYEARS', '7', '1', '0', '0'),
('613', 'EID123', 'EID123', 'EMIRATES ID FINE REMOVAL REQUEST', '7', '1', '0', '0'),
('614', 'MT1', 'MT1', 'MEDICALTOPUP  ( NORMAL TO 48 HRS )', '5', '1', '0', '0'),
('615', 'ET24', 'ET24', 'MEDICALTOPUP  ( NORMAL TO 24 HRS )', '5', '1', '0', '0'),
('616', 'MV', 'MV', 'MEDICALTOPUP  ( NORMAL TO VIP  )', '5', '1', '0', '0'),
('618', '2268', '2268', 'Residence - Renew - Son Older Than 21 Years Old - Resident Sponsor Working In Private Sector or Free Zone (Urgent)', '6', '1', '0', '0'),
('851', '11001', '11001', 'Contract Nawakas', '1', '1', '0', '0'),
('852', '11002', '11002', 'Nawakas Scanning Document', '1', '1', '0', '0'),
('853', '11003', '11003', 'Receiving Transaction for withdraw abscond', '1', '1', '0', '0'),
('854', '11004', '11004', 'Sponsor Information', '1', '1', '0', '0'),
('855', '11005', '11005', 'Update Immigration File Number', '1', '1', '0', '0'),
('856', '11006', '11006', 'Complaint Cancellation', '1', '1', '0', '0'),
('857', '11007', '11007', 'Complaint Reactivation', '1', '1', '0', '0'),
('858', '11008', '11008', 'Complaint settlement', '1', '1', '0', '0'),
('859', '11009', '11009', 'Modify Complain Contact Information', '1', '1', '0', '0'),
('860', '11010', '11010', 'Modify Withdraw Abscond Contact Information', '1', '1', '0', '0'),
('861', '11011', '11011', 'Withdraw Absconding Questionaries&#039;', '1', '1', '0', '0'),
('862', '11012', '11012', 'Withdraw Absconding Request', '1', '1', '0', '0'),
('863', '11013', '11013', 'Company Employees List', '1', '1', '0', '0'),
('864', '11014', '11014', 'Electronic Company Card', '1', '1', '0', '0'),
('865', '11015', '11015', 'Electronic Work Permit Information', '1', '1', '0', '0'),
('866', '11016', '11016', 'E-Netwasal Employee Request', '1', '1', '0', '0'),
('867', '11017', '11017', 'Expired Electronic Work Permit List', '1', '1', '0', '0'),
('868', '11018', '11018', 'National Labor List', '1', '1', '0', '0'),
('869', '11019', '11019', 'Owner Role Information', '1', '1', '0', '0'),
('870', '11020', '11020', 'Person Information', '1', '1', '0', '0'),
('871', '11021', '11021', 'PRO Details', '1', '1', '0', '0'),
('872', '11022', '11022', 'Employee Certificate', '1', '1', '0', '0'),
('873', '11023', '11023', 'Company Report (All in One)', '1', '1', '0', '0'),
('874', '11024', '11024', 'Typing Electronic Pre Approval for Work Permit Application - Prepaid', '1', '1', '0', '0'),
('875', '11025', '11025', 'Replacement of Pre Approval for Work Permit', '1', '1', '0', '0'),
('876', '11026', '11026', 'Typing Electronic Pre Approval for Work Permit Application-Zones Corp', '1', '1', '0', '0'),
('877', '11027', '11027', 'Typing New Job Offer Letter', '1', '1', '0', '0'),
('878', '11028', '11028', 'Typing Modification of Job Offer Letter', '1', '1', '0', '0'),
('879', '11029', '11029', 'Typing Cancellation of Job Offer Letter', '1', '1', '0', '0'),
('880', '11030', '11030', 'Typing Work permit for Student Training', '1', '1', '0', '0'),
('881', '11031', '11031', 'Application for Incomplete Contract', '1', '1', '0', '0'),
('882', '11032', '11032', 'Modification of National or GCC Electronic Work Permit', '1', '1', '0', '0'),
('883', '11033', '11033', 'New Electronic Work Permit', '1', '1', '0', '0'),
('884', '11034', '11034', 'New National and GCC Electronic Work Permit', '1', '1', '0', '0'),
('885', '11035', '11035', 'Renew Mission Electronic Work Permit', '1', '1', '0', '0'),
('886', '11036', '11036', 'Request for Original Contract', '1', '1', '0', '0'),
('887', '11037', '11037', 'Electronic Work Permit / Pre Approval for Work Permit Fines', '1', '1', '0', '0'),
('888', '11038', '11038', 'Company Fines', '1', '1', '0', '0'),
('889', '11039', '11039', 'Not Re-New License for temporary employment agency/ employment agency', '1', '1', '0', '0'),
('890', '11040', '11040', 'Payment for New Company', '1', '1', '0', '0'),
('891', '11041', '11041', 'Cancel Bank Guarantee Refund Request-Before Submission', '1', '1', '0', '0'),
('892', '11042', '11042', 'Refund of Bank Guarantee', '1', '1', '0', '0'),
('893', '11043', '11043', 'Death Cancellation', '1', '1', '0', '0'),
('894', '11044', '11044', 'Electronic Work Permit Cancellation', '1', '1', '0', '0'),
('895', '11045', '11045', 'Labor Case Cancellation', '1', '1', '0', '0'),
('896', '11046', '11046', 'Outside the Country Cancellation', '1', '1', '0', '0'),
('897', '11047', '11047', 'Pre Approval for Work Permit Cancellation', '1', '1', '0', '0'),
('898', '11048', '11048', 'Sick Cancellation', '1', '1', '0', '0'),
('899', '11049', '11049', 'Temporary/ Part Time/ Juvenile/ Student Training Work Permit Cancellation', '1', '1', '0', '0'),
('900', '11050', '11050', 'Unused Pre Approval for Work Permit Cancellation', '1', '1', '0', '0'),
('901', '11051', '11051', 'Submit Modify or Renew - Modify Electronic Work Permit Application', '1', '1', '0', '0'),
('902', '11052', '11052', 'Submit National New Electronic Work Permit Application', '1', '1', '0', '0'),
('903', '11053', '11053', 'Submit New Electronic Work Permit and Mission Electronic Work Permit Application', '1', '1', '0', '0'),
('904', '11054', '11054', 'Submit Renew Labor Card Application', '1', '1', '0', '0'),
('905', '11055', '11055', 'Submit Replacement of Pre Approval for Work Permit', '1', '1', '0', '0'),
('906', '11056', '11056', 'Submit Work Permit for Student Training', '1', '1', '0', '0'),
('907', '11057', '11057', 'Update Work Permit Information', '1', '1', '0', '0'),
('908', '11058', '11058', 'Deduction Duplicate File', '1', '1', '0', '0'),
('909', '11059', '11059', 'Deduction Electronic Work Permit in another company', '1', '1', '0', '0'),
('910', '11060', '11060', 'Deduction No data in Immigration', '1', '1', '0', '0'),
('911', '11061', '11061', 'Deduction Old Cancellation not sent to computer', '1', '1', '0', '0'),
('912', '11062', '11062', 'Deported by other Authority Cancellation', '1', '1', '0', '0'),
('913', '11063', '11063', 'Request for Mission Quota', '1', '1', '0', '0'),
('914', '11064', '11064', 'Request for quota for Electronic companies', '1', '1', '0', '0'),
('915', '11065', '11065', 'Applying new Quota', '1', '1', '0', '0'),
('916', '11066', '11066', 'Cancellation of E-quota application', '1', '1', '0', '0'),
('917', '11067', '11067', 'Quota for Zones Corp', '1', '1', '0', '0'),
('918', '11068', '11068', 'Update Approved Quota', '1', '1', '0', '0'),
('919', '11069', '11069', 'Customer Service Request', '1', '1', '0', '0'),
('920', '11070', '11070', 'Police Letter To Arrest Runaway Labor', '1', '1', '0', '0'),
('921', '11071', '11071', 'Request for Certificate Exemption', '1', '1', '0', '0'),
('922', '11072', '11072', 'Modify Person Information', '1', '1', '0', '0'),
('923', '11073', '11073', 'New Person Creation', '1', '1', '0', '0'),
('924', '11074', '11074', 'Cancel New Electronic Work Permit Application', '1', '1', '0', '0'),
('925', '11075', '11075', 'Online Cancellation', '1', '1', '0', '0'),
('926', '11076', '11076', 'Submit New Person', '1', '1', '0', '0'),
('927', '11077', '11077', 'Submit of Bank Guarantee', '1', '1', '0', '0'),
('928', '11078', '11078', 'Company License Renewal', '1', '1', '0', '0'),
('929', '11079', '11079', 'Contract Registration', '1', '1', '0', '0'),
('930', '11080', '11080', 'Sub Contract Registration', '1', '1', '0', '0'),
('931', '11081', '11081', 'Submit Add/Modify Owner', '1', '1', '0', '0'),
('932', '11082', '11082', 'Submit Cancel Establishment', '1', '1', '0', '0'),
('933', '11083', '11083', 'Absconding - Electronic', '1', '1', '0', '0'),
('934', '11084', '11084', 'Typing Mission Pre Approval for Work Permit Application', '1', '1', '0', '0'),
('935', '11085', '11085', 'Typing Temporary Pre Approval for Work Permit Application', '1', '1', '0', '0'),
('936', '11086', '11086', 'Typing Part Time Pre Approval for Work Permit Application', '1', '1', '0', '0'),
('937', '11087', '11087', 'Typing Juvenile Pre Approval for Work Permit Application', '1', '1', '0', '0'),
('938', '11088', '11088', 'Typing Electronic Pre Approval for Work Permit Application', '1', '1', '0', '0'),
('939', '11089', '11089', 'Typing Relative Pre Approval for Work Permit', '1', '1', '0', '0'),
('940', '11090', '11090', 'Modify Contract', '1', '1', '0', '0'),
('941', '11091', '11091', 'Modify Electronic Work Permit Application', '1', '1', '0', '0'),
('942', '11092', '11092', 'Payment Form - Electronic Quota', '1', '1', '0', '0'),
('943', '11093', '11093', 'Renew Electronic Work Permit ? Level 1', '1', '1', '0', '0'),
('944', '11094', '11094', 'Pre Approval for Work Permit Payment Fees ? Level 1', '1', '1', '0', '0'),
('945', '11095', '11095', 'Modify + Renewal - Modify Electronic Work Permit Application level 1', '1', '1', '0', '0'),
('946', '11096', '11096', 'Payment for Pre Approval for Work Permit extension 10 days', '1', '1', '0', '0'),
('947', '11097', '11097', 'Submit Part Time Pre Approval for Work Permit', '1', '1', '0', '0'),
('948', '11098', '11098', 'Submit Pre Approval for Work Permit for Juvenile', '1', '1', '0', '0'),
('949', '11099', '11099', 'Submit Temporary Pre Approval for Work Permit', '1', '1', '0', '0'),
('950', '11100', '11100', 'Renew Electronic Work Permit ? Level 2A', '1', '1', '0', '0'),
('951', '11101', '11101', 'Pre Approval for Work Permit Payment Fees ? Level 2A', '1', '1', '0', '0'),
('952', '11102', '11102', 'Submit Relative Sponsor Pre Approval for Work Permit ? Level 2A', '1', '1', '0', '0'),
('953', '11103', '11103', 'Modify + Renewal - Modify Electronic Work Permit Application level 2A', '1', '1', '0', '0'),
('954', '11104', '11104', 'Payment for Pre Approval for Work Permit extension 20 days', '1', '1', '0', '0'),
('955', '11105', '11105', 'Renew Electronic Work Permit ? LEVEL 2B', '1', '1', '0', '0'),
('956', '11106', '11106', 'Pre Approval for Work Permit Payment Fees ? Level 2B', '1', '1', '0', '0'),
('957', '11107', '11107', 'Payment for Pre Approval for Work Permit extension 30 days', '1', '1', '0', '0'),
('958', '11108', '11108', 'Submit Relative Sponsor Pre Approval for Work Permit ? Level 2B', '1', '1', '0', '0'),
('959', '11109', '11109', 'Modify + Renewal - Modify Electronic Work Permit Application level 2B', '1', '1', '0', '0'),
('960', '11110', '11110', 'Renew Electronic Work Permit ? LEVEL 2C', '1', '1', '0', '0'),
('961', '11111', '11111', 'Pre Approval for Work Permit Payment Fees ? Level 2C', '1', '1', '0', '0'),
('962', '11112', '11112', 'Payment for Pre Approval for Work Permit extension 40 days', '1', '1', '0', '0'),
('963', '11113', '11113', 'Submit Relative Sponsor Pre Approval for Work Permit ? Level 2C', '1', '1', '0', '0'),
('964', '11114', '11114', 'Modify + Renewal - Modify Electronic Work Permit Application level 2C', '1', '1', '0', '0'),
('965', '11115', '11115', 'Payment for Pre Approval for Work Permit extension 50 days', '1', '1', '0', '0'),
('966', '11116', '11116', 'Renew Electronic Work Permit ? level 3 Age Above 65 yrs. ', '1', '1', '0', '0'),
('967', '11117', '11117', 'Pre Approval for Work Permit Payment Fees ? Level 3', '1', '1', '0', '0'),
('968', '11118', '11118', 'Payment for Pre Approval for Work Permit extension 60 days', '1', '1', '0', '0'),
('969', '11119', '11119', 'Modify + Renewal - Modify Electronic Work Permit Application level 3', '1', '1', '0', '0'),
('970', '11120', '11120', 'New License for temporary employment agency', '1', '1', '0', '0'),
('971', '11121', '11121', 'Re-New License For employment agency', '1', '1', '0', '0'),
('972', '11122', '11122', 'New License For employment agency', '1', '1', '0', '0'),
('973', '11123', '11123', 'Re-New License for temporary employment agency', '1', '1', '0', '0'),
('974', '11000', '11000', 'Fine - Tasheel', '1', '1', '0', '0'),
('975', '1T1', '1T1', 'TASHEEL SERVICE', '1', '1', '0', '0'),
('976', '6000', '6000', 'LETTER TYPING', '8', '1', '0', '0');

### Structure of table `0_item_tax_type_exemptions` ###

DROP TABLE IF EXISTS `0_item_tax_type_exemptions`;

CREATE TABLE `0_item_tax_type_exemptions` (
  `item_tax_type_id` int(11) NOT NULL DEFAULT '0',
  `tax_type_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`item_tax_type_id`,`tax_type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

### Data of table `0_item_tax_type_exemptions` ###


### Structure of table `0_item_tax_types` ###

DROP TABLE IF EXISTS `0_item_tax_types`;

CREATE TABLE `0_item_tax_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(60) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `exempt` tinyint(1) NOT NULL DEFAULT '0',
  `inactive` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

### Data of table `0_item_tax_types` ###

INSERT INTO `0_item_tax_types` VALUES
('1', 'Regular', '0', '0'),
('2', 'No Tax', '1', '0');

### Structure of table `0_item_units` ###

DROP TABLE IF EXISTS `0_item_units`;

CREATE TABLE `0_item_units` (
  `abbr` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `decimals` tinyint(2) NOT NULL,
  `inactive` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`abbr`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

### Data of table `0_item_units` ###

INSERT INTO `0_item_units` VALUES
('each', 'Each', '0', '0'),
('hr', 'Hours', '0', '0');

### Structure of table `0_journal` ###

DROP TABLE IF EXISTS `0_journal`;

CREATE TABLE `0_journal` (
  `type` smallint(6) NOT NULL DEFAULT '0',
  `trans_no` int(11) NOT NULL DEFAULT '0',
  `tran_date` date DEFAULT '0000-00-00',
  `reference` varchar(60) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `source_ref` varchar(60) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `event_date` date DEFAULT '0000-00-00',
  `doc_date` date NOT NULL DEFAULT '0000-00-00',
  `currency` char(3) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `amount` double NOT NULL DEFAULT '0',
  `rate` double NOT NULL DEFAULT '1',
  PRIMARY KEY (`type`,`trans_no`),
  KEY `tran_date` (`tran_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

### Data of table `0_journal` ###

INSERT INTO `0_journal` VALUES
('0', '1', '2018-10-17', '001/2018', '', '2018-10-17', '2018-10-17', 'AED', '5000', '1');

### Structure of table `0_kv_empl_allowance_advanced` ###

DROP TABLE IF EXISTS `0_kv_empl_allowance_advanced`;

CREATE TABLE `0_kv_empl_allowance_advanced` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `allowance_id` int(11) NOT NULL,
  `grade_id` int(11) NOT NULL,
  `formula` text NOT NULL,
  `value` varchar(20) NOT NULL,
  `percentage` double NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ;

### Data of table `0_kv_empl_allowance_advanced` ###


### Structure of table `0_kv_empl_allowances` ###

DROP TABLE IF EXISTS `0_kv_empl_allowances`;

CREATE TABLE `0_kv_empl_allowances` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `debit_code` int(10) NOT NULL,
  `credit_code` int(10) NOT NULL,
  `unique_name` varchar(5) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `type` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `basic` int(11) DEFAULT NULL,
  `Tax` int(2) NOT NULL,
  `loan` tinyint(1) NOT NULL,
  `esic` tinyint(1) NOT NULL,
  `pf` tinyint(1) NOT NULL,
  `gross` tinyint(1) NOT NULL,
  `sort_order` int(3) NOT NULL,
  `inactive` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

### Data of table `0_kv_empl_allowances` ###


### Structure of table `0_kv_empl_attendance_settings` ###

DROP TABLE IF EXISTS `0_kv_empl_attendance_settings`;

CREATE TABLE `0_kv_empl_attendance_settings` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `dept_id` int(5) NOT NULL,
  `option_name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `option_value` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

### Data of table `0_kv_empl_attendance_settings` ###


### Structure of table `0_kv_empl_attendancee` ###

DROP TABLE IF EXISTS `0_kv_empl_attendancee`;

CREATE TABLE `0_kv_empl_attendancee` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `month` int(2) DEFAULT NULL,
  `year` int(2) DEFAULT NULL,
  `dept_id` int(10) NOT NULL,
  `empl_id` int(10) DEFAULT NULL,
  `1` varchar(2) NOT NULL,
  `1_in` time NOT NULL,
  `1_out` time NOT NULL,
  `2` varchar(2) NOT NULL,
  `2_in` time NOT NULL,
  `2_out` time NOT NULL,
  `3` varchar(2) NOT NULL,
  `3_in` time NOT NULL,
  `3_out` time NOT NULL,
  `4` varchar(2) NOT NULL,
  `4_in` time NOT NULL,
  `4_out` time NOT NULL,
  `5` varchar(2) NOT NULL,
  `5_in` time NOT NULL,
  `5_out` time NOT NULL,
  `6` varchar(2) NOT NULL,
  `6_in` time NOT NULL,
  `6_out` time NOT NULL,
  `7` varchar(2) NOT NULL,
  `7_in` time NOT NULL,
  `7_out` time NOT NULL,
  `8` varchar(2) NOT NULL,
  `8_in` time NOT NULL,
  `8_out` time NOT NULL,
  `9` varchar(2) NOT NULL,
  `9_in` time NOT NULL,
  `9_out` time NOT NULL,
  `10` varchar(2) NOT NULL,
  `10_in` time NOT NULL,
  `10_out` time NOT NULL,
  `11` varchar(2) NOT NULL,
  `11_in` time NOT NULL,
  `11_out` time NOT NULL,
  `12` varchar(2) NOT NULL,
  `12_in` time NOT NULL,
  `12_out` time NOT NULL,
  `13` varchar(2) NOT NULL,
  `13_in` time NOT NULL,
  `13_out` time NOT NULL,
  `14` varchar(2) NOT NULL,
  `14_in` time NOT NULL,
  `14_out` time NOT NULL,
  `15` varchar(2) NOT NULL,
  `15_in` time NOT NULL,
  `15_out` time NOT NULL,
  `16` varchar(2) NOT NULL,
  `16_in` time NOT NULL,
  `16_out` time NOT NULL,
  `17` varchar(2) NOT NULL,
  `17_in` time NOT NULL,
  `17_out` time NOT NULL,
  `18` varchar(2) NOT NULL,
  `18_in` time NOT NULL,
  `18_out` time NOT NULL,
  `19` varchar(2) NOT NULL,
  `19_in` time NOT NULL,
  `19_out` time NOT NULL,
  `20` varchar(2) NOT NULL,
  `20_in` time NOT NULL,
  `20_out` time NOT NULL,
  `21` varchar(2) NOT NULL,
  `21_in` time NOT NULL,
  `21_out` time NOT NULL,
  `22` varchar(2) NOT NULL,
  `22_in` time NOT NULL,
  `22_out` time NOT NULL,
  `23` varchar(2) NOT NULL,
  `23_in` time NOT NULL,
  `23_out` time NOT NULL,
  `24` varchar(2) NOT NULL,
  `24_in` time NOT NULL,
  `24_out` time NOT NULL,
  `25` varchar(2) NOT NULL,
  `25_in` time NOT NULL,
  `25_out` time NOT NULL,
  `26` varchar(2) NOT NULL,
  `26_in` time NOT NULL,
  `26_out` time NOT NULL,
  `27` varchar(2) NOT NULL,
  `27_in` time NOT NULL,
  `27_out` time NOT NULL,
  `28` varchar(2) NOT NULL,
  `28_in` time NOT NULL,
  `28_out` time NOT NULL,
  `29` varchar(2) NOT NULL,
  `29_in` time NOT NULL,
  `29_out` time NOT NULL,
  `30` varchar(2) NOT NULL,
  `30_in` time NOT NULL,
  `30_out` time NOT NULL,
  `31` varchar(2) NOT NULL,
  `31_in` time NOT NULL,
  `31_out` time NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

### Data of table `0_kv_empl_attendancee` ###


### Structure of table `0_kv_empl_country` ###

DROP TABLE IF EXISTS `0_kv_empl_country`;

CREATE TABLE `0_kv_empl_country` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `iso` varchar(50) DEFAULT NULL,
  `local_name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=247 DEFAULT CHARSET=utf8 ;

### Data of table `0_kv_empl_country` ###

INSERT INTO `0_kv_empl_country` VALUES
('1', 'AD', 'Andorra'),
('2', 'AE', 'United Arab Emirates'),
('3', 'AF', 'Afghanistan'),
('4', 'AG', 'Antigua and Barbuda'),
('5', 'AI', 'Anguilla'),
('6', 'AL', 'Albania'),
('7', 'AM', 'Armenia'),
('8', 'AN', 'Netherlands Antilles'),
('9', 'AO', 'Angola'),
('10', 'AQ', 'Antarctica'),
('11', 'AR', 'Argentina'),
('12', 'AS', 'American Samoa'),
('13', 'AT', 'Austria'),
('14', 'AU', 'Australia'),
('15', 'AW', 'Aruba'),
('16', 'AX', 'Aland Islands'),
('17', 'AZ', 'Azerbaijan'),
('18', 'BA', 'Bosnia and Herzegovina'),
('19', 'BB', 'Barbados'),
('20', 'BD', 'Bangladesh'),
('21', 'BE', 'Belgium'),
('22', 'BF', 'Burkina Faso'),
('23', 'BG', 'Bulgaria'),
('24', 'BH', 'Bahrain'),
('25', 'BI', 'Burundi'),
('26', 'BJ', 'Benin'),
('27', 'BL', 'Saint Barthlemy'),
('28', 'BM', 'Bermuda'),
('29', 'BN', 'Brunei Darussalam'),
('30', 'BO', 'BoliviaBolivia, Plurinational state of'),
('31', 'BR', 'Brazil'),
('32', 'BS', 'Bahamas'),
('33', 'BT', 'Bhutan'),
('34', 'BV', 'Bouvet Island'),
('35', 'BW', 'Botswana'),
('36', 'BY', 'Belarus'),
('37', 'BZ', 'Belize'),
('38', 'CA', 'Canada'),
('39', 'CC', 'Cocos (Keeling) Islands'),
('40', 'CD', 'Congo, The Democratic Republic of the'),
('41', 'CF', 'Central African Republic'),
('42', 'CG', 'Congo'),
('43', 'CH', 'Switzerland'),
('45', 'CK', 'Cook Islands'),
('46', 'CL', 'Chile'),
('47', 'CM', 'Cameroon'),
('48', 'CN', 'China'),
('49', 'CO', 'Colombia'),
('50', 'CR', 'Costa Rica'),
('51', 'CU', 'Cuba'),
('52', 'CV', 'Cape Verde'),
('53', 'CX', 'Christmas Island'),
('54', 'CY', 'Cyprus'),
('55', 'CZ', 'Czech Republic'),
('56', 'DE', 'Germany'),
('57', 'DJ', 'Djibouti'),
('58', 'DK', 'Denmark'),
('59', 'DM', 'Dominica'),
('60', 'DO', 'Dominican Republic'),
('61', 'DZ', 'Algeria'),
('62', 'EC', 'Ecuador'),
('63', 'EE', 'Estonia'),
('64', 'EG', 'Egypt'),
('65', 'EH', 'Western Sahara'),
('66', 'ER', 'Eritrea'),
('67', 'ES', 'Spain'),
('68', 'ET', 'Ethiopia'),
('69', 'FI', 'Finland'),
('70', 'FJ', 'Fiji'),
('71', 'FK', 'Falkland Islands (Malvinas)'),
('72', 'FM', 'Micronesia, Federated States of'),
('73', 'FO', 'Faroe Islands'),
('74', 'FR', 'France'),
('75', 'GA', 'Gabon'),
('76', 'GB', 'United Kingdom'),
('77', 'GD', 'Grenada'),
('78', 'GE', 'Georgia'),
('79', 'GF', 'French Guiana'),
('80', 'GG', 'Guernsey'),
('81', 'GH', 'Ghana'),
('82', 'GI', 'Gibraltar'),
('83', 'GL', 'Greenland'),
('84', 'GM', 'Gambia'),
('85', 'GN', 'Guinea'),
('86', 'GP', 'Guadeloupe'),
('87', 'GQ', 'Equatorial Guinea'),
('88', 'GR', 'Greece'),
('89', 'GS', 'South Georgia and the South Sandwich Islands'),
('90', 'GT', 'Guatemala'),
('91', 'GU', 'Guam'),
('92', 'GW', 'Guinea-Bissau'),
('93', 'GY', 'Guyana'),
('94', 'HK', 'Hong Kong'),
('95', 'HM', 'Heard Island and McDonald Islands'),
('96', 'HN', 'Honduras'),
('97', 'HR', 'Croatia'),
('98', 'HT', 'Haiti'),
('99', 'HU', 'Hungary'),
('100', 'ID', 'Indonesia'),
('101', 'IE', 'Ireland'),
('102', 'IL', 'Israel'),
('103', 'IM', 'Isle of Man'),
('104', 'IN', 'India'),
('105', 'IO', 'British Indian Ocean Territory'),
('106', 'IQ', 'Iraq'),
('107', 'IR', 'Iran, Islamic Republic of'),
('108', 'IS', 'Iceland'),
('109', 'IT', 'Italy'),
('110', 'JE', 'Jersey'),
('111', 'JM', 'Jamaica'),
('112', 'JO', 'Jordan'),
('113', 'JP', 'Japan'),
('114', 'KE', 'Kenya'),
('115', 'KG', 'Kyrgyzstan'),
('116', 'KH', 'Cambodia'),
('117', 'KI', 'Kiribati'),
('118', 'KM', 'Comoros'),
('119', 'KN', 'Saint Kitts and Nevis'),
('120', 'KP', 'Korea, Democratic People&#039;s Republic of'),
('121', 'KR', 'Korea, Republic of'),
('122', 'KW', 'Kuwait'),
('123', 'KY', 'Cayman Islands'),
('124', 'KZ', 'Kazakhstan'),
('125', 'LA', 'Lao People&#039;s Democratic Republic'),
('126', 'LB', 'Lebanon'),
('127', 'LC', 'Saint Lucia'),
('128', 'LI', 'Liechtenstein'),
('129', 'LK', 'Sri Lanka'),
('130', 'LR', 'Liberia'),
('131', 'LS', 'Lesotho'),
('132', 'LT', 'Lithuania'),
('133', 'LU', 'Luxembourg'),
('134', 'LV', 'Latvia'),
('135', 'LY', 'Libyan Arab Jamahiriya'),
('136', 'MA', 'Morocco'),
('137', 'MC', 'Monaco'),
('138', 'MD', 'Moldova, Republic of'),
('139', 'ME', 'Montenegro'),
('140', 'MF', 'Saint Martin'),
('141', 'MG', 'Madagascar'),
('142', 'MH', 'Marshall Islands'),
('143', 'MK', 'Macedonia'),
('144', 'ML', 'Mali'),
('145', 'MM', 'Myanmar'),
('146', 'MN', 'Mongolia'),
('147', 'MO', 'Macao'),
('148', 'MP', 'Northern Mariana Islands'),
('149', 'MQ', 'Martinique'),
('150', 'MR', 'Mauritania'),
('151', 'MS', 'Montserrat'),
('152', 'MT', 'Malta'),
('153', 'MU', 'Mauritius'),
('154', 'MV', 'Maldives'),
('155', 'MW', 'Malawi'),
('156', 'MX', 'Mexico'),
('157', 'MY', 'Malaysia'),
('158', 'MZ', 'Mozambique'),
('159', 'NA', 'Namibia'),
('160', 'NC', 'New Caledonia'),
('161', 'NE', 'Niger'),
('162', 'NF', 'Norfolk Island'),
('163', 'NG', 'Nigeria'),
('164', 'NI', 'Nicaragua'),
('165', 'NL', 'Netherlands'),
('166', 'NO', 'Norway'),
('167', 'NP', 'Nepal'),
('168', 'NR', 'Nauru'),
('169', 'NU', 'Niue'),
('170', 'NZ', 'New Zealand'),
('171', 'OM', 'Oman'),
('172', 'PA', 'Panama'),
('173', 'PE', 'Peru'),
('174', 'PF', 'French Polynesia'),
('175', 'PG', 'Papua New Guinea'),
('176', 'PH', 'Philippines'),
('177', 'PK', 'Pakistan'),
('178', 'PL', 'Poland'),
('179', 'PM', 'Saint Pierre and Miquelon'),
('180', 'PN', 'Pitcairn'),
('181', 'PR', 'Puerto Rico'),
('182', 'PS', 'Palestinian Territory, Occupied'),
('183', 'PT', 'Portugal'),
('184', 'PW', 'Palau'),
('185', 'PY', 'Paraguay'),
('186', 'QA', 'Qatar'),
('188', 'RO', 'Romania'),
('189', 'RS', 'Serbia'),
('190', 'RU', 'Russian Federation'),
('191', 'RW', 'Rwanda'),
('192', 'SA', 'Saudi Arabia'),
('193', 'SB', 'Solomon Islands'),
('194', 'SC', 'Seychelles'),
('195', 'SD', 'Sudan'),
('196', 'SE', 'Sweden'),
('197', 'SG', 'Singapore'),
('198', 'SH', 'Saint Helena'),
('199', 'SI', 'Slovenia'),
('200', 'SJ', 'Svalbard and Jan Mayen'),
('201', 'SK', 'Slovakia'),
('202', 'SL', 'Sierra Leone'),
('203', 'SM', 'San Marino'),
('204', 'SN', 'Senegal'),
('205', 'SO', 'Somalia'),
('206', 'SR', 'Suriname'),
('207', 'ST', 'Sao Tome and Principe'),
('208', 'SV', 'El Salvador'),
('209', 'SY', 'Syrian Arab Republic'),
('210', 'SZ', 'Swaziland'),
('211', 'TC', 'Turks and Caicos Islands'),
('212', 'TD', 'Chad'),
('213', 'TF', 'French Southern Territories'),
('214', 'TG', 'Togo'),
('215', 'TH', 'Thailand'),
('216', 'TJ', 'Tajikistan'),
('217', 'TK', 'Tokelau'),
('218', 'TL', 'Timor-Leste'),
('219', 'TM', 'Turkmenistan'),
('220', 'TN', 'Tunisia'),
('221', 'TO', 'Tonga'),
('222', 'TR', 'Turkey'),
('223', 'TT', 'Trinidad and Tobago'),
('224', 'TV', 'Tuvalu'),
('225', 'TW', 'Taiwan'),
('226', 'TZ', 'Tanzania, United Republic of'),
('227', 'UA', 'Ukraine'),
('228', 'UG', 'Uganda'),
('229', 'UM', 'United States Minor Outlying Islands'),
('230', 'US', 'United States'),
('231', 'UY', 'Uruguay'),
('232', 'UZ', 'Uzbekistan'),
('233', 'VA', 'Holy See (Vatican City State)'),
('234', 'VC', 'Saint Vincent and the Grenadines'),
('235', 'VE', 'Venezuela, Bolivarian Republic of'),
('236', 'VG', 'Virgin Islands, British'),
('237', 'VI', 'Virgin Islands, U.S.'),
('238', 'VN', 'Viet Nam'),
('239', 'VU', 'Vanuatu'),
('240', 'WF', 'Wallis and Futuna'),
('241', 'WS', 'Samoa'),
('242', 'YE', 'Yemen'),
('243', 'YT', 'Mayotte'),
('244', 'ZA', 'South Africa'),
('245', 'ZM', 'Zambia'),
('246', 'ZW', 'Zimbabwe');

### Structure of table `0_kv_empl_cv` ###

DROP TABLE IF EXISTS `0_kv_empl_cv`;

CREATE TABLE `0_kv_empl_cv` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `empl_id` varchar(10) NOT NULL,
  `cv_title` varchar(60) NOT NULL,
  `doc_type` varchar(100) NOT NULL,
  `exp_date` date NOT NULL,
  `notify_from` date NOT NULL,
  `alert` tinyint(1) NOT NULL,
  `related_to` int(11) NOT NULL,
  `filename` varchar(600) NOT NULL,
  `filesize` int(11) NOT NULL,
  `filetype` varchar(60) NOT NULL,
  `unique_name` varchar(60) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ;

### Data of table `0_kv_empl_cv` ###


### Structure of table `0_kv_empl_degree` ###

DROP TABLE IF EXISTS `0_kv_empl_degree`;

CREATE TABLE `0_kv_empl_degree` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `empl_id` varchar(10) NOT NULL,
  `degree` varchar(20) NOT NULL,
  `major` varchar(20) NOT NULL,
  `university` varchar(80) NOT NULL,
  `grade` varchar(20) NOT NULL,
  `year` date NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ;

### Data of table `0_kv_empl_degree` ###


### Structure of table `0_kv_empl_departments` ###

DROP TABLE IF EXISTS `0_kv_empl_departments`;

CREATE TABLE `0_kv_empl_departments` (
  `id` smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  `description` varchar(60) NOT NULL DEFAULT '',
  `inactive` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1 ;

### Data of table `0_kv_empl_departments` ###

INSERT INTO `0_kv_empl_departments` VALUES
('1', 'Sales', '0'),
('2', 'Accounts', '0');

### Structure of table `0_kv_empl_designation` ###

DROP TABLE IF EXISTS `0_kv_empl_designation`;

CREATE TABLE `0_kv_empl_designation` (
  `id` smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  `description` varchar(60) NOT NULL DEFAULT '',
  `inactive` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `description` (`description`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 ;

### Data of table `0_kv_empl_designation` ###

INSERT INTO `0_kv_empl_designation` VALUES
('1', 'Developer', '0');

### Structure of table `0_kv_empl_designation_group` ###

DROP TABLE IF EXISTS `0_kv_empl_designation_group`;

CREATE TABLE `0_kv_empl_designation_group` (
  `id` smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  `description` varchar(60) NOT NULL DEFAULT '',
  `inactive` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `description` (`description`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 ;

### Data of table `0_kv_empl_designation_group` ###

INSERT INTO `0_kv_empl_designation_group` VALUES
('1', 'Staff', '0');

### Structure of table `0_kv_empl_doc_type` ###

DROP TABLE IF EXISTS `0_kv_empl_doc_type`;

CREATE TABLE `0_kv_empl_doc_type` (
  `id` smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  `description` varchar(60) NOT NULL DEFAULT '',
  `days` int(4) NOT NULL,
  `inactive` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ;

### Data of table `0_kv_empl_doc_type` ###


### Structure of table `0_kv_empl_esb` ###

DROP TABLE IF EXISTS `0_kv_empl_esb`;

CREATE TABLE `0_kv_empl_esb` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `empl_id` int(10) NOT NULL,
  `last_gross` double NOT NULL,
  `date` date NOT NULL,
  `days_worked` decimal(20,2) NOT NULL,
  `status` int(2) NOT NULL,
  `loan_amount` double NOT NULL,
  `amount` double NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

### Data of table `0_kv_empl_esb` ###


### Structure of table `0_kv_empl_esic_pf` ###

DROP TABLE IF EXISTS `0_kv_empl_esic_pf`;

CREATE TABLE `0_kv_empl_esic_pf` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `allowance_id` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `amt_limit` double NOT NULL,
  `date` date NOT NULL,
  `employer` double NOT NULL,
  `company` double NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

### Data of table `0_kv_empl_esic_pf` ###


### Structure of table `0_kv_empl_experience` ###

DROP TABLE IF EXISTS `0_kv_empl_experience`;

CREATE TABLE `0_kv_empl_experience` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `empl_id` varchar(10) NOT NULL,
  `company_name` varchar(260) NOT NULL,
  `company_location` varchar(160) NOT NULL,
  `department` varchar(150) NOT NULL,
  `designation` varchar(150) NOT NULL,
  `s_date` date NOT NULL,
  `e_date` date NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ;

### Data of table `0_kv_empl_experience` ###


### Structure of table `0_kv_empl_family` ###

DROP TABLE IF EXISTS `0_kv_empl_family`;

CREATE TABLE `0_kv_empl_family` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `empl_id` int(10) NOT NULL,
  `full_name` varchar(160) NOT NULL,
  `relation` int(11) NOT NULL,
  `filename` varchar(600) NOT NULL,
  `filesize` int(11) NOT NULL,
  `filetype` varchar(60) NOT NULL,
  `unique_name` varchar(60) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ;

### Data of table `0_kv_empl_family` ###


### Structure of table `0_kv_empl_gazetted_holidays` ###

DROP TABLE IF EXISTS `0_kv_empl_gazetted_holidays`;

CREATE TABLE `0_kv_empl_gazetted_holidays` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `year` int(5) NOT NULL,
  `date` date NOT NULL,
  `reason` text COLLATE utf8_unicode_ci NOT NULL,
  `inactive` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

### Data of table `0_kv_empl_gazetted_holidays` ###


### Structure of table `0_kv_empl_grade` ###

DROP TABLE IF EXISTS `0_kv_empl_grade`;

CREATE TABLE `0_kv_empl_grade` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `description` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `min_salary` int(20) NOT NULL,
  `max_salary` int(20) NOT NULL,
  `cl` double NOT NULL,
  `al` double NOT NULL,
  `ml` double NOT NULL,
  `inactive` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

### Data of table `0_kv_empl_grade` ###

INSERT INTO `0_kv_empl_grade` VALUES
('1', 'STAFF', '2000', '10000', '30', '0', '0', '0');

### Structure of table `0_kv_empl_info` ###

DROP TABLE IF EXISTS `0_kv_empl_info`;

CREATE TABLE `0_kv_empl_info` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `empl_id` int(10) NOT NULL,
  `empl_salutation` varchar(9) NOT NULL,
  `empl_firstname` varchar(120) NOT NULL,
  `empl_lastname` varchar(50) NOT NULL,
  `addr_line1` varchar(200) NOT NULL,
  `addr_line2` varchar(200) NOT NULL,
  `address2` text NOT NULL,
  `empl_city` varchar(60) NOT NULL,
  `empl_state` varchar(100) NOT NULL,
  `country` int(5) NOT NULL,
  `gender` int(2) NOT NULL,
  `date_of_birth` date NOT NULL,
  `age` int(3) NOT NULL,
  `marital_status` int(2) NOT NULL,
  `office_phone` varchar(15) NOT NULL,
  `home_phone` varchar(15) NOT NULL,
  `mobile_phone` varchar(15) NOT NULL,
  `email` varchar(120) NOT NULL,
  `status` int(2) NOT NULL,
  `date_of_status_change` date NOT NULL,
  `reason_status_change` text NOT NULL,
  `empl_pic` varchar(10) NOT NULL,
  `report_to` varchar(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 ;

### Data of table `0_kv_empl_info` ###

INSERT INTO `0_kv_empl_info` VALUES
('1', '101', '1', 'Bipin', 'Ks', '', '', '', '', '', '2', '1', '1998-08-02', '0', '1', '', '', '0544706704', '', '1', '0000-00-00', '', '', '');

### Structure of table `0_kv_empl_job` ###

DROP TABLE IF EXISTS `0_kv_empl_job`;

CREATE TABLE `0_kv_empl_job` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `empl_id` int(10) NOT NULL,
  `grade` tinyint(2) NOT NULL,
  `al` int(11) NOT NULL,
  `cl` int(11) NOT NULL,
  `ml` int(11) NOT NULL,
  `department` tinyint(2) NOT NULL,
  `shift` int(3) NOT NULL,
  `desig_group` tinyint(2) NOT NULL,
  `desig` varchar(40) NOT NULL,
  `currency` varchar(5) NOT NULL,
  `joining` date NOT NULL,
  `date_of_desig_change` date NOT NULL,
  `empl_type` tinyint(2) NOT NULL,
  `expd_percentage_amt` double NOT NULL,
  `working_branch` tinyint(2) NOT NULL,
  `mod_of_pay` int(2) NOT NULL,
  `bank_name` varchar(40) NOT NULL,
  `acc_no` varchar(30) NOT NULL,
  `branch_detail` varchar(100) NOT NULL,
  `ifsc` varchar(20) NOT NULL,
  `ESIC` varchar(50) NOT NULL,
  `PF` varchar(50) NOT NULL,
  `PAN` varchar(30) NOT NULL,
  `bloog_group` int(2) NOT NULL,
  `aadhar` int(20) NOT NULL,
  `nominee_name` varchar(100) NOT NULL,
  `nominee_phone` varchar(16) NOT NULL,
  `nominee_email` varchar(60) NOT NULL,
  `nominee_address` text NOT NULL,
  `gross_pay_annum` double NOT NULL,
  `gross` double NOT NULL,
  `3` double NOT NULL,
  `6` double NOT NULL,
  `12` double NOT NULL,
  `13` double NOT NULL,
  `14` double NOT NULL,
  `15` double NOT NULL,
  `16` double NOT NULL,
  `17` double NOT NULL,
  `8` double NOT NULL,
  `9` double NOT NULL,
  `10` double NOT NULL,
  `11` double NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 ;

### Data of table `0_kv_empl_job` ###

INSERT INTO `0_kv_empl_job` VALUES
('1', '101', '1', '0', '360', '0', '2', '0', '1', '1', 'AED', '2018-08-02', '0000-00-00', '1', '0', '1', '1', '', '', '', '', '', '', '', '1', '0', '', '', '', '', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0');

### Structure of table `0_kv_empl_leave_applied` ###

DROP TABLE IF EXISTS `0_kv_empl_leave_applied`;

CREATE TABLE `0_kv_empl_leave_applied` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `year` int(5) NOT NULL,
  `empl_id` int(10) NOT NULL,
  `leave_type` varchar(5) COLLATE utf8_unicode_ci NOT NULL,
  `reason` text COLLATE utf8_unicode_ci NOT NULL,
  `date` date NOT NULL,
  `days` int(3) NOT NULL,
  `status` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

### Data of table `0_kv_empl_leave_applied` ###


### Structure of table `0_kv_empl_leave_encashment` ###

DROP TABLE IF EXISTS `0_kv_empl_leave_encashment`;

CREATE TABLE `0_kv_empl_leave_encashment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `empl_id` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `department` int(11) NOT NULL,
  `year` int(5) NOT NULL,
  `month` int(5) NOT NULL,
  `date` date NOT NULL,
  `allowances` text COLLATE utf8_unicode_ci NOT NULL,
  `payable_days` double NOT NULL,
  `amount` double NOT NULL,
  `carry_forward` tinyint(1) NOT NULL,
  `inactive` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

### Data of table `0_kv_empl_leave_encashment` ###


### Structure of table `0_kv_empl_loan` ###

DROP TABLE IF EXISTS `0_kv_empl_loan`;

CREATE TABLE `0_kv_empl_loan` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `empl_id` varchar(10) NOT NULL,
  `date` date NOT NULL,
  `loan_date` date NOT NULL,
  `loan_amount` decimal(15,2) NOT NULL,
  `currency` varchar(4) NOT NULL,
  `rate` double NOT NULL,
  `loan_type_id` int(5) NOT NULL,
  `periods` int(5) NOT NULL,
  `monthly_pay` decimal(15,2) NOT NULL,
  `periods_paid` int(5) NOT NULL,
  `status` varchar(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ;

### Data of table `0_kv_empl_loan` ###


### Structure of table `0_kv_empl_loan_types` ###

DROP TABLE IF EXISTS `0_kv_empl_loan_types`;

CREATE TABLE `0_kv_empl_loan_types` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `loan_name` varchar(200) NOT NULL,
  `interest_rate` double NOT NULL,
  `allowance_id` int(5) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ;

### Data of table `0_kv_empl_loan_types` ###


### Structure of table `0_kv_empl_memo` ###

DROP TABLE IF EXISTS `0_kv_empl_memo`;

CREATE TABLE `0_kv_empl_memo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `empl_id` int(11) NOT NULL,
  `emplr_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `reason` text COLLATE utf8_unicode_ci NOT NULL,
  `status` varchar(80) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

### Data of table `0_kv_empl_memo` ###


### Structure of table `0_kv_empl_option` ###

DROP TABLE IF EXISTS `0_kv_empl_option`;

CREATE TABLE `0_kv_empl_option` (
  `id` int(20) NOT NULL AUTO_INCREMENT,
  `option_name` varchar(150) NOT NULL,
  `option_value` longtext NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=latin1 ;

### Data of table `0_kv_empl_option` ###

INSERT INTO `0_kv_empl_option` VALUES
('1', 'weekly_off', 'YToxOntpOjA7czozOiJGcmkiO30='),
('2', 'empl_ref_type', '1'),
('3', 'salary_account', ''),
('4', 'paid_from_account', ''),
('5', 'expd_percentage_amt', '30'),
('6', 'next_empl_id', '102'),
('7', 'non_taxable_allowance_group', '12'),
('8', 'taxable_allowance_group', '11'),
('9', 'BeginTime', '08:00:00'),
('10', 'EndTime', '17:00:00'),
('11', 'ot_factor', '1.25'),
('12', 'frequency', '6'),
('15', 'test_mode', '0'),
('19', 'travel_debit', ''),
('20', 'petrol_debit', ''),
('21', 'petrol_credit', ''),
('22', 'travel_credit', ''),
('23', 'car_rate', '6'),
('24', 'bike_rate', '3'),
('25', 'BeginDay', '1'),
('26', 'EndDay', '31'),
('28', 'monthsList', 'YToyOntpOjA7aTo4O2k6MTtpOjI7fQ=='),
('29', 'home_country', '2'),
('30', 'debit_encashment', ''),
('31', 'credit_encashment', ''),
('32', 'special_ot_factor', '1.5'),
('33', 'zk_ip', ''),
('34', 'tax_used', ''),
('35', 'enable_employee_access', '1'),
('36', 'master_role', '2'),
('37', 'max_leave_forward', '6'),
('38', 'days_round_to_one_month', '22'),
('39', 'esb_salary', '1'),
('40', 'esb_country', '0');

### Structure of table `0_kv_empl_picklist` ###

DROP TABLE IF EXISTS `0_kv_empl_picklist`;

CREATE TABLE `0_kv_empl_picklist` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `type` int(3) NOT NULL,
  `description` varchar(250) NOT NULL,
  `inactive` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ;

### Data of table `0_kv_empl_picklist` ###


### Structure of table `0_kv_empl_salary` ###

DROP TABLE IF EXISTS `0_kv_empl_salary`;

CREATE TABLE `0_kv_empl_salary` (
  `id` int(20) NOT NULL AUTO_INCREMENT,
  `empl_id` varchar(10) NOT NULL,
  `month` int(2) NOT NULL,
  `year` int(2) NOT NULL,
  `date` date NOT NULL,
  `currency` varchar(5) NOT NULL,
  `rate` double NOT NULL,
  `AL` double NOT NULL,
  `GL` tinyint(1) NOT NULL,
  `CL` double NOT NULL,
  `ML` double NOT NULL,
  `gross` double NOT NULL,
  `ctc` double NOT NULL,
  `lop_amount` double NOT NULL,
  `loans` text NOT NULL,
  `dimension` int(5) NOT NULL,
  `dimension2` int(5) NOT NULL,
  `adv_sal` double NOT NULL,
  `net_pay` double NOT NULL,
  `misc` double NOT NULL,
  `ot_other_allowance` double NOT NULL,
  `ot_earnings` double NOT NULL,
  `3` double NOT NULL,
  `6` double NOT NULL,
  `12` double NOT NULL,
  `13` double NOT NULL,
  `14` double NOT NULL,
  `15` double NOT NULL,
  `16` double NOT NULL,
  `17` double NOT NULL,
  `8` double NOT NULL,
  `9` double NOT NULL,
  `10` double NOT NULL,
  `11` double NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 ;

### Data of table `0_kv_empl_salary` ###

INSERT INTO `0_kv_empl_salary` VALUES
('1', '101', '7', '2', '2018-08-02', '', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0', '-1', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0');

### Structure of table `0_kv_empl_salary_advance` ###

DROP TABLE IF EXISTS `0_kv_empl_salary_advance`;

CREATE TABLE `0_kv_empl_salary_advance` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `empl_id` int(10) NOT NULL,
  `date` date NOT NULL,
  `month` int(10) NOT NULL,
  `year` int(10) NOT NULL,
  `amount` double NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

### Data of table `0_kv_empl_salary_advance` ###


### Structure of table `0_kv_empl_shifts` ###

DROP TABLE IF EXISTS `0_kv_empl_shifts`;

CREATE TABLE `0_kv_empl_shifts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `description` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  `BeginTime` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `EndTime` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `inactive` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

### Data of table `0_kv_empl_shifts` ###


### Structure of table `0_kv_empl_status_types` ###

DROP TABLE IF EXISTS `0_kv_empl_status_types`;

CREATE TABLE `0_kv_empl_status_types` (
  `id` smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  `description` varchar(60) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `inactive` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `description` (`description`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

### Data of table `0_kv_empl_status_types` ###

INSERT INTO `0_kv_empl_status_types` VALUES
('1', 'Active', '0'),
('2', 'Inactive', '0'),
('3', 'Resigned', '0'),
('4', 'Decesed', '0'),
('5', 'Terminated', '0');

### Structure of table `0_kv_empl_taxes` ###

DROP TABLE IF EXISTS `0_kv_empl_taxes`;

CREATE TABLE `0_kv_empl_taxes` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `description` varchar(100) NOT NULL,
  `min_sal` int(10) NOT NULL,
  `max_sal` int(10) NOT NULL,
  `percentage` int(10) NOT NULL,
  `frequency` int(3) NOT NULL,
  `offset` int(10) NOT NULL,
  `year` int(5) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ;

### Data of table `0_kv_empl_taxes` ###


### Structure of table `0_kv_empl_training` ###

DROP TABLE IF EXISTS `0_kv_empl_training`;

CREATE TABLE `0_kv_empl_training` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `empl_id` varchar(10) NOT NULL,
  `training_desc` varchar(60) NOT NULL,
  `course` varchar(50) NOT NULL,
  `cost` int(8) NOT NULL,
  `institute` varchar(60) NOT NULL,
  `s_date` date NOT NULL,
  `e_date` date NOT NULL,
  `notes` longtext NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ;

### Data of table `0_kv_empl_training` ###


### Structure of table `0_loc_stock` ###

DROP TABLE IF EXISTS `0_loc_stock`;

CREATE TABLE `0_loc_stock` (
  `loc_code` char(5) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `stock_id` char(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `reorder_level` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`loc_code`,`stock_id`),
  KEY `stock_id` (`stock_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

### Data of table `0_loc_stock` ###

INSERT INTO `0_loc_stock` VALUES
('DEF', '1000', '0'),
('DEF', '101', '0'),
('DEF', '102', '0'),
('DEF', '103', '0'),
('DEF', '10365939', '0'),
('DEF', '1339', '0'),
('DEF', '1340', '0'),
('DEF', '1341', '0'),
('DEF', '200', '0'),
('DEF', '20000', '0'),
('DEF', '201', '0'),
('DEF', '202', '0'),
('DEF', '22559319', '0'),
('DEF', '23975057', '0'),
('DEF', '24198752', '0'),
('DEF', '25024173', '0'),
('DEF', '2YMU', '0'),
('DEF', '301', '0'),
('DEF', '402', '0'),
('DEF', '404', '0'),
('DEF', '405', '0'),
('DEF', '406', '0'),
('DEF', '407', '0'),
('DEF', '408', '0'),
('DEF', '409', '0'),
('DEF', '410', '0'),
('DEF', '411', '0'),
('DEF', '412', '0'),
('DEF', '413', '0'),
('DEF', '414', '0'),
('DEF', '415', '0'),
('DEF', '416', '0'),
('DEF', '417', '0'),
('DEF', '418', '0'),
('DEF', '419', '0'),
('DEF', '420', '0'),
('DEF', '421', '0'),
('DEF', '422', '0'),
('DEF', '423', '0'),
('DEF', '424', '0'),
('DEF', '425', '0'),
('DEF', '427', '0'),
('DEF', '500', '0'),
('DEF', '501', '0'),
('DEF', '502', '0'),
('DEF', '503', '0'),
('DEF', '504', '0'),
('DEF', '505', '0'),
('DEF', '506', '0'),
('DEF', '507', '0'),
('DEF', '508', '0'),
('DEF', '509', '0'),
('DEF', '51792725', '0'),
('DEF', '53259752', '0'),
('DEF', '600', '0'),
('DEF', '601', '0'),
('DEF', '602', '0'),
('DEF', '603', '0'),
('DEF', '604', '0'),
('DEF', '605', '0'),
('DEF', '61606883', '0'),
('DEF', '68630614', '0'),
('DEF', '700', '0'),
('DEF', '80882688', '0'),
('DEF', '82808587', '0'),
('DEF', '89732700', '0'),
('DEF', '93892063', '0'),
('DEF', '95143590', '0'),
('DEF', '98461677', '0'),
('DEF', 'BG', '0'),
('DEF', 'EID1', '0'),
('DEF', 'EID10', '0'),
('DEF', 'EID2', '0'),
('DEF', 'EID3', '0'),
('DEF', 'EID5', '0'),
('DEF', 'EIDFINE', '0'),
('DEF', 'EIDRE', '0'),
('DEF', 'EIDREPL', '0'),
('DEF', 'NCHANGE', '0'),
('DEF', 'TOPUP', '0'),
('DEF', 'VISA2YR', '0');

### Structure of table `0_locations` ###

DROP TABLE IF EXISTS `0_locations`;

CREATE TABLE `0_locations` (
  `loc_code` varchar(5) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `location_name` varchar(60) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `delivery_address` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `phone` varchar(30) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `phone2` varchar(30) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `fax` varchar(30) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `email` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `contact` varchar(30) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `fixed_asset` tinyint(1) NOT NULL DEFAULT '0',
  `inactive` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`loc_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

### Data of table `0_locations` ###

INSERT INTO `0_locations` VALUES
('DEF', 'Default', 'N/A', '', '', '', '', '', '0', '0');

### Structure of table `0_other_charges_trans_details` ###

DROP TABLE IF EXISTS `0_other_charges_trans_details`;

CREATE TABLE `0_other_charges_trans_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `debtor_trans_detail_id` int(11) NOT NULL DEFAULT '0',
  `acc_code` varchar(256) COLLATE utf8_unicode_ci DEFAULT NULL,
  `amount` double DEFAULT '0',
  `description` varchar(256) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

### Data of table `0_other_charges_trans_details` ###

INSERT INTO `0_other_charges_trans_details` VALUES
('1', '625', '4321', '23', 'TASHEEL CHARGES');

### Structure of table `0_overtime` ###

DROP TABLE IF EXISTS `0_overtime`;

CREATE TABLE `0_overtime` (
  `overtime_id` int(11) NOT NULL AUTO_INCREMENT,
  `overtime_name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `overtime_rate` float NOT NULL,
  `inactive` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`overtime_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

### Data of table `0_overtime` ###


### Structure of table `0_payment_terms` ###

DROP TABLE IF EXISTS `0_payment_terms`;

CREATE TABLE `0_payment_terms` (
  `terms_indicator` int(11) NOT NULL AUTO_INCREMENT,
  `terms` char(80) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `days_before_due` smallint(6) NOT NULL DEFAULT '0',
  `day_in_following_month` smallint(6) NOT NULL DEFAULT '0',
  `inactive` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`terms_indicator`),
  UNIQUE KEY `terms` (`terms`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

### Data of table `0_payment_terms` ###

INSERT INTO `0_payment_terms` VALUES
('4', 'Invoice without payment', '1', '0', '0'),
('5', '1 month', '30', '0', '0');

### Structure of table `0_payroll_account` ###

DROP TABLE IF EXISTS `0_payroll_account`;

CREATE TABLE `0_payroll_account` (
  `account_id` int(11) NOT NULL AUTO_INCREMENT,
  `account_code` int(11) NOT NULL,
  PRIMARY KEY (`account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

### Data of table `0_payroll_account` ###


### Structure of table `0_payroll_structure` ###

DROP TABLE IF EXISTS `0_payroll_structure`;

CREATE TABLE `0_payroll_structure` (
  `salary_scale_id` int(11) NOT NULL,
  `payroll_rule` text COLLATE utf8_unicode_ci NOT NULL,
  KEY `salary_scale_id` (`salary_scale_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

### Data of table `0_payroll_structure` ###


### Structure of table `0_payslip` ###

DROP TABLE IF EXISTS `0_payslip`;

CREATE TABLE `0_payslip` (
  `payslip_no` int(11) NOT NULL AUTO_INCREMENT,
  `trans_no` int(11) NOT NULL DEFAULT '0',
  `emp_id` int(11) NOT NULL,
  `generated_date` date NOT NULL,
  `from_date` date NOT NULL,
  `to_date` date NOT NULL,
  `leaves` int(11) NOT NULL,
  `deductable_leaves` int(11) NOT NULL,
  `payable_amount` double NOT NULL DEFAULT '0',
  `salary_amount` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`payslip_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

### Data of table `0_payslip` ###


### Structure of table `0_payslip_details` ###

DROP TABLE IF EXISTS `0_payslip_details`;

CREATE TABLE `0_payslip_details` (
  `payslip_no` int(11) NOT NULL AUTO_INCREMENT,
  `detail` int(11) NOT NULL DEFAULT '0',
  `amount` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`payslip_no`,`detail`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

### Data of table `0_payslip_details` ###


### Structure of table `0_prices` ###

DROP TABLE IF EXISTS `0_prices`;

CREATE TABLE `0_prices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `stock_id` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `sales_type_id` int(11) NOT NULL DEFAULT '0',
  `curr_abrev` char(3) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `price` double NOT NULL DEFAULT '0',
  `pf_amount` double DEFAULT '0',
  `govt_fee` double DEFAULT '0',
  `govt_bank_account` varchar(128) COLLATE utf8_unicode_ci DEFAULT '0',
  `bank_service_charge` double DEFAULT '0',
  `bank_service_charge_vat` double DEFAULT '0',
  `commission_loc_user` double DEFAULT '0',
  `commission_non_loc_user` double DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `price` (`stock_id`,`sales_type_id`,`curr_abrev`)
) ENGINE=InnoDB AUTO_INCREMENT=548 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

### Data of table `0_prices` ###

INSERT INTO `0_prices` VALUES
('262', 'EID1', '1', 'AED', '27.37', '0', '0', '0', '0', '0', '0', '0'),
('263', 'EID2', '1', 'AED', '27.37', '0', '0', '0', '0', '0', '0', '0'),
('264', 'EID3', '1', 'AED', '27.37', '0', '0', '0', '0', '0', '0', '0'),
('265', 'EID5', '1', 'AED', '27.37', '0', '0', '0', '0', '0', '0', '0'),
('266', 'EIDREPL', '1', 'AED', '27.37', '0', '0', '0', '0', '0', '0', '0'),
('267', 'EIDFINE', '1', 'AED', '0', '0', '0', '0', '0', '0', '0', '0'),
('268', 'EIDRE', '1', 'AED', '10', '0', '0', '0', '0', '0', '0', '0'),
('269', 'EID10', '1', 'AED', '27.37', '0', '0', '0', '0', '0', '0', '0'),
('270', 'TOPUP', '1', 'AED', '7.37', '0', '0', '0', '0', '0', '0', '0'),
('271', '125', '1', 'AED', '100', '0', '0', '0', '0', '0', '0', '0'),
('272', '227', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('273', '272', '1', 'AED', '0', '0', '0', '0', '0', '0', '0', '0'),
('274', '5000', '1', 'AED', '10', '0', '0', '0', '0', '0', '0', '0'),
('276', '801', '1', 'AED', '46.84', '0', '0', '0', '0', '0', '0', '0'),
('277', '803', '1', 'AED', '46.84', '0', '0', '0', '0', '0', '0', '0'),
('278', '804', '1', 'AED', '46.84', '0', '0', '0', '0', '0', '0', '0'),
('279', '805', '1', 'AED', '46.84', '0', '0', '0', '0', '0', '0', '0'),
('280', '806', '1', 'AED', '46.84', '0', '0', '0', '0', '0', '0', '0'),
('281', '807', '1', 'AED', '46.84', '0', '0', '0', '0', '0', '0', '0'),
('282', '101', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('283', '102', '1', 'AED', '47', '0', '0', '0', '0', '0', '0', '0'),
('284', '105', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('285', '106', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('286', '108', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('287', '109', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('288', '110', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('289', '111', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('290', '116', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('291', '128', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('292', '130', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('293', '141', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('294', '142', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('295', '155', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('296', '156', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('297', '157', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('298', '158', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('299', '159', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('300', '163', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('301', '170', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('302', '171', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('303', '172', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('304', '175', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('305', '179', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('306', '183', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('307', '190', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('308', '196', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('309', '202', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('310', '203', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('311', '212', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('312', '221', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('313', '223', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('314', '225', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('315', '226', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('316', '229', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('317', '231', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('318', '235', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('319', '238', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('320', '239', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('321', '241', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('322', '242', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('323', '243', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('324', '248', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('325', '249', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('326', '252', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('327', '254', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('328', '257', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('329', '259', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('330', '261', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('331', '264', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('332', '265', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('333', '266', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('334', '267', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('335', '268', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('336', '269', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('337', '270', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('338', '273', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('339', '280', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('340', '282', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('341', '283', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('342', '284', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('343', '285', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('344', '288', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('345', '290', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('346', '303', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('347', '311', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('348', '320', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('349', '501', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('350', '502', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('351', '503', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('352', '504', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('353', '505', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('354', '506', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('355', '507', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('356', '508', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('357', '509', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('358', '510', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('359', '511', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('360', '512', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('361', '513', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('362', '514', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('363', '515', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('364', '516', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('365', '517', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('366', '518', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('367', '519', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('368', '520', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('369', '521', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('370', '522', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('371', '523', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('372', '524', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('373', '525', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('374', '526', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('375', '527', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('376', '528', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('377', '529', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('378', '530', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('379', '300', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('380', '305', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('381', '316', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('382', '317', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('383', '319', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('384', '326', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('385', '322', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('386', '325', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('387', '328', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('388', '330', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('389', '358', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('390', '329', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('391', '344', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('403', '401', '1', 'AED', '46.84', '0', '0', '0', '0', '0', '0', '0'),
('404', 'M24', '1', 'AED', '46.84', '0', '0', '0', '0', '0', '0', '0'),
('408', 'ED2', '1', 'AED', '27.37', '0', '0', '0', '0', '0', '0', '0'),
('409', '3012', '1', 'AED', '0', '0', '0', '0', '0', '0', '0', '0'),
('411', 'EID123', '1', 'AED', '30', '0', '0', '0', '0', '0', '0', '0'),
('412', 'CBD', '1', 'AED', '0', '0', '0', '0', '0', '0', '0', '0'),
('413', '301', '1', 'AED', '0', '0', '0', '0', '0', '0', '0', '0'),
('414', 'MT1', '1', 'AED', '46.84', '0', '0', '0', '0', '0', '0', '0'),
('415', 'ET24', '1', 'AED', '0', '0', '0', '0', '0', '0', '0', '0'),
('416', 'MV', '1', 'AED', '0', '0', '0', '0', '0', '0', '0', '0'),
('420', '2268', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('421', '11001', '1', 'AED', '0', '0', '0', '0', '0', '0', '0', '0'),
('422', '11002', '1', 'AED', '0', '0', '0', '0', '0', '0', '0', '0'),
('423', '11003', '1', 'AED', '19', '0', '0', '0', '0', '0', '0', '0'),
('424', '11004', '1', 'AED', '19', '0', '0', '0', '0', '0', '0', '0'),
('425', '11005', '1', 'AED', '19', '0', '0', '0', '0', '0', '0', '0'),
('426', '11006', '1', 'AED', '19', '0', '0', '0', '0', '0', '0', '0'),
('427', '11007', '1', 'AED', '19', '0', '0', '0', '0', '0', '0', '0'),
('428', '11008', '1', 'AED', '19', '0', '0', '0', '0', '0', '0', '0'),
('429', '11009', '1', 'AED', '19', '0', '0', '0', '0', '0', '0', '0'),
('430', '11010', '1', 'AED', '19', '0', '0', '0', '0', '0', '0', '0'),
('431', '11011', '1', 'AED', '19', '0', '0', '0', '0', '0', '0', '0'),
('432', '11012', '1', 'AED', '19', '0', '0', '0', '0', '0', '0', '0'),
('433', '11013', '1', 'AED', '19', '0', '0', '0', '0', '0', '0', '0'),
('434', '11014', '1', 'AED', '19', '0', '0', '0', '0', '0', '0', '0'),
('435', '11015', '1', 'AED', '19', '0', '0', '0', '0', '0', '0', '0'),
('436', '11016', '1', 'AED', '19', '0', '0', '0', '0', '0', '0', '0'),
('437', '11017', '1', 'AED', '19', '0', '0', '0', '0', '0', '0', '0'),
('438', '11018', '1', 'AED', '19', '0', '0', '0', '0', '0', '0', '0'),
('439', '11019', '1', 'AED', '19', '0', '0', '0', '0', '0', '0', '0'),
('440', '11020', '1', 'AED', '19', '0', '0', '0', '0', '0', '0', '0'),
('441', '11021', '1', 'AED', '19', '0', '0', '0', '0', '0', '0', '0'),
('442', '11022', '1', 'AED', '19', '0', '0', '0', '0', '0', '0', '0'),
('443', '11023', '1', 'AED', '19', '0', '0', '0', '0', '0', '0', '0'),
('444', '11024', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('445', '11025', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('446', '11026', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('447', '11027', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('448', '11028', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('449', '11029', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('450', '11030', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('451', '11031', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('452', '11032', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('453', '11033', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('454', '11034', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('455', '11035', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('456', '11036', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('457', '11037', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('458', '11038', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('459', '11039', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('460', '11040', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('461', '11041', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('462', '11042', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('463', '11043', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('464', '11044', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('465', '11045', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('466', '11046', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('467', '11047', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('468', '11048', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('469', '11049', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('470', '11050', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('471', '11051', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('472', '11052', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('473', '11053', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('474', '11054', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('475', '11055', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('476', '11056', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('477', '11057', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('478', '11058', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('479', '11059', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('480', '11060', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('481', '11061', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('482', '11062', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('483', '11063', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('484', '11064', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('485', '11065', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('486', '11066', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('487', '11067', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('488', '11068', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('489', '11069', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('490', '11070', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('491', '11071', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('492', '11072', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('493', '11073', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('494', '11074', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('495', '11075', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('496', '11076', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('497', '11077', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('498', '11078', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('499', '11079', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('500', '11080', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('501', '11081', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('502', '11082', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('503', '11083', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('504', '11084', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('505', '11085', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('506', '11086', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('507', '11087', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('508', '11088', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('509', '11089', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('510', '11090', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('511', '11091', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('512', '11092', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('513', '11093', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('514', '11094', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('515', '11095', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('516', '11096', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('517', '11097', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('518', '11098', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('519', '11099', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('520', '11100', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('521', '11101', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('522', '11102', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('523', '11103', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('524', '11104', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('525', '11105', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('526', '11106', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('527', '11107', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('528', '11108', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('529', '11109', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('530', '11110', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('531', '11111', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('532', '11112', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('533', '11113', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('534', '11114', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('535', '11115', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('536', '11116', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('537', '11117', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('538', '11118', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('539', '11119', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('540', '11120', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('541', '11121', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('542', '11122', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('543', '11123', '1', 'AED', '80', '0', '0', '0', '0', '0', '0', '0'),
('544', '11000', '1', 'AED', '0', '0', '0', '0', '0', '0', '0', '0'),
('545', '1T1', '1', 'AED', '0', '0', '0', '0', '0', '0', '0', '0'),
('546', '701', '1', 'AED', '0', '0', '0', '0', '0', '0', '0', '0'),
('547', '6000', '1', 'AED', '30', '0', '0', '0', '0', '0', '0', '0');

### Structure of table `0_print_profiles` ###

DROP TABLE IF EXISTS `0_print_profiles`;

CREATE TABLE `0_print_profiles` (
  `id` smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  `profile` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `report` varchar(5) COLLATE utf8_unicode_ci DEFAULT NULL,
  `printer` tinyint(3) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `profile` (`profile`,`report`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

### Data of table `0_print_profiles` ###

INSERT INTO `0_print_profiles` VALUES
('1', 'Out of office', NULL, '0'),
('2', 'Sales Department', NULL, '0'),
('3', 'Central', NULL, '2'),
('4', 'Sales Department', '104', '2'),
('5', 'Sales Department', '105', '2'),
('6', 'Sales Department', '107', '2'),
('7', 'Sales Department', '109', '2'),
('8', 'Sales Department', '110', '2'),
('9', 'Sales Department', '201', '2');

### Structure of table `0_printers` ###

DROP TABLE IF EXISTS `0_printers`;

CREATE TABLE `0_printers` (
  `id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `queue` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `host` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `port` smallint(11) unsigned NOT NULL,
  `timeout` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

### Data of table `0_printers` ###

INSERT INTO `0_printers` VALUES
('1', 'QL500', 'Label printer', 'QL500', 'server', '127', '20'),
('2', 'Samsung', 'Main network printer', 'scx4521F', 'server', '515', '5'),
('3', 'Local', 'Local print server at user IP', 'lp', '', '515', '10');

### Structure of table `0_purch_data` ###

DROP TABLE IF EXISTS `0_purch_data`;

CREATE TABLE `0_purch_data` (
  `supplier_id` int(11) NOT NULL DEFAULT '0',
  `stock_id` char(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `price` double NOT NULL DEFAULT '0',
  `suppliers_uom` char(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `conversion_factor` double NOT NULL DEFAULT '1',
  `supplier_description` char(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`supplier_id`,`stock_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

### Data of table `0_purch_data` ###

INSERT INTO `0_purch_data` VALUES
('1', '101', '200', '', '1', 'iPad Air 2 16GB'),
('1', '102', '150', '', '1', 'iPhone 6 64GB'),
('1', '103', '10', '', '1', 'iPhone Cover Case'),
('2', 'EID3', '5000', '', '1', 'EMIRATES ID FORM/ THREE YEARS');

### Structure of table `0_purch_order_details` ###

DROP TABLE IF EXISTS `0_purch_order_details`;

CREATE TABLE `0_purch_order_details` (
  `po_detail_item` int(11) NOT NULL AUTO_INCREMENT,
  `order_no` int(11) NOT NULL DEFAULT '0',
  `item_code` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `description` tinytext COLLATE utf8_unicode_ci,
  `delivery_date` date NOT NULL DEFAULT '0000-00-00',
  `qty_invoiced` double NOT NULL DEFAULT '0',
  `unit_price` double NOT NULL DEFAULT '0',
  `act_price` double NOT NULL DEFAULT '0',
  `std_cost_unit` double NOT NULL DEFAULT '0',
  `quantity_ordered` double NOT NULL DEFAULT '0',
  `quantity_received` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`po_detail_item`),
  KEY `order` (`order_no`,`po_detail_item`),
  KEY `itemcode` (`item_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

### Data of table `0_purch_order_details` ###


### Structure of table `0_purch_orders` ###

DROP TABLE IF EXISTS `0_purch_orders`;

CREATE TABLE `0_purch_orders` (
  `order_no` int(11) NOT NULL AUTO_INCREMENT,
  `supplier_id` int(11) NOT NULL DEFAULT '0',
  `comments` tinytext COLLATE utf8_unicode_ci,
  `ord_date` date NOT NULL DEFAULT '0000-00-00',
  `reference` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `requisition_no` tinytext COLLATE utf8_unicode_ci,
  `into_stock_location` varchar(5) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `delivery_address` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `total` double NOT NULL DEFAULT '0',
  `prep_amount` double NOT NULL DEFAULT '0',
  `alloc` double NOT NULL DEFAULT '0',
  `tax_included` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`order_no`),
  KEY `ord_date` (`ord_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

### Data of table `0_purch_orders` ###


### Structure of table `0_quick_entries` ###

DROP TABLE IF EXISTS `0_quick_entries`;

CREATE TABLE `0_quick_entries` (
  `id` smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  `type` tinyint(1) NOT NULL DEFAULT '0',
  `description` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `usage` varchar(120) COLLATE utf8_unicode_ci DEFAULT NULL,
  `base_amount` double NOT NULL DEFAULT '0',
  `base_desc` varchar(60) COLLATE utf8_unicode_ci DEFAULT NULL,
  `bal_type` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `description` (`description`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

### Data of table `0_quick_entries` ###

INSERT INTO `0_quick_entries` VALUES
('1', '1', 'Maintenance', NULL, '0', 'Amount', '0'),
('2', '4', 'Phone', NULL, '0', 'Amount', '0'),
('3', '2', 'Cash Sales', 'Retail sales without invoice', '0', 'Amount', '0');

### Structure of table `0_quick_entry_lines` ###

DROP TABLE IF EXISTS `0_quick_entry_lines`;

CREATE TABLE `0_quick_entry_lines` (
  `id` smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  `qid` smallint(6) unsigned NOT NULL,
  `amount` double DEFAULT '0',
  `memo` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `action` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `dest_id` varchar(15) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `dimension_id` smallint(6) unsigned DEFAULT NULL,
  `dimension2_id` smallint(6) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `qid` (`qid`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

### Data of table `0_quick_entry_lines` ###

INSERT INTO `0_quick_entry_lines` VALUES
('1', '1', '0', '', 't-', '1', '0', '0'),
('2', '2', '0', '', 't-', '1', '0', '0'),
('3', '3', '0', '', 't-', '1', '0', '0'),
('4', '3', '0', '', '=', '4010', '0', '0'),
('5', '1', '0', '', '=', '5765', '0', '0'),
('6', '2', '0', '', '=', '5780', '0', '0');

### Structure of table `0_recurrent_invoices` ###

DROP TABLE IF EXISTS `0_recurrent_invoices`;

CREATE TABLE `0_recurrent_invoices` (
  `id` smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  `description` varchar(60) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `order_no` int(11) unsigned NOT NULL,
  `debtor_no` int(11) unsigned DEFAULT NULL,
  `group_no` smallint(6) unsigned DEFAULT NULL,
  `days` int(11) NOT NULL DEFAULT '0',
  `monthly` int(11) NOT NULL DEFAULT '0',
  `begin` date NOT NULL DEFAULT '0000-00-00',
  `end` date NOT NULL DEFAULT '0000-00-00',
  `last_sent` date NOT NULL DEFAULT '0000-00-00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `description` (`description`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

### Data of table `0_recurrent_invoices` ###

INSERT INTO `0_recurrent_invoices` VALUES
('1', 'Weekly Maintenance', '6', '1', '1', '7', '0', '2017-04-01', '2020-05-07', '2017-04-08');

### Structure of table `0_reflines` ###

DROP TABLE IF EXISTS `0_reflines`;

CREATE TABLE `0_reflines` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `trans_type` int(11) NOT NULL,
  `prefix` char(5) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `pattern` varchar(35) COLLATE utf8_unicode_ci NOT NULL DEFAULT '1',
  `description` varchar(60) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `default` tinyint(1) NOT NULL DEFAULT '0',
  `inactive` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `prefix` (`trans_type`,`prefix`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

### Data of table `0_reflines` ###

INSERT INTO `0_reflines` VALUES
('1', '0', '', '{001}/{YYYY}', '', '1', '0'),
('2', '1', '', '{001}/{YYYY}', '', '1', '0'),
('3', '2', '', '{001}/{YYYY}', '', '1', '0'),
('4', '4', '', '{001}/{YYYY}', '', '1', '0'),
('5', '10', '', '{200001}', '', '1', '0'),
('6', '11', '', '{001}/{YYYY}', '', '1', '0'),
('7', '12', '', '{001}/{YYYY}', '', '1', '0'),
('8', '13', '', '{001}/{YYYY}', '', '1', '0'),
('9', '16', '', '{001}/{YYYY}', '', '1', '0'),
('10', '17', '', '{001}/{YYYY}', '', '1', '0'),
('11', '18', '', '{001}/{YYYY}', '', '1', '0'),
('12', '20', '', '{001}/{YYYY}', '', '1', '0'),
('13', '21', '', '{001}/{YYYY}', '', '1', '0'),
('14', '22', '', '{001}/{YYYY}', '', '1', '0'),
('15', '25', '', '{001}/{YYYY}', '', '1', '0'),
('16', '26', '', '{001}/{YYYY}', '', '1', '0'),
('17', '28', '', '{001}/{YYYY}', '', '1', '0'),
('18', '29', '', '{001}/{YYYY}', '', '1', '0'),
('19', '30', '', '{001}/{YYYY}', '', '1', '0'),
('20', '32', '', '{001}/{YYYY}', '', '1', '0'),
('21', '35', '', '{001}/{YYYY}', '', '1', '0'),
('22', '40', '', '{001}/{YYYY}', '', '1', '0');

### Structure of table `0_refs` ###

DROP TABLE IF EXISTS `0_refs`;

CREATE TABLE `0_refs` (
  `id` int(11) NOT NULL DEFAULT '0',
  `type` int(11) NOT NULL DEFAULT '0',
  `reference` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`,`type`),
  KEY `Type_and_Reference` (`type`,`reference`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

### Data of table `0_refs` ###

INSERT INTO `0_refs` VALUES
('1', '0', '001/2018'),
('1', '10', '000001'),
('2', '10', '000002'),
('3', '10', '000003'),
('6', '10', '000003'),
('4', '10', '000004'),
('5', '10', '000005'),
('7', '10', '000006'),
('1', '12', '001/2018'),
('2', '12', '002/2018');

### Structure of table `0_salary_structure` ###

DROP TABLE IF EXISTS `0_salary_structure`;

CREATE TABLE `0_salary_structure` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL,
  `salary_scale_id` int(11) NOT NULL,
  `pay_rule_id` varchar(15) COLLATE utf8_unicode_ci NOT NULL,
  `pay_amount` double NOT NULL,
  `type` tinyint(1) NOT NULL COMMENT '0 for credit, 1 for debit',
  `is_basic` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

### Data of table `0_salary_structure` ###

INSERT INTO `0_salary_structure` VALUES
('1', '2018-05-06', '1', '5410', '30000', '1', '1'),
('2', '2018-07-08', '2', '5410', '12000', '1', '1'),
('3', '2018-07-21', '3', '5410', '10000', '1', '1'),
('4', '2018-07-21', '4', '5410', '9000', '1', '1'),
('5', '2018-07-21', '5', '5410', '8000', '1', '1'),
('6', '2018-07-21', '6', '5410', '7000', '1', '1'),
('7', '2018-07-21', '7', '5410', '6500', '1', '1'),
('8', '2018-07-21', '8', '5410', '6000', '1', '1'),
('9', '2018-07-21', '9', '5410', '5500', '1', '1'),
('10', '2018-07-21', '10', '5410', '5000', '1', '1'),
('11', '2018-07-21', '11', '5410', '4500', '1', '1'),
('12', '2018-07-21', '12', '5410', '4000', '1', '1'),
('13', '2018-07-21', '13', '5410', '3500', '1', '1'),
('14', '2018-07-21', '14', '5410', '3000', '1', '1'),
('15', '2018-07-21', '15', '5410', '2500', '1', '1'),
('16', '2018-07-21', '16', '5410', '2000', '1', '1');

### Structure of table `0_salaryscale` ###

DROP TABLE IF EXISTS `0_salaryscale`;

CREATE TABLE `0_salaryscale` (
  `scale_id` int(11) NOT NULL AUTO_INCREMENT,
  `scale_name` text COLLATE utf8_unicode_ci NOT NULL,
  `inactive` tinyint(1) NOT NULL DEFAULT '0',
  `pay_basis` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0 = monthly, 1 = daily',
  PRIMARY KEY (`scale_id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

### Data of table `0_salaryscale` ###

INSERT INTO `0_salaryscale` VALUES
('1', '30,000.00', '0', '0'),
('2', '12,000.00', '0', '0'),
('3', '10,000.00', '0', '0'),
('4', '9,000.00', '0', '0'),
('5', '8,000.00', '0', '0'),
('6', '7,000.00', '0', '0'),
('7', '6,500.00', '0', '0'),
('8', '6,000.00', '0', '0'),
('9', '5,500.00', '0', '0'),
('10', '5,000.00', '0', '0'),
('11', '4,500.00', '0', '0'),
('12', '4,000.00', '0', '0'),
('13', '3,500.00', '0', '0'),
('14', '3,000.00', '0', '0'),
('15', '2,500.00', '0', '0'),
('16', '2,000.00', '0', '0');

### Structure of table `0_sales_order_details` ###

DROP TABLE IF EXISTS `0_sales_order_details`;

CREATE TABLE `0_sales_order_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_no` int(11) NOT NULL DEFAULT '0',
  `trans_type` smallint(6) NOT NULL DEFAULT '30',
  `stk_code` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `description` tinytext COLLATE utf8_unicode_ci,
  `qty_sent` double NOT NULL DEFAULT '0',
  `unit_price` double NOT NULL DEFAULT '0',
  `quantity` double NOT NULL DEFAULT '0',
  `invoiced` double NOT NULL DEFAULT '0',
  `discount_percent` double NOT NULL DEFAULT '0',
  `discount_amount` double DEFAULT '0',
  `govt_fee` double DEFAULT '0',
  `bank_service_charge` double DEFAULT '0',
  `bank_service_charge_vat` double DEFAULT '0',
  `pf_amount` double DEFAULT '0',
  `transaction_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sorder` (`trans_type`,`order_no`),
  KEY `stkcode` (`stk_code`)
) ENGINE=InnoDB AUTO_INCREMENT=356 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

### Data of table `0_sales_order_details` ###

INSERT INTO `0_sales_order_details` VALUES
('349', '1', '30', '108', 'Entry Permit - New - Long Term Visit - Single Entry - Leisure (Inside) - 	أذونات الدخول - جديد - زيارة طويلة - سفرة واحدة - ترفيه	', '1', '80', '1', '0', '0', '0', '2506.75', '3.15', '0', '0', NULL),
('350', '2', '30', 'EIDRE', 'EID RESCANNING - مسح نواقص للهوية', '1', '10', '1', '0', '0', '0', '89.5', '0', '0', '0', NULL),
('352', '4', '30', '102', 'Entry Permit - Cancel - Work (Company) - 	أذونات الدخول - إلغاء - عمل	', '1', '47', '1', '0', '0.213', '10', '53', '3.15', '0', '0', NULL),
('353', '5', '30', '105', 'Entry Permit - Extend - Short Term Visit - On Arrival (Inside) - 	أذونات الدخول - تمديد - تأشيرة زيارة قصيرة - عند الوصول	', '1', '80', '1', '0', '0.125', '10', '1462.5', '3.15', '0', '0', NULL),
('354', '6', '30', 'EID5', 'EMIRATES ID FORM/ FIVE YEARS (GCC) - طلب الهوية الإماراتية/ 5 سنوات', '1', '30', '1', '0', '0', '0', '140', '0', '0', '2.63', NULL),
('355', '7', '30', '11083', 'Absconding - Electronic - 	بلاغ - إلكتروني	', '1', '80', '1', '0', '0', '0', '23', '0', '0', '0', NULL);

### Structure of table `0_sales_orders` ###

DROP TABLE IF EXISTS `0_sales_orders`;

CREATE TABLE `0_sales_orders` (
  `order_no` int(11) NOT NULL,
  `trans_type` smallint(6) NOT NULL DEFAULT '30',
  `version` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `type` tinyint(1) NOT NULL DEFAULT '0',
  `debtor_no` int(11) NOT NULL DEFAULT '0',
  `branch_code` int(11) NOT NULL DEFAULT '0',
  `reference` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `customer_ref` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `comments` tinytext COLLATE utf8_unicode_ci,
  `ord_date` date NOT NULL DEFAULT '0000-00-00',
  `order_type` int(11) NOT NULL DEFAULT '0',
  `ship_via` int(11) NOT NULL DEFAULT '0',
  `delivery_address` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `contact_phone` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
  `contact_email` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `deliver_to` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `freight_cost` double NOT NULL DEFAULT '0',
  `from_stk_loc` varchar(5) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `delivery_date` date NOT NULL DEFAULT '0000-00-00',
  `payment_terms` int(11) DEFAULT NULL,
  `total` double NOT NULL DEFAULT '0',
  `prep_amount` double NOT NULL DEFAULT '0',
  `alloc` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`trans_type`,`order_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

### Data of table `0_sales_orders` ###

INSERT INTO `0_sales_orders` VALUES
('1', '30', '1', '0', '1', '1', 'auto', '', NULL, '2018-10-17', '1', '1', '', NULL, NULL, 'Walk-in Customer', '0', 'DEF', '2018-10-18', '4', '2593.9', '0', '0'),
('2', '30', '1', '0', '1', '1', 'auto', '', NULL, '2018-10-17', '1', '1', '', NULL, NULL, 'Walk-in Customer', '0', 'DEF', '2018-10-18', '4', '100', '0', '0'),
('4', '30', '1', '0', '19', '19', 'auto', '', NULL, '2018-10-17', '2', '1', '', NULL, NULL, 'adsdasd', '0', 'DEF', '2018-10-17', '5', '95.49', '0', '0'),
('5', '30', '1', '0', '19', '19', 'auto', '', NULL, '2018-10-17', '2', '1', '', NULL, NULL, 'adsdasd', '0', 'DEF', '2018-10-17', '5', '1539.65', '0', '0'),
('6', '30', '1', '0', '19', '19', 'auto', '', NULL, '2018-10-17', '2', '1', '', NULL, NULL, 'adsdasd', '0', 'DEF', '2018-11-16', '5', '171.5', '0', '0'),
('7', '30', '1', '0', '1', '1', 'auto', '', NULL, '2018-10-18', '1', '1', '', NULL, NULL, 'Walk-in Customer', '0', 'DEF', '2018-10-19', '4', '103', '0', '0');

### Structure of table `0_sales_pos` ###

DROP TABLE IF EXISTS `0_sales_pos`;

CREATE TABLE `0_sales_pos` (
  `id` smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  `pos_name` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `cash_sale` tinyint(1) NOT NULL,
  `credit_sale` tinyint(1) NOT NULL,
  `pos_location` varchar(5) COLLATE utf8_unicode_ci NOT NULL,
  `pos_account` smallint(6) unsigned NOT NULL,
  `inactive` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `pos_name` (`pos_name`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

### Data of table `0_sales_pos` ###

INSERT INTO `0_sales_pos` VALUES
('1', 'Default', '1', '1', 'DEF', '2', '0');

### Structure of table `0_sales_types` ###

DROP TABLE IF EXISTS `0_sales_types`;

CREATE TABLE `0_sales_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sales_type` char(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `tax_included` int(1) NOT NULL DEFAULT '0',
  `factor` double NOT NULL DEFAULT '1',
  `inactive` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `sales_type` (`sales_type`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

### Data of table `0_sales_types` ###

INSERT INTO `0_sales_types` VALUES
('1', 'Retail', '0', '1', '0'),
('2', '2-A Certified', '0', '1', '0'),
('3', '2-A NonCertified', '0', '1', '0'),
('4', '2-B Certified', '0', '1', '0'),
('5', '2-B NonCertified', '0', '1', '0'),
('6', '2-C Certified', '0', '1', '0'),
('7', '2-C NonCertified', '0', '1', '0'),
('8', '2-D Certified', '0', '1', '0'),
('9', '2-D NonCertified', '0', '1', '0');

### Structure of table `0_salesman` ###

DROP TABLE IF EXISTS `0_salesman`;

CREATE TABLE `0_salesman` (
  `salesman_code` int(11) NOT NULL AUTO_INCREMENT,
  `salesman_name` char(60) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `salesman_phone` char(30) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `salesman_fax` char(30) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `salesman_email` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `provision` double NOT NULL DEFAULT '0',
  `break_pt` double NOT NULL DEFAULT '0',
  `provision2` double NOT NULL DEFAULT '0',
  `inactive` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`salesman_code`),
  UNIQUE KEY `salesman_name` (`salesman_name`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

### Data of table `0_salesman` ###

INSERT INTO `0_salesman` VALUES
('3', 'N/A', '', '', '', '0', '0', '0', '0');

### Structure of table `0_security_roles` ###

DROP TABLE IF EXISTS `0_security_roles`;

CREATE TABLE `0_security_roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `sections` text COLLATE utf8_unicode_ci,
  `areas` text COLLATE utf8_unicode_ci,
  `inactive` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `role` (`role`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

### Data of table `0_security_roles` ###

INSERT INTO `0_security_roles` VALUES
('1', 'Inquiries', 'Inquiries', '768;2816;3072;3328;5632;5888;8192;8448;10752;11008;13312;15872;16128', '257;258;259;260;513;514;515;516;517;518;519;520;521;522;523;524;525;773;774;2822;3073;3075;3076;3077;3329;3330;3331;3332;3333;3334;3335;5377;5633;5640;5889;5890;5891;7937;7938;7939;7940;8193;8194;8450;8451;10497;10753;11009;11010;11012;13313;13315;15617;15618;15619;15620;15621;15622;15623;15624;15625;15626;15873;15882;16129;16130;16131;16132;775', '0'),
('2', 'System Administrator', 'System Administrator', '256;512;768;2816;3072;3328;5376;5632;5888;7936;8192;8448;9472;9728;10496;10752;11008;13056;13312;15616;15872;16128;156672;353280;877568;877824;878080;878336', '257;258;259;260;513;514;515;516;517;518;519;520;521;522;523;524;525;526;769;770;771;772;773;774;775;2817;2818;2819;2820;2821;2822;2823;3073;3074;3082;3075;3076;3077;3078;3079;3080;3081;3329;3330;3331;3332;3333;3334;3335;5377;5633;5634;5635;5636;5637;5641;5638;5639;5640;5889;5890;5891;7937;7938;7939;7940;8193;8194;8195;8196;8197;8449;8450;8451;9473;9474;9475;9476;9729;10497;10753;10754;10755;10756;10757;11009;11010;11011;11012;13057;13313;13314;13315;15617;15618;15619;15620;15621;15622;15623;15624;15628;15625;15626;15627;15873;15874;15875;15876;15877;15878;15879;15880;15883;15881;15882;16129;16130;16131;16132;156772;353380;353381;877668;877671;877672;877675;877676;877677;877678;877929;877930;877935;877936;878181;878182;878449;878450;878451;878452;878453;9217;9218;9220', '0'),
('3', 'Counter Staff', 'Counter Staff', '768;3072', '771;772;774;3073;3075;3077;5633', '0'),
('4', 'Stock Manager', 'Stock Manager', '768;2816;3072;3328;5632;5888;8192;8448;10752;11008;13312;15872;16128', '2818;2822;3073;3076;3077;3329;3330;3330;3330;3331;3331;3332;3333;3334;3335;5633;5640;5889;5890;5891;8193;8194;8450;8451;10753;11009;11010;11012;13313;13315;15882;16129;16130;16131;16132;775', '0'),
('5', 'Production Manager', 'Production Manager', '512;768;2816;3072;3328;5632;5888;8192;8448;10752;11008;13312;15616;15872;16128', '521;523;524;2818;2819;2820;2821;2822;2823;3073;3074;3076;3077;3078;3079;3080;3081;3329;3330;3330;3330;3331;3331;3332;3333;3334;3335;5633;5640;5640;5889;5890;5891;8193;8194;8196;8197;8450;8451;10753;10755;11009;11010;11012;13313;13315;15617;15619;15620;15621;15624;15624;15876;15877;15880;15882;16129;16130;16131;16132;775', '0'),
('6', 'Purchase Officer', 'Purchase Officer', '512;768;2816;3072;3328;5376;5632;5888;8192;8448;10752;11008;13312;15616;15872;16128', '521;523;524;2818;2819;2820;2821;2822;2823;3073;3074;3076;3077;3078;3079;3080;3081;3329;3330;3330;3330;3331;3331;3332;3333;3334;3335;5377;5633;5635;5640;5640;5889;5890;5891;8193;8194;8196;8197;8449;8450;8451;10753;10755;11009;11010;11012;13313;13315;15617;15619;15620;15621;15624;15624;15876;15877;15880;15882;16129;16130;16131;16132;775', '0'),
('7', 'AR Officer', 'AR Officer', '512;768;2816;3072;3328;5632;5888;8192;8448;10752;11008;13312;15616;15872;16128', '521;523;524;771;773;774;2818;2819;2820;2821;2822;2823;3073;3073;3074;3075;3076;3077;3078;3079;3080;3081;3081;3329;3330;3330;3330;3331;3331;3332;3333;3334;3335;5633;5633;5634;5637;5638;5639;5640;5640;5889;5890;5891;8193;8194;8194;8196;8197;8450;8451;10753;10755;11009;11010;11012;13313;13315;15617;15619;15620;15621;15624;15624;15873;15876;15877;15878;15880;15882;16129;16130;16131;16132;775', '0'),
('8', 'AP Officer', 'AP Officer', '512;768;2816;3072;3328;5376;5632;5888;8192;8448;10752;11008;13312;15616;15872;16128', '257;258;259;260;521;523;524;769;770;771;772;773;774;2818;2819;2820;2821;2822;2823;3073;3074;3082;3076;3077;3078;3079;3080;3081;3329;3330;3331;3332;3333;3334;3335;5377;5633;5635;5640;5889;5890;5891;7937;7938;7939;7940;8193;8194;8196;8197;8449;8450;8451;10497;10753;10755;11009;11010;11012;13057;13313;13315;15617;15619;15620;15621;15624;15876;15877;15880;15882;16129;16130;16131;16132;775', '0'),
('9', 'Accountant', 'New Accountant', '512;768;2816;3072;3328;5376;5632;5888;8192;8448;10752;11008;13312;15616;15872;16128', '257;258;259;260;521;523;524;771;772;773;774;2818;2819;2820;2821;2822;2823;3073;3074;3075;3076;3077;3078;3079;3080;3081;3329;3330;3331;3332;3333;3334;3335;5377;5633;5634;5635;5637;5638;5639;5640;5889;5890;5891;7937;7938;7939;7940;8193;8194;8196;8197;8449;8450;8451;10497;10753;10755;11009;11010;11012;13313;13315;15617;15618;15619;15620;15621;15624;15873;15876;15877;15878;15880;15882;16129;16130;16131;16132;775', '0'),
('10', 'Sub Admin', 'Sub Admin', '512;768;2816;3072;3328;5376;5632;5888;8192;8448;10752;11008;13312;15616;15872;16128', '257;258;259;260;521;523;524;771;772;773;774;2818;2819;2820;2821;2822;2823;3073;3074;3082;3075;3076;3077;3078;3079;3080;3081;3329;3330;3331;3332;3333;3334;3335;5377;5633;5634;5635;5637;5638;5639;5640;5889;5890;5891;7937;7938;7939;7940;8193;8194;8196;8197;8449;8450;8451;10497;10753;10755;11009;11010;11012;13057;13313;13315;15617;15619;15620;15621;15624;15873;15874;15876;15877;15878;15879;15880;15882;16129;16130;16131;16132;775', '0'),
('11', 'Test Role', 'Test Role', '768', '775', '0');

### Structure of table `0_shippers` ###

DROP TABLE IF EXISTS `0_shippers`;

CREATE TABLE `0_shippers` (
  `shipper_id` int(11) NOT NULL AUTO_INCREMENT,
  `shipper_name` varchar(60) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `phone` varchar(30) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `phone2` varchar(30) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `contact` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `address` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `inactive` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`shipper_id`),
  UNIQUE KEY `name` (`shipper_name`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

### Data of table `0_shippers` ###

INSERT INTO `0_shippers` VALUES
('1', 'Default', '', '', '', '', '0');

### Structure of table `0_sql_trail` ###

DROP TABLE IF EXISTS `0_sql_trail`;

CREATE TABLE `0_sql_trail` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `sql` text COLLATE utf8_unicode_ci NOT NULL,
  `result` tinyint(1) NOT NULL,
  `msg` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=80 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

### Data of table `0_sql_trail` ###

INSERT INTO `0_sql_trail` VALUES
('1', 'SET sql_mode = &#039;&#039;', '1', ''),
('2', 'SHOW TABLES LIKE &#039;saai_theme_option&#039;', '1', 'Table Checking'),
('3', 'SHOW TABLES LIKE &#039;saai_theme_option&#039;', '1', 'Table Checking'),
('4', 'SHOW TABLES LIKE &#039;saai_theme_option&#039;', '1', 'Table Checking'),
('5', 'SHOW TABLES LIKE &#039;saai_theme_option&#039;', '1', 'Table Checking'),
('6', 'SHOW TABLES LIKE &#039;saai_theme_option&#039;', '1', 'Table Checking'),
('7', 'SHOW TABLES LIKE &#039;saai_theme_option&#039;', '1', 'Table Checking'),
('8', 'SHOW TABLES LIKE &#039;saai_theme_option&#039;', '1', 'Table Checking'),
('9', 'SHOW TABLES LIKE &#039;saai_theme_option&#039;', '1', 'Table Checking'),
('10', 'SHOW TABLES LIKE &#039;saai_theme_option&#039;', '1', 'Table Checking'),
('11', 'SHOW TABLES LIKE &#039;saai_theme_option&#039;', '1', 'Table Checking'),
('12', 'SHOW TABLES LIKE &#039;saai_theme_option&#039;', '1', 'Table Checking'),
('13', 'SHOW TABLES LIKE &#039;saai_theme_option&#039;', '1', 'Table Checking'),
('14', 'SHOW TABLES LIKE &#039;saai_theme_option&#039;', '1', 'Table Checking'),
('15', 'SET sql_mode = &#039;&#039;', '1', ''),
('16', 'SHOW TABLES LIKE &#039;saai_theme_option&#039;', '1', 'Table Checking'),
('17', 'SHOW TABLES LIKE &#039;saai_theme_option&#039;', '1', 'Table Checking'),
('18', 'SHOW TABLES LIKE &#039;saai_theme_option&#039;', '1', 'Table Checking'),
('19', 'SHOW TABLES LIKE &#039;saai_theme_option&#039;', '1', 'Table Checking'),
('20', 'SHOW TABLES LIKE &#039;saai_theme_option&#039;', '1', 'Table Checking'),
('21', 'SHOW TABLES LIKE &#039;saai_theme_option&#039;', '1', 'Table Checking'),
('22', 'SHOW TABLES LIKE &#039;saai_theme_option&#039;', '1', 'Table Checking'),
('23', 'SHOW TABLES LIKE &#039;saai_theme_option&#039;', '1', 'Table Checking'),
('24', 'SHOW TABLES LIKE &#039;saai_theme_option&#039;', '1', 'Table Checking'),
('25', 'SHOW TABLES LIKE &#039;saai_theme_option&#039;', '1', 'Table Checking'),
('26', 'SHOW TABLES LIKE &#039;saai_theme_option&#039;', '1', 'Table Checking'),
('27', 'SHOW TABLES LIKE &#039;saai_theme_option&#039;', '1', 'Table Checking'),
('28', 'SHOW TABLES LIKE &#039;saai_theme_option&#039;', '1', 'Table Checking'),
('29', 'SET sql_mode = &#039;&#039;', '1', ''),
('30', 'SHOW TABLES LIKE &#039;saai_theme_option&#039;', '1', 'Table Checking'),
('31', 'SHOW TABLES LIKE &#039;saai_theme_option&#039;', '1', 'Table Checking'),
('32', 'SHOW TABLES LIKE &#039;saai_theme_option&#039;', '1', 'Table Checking'),
('33', 'SHOW TABLES LIKE &#039;saai_theme_option&#039;', '1', 'Table Checking'),
('34', 'SHOW TABLES LIKE &#039;saai_theme_option&#039;', '1', 'Table Checking'),
('35', 'SHOW TABLES LIKE &#039;saai_theme_option&#039;', '1', 'Table Checking'),
('36', 'SET sql_mode = &#039;&#039;', '1', ''),
('37', 'BEGIN', '1', 'could not start a transaction'),
('38', 'INSERT INTO 0_sales_orders (order_no, type, debtor_no, trans_type, branch_code, customer_ref, reference, comments, ord_date,\n		order_type, ship_via, deliver_to, delivery_address, contact_phone,\n		freight_cost, from_stk_loc, delivery_date, payment_terms, total, prep_amount)\n		VALUES (&#039;12&#039;,&#039;0&#039;,&#039;1&#039;, &#039;30&#039;,&#039;1&#039;, &#039;&#039;,&#039;auto&#039;,&#039;&#039;,&#039;2018-08-05&#039;, &#039;1&#039;, &#039;1&#039;,&#039;Walk-in Customer&#039;,&#039;&#039;, &#039;&#039;, &#039;0&#039;, &#039;DEF&#039;, &#039;2018-08-06&#039;,&#039;4&#039;,&#039;86.76&#039;,&#039;0&#039;)', '1', 'order Cannot be Added'),
('39', 'INSERT INTO 0_sales_order_details (\n		order_no, trans_type, stk_code, description, unit_price, quantity, discount_percent,\n		govt_fee,bank_service_charge,bank_service_charge_vat,transaction_id,discount_amount,pf_amount  \n		) VALUES (12,30,&#039;2011&#039;, &#039;EDNRD/GDRFA SERVICE FEES&#039;, 82.63,\n				1,\n				0,\n				0,\n				0,\n				0,&#039;&#039;,0,2.63  \n				)', '1', 'order Details Cannot be Added'),
('40', 'INSERT INTO 0_audit_trail (type, trans_no, user, gl_date, description)\n			VALUES(&#039;30&#039;, &#039;12&#039;,1,&#039;2018-08-05&#039;,&#039;&#039;)', '1', 'Cannot add audit info'),
('41', 'UPDATE 0_audit_trail audit LEFT JOIN 0_fiscal_year year ON year.begin&lt;=&#039;2018-08-05&#039; AND year.end&gt;=&#039;2018-08-05&#039;\n		SET audit.gl_seq = IF(audit.id=1714, 0, NULL),audit.fiscal_year=year.id WHERE type=&#039;30&#039; AND trans_no=&#039;12&#039;', '1', 'Cannot update audit gl_seq'),
('42', 'UPDATE 0_sales_orders SET version=version+1 WHERE order_no=&#039;12&#039; AND version=0 AND trans_type=30', '1', 'Concurrent editing conflict while sales order update'),
('43', 'INSERT INTO 0_debtor_trans (\n		trans_no, type,\n		debtor_no, branch_code,\n		tran_date, due_date,\n		reference, tpe,\n		order_, ov_amount, ov_discount,\n		ov_gst, ov_freight, ov_freight_tax,\n		rate, ship_via, alloc,\n		dimension_id, dimension2_id, payment_terms, tax_included, prep_amount,\n		display_customer,customer_trn,customer_mobile,customer_email,customer_ref,barcode,credit_card_charge,payment_method     \n		) VALUES (&#039;12&#039;, &#039;13&#039;,\n		&#039;1&#039;, &#039;1&#039;,\n		&#039;2018-08-05&#039;, &#039;2018-08-06&#039;, &#039;auto&#039;,\n		&#039;1&#039;, &#039;12&#039;, 82.63, &#039;0&#039;, 4.13,\n		&#039;0&#039;,\n		0, 1, &#039;1&#039;, 0,\n		&#039;&#039;, &#039;&#039;, \n		&#039;4&#039;, \n		&#039;0&#039;, \n		&#039;0&#039;,\n		&#039;Walk-in Customer&#039;,\n		&#039;&#039;,\n		&#039;&#039;,\n		&#039;&#039;,\n		&#039;&#039;,\n		&#039;689672592052&#039;,\n		&#039;&#039;,\n		&#039;&#039;)', '1', 'The debtor transaction record could not be inserted'),
('44', 'INSERT INTO 0_audit_trail (type, trans_no, user, gl_date, description)\n			VALUES(&#039;13&#039;, &#039;12&#039;,1,&#039;2018-08-05&#039;,&#039;&#039;)', '1', 'Cannot add audit info'),
('45', 'UPDATE 0_audit_trail audit LEFT JOIN 0_fiscal_year year ON year.begin&lt;=&#039;2018-08-05&#039; AND year.end&gt;=&#039;2018-08-05&#039;\n		SET audit.gl_seq = IF(audit.id=1715, 0, NULL),audit.fiscal_year=year.id WHERE type=&#039;13&#039; AND trans_no=&#039;12&#039;', '1', 'Cannot update audit gl_seq'),
('46', 'INSERT INTO 0_debtor_trans_details (debtor_trans_no,\n				debtor_trans_type, stock_id, description, quantity, unit_price,\n				unit_tax, discount_percent, standard_cost, src_id,\n				govt_fee,bank_service_charge,bank_service_charge_vat,transaction_id,\n				discount_amount,created_by,user_commission,pf_amount,updated_by)\n			VALUES (&#039;12&#039;, &#039;13&#039;, &#039;2011&#039;, &#039;EDNRD/GDRFA SERVICE FEES&#039;,\n				1, 82.63, 4.13, \n				0, 0,&#039;402&#039;,0,\n				0,0,&#039;&#039;,\n				0,1,0,2.63,1)', '1', 'The debtor transaction detail could not be written'),
('47', 'UPDATE 0_sales_order_details\n				SET qty_sent = qty_sent + 1 WHERE id=&#039;402&#039;', '1', 'The parent document detail record could not be updated'),
('48', 'INSERT INTO 0_stock_moves (stock_id, trans_no, type, loc_code,\n		tran_date, reference, qty, standard_cost, price) VALUES (&#039;2011&#039;, &#039;12&#039;, &#039;13&#039;, &#039;DEF&#039;, &#039;2018-08-05&#039;, &#039;auto&#039;, &#039;-1&#039;, &#039;0&#039;,&#039;82.63&#039;)', '1', 'The stock movement record cannot be inserted'),
('49', 'INSERT INTO 0_trans_tax_details \n		(trans_type, trans_no, tran_date, tax_type_id, rate, ex_rate,\n			included_in_price, net_amount, amount, memo, reg_type)\n		VALUES (&#039;13&#039;,&#039;12&#039;,&#039;2018-08-05&#039;,&#039;1&#039;,&#039;5&#039;,&#039;1&#039;,0,&#039;82.63&#039;,&#039;4.13&#039;,&#039;auto&#039;,NULL)', '1', 'Cannot save trans tax details'),
('50', 'UPDATE 0_debtor_trans SET version=version+1\n			WHERE type=&#039;13&#039; AND ((trans_no=&#039;12&#039; AND version=0))', '1', 'Concurrent editing conflict'),
('51', 'INSERT INTO 0_debtor_trans (\n		trans_no, type,\n		debtor_no, branch_code,\n		tran_date, due_date,\n		reference, tpe,\n		order_, ov_amount, ov_discount,\n		ov_gst, ov_freight, ov_freight_tax,\n		rate, ship_via, alloc,\n		dimension_id, dimension2_id, payment_terms, tax_included, prep_amount,\n		display_customer,customer_trn,customer_mobile,customer_email,customer_ref,barcode,credit_card_charge,payment_method     \n		) VALUES (&#039;12&#039;, &#039;10&#039;,\n		&#039;1&#039;, &#039;1&#039;,\n		&#039;2018-08-05&#039;, &#039;2018-08-06&#039;, &#039;000012&#039;,\n		&#039;1&#039;, &#039;12&#039;, 82.63, &#039;0&#039;, 4.13,\n		&#039;0&#039;,\n		0, 1, &#039;1&#039;, 0,\n		&#039;&#039;, &#039;&#039;, \n		&#039;4&#039;, \n		&#039;0&#039;, \n		&#039;0&#039;,\n		&#039;Walk-in Customer&#039;,\n		&#039;&#039;,\n		&#039;&#039;,\n		&#039;&#039;,\n		&#039;&#039;,\n		&#039;689672592052&#039;,\n		&#039;&#039;,\n		&#039;&#039;)', '1', 'The debtor transaction record could not be inserted'),
('52', 'INSERT INTO 0_audit_trail (type, trans_no, user, gl_date, description)\n			VALUES(&#039;10&#039;, &#039;12&#039;,1,&#039;2018-08-05&#039;,&#039;&#039;)', '1', 'Cannot add audit info'),
('53', 'UPDATE 0_audit_trail audit LEFT JOIN 0_fiscal_year year ON year.begin&lt;=&#039;2018-08-05&#039; AND year.end&gt;=&#039;2018-08-05&#039;\n		SET audit.gl_seq = IF(audit.id=1716, 0, NULL),audit.fiscal_year=year.id WHERE type=&#039;10&#039; AND trans_no=&#039;12&#039;', '1', 'Cannot update audit gl_seq'),
('54', 'select * from customer_discount_items where item_id=\n            (select category_id from 0_stock_master where stock_id=&#039;2011&#039; limit 1) \n            and customer_id=&#039;1&#039;', '1', 'could not get customer'),
('55', 'INSERT INTO 0_debtor_trans_details (debtor_trans_no,\n				debtor_trans_type, stock_id, description, quantity, unit_price,\n				unit_tax, discount_percent, standard_cost, src_id,\n				govt_fee,bank_service_charge,bank_service_charge_vat,transaction_id,\n				discount_amount,created_by,user_commission,pf_amount,updated_by)\n			VALUES (&#039;12&#039;, &#039;10&#039;, &#039;2011&#039;, &#039;EDNRD/GDRFA SERVICE FEES&#039;,\n				1, 82.63, 4.13, \n				0, 0,&#039;795&#039;,0,\n				0,0,&#039;&#039;,\n				0,1,0,2.63,1)', '1', 'The debtor transaction detail could not be written'),
('56', 'UPDATE 0_debtor_trans_details\n				SET qty_done = qty_done + 1\n				WHERE id=&#039;795&#039;', '1', 'The parent document detail record could not be updated'),
('57', 'INSERT INTO 0_gl_trans ( type, type_no, tran_date,\n		account, dimension_id, dimension2_id, memo_, amount,transaction_id) VALUES (&#039;10&#039;, &#039;12&#039;, &#039;2018-08-05&#039;,\n		&#039;4010&#039;, &#039;0&#039;, &#039;0&#039;, &#039;&#039;, &#039;-82.63&#039;,&#039;&#039;) ', '1', 'The sales price GL posting could not be inserted'),
('58', 'INSERT INTO 0_gl_trans ( type, type_no, tran_date,\n		account, dimension_id, dimension2_id, memo_, amount,transaction_id) VALUES (&#039;10&#039;, &#039;12&#039;, &#039;2018-08-05&#039;,\n		&#039;1112&#039;, &#039;0&#039;, &#039;0&#039;, &#039;Amer service charge&#039;, &#039;-2.63&#039;,&#039;N/A&#039;) ', '1', 'The sales price GL posting could not be inserted'),
('59', 'INSERT INTO 0_gl_trans ( type, type_no, tran_date,\n		account, dimension_id, dimension2_id, memo_, amount,transaction_id) VALUES (&#039;10&#039;, &#039;12&#039;, &#039;2018-08-05&#039;,\n		&#039;5010&#039;, &#039;0&#039;, &#039;0&#039;, &#039;&#039;, &#039;2.63&#039;,&#039;&#039;) ', '1', 'Cost of Goods Sold'),
('60', 'INSERT INTO 0_gl_trans ( type, type_no, tran_date,\n		account, dimension_id, dimension2_id, memo_, amount,transaction_id, person_type_id, person_id) VALUES (&#039;10&#039;, &#039;12&#039;, &#039;2018-08-05&#039;,\n		&#039;1200&#039;, &#039;0&#039;, &#039;0&#039;, &#039;&#039;, &#039;86.76&#039;,&#039;&#039;, &#039;2&#039;, &#039;1&#039;) ', '1', 'The total debtor GL posting could not be inserted'),
('61', 'INSERT INTO 0_trans_tax_details \n		(trans_type, trans_no, tran_date, tax_type_id, rate, ex_rate,\n			included_in_price, net_amount, amount, memo, reg_type)\n		VALUES (&#039;10&#039;,&#039;12&#039;,&#039;2018-08-05&#039;,&#039;1&#039;,&#039;5&#039;,&#039;1&#039;,0,&#039;82.63&#039;,&#039;4.13&#039;,&#039;000012&#039;,&#039;0&#039;)', '1', 'Cannot save trans tax details'),
('62', 'INSERT INTO 0_gl_trans ( type, type_no, tran_date,\n		account, dimension_id, dimension2_id, memo_, amount,transaction_id) VALUES (&#039;10&#039;, &#039;12&#039;, &#039;2018-08-05&#039;,\n		&#039;2150&#039;, &#039;0&#039;, &#039;0&#039;, &#039;&#039;, &#039;-4.13&#039;,&#039;&#039;) ', '1', 'A tax GL posting could not be inserted'),
('63', 'REPLACE 0_refs SET reference=&#039;000012&#039;, type=&#039;10&#039;, id=&#039;12&#039;', '1', 'could not update reference entry'),
('64', 'COMMIT', '1', 'could not commit a transaction'),
('65', 'SET sql_mode = &#039;&#039;', '1', ''),
('66', 'SHOW TABLES LIKE &#039;saai_theme_option&#039;', '1', 'Table Checking'),
('67', 'SHOW TABLES LIKE &#039;saai_theme_option&#039;', '1', 'Table Checking'),
('68', 'SHOW TABLES LIKE &#039;saai_theme_option&#039;', '1', 'Table Checking'),
('69', 'SHOW TABLES LIKE &#039;saai_theme_option&#039;', '1', 'Table Checking'),
('70', 'SHOW TABLES LIKE &#039;saai_theme_option&#039;', '1', 'Table Checking'),
('71', 'SHOW TABLES LIKE &#039;saai_theme_option&#039;', '1', 'Table Checking'),
('72', 'SHOW TABLES LIKE &#039;saai_theme_option&#039;', '1', 'Table Checking'),
('73', 'SHOW TABLES LIKE &#039;saai_theme_option&#039;', '1', 'Table Checking'),
('74', 'SHOW TABLES LIKE &#039;saai_theme_option&#039;', '1', 'Table Checking'),
('75', 'SHOW TABLES LIKE &#039;saai_theme_option&#039;', '1', 'Table Checking'),
('76', 'SHOW TABLES LIKE &#039;saai_theme_option&#039;', '1', 'Table Checking'),
('77', 'SHOW TABLES LIKE &#039;saai_theme_option&#039;', '1', 'Table Checking'),
('78', 'SHOW TABLES LIKE &#039;saai_theme_option&#039;', '1', 'Table Checking'),
('79', 'SET sql_mode = &#039;&#039;', '1', '');

### Structure of table `0_stock_category` ###

DROP TABLE IF EXISTS `0_stock_category`;

CREATE TABLE `0_stock_category` (
  `category_id` int(11) NOT NULL AUTO_INCREMENT,
  `description` varchar(60) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `dflt_tax_type` int(11) NOT NULL DEFAULT '1',
  `sort_order` int(11) DEFAULT '99',
  `dflt_units` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'each',
  `dflt_mb_flag` char(1) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'B',
  `dflt_sales_act` varchar(15) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `dflt_cogs_act` varchar(15) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `dflt_inventory_act` varchar(15) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `dflt_adjustment_act` varchar(15) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `dflt_wip_act` varchar(15) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `dflt_dim1` int(11) DEFAULT NULL,
  `dflt_dim2` int(11) DEFAULT NULL,
  `inactive` tinyint(1) NOT NULL DEFAULT '0',
  `dflt_no_sale` tinyint(1) NOT NULL DEFAULT '0',
  `dflt_no_purchase` tinyint(1) NOT NULL DEFAULT '0',
  `is_tasheel` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`category_id`),
  UNIQUE KEY `description` (`description`)
) ENGINE=InnoDB AUTO_INCREMENT=1000001 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

### Data of table `0_stock_category` ###

INSERT INTO `0_stock_category` VALUES
('1', 'TASHEEL', '2', '99', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '1'),
('4', 'INSURANCE', '1', '99', 'each', 'D', '4014', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0'),
('5', 'MEDICAL', '1', '99', 'each', 'D', '4013', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0'),
('6', 'IMMIGRATION', '1', '1', 'each', 'D', '4011', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0'),
('7', 'EMIRATES ID', '1', '99', 'each', 'D', '4012', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0'),
('8', 'OTHER SERVICES', '1', '99', 'each', 'D', '4015', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0');

### Structure of table `0_stock_fa_class` ###

DROP TABLE IF EXISTS `0_stock_fa_class`;

CREATE TABLE `0_stock_fa_class` (
  `fa_class_id` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `parent_id` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `description` varchar(200) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `long_description` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `depreciation_rate` double NOT NULL DEFAULT '0',
  `inactive` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`fa_class_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

### Data of table `0_stock_fa_class` ###


### Structure of table `0_stock_master` ###

DROP TABLE IF EXISTS `0_stock_master`;

CREATE TABLE `0_stock_master` (
  `stock_id` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `category_id` int(11) NOT NULL DEFAULT '0',
  `sub_category_id` int(11) NOT NULL DEFAULT '0',
  `tax_type_id` int(11) NOT NULL DEFAULT '0',
  `description` varchar(200) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `long_description` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `units` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'each',
  `mb_flag` char(1) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'B',
  `sales_account` varchar(15) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `cogs_account` varchar(15) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `inventory_account` varchar(15) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `adjustment_account` varchar(15) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `wip_account` varchar(15) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `dimension_id` int(11) DEFAULT NULL,
  `dimension2_id` int(11) DEFAULT NULL,
  `purchase_cost` double NOT NULL DEFAULT '0',
  `material_cost` double NOT NULL DEFAULT '0',
  `labour_cost` double NOT NULL DEFAULT '0',
  `overhead_cost` double NOT NULL DEFAULT '0',
  `inactive` tinyint(1) NOT NULL DEFAULT '0',
  `no_sale` tinyint(1) NOT NULL DEFAULT '0',
  `no_purchase` tinyint(1) NOT NULL DEFAULT '0',
  `editable` tinyint(1) NOT NULL DEFAULT '0',
  `depreciation_method` char(1) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'S',
  `depreciation_rate` double NOT NULL DEFAULT '0',
  `depreciation_factor` double NOT NULL DEFAULT '1',
  `depreciation_start` date NOT NULL DEFAULT '0000-00-00',
  `depreciation_date` date NOT NULL DEFAULT '0000-00-00',
  `fa_class_id` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `pf_amount` double DEFAULT '0',
  `govt_fee` double NOT NULL DEFAULT '0',
  `govt_bank_account` varchar(128) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `bank_service_charge` double NOT NULL DEFAULT '0',
  `bank_service_charge_vat` double NOT NULL DEFAULT '0',
  `commission_loc_user` double NOT NULL DEFAULT '0',
  `commission_non_loc_user` double NOT NULL DEFAULT '0',
  `notify_customer` int(11) NOT NULL DEFAULT '0' COMMENT '0 -false, 1-true',
  `expired_in_days` int(11) NOT NULL DEFAULT '0',
  `notify_before_days` int(11) NOT NULL DEFAULT '0',
  `customer_commission` double DEFAULT '0',
  PRIMARY KEY (`stock_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

### Data of table `0_stock_master` ###

INSERT INTO `0_stock_master` VALUES
('101', '6', '1', '1', 'Entry Permit - Cancel - Residence (Family)', '	أذونات الدخول - إلغاء - إقامة	', 'each', 'D', '4011', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '85.75', '1111', '3.15', '0', '45', '0', '0', '0', '0', '0'),
('102', '6', '1', '1', 'Entry Permit - Cancel - Work (Company)', '	أذونات الدخول - إلغاء - عمل	', 'each', 'D', '4011', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '53', '1111', '3.15', '0', '45', '0', '0', '0', '0', '0'),
('105', '6', '2', '1', 'Entry Permit - Extend - Short Term Visit - On Arrival (Inside)', '	أذونات الدخول - تمديد - تأشيرة زيارة قصيرة - عند الوصول	', 'each', 'D', '4011', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '1462.5', '1111', '3.15', '0', '45', '0', '0', '0', '0', '0'),
('106', '6', '2', '1', 'Entry Permit - Extend - Short Term Visit - On Arrival (Outside)', '	أذونات الدخول - تمديد - تأشيرة زيارة قصيرة - عند الوصول	', 'each', 'D', '4011', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '792.5', '1111', '3.15', '0', '45', '0', '0', '0', '0', '0'),
('108', '6', '3', '1', 'Entry Permit - New - Long Term Visit - Single Entry - Leisure (Inside)', '	أذونات الدخول - جديد - زيارة طويلة - سفرة واحدة - ترفيه	', 'each', 'D', '4011', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '2506.75', '1111', '3.15', '0', '45', '0', '0', '0', '0', '0'),
('109', '6', '3', '1', 'Entry Permit - New - Long Term Visit - Single Entry - Leisure (Outside)', '	أذونات الدخول - جديد - زيارة طويلة - سفرة واحدة - ترفيه	', 'each', 'D', '4011', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '1836.8', '1111', '3', '0.15', '45', '0', '0', '0', '0', '0'),
('110', '6', '3', '1', 'Entry Permit - New - Residence - Children /wife- Resident Sponsor Working In Private Sector or Free Zone (Inside)', '	أذونات الدخول - جديد - إقامة - أبناء - كفيل مقيم يعمل في القطاع الخاص أو منطقة حرة	', 'each', 'D', '4011', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '1055.75', '1111', '3.15', '0', '45', '0', '0', '0', '0', '0'),
('11000', '1', '0', '2', 'Fine - Tasheel', '', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '0', '', '0', '0', '0', '0', '0', '0', '0', '0'),
('11001', '1', '0', '2', 'Contract Nawakas', '	مسح نواقص تسجيل العقود	', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '0', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('11002', '1', '0', '2', 'Nawakas Scanning Document', '	مسح مستندات النواقص	', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '0', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('11003', '1', '0', '2', 'Receiving Transaction for withdraw abscond', '	استلام معاملة إلى سحب بلاغ الهروب	', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '6', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('11004', '1', '0', '2', 'Sponsor Information', '	بيانات الكفيل	', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '6', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('11005', '1', '0', '2', 'Update Immigration File Number', '	تعديل رقم ملف الهجرة	', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '6', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('11006', '1', '0', '2', 'Complaint Cancellation', '	إلغاء الشكوى	', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '6', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('11007', '1', '0', '2', 'Complaint Reactivation', '	إعادة تفعيل الشكوى	', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '6', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('11008', '1', '0', '2', 'Complaint settlement', '	حل الشكوى	', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '6', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('11009', '1', '0', '2', 'Modify Complain Contact Information', '	تعديل بيانات التواصل بخصوص الشكوى	', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '6', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('11010', '1', '0', '2', 'Modify Withdraw Abscond Contact Information', '	تعديل بيانات التواصل بخصوص سحب بلاغ الهروب	', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '6', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('11011', '1', '0', '2', 'Withdraw Absconding Questionaries&#039;', '	الرد على أسئلة الطرف الثاني لطلب سحب بلاغ هروب	', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '6', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('11012', '1', '0', '2', 'Withdraw Absconding Request', '	طلب تقديم سحب بلاغ الهروب	', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '6', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('11013', '1', '0', '2', 'Company Employees List', '	كشف عمال المنشأة	', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '6', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('11014', '1', '0', '2', 'Electronic Company Card', '	بطاقة اعتماد التواقيع	', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '6', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('11015', '1', '0', '2', 'Electronic Work Permit Information', '	بيانات تصريح عمل الكتروني	', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '6', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('11016', '1', '0', '2', 'E-Netwasal Employee Request', '	خدمات نتواصل	', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '6', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('11017', '1', '0', '2', 'Expired Electronic Work Permit List', '	قائمة تصريح عمل الكتروني المنتهية	', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '6', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('11018', '1', '0', '2', 'National Labor List', '	قائمة تصريح عمل الكتروني المواطنين	', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '6', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('11019', '1', '0', '2', 'Owner Role Information', '	كشف ملاك	', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '6', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('11020', '1', '0', '2', 'Person Information', '	بيانات الشخص	', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '6', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('11021', '1', '0', '2', 'PRO Details', '	بيانات المندوب	', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '6', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('11022', '1', '0', '2', 'Employee Certificate', '	شهادة عمل	', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '6', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('11023', '1', '0', '2', 'Company Report (All in One)', '	تقرير شامل عن المنشأة	', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '6', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('11024', '1', '0', '2', 'Typing Electronic Pre Approval for Work Permit Application - Prepaid', '	 طباعة طلب اشعار الموافقة المبدئية لتصريح العمل آلي - المدفوع مسبقاً	', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '23', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('11025', '1', '0', '2', 'Replacement of Pre Approval for Work Permit', '	استبدال اشعار الموافقة المبدئية لتصريح العمل	', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '23', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('11026', '1', '0', '2', 'Typing Electronic Pre Approval for Work Permit Application-Zones Corp', '	طباعة طلب اشعار الموافقة المبدئية لتصريح العمل آلي - Zones Corp	', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '23', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('11027', '1', '0', '2', 'Typing New Job Offer Letter', '	طباعة رسالة عرض العمل	', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '23', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('11028', '1', '0', '2', 'Typing Modification of Job Offer Letter', '	طباعة رسالة تعديل عرض العمل	', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '23', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('11029', '1', '0', '2', 'Typing Cancellation of Job Offer Letter', '	طباعة رسالة إلغاء عرض العمل	', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '23', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('11030', '1', '0', '2', 'Typing Work permit for Student Training', '	طباعة طلب تصريح عمل تدريب طالب	', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '23', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('11031', '1', '0', '2', 'Application for Incomplete Contract', '	استكمال نواقص عقد العمل	', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '23', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('11032', '1', '0', '2', 'Modification of National or GCC Electronic Work Permit', '	تعديل تصريح عمل الكتروني مواطن او دول مجلس التعاون	', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '23', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('11033', '1', '0', '2', 'New Electronic Work Permit', '	تصريح عمل الكتروني جديد	', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '23', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('11034', '1', '0', '2', 'New National and GCC Electronic Work Permit', '	طلب تصريح عمل الكتروني مواطن او دول مجلس التعاون	', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '23', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('11035', '1', '0', '2', 'Renew Mission Electronic Work Permit', '	تجديد تصريح عمل الكتروني لمهمة	', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '23', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('11036', '1', '0', '2', 'Request for Original Contract', '	طلب العقد الأصلي	', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '23', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('11037', '1', '0', '2', 'Electronic Work Permit / Pre Approval for Work Permit Fines', '	غرامة تصريح عمل الكتروني/ اشعار الموافقة المبدئية لتصريح العمل	', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '23', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('11038', '1', '0', '2', 'Company Fines', '	غرامة المنشأة	', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '23', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('11039', '1', '0', '2', 'Not Re-New License for temporary employment agency/ employment agency', '	عدم تجديد الترخيص في المواعيد المقررة	', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '23', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('11040', '1', '0', '2', 'Payment for New Company', '	رسوم منشأة جديدة	', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '23', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('11041', '1', '0', '2', 'Cancel Bank Guarantee Refund Request-Before Submission', '	الغاء طلب استرجاع الضمان المصرفي-قبل الاستلام	', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '23', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('11042', '1', '0', '2', 'Refund of Bank Guarantee', '	طلب استرجاع ضمان مصرفي	', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '23', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('11043', '1', '0', '2', 'Death Cancellation', '	طلب إلغاء تصريح عمل الكتروني - عامل متوفي	', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '23', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('11044', '1', '0', '2', 'Electronic Work Permit Cancellation', '	طلب إلغاء عامل تصريح عمل الكتروني	', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '23', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('11045', '1', '0', '2', 'Labor Case Cancellation', '	طلب إلغاء تصريح عمل الكتروني عامل لديه قضية عمالية	', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '23', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('11046', '1', '0', '2', 'Outside the Country Cancellation', '	إلغاء كفالة عامل خارج الدولة	', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '23', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('11047', '1', '0', '2', 'Pre Approval for Work Permit Cancellation', '	طلب إلغاء عامل اشعار الموافقة المبدئية لتصريح العمل مستخدم	', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '23', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('11048', '1', '0', '2', 'Sick Cancellation', '	إلغاء كفالة عامل امراض معدية	', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '23', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('11049', '1', '0', '2', 'Temporary/ Part Time/ Juvenile/ Student Training Work Permit Cancellation', '	إلغاء تصريح عمل لبعض الوقت/مؤقت/حدث/تدريب طالب	', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '23', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('11050', '1', '0', '2', 'Unused Pre Approval for Work Permit Cancellation', '	طلب خصم اشعار الموافقة المبدئية لتصريح العمل غير مستخدم	', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '23', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('11051', '1', '0', '2', 'Submit Modify or Renew - Modify Electronic Work Permit Application', '	استلام معاملة تعديل تصريح عمل الكتروني أو تجديد + تعديل	', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '23', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('11052', '1', '0', '2', 'Submit National New Electronic Work Permit Application', '	استلام معاملة تصريح عمل الكتروني مواطن	', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '23', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('11053', '1', '0', '2', 'Submit New Electronic Work Permit and Mission Electronic Work Permit Application', '	استلام معاملة تصريح عمل الكتروني جديد وتصريح عمل الكتروني مهمة	', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '23', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('11054', '1', '0', '2', 'Submit Renew Labor Card Application', '	استلام معاملة تجديد بطاقة عمل	', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '23', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('11055', '1', '0', '2', 'Submit Replacement of Pre Approval for Work Permit', '	استلام معاملة استبدال اشعار الموافقة المبدئية لتصريح العمل	', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '23', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('11056', '1', '0', '2', 'Submit Work Permit for Student Training', '	استلام صريح عمل تدريب طالب	', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '23', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('11057', '1', '0', '2', 'Update Work Permit Information', '	تحديث بيانات اشعار الموافقة المبدئية لتصريح العمل	', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '23', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('11058', '1', '0', '2', 'Deduction Duplicate File', '	خصم من سجلات وزارة العمل مكرر	', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '23', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('11059', '1', '0', '2', 'Deduction Electronic Work Permit in another company', '	طلب خصم سجل عامل لديه تصريح عمل الكتروني أو إقامة على منشأة أخرى	', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '23', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('11060', '1', '0', '2', 'Deduction No data in Immigration', '	طلب خصم سجل عامل لا توجد بيانات في إدارة الجنسية و الإقامة	', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '23', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('11061', '1', '0', '2', 'Deduction Old Cancellation not sent to computer', '	طلب خصم سجل عامل إلغاء قديم لم يرحل في الحاسب الآلي	', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '23', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('11062', '1', '0', '2', 'Deported by other Authority Cancellation', '	طلب خصم سجل عامل غادر الدولة بإبعاد إداري	', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '23', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('11063', '1', '0', '2', 'Request for Mission Quota', '	حصة اشعار الموافقة المبدئية لتصريح العمل لمهمة	', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '23', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('11064', '1', '0', '2', 'Request for quota for Electronic companies', '	طلب الإشتراك في خدمة المنشآت الإلكترونية	', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '23', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('11065', '1', '0', '2', 'Applying new Quota', '	حصة منشأة	', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '23', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('11066', '1', '0', '2', 'Cancellation of E-quota application', '	إلغاء حصة منشأة	', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '23', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('11067', '1', '0', '2', 'Quota for Zones Corp', '	حصة لمنشآت المؤسسة العليا للمناطق الاقتصادية المتخصصة	', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '23', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('11068', '1', '0', '2', 'Update Approved Quota', '	تعديل بيانات حصة منشأة موافق عليها	', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '23', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('11069', '1', '0', '2', 'Customer Service Request', '	طلب خدمة عملاء	', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '23', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('11070', '1', '0', '2', 'Police Letter To Arrest Runaway Labor', '	رسالة للشرطة للتحفظ على عامل هارب	', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '23', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('11071', '1', '0', '2', 'Request for Certificate Exemption', '	طلب استثناء من الشهادة	', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '23', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('11072', '1', '0', '2', 'Modify Person Information', '	تعديل بيانات الشخص	', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '23', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('11073', '1', '0', '2', 'New Person Creation', '	إضافة شخص جديد	', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '23', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('11074', '1', '0', '2', 'Cancel New Electronic Work Permit Application', '	طلب إلغاء تصريح عمل الكتروني جديدة	', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '23', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('11075', '1', '0', '2', 'Online Cancellation', '	إلغاء تصريح عمل الكتروني	', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '23', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('11076', '1', '0', '2', 'Submit New Person', '	تسليم بيانات شخص جديد	', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '23', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('11077', '1', '0', '2', 'Submit of Bank Guarantee', '	تقديم طلب استرجاع ضمان مصرفي	', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '23', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('11078', '1', '0', '2', 'Company License Renewal', '	تجديد رخصة منشأة	', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '23', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('11079', '1', '0', '2', 'Contract Registration', '	تسجيل العقود	', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '23', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('11080', '1', '0', '2', 'Sub Contract Registration', '	تسجيل العقود الفرعية	', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '23', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('11081', '1', '0', '2', 'Submit Add/Modify Owner', '	استلام إضافة / تعديل مالك	', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '23', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('11082', '1', '0', '2', 'Submit Cancel Establishment', '	استلام إلغاء منشأة	', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '23', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('11083', '1', '0', '2', 'Absconding - Electronic', '	بلاغ - إلكتروني	', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '23', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('11084', '1', '0', '2', 'Typing Mission Pre Approval for Work Permit Application', '	 طباعة طلب اشعار الموافقة المبدئية لتصريح العمل مهمة	', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '123', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('11085', '1', '0', '2', 'Typing Temporary Pre Approval for Work Permit Application', '	طباعة طلب اشعار الموافقة المبدئية لتصريح العمل مؤقت	', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '123', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('11086', '1', '0', '2', 'Typing Part Time Pre Approval for Work Permit Application', '	طباعة طلب اشعار الموافقة المبدئية لتصريح العمل لبعض الوقت	', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '123', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('11087', '1', '0', '2', 'Typing Juvenile Pre Approval for Work Permit Application', '	طباعة طلب اشعار الموافقة المبدئية لتصريح العمل حدث	', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '123', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('11088', '1', '0', '2', 'Typing Electronic Pre Approval for Work Permit Application', '	طباعة طلب اشعار الموافقة المبدئية لتصريح العمل آلي	', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '223', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('11089', '1', '0', '2', 'Typing Relative Pre Approval for Work Permit', '	اشعار الموافقة المبدئية لتصريح العمل على كفالة ذويهم	', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '223', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('11090', '1', '0', '2', 'Modify Contract', '	تعديل عقد العمل	', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '223', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('11091', '1', '0', '2', 'Modify Electronic Work Permit Application', '	تعديل بيانات تصريح عمل الكتروني	', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '223', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('11092', '1', '0', '2', 'Payment Form - Electronic Quota', '	طلب دفع رسوم - حصة الكتروني	', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '223', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('11093', '1', '0', '2', 'Renew Electronic Work Permit ? Level 1', '	تجديد تصريح عمل الكتروني – الفئة الاولى	', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '323', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('11094', '1', '0', '2', 'Pre Approval for Work Permit Payment Fees ? Level 1', '	دفع رسوم اشعار الموافقة المبدئية لتصريح العمل- الفئة الاولى 	', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '323', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('11095', '1', '0', '2', 'Modify + Renewal - Modify Electronic Work Permit Application level 1', '	تجديد تصريح عمل الكتروني – الفئة الاولى	', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '523', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('11096', '1', '0', '2', 'Payment for Pre Approval for Work Permit extension 10 days', '	دفع رسوم تمديد اشعار الموافقة المبدئية لتصريح العمل 10 أيام	', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '523', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('11097', '1', '0', '2', 'Submit Part Time Pre Approval for Work Permit', '	 استلام اشعار الموافقة المبدئية لتصريح العمل لبعض الوقت	', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '523', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('11098', '1', '0', '2', 'Submit Pre Approval for Work Permit for Juvenile', '	 استلام اشعار الموافقة المبدئية لتصريح العمل أحداث	', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '523', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('11099', '1', '0', '2', 'Submit Temporary Pre Approval for Work Permit', '	 استلام اشعار الموافقة المبدئية لتصريح العمل مؤقت	', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '523', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('111', '6', '3', '1', 'Entry Permit - New - Residence - Children /wife- Resident Sponsor Working In Private Sector or Free Zone (Outside)', '	أذونات الدخول - جديد - إقامة - أبناء - كفيل مقيم يعمل في القطاع الخاص أو منطقة حرة	', 'each', 'D', '4011', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '385.75', '1111', '3.15', '0', '45', '0', '0', '0', '0', '0'),
('11100', '1', '0', '2', 'Renew Electronic Work Permit ? Level 2A', '	تجديد تصريح عمل الكتروني – الفئة أ 2	', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '623', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('11101', '1', '0', '2', 'Pre Approval for Work Permit Payment Fees ? Level 2A', '	دفع رسوم اشعار الموافقة المبدئية لتصريح العمل أ2	', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '623', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('11102', '1', '0', '2', 'Submit Relative Sponsor Pre Approval for Work Permit ? Level 2A', '	استلام معاملة اشعار الموافقة المبدئية لتصريح العمل على كفالة ذويهم أ2	', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '623', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('11103', '1', '0', '2', 'Modify + Renewal - Modify Electronic Work Permit Application level 2A', '	تجديد + تعديل تصريح عمل الكتروني – الفئة أ2	', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '823', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('11104', '1', '0', '2', 'Payment for Pre Approval for Work Permit extension 20 days', '	دفع رسوم تمديد اشعار الموافقة المبدئية لتصريح العمل 20 يوم	', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '1023', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('11105', '1', '0', '2', 'Renew Electronic Work Permit ? LEVEL 2B', '	تجديد تصريح عمل الكتروني – الفئة ب2 	', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '1523', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('11106', '1', '0', '2', 'Pre Approval for Work Permit Payment Fees ? Level 2B', '	دفع رسوم اشعار الموافقة المبدئية لتصريح العمل- ب2	', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '1523', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('11107', '1', '0', '2', 'Payment for Pre Approval for Work Permit extension 30 days', '	دفع رسوم تمديد اشعار الموافقة المبدئية لتصريح العمل 30 يوم	', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '1523', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('11108', '1', '0', '2', 'Submit Relative Sponsor Pre Approval for Work Permit ? Level 2B', '	استلام معاملة اشعار الموافقة المبدئية لتصريح العمل على كفالة ذويهم ب2	', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '1523', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('11109', '1', '0', '2', 'Modify + Renewal - Modify Electronic Work Permit Application level 2B', '	تجديد + تعديل تصريح عمل الكتروني – الفئة ب2 	', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '1723', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('11110', '1', '0', '2', 'Renew Electronic Work Permit ? LEVEL 2C', '	تجديد تصريح عمل الكتروني – الفئة ج2	', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '2023', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('11111', '1', '0', '2', 'Pre Approval for Work Permit Payment Fees ? Level 2C', '	دفع رسوم اشعار الموافقة المبدئية لتصريح العمل – ج2	', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '2023', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('11112', '1', '0', '2', 'Payment for Pre Approval for Work Permit extension 40 days', '	دفع رسوم تمديد اشعار الموافقة المبدئية لتصريح العمل 40 يوم	', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '2023', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('11113', '1', '0', '2', 'Submit Relative Sponsor Pre Approval for Work Permit ? Level 2C', '	استلام معاملة اشعار الموافقة المبدئية لتصريح العمل على كفالة ذويهم ج2	', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '2023', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('11114', '1', '0', '2', 'Modify + Renewal - Modify Electronic Work Permit Application level 2C', '	تجديد + تعديل تصريح عمل الكتروني – الفئة ج2	', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '2223', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('11115', '1', '0', '2', 'Payment for Pre Approval for Work Permit extension 50 days', '	دفع رسوم تمديد اشعار الموافقة المبدئية لتصريح العمل 50 يوم	', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '2523', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('11116', '1', '0', '2', 'Renew Electronic Work Permit ? level 3 Age Above 65 yrs. ', '	تجديد تصريح عمل الكتروني – الفئة الثالثة العمر اكبر عن 65 سنة  	', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '5023', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('11117', '1', '0', '2', 'Pre Approval for Work Permit Payment Fees ? Level 3', '	دفع رسوم اشعار الموافقة المبدئية لتصريح العمل - الفئة الثالثة	', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '5023', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('11118', '1', '0', '2', 'Payment for Pre Approval for Work Permit extension 60 days', '	دفع رسوم تمديد اشعار الموافقة المبدئية لتصريح العمل 60 يوم	', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '5023', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('11119', '1', '0', '2', 'Modify + Renewal - Modify Electronic Work Permit Application level 3', '	تجديد + تعديل تصريح عمل الكتروني – الفئة الثالثة	', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '5223', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('11120', '1', '0', '2', 'New License for temporary employment agency', '	ترخيص جديد لوكالة التوظيف المؤقت للعمالة	', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '10023', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('11121', '1', '0', '2', 'Re-New License For employment agency', '	تجديد ترخيص وكالة التوسط للعمالة	', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '25023', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('11122', '1', '0', '2', 'New License For employment agency', '	طلب ترخيص وكالة توظيف خاصة	', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '50023', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('11123', '1', '0', '2', 'Re-New License for temporary employment agency', '	تجديد ترخيص وكالة التوظيف المؤقت للعمالة	', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '50023', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('116', '6', '3', '1', 'Entry Permit - New - Residence - Children/wife - Investor or Partner Sponsor (Inside)', '	أذونات الدخول - جديد - إقامة - أبناء - كفيل مستثمر أو شريك	', 'each', 'D', '4011', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '4116.75', '1111', '3.15', '0', '45', '0', '0', '0', '0', '0'),
('125', '6', '3', '1', 'Entry Permit - New - Sponsor Registration (OPEN FILE  )', '	فتح ملف 	', 'each', 'D', '4015', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '215.75', '1111', '3', '0.15', '45', '0', '0', '0', '0', '0'),
('128', '6', '3', '1', 'Entry Permit - New - Residence - Children/wife - Investor or Partner Sponsor (Outside)', '	أذونات الدخول - جديد - إقامة - أبناء - كفيل مستثمر أو شريك	', 'each', 'D', '4011', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '3446.75', '1111', '3.15', '0', '45', '0', '0', '0', '0', '0'),
('130', '6', '3', '1', 'Entry Permit - New - Residence - Children/Wife - Resident Sponsor Working In Government (Inside)', '	أذونات الدخول - جديد - إقامة - أبناء - كفيل مقيم يعمل في الجهات الحكومية	', 'each', 'D', '4011', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '1005.75', '1111', '3.15', '0', '45', '0', '0', '0', '0', '0'),
('141', '6', '3', '1', 'Entry Permit - New - Residence - Children/Wife - Resident Sponsor Working In Government (Outside)', '	أذونات الدخول - جديد - إقامة - أبناء - كفيل مقيم يعمل في الجهات الحكومية	', 'each', 'D', '4011', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '335.75', '1111', '3.15', '0', '45', '0', '0', '0', '0', '0'),
('142', '6', '3', '1', 'Entry Permit - New - Residence - Parents - Investor or Partner Sponsor (Inside)', '	أذونات الدخول - جديد - إقامة - والدين - كفيل مستثمر أو شريك	', 'each', 'D', '4011', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '1055.75', '1111', '3.15', '0', '45', '0', '0', '0', '0', '0'),
('155', '6', '3', '1', 'Entry Permit - New - Residence - Parents - Investor or Partner Sponsor (Outside)', '	أذونات الدخول - جديد - إقامة - والدين - كفيل مستثمر أو شريك	', 'each', 'D', '4011', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '385.75', '1111', '3.15', '0', '45', '0', '0', '0', '0', '0'),
('156', '6', '3', '1', 'Entry Permit - New - Residence - Parents - National Sponsor (Inside)', '	أذونات الدخول - جديد - إقامة - والدين - كفيل مواطن	', 'each', 'D', '4011', '5010', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0000-00-00', '0000-00-00', '', '0', '955.75', '1111', '3.15', '45', '0', '0', '0', '0', '0', '0');
INSERT INTO `0_stock_master` VALUES
('157', '6', '3', '1', 'Entry Permit - New - Residence - Parents - National Sponsor (Outside)', '	أذونات الدخول - جديد - إقامة - والدين - كفيل مواطن	', 'each', 'D', '4011', '5010', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0000-00-00', '0000-00-00', '', '0', '285.75', '1111', '3.15', '45', '0', '0', '0', '0', '0', '0'),
('158', '6', '3', '1', 'Entry Permit - New - Residence - Parents - Resident Sponsor Working In Private Sector or Free Zone (Inside)', '	أذونات الدخول - جديد - إقامة - والدين - كفيل مقيم يعمل في القطاع الخاص أو منطقة حرة	', 'each', 'D', '4011', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '1055.75', '1111', '3.15', '0', '45', '0', '0', '0', '0', '0'),
('159', '6', '3', '1', 'Entry Permit - New - Residence - Parents - Resident Sponsor Working In Private Sector or Free Zone (Outside)', '	أذونات الدخول - جديد - إقامة - والدين - كفيل مقيم يعمل في القطاع الخاص أو منطقة حرة	', 'each', 'D', '4011', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '385.75', '1111', '3.15', '0', '45', '0', '0', '0', '0', '0'),
('163', '6', '3', '1', 'Entry Permit - New - Residence - Wife - National Sponsor (Inside)', '	أذونات الدخول - جديد - إقامة - زوجة - كفيل مواطن	', 'each', 'D', '4011', '5010', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0000-00-00', '0000-00-00', '', '0', '955.75', '1111', '3.15', '45', '0', '0', '0', '0', '0', '0'),
('170', '6', '3', '1', 'Entry Permit - New - Residence - Wife - National Sponsor (Outside)', '	أذونات الدخول - جديد - إقامة - زوجة - كفيل مواطن	', 'each', 'D', '4011', '5010', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0000-00-00', '0000-00-00', '', '0', '285.75', '1111', '3.15', '45', '0', '0', '0', '0', '0', '0'),
('171', '6', '3', '1', 'Entry Permit - New - Residence - Wife/ Children - Investor or Partner Sponsor (Inside)', '	أذونات الدخول - جديد - إقامة - والدين - كفيل مستثمر أو شريك	', 'each', 'D', '4011', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '4116.75', '1111', '3.15', '0', '45', '0', '0', '0', '0', '0'),
('172', '6', '3', '1', 'Entry Permit - New - Residence - Wife/ Children - Investor or Partner Sponsor (Outside)', '	أذونات الدخول - جديد - إقامة - والدين - كفيل مستثمر أو شريك	', 'each', 'D', '4011', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '3415.75', '1111', '3.15', '0', '45', '0', '0', '0', '0', '0'),
('175', '6', '3', '1', 'Entry Permit - New - Short Term Visit - Single Entry - Leisure', '	أذونات الدخول - جديد - زيارة قصيرة - سفرة واحدة - ترفيه	', 'each', 'D', '4011', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '1436.75', '1111', '3.15', '0', '45', '0', '0', '0', '0', '0'),
('179', '6', '3', '1', 'Entry Permit - New - Work - Investor - partner (Inside)', '	أذونات الدخول - جديد - عمل - مستثمر	', 'each', 'D', '4011', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '1092.5', '1111', '3.15', '0', '45', '0', '0', '0', '0', '0'),
('183', '6', '3', '1', 'Entry Permit - New - Work - Investor - partner (Outside)', '	أذونات الدخول - جديد - عمل - مستثمر	', 'each', 'D', '4011', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '422.5', '1111', '3.15', '0', '45', '0', '0', '0', '0', '0'),
('190', '6', '3', '1', 'Entry Permit - New - Work - Private Sector or Free Zone( Inside)', '	أذونات الدخول - جديد - عمل - القطاع الخاص أو منطقة حرة	', 'each', 'D', '4011', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '1092.5', '1111', '3.15', '0', '45', '0', '0', '0', '0', '0'),
('196', '6', '3', '1', 'Entry Permit - New - Work - Private Sector or Free Zone( Outside)', '	أذونات الدخول - جديد - عمل - القطاع الخاص أو منطقة حرة	', 'each', 'D', '4011', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '422.5', '1111', '3.15', '0', '45', '0', '0', '0', '0', '0'),
('1T1', '1', '0', '2', 'TASHEEL SERVICE', 'خدمة تسهيل', 'each', 'D', '4018', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '1', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '0', '', '0', '0', '0', '0', '0', '0', '0', '0'),
('202', '6', '4', '1', 'Residence - Cancel - Cancel Residence  Inside (company)', '	طلبات الإقامة - إلغاء - إلغاء تصريح الإقامة	', 'each', 'D', '4011', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '122.5', '1111', '3.15', '0', '45', '0', '0', '0', '0', '0'),
('203', '6', '4', '1', 'Residence - Cancel - Cancel Residence  Inside (Family)', '	طلبات الإقامة - إلغاء - إلغاء تصريح الإقامة	', 'each', 'D', '4011', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '85.75', '1111', '3.15', '0', '45', '0', '0', '0', '0', '0'),
('212', '6', '4', '1', 'Residence - Cancel - Cancel Residence outside  (company)', '	طلبات الإقامة - إلغاء - إلغاء تصريح الإقامة	', 'each', 'D', '4011', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '222.5', '1111', '3.15', '0', '45', '0', '0', '0', '0', '0'),
('221', '6', '4', '1', 'Residence - Cancel - Cancel Residence-outside (Family)', '	طلبات الإقامة - إلغاء - إلغاء تصريح الإقامة	', 'each', 'D', '4011', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '185.75', '1111', '3.15', '0', '45', '0', '0', '0', '0', '0'),
('223', '6', '4', '1', 'Residence - Cancel - Cancel Visa After Entering (family)', '	طلبات الإقامة - إلغاء - إلغاء التأشيرة بعد الدخول	', 'each', 'D', '4011', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '85.75', '1111', '3.15', '0', '45', '0', '0', '0', '0', '0'),
('225', '6', '5', '1', 'Residence - Data Modification - Nationality Change (Normal)', '	طلبات الإقامة - تعديل بيانات - تغيير الجنسية	', 'each', 'D', '4011', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '272.5', '1111', '3.15', '0', '45', '0', '0', '0', '0', '0'),
('226', '6', '5', '1', 'Residence - Data Modification - Nationality Change (Urgent)', '	طلبات الإقامة - تعديل بيانات - تغيير الجنسية	', 'each', 'D', '4011', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '352.5', '1111', '3.15', '0', '45', '0', '0', '0', '0', '0'),
('2268', '6', '6', '1', 'Residence - Renew - Son Older Than 21 Years Old - Resident Sponsor Working In Private Sector or Free Zone (Urgent)', '', 'each', 'D', '4011', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '335.75', '1112', '3', '0.15', '0', '0', '0', '0', '0', '0'),
('227', '8', '0', '1', 'DEPOSIT', '	ضمان 	', 'each', 'D', '4015', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '0', '1111', '3', '0.15', '45', '0', '0', '0', '0', '0'),
('229', '6', '5', '1', 'Residence - Data Modification - Update Personal Information (Urgent)', '	طلبات الإقامة - تعديل بيانات - تحديث البيانات الشخصية	', 'each', 'D', '4011', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '272.5', '1111', '3.15', '0', '45', '0', '0', '0', '0', '0'),
('231', '6', '5', '1', 'Residence - Data Modification - Update Personal Information (Normal)', '	طلبات الإقامة - تعديل بيانات - تحديث البيانات الشخصية	', 'each', 'D', '4011', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '193.5', '1111', '3.15', '0', '45', '0', '0', '0', '0', '0'),
('235', '6', '7', '1', 'Residence - New - Investor or Partner (Urgent)', '	طلبات الإقامة - جديد - مستثمر أو شريك	', 'each', 'D', '4011', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '822.5', '1111', '3.15', '0', '45', '0', '0', '0', '0', '0'),
('238', '6', '7', '1', 'Residence - New - Investor or Partner (Normal)', '	طلبات الإقامة - جديد - مستثمر أو شريك	', 'each', 'D', '4011', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '743.5', '1111', '3.15', '0', '45', '0', '0', '0', '0', '0'),
('239', '6', '7', '1', 'Residence - New - New Born Baby - Investor or Partner Sponsor (Normal)', '	طلبات الإقامة - جديد - مولود جديد - كفيل مستثمر أو شريك	', 'each', 'D', '4011', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '3567.75', '1111', '3.15', '0', '45', '0', '0', '0', '0', '0'),
('241', '6', '7', '1', 'Residence - New - New Born Baby - Investor or Partner Sponsor (Urgent)', '	طلبات الإقامة - جديد - مولود جديد - كفيل مستثمر أو شريك	', 'each', 'D', '4011', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '3647.8', '1111', '3.15', '0', '45', '0', '0', '0', '0', '0'),
('242', '6', '7', '1', 'Residence - New - New Born Baby - Resident Sponsor Working In Government (Normal)', '	طلبات الإقامة - جديد - مولود جديد - كفيل مقيم يعمل في الجهات الحكومية	', 'each', 'D', '4011', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '535.75', '1111', '3.15', '0', '45', '0', '0', '0', '0', '0'),
('243', '6', '7', '1', 'Residence - New - New Born Baby - Resident Sponsor Working In Government (Urgent)', '	طلبات الإقامة - جديد - مولود جديد - كفيل مقيم يعمل في الجهات الحكومية	', 'each', 'D', '4011', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '615.75', '1111', '3.15', '0', '45', '0', '0', '0', '0', '0'),
('248', '6', '7', '1', 'Residence - New - New Born Baby - Resident Sponsor Working In Private Sector or Free Zone 2 Year (Normal)', '	طلبات الإقامة - جديد - مولود جديد - كفيل مقيم يعمل في القطاع الخاص أو منطقة حرة	', 'each', 'D', '4011', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '356.75', '1111', '3.15', '0', '45', '0', '0', '0', '0', '0'),
('249', '6', '7', '1', 'Residence - New - New Born Baby - Resident Sponsor Working In Private Sector or Free Zone 2 Year (Urgent)', '	طلبات الإقامة - جديد - مولود جديد - كفيل مقيم يعمل في القطاع الخاص أو منطقة حرة	', 'each', 'D', '4011', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '436.75', '1111', '3.15', '0', '45', '0', '0', '0', '0', '0'),
('252', '6', '7', '1', 'Residence - New - New Born Baby - Resident Sponsor Working In Private Sector or Free Zone 3 Year (Urgent)', '	طلبات الإقامة - جديد - مولود جديد - كفيل مقيم يعمل في القطاع الخاص أو منطقة حرة	', 'each', 'D', '4011', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '536.75', '1111', '3.15', '0', '45', '0', '0', '0', '0', '0'),
('254', '6', '7', '1', 'Residence - New - New Born Baby - Resident Sponsor Working In Private Sector or Free Zone 3 Year (Normal)', '	طلبات الإقامة - جديد - مولود جديد - كفيل مقيم يعمل في القطاع الخاص أو منطقة حرة	', 'each', 'D', '4011', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '456.75', '1111', '3.15', '0', '45', '0', '0', '0', '0', '0'),
('257', '6', '7', '1', 'Residence - New - Parents - Investor or Partner Sponsor (Urgent)', '	طلبات الإقامة - جديد - والدين - كفيل مستثمر أو شريك	', 'each', 'D', '4011', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '385.75', '1111', '3.15', '0', '45', '0', '0', '0', '0', '0'),
('259', '6', '7', '1', 'Residence - New - Parents - Investor or Partner Sponsor (Normal)', '	طلبات الإقامة - جديد - والدين - كفيل مستثمر أو شريك	', 'each', 'D', '4011', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '305.75', '1111', '3.15', '0', '45', '0', '0', '0', '0', '0'),
('261', '6', '7', '1', 'Residence - New - Parents - Resident Sponsor Working In Government (Normal)', '	طلبات الإقامة - جديد - والدين - كفيل مقيم يعمل في الجهات الحكومية	', 'each', 'D', '4011', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '256.75', '1111', '3.15', '0', '45', '0', '0', '0', '0', '0'),
('264', '6', '7', '1', 'Residence - New - Parents - Resident Sponsor Working In Government (Urgent)', '	طلبات الإقامة - جديد - والدين - كفيل مقيم يعمل في الجهات الحكومية	', 'each', 'D', '4011', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '336.75', '1111', '3.15', '0', '45', '0', '0', '0', '0', '0'),
('265', '6', '7', '1', 'Residence - New - Parents - Resident Sponsor Working In Private Sector or Free Zone (Normal)', '	طلبات الإقامة - جديد - والدين - كفيل مقيم يعمل في القطاع الخاص أو منطقة حرة	', 'each', 'D', '4011', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '256.75', '1111', '3.15', '0', '45', '0', '0', '0', '0', '0'),
('266', '6', '7', '1', 'Residence - New - Parents - Resident Sponsor Working In Private Sector or Free Zone (Urgent)', '	طلبات الإقامة - جديد - والدين - كفيل مقيم يعمل في القطاع الخاص أو منطقة حرة	', 'each', 'D', '4011', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '336.75', '1111', '3.15', '0', '45', '0', '0', '0', '0', '0'),
('267', '6', '7', '1', 'Residence - New - Son Older Than 21 Years Old - Resident Sponsor Working In Private Sector or Free Zone (Normal)', '	طلبات الإقامة - جديد - ابن أكبر من 18 سنة - كفيل مقيم يعمل في القطاع الخاص أو منطقة حرة	', 'each', 'D', '4011', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '256.75', '1111', '3.15', '0', '45', '0', '0', '0', '0', '0'),
('268', '6', '7', '1', 'Residence - New - Son Older Than 21 Years Old - Resident Sponsor Working In Private Sector or Free Zone (Urgent)', '	طلبات الإقامة - جديد - ابن أكبر من 18 سنة - كفيل مقيم يعمل في القطاع الخاص أو منطقة حرة	', 'each', 'D', '4011', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '336.75', '1111', '3.15', '0', '45', '0', '0', '0', '0', '0'),
('269', '6', '7', '1', 'Residence - New - Wife And Children - Investor or Partner Sponsor (Normal)', '	طلبات الإقامة - جديد - زوجة وأبناء - كفيل مستثمر أو شريك	', 'each', 'D', '4011', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '506.75', '1111', '3.15', '0', '45', '0', '0', '0', '0', '0'),
('270', '6', '7', '1', 'Residence - New - Wife And Children - Investor or Partner Sponsor (Urgent)', '	طلبات الإقامة - جديد - زوجة وأبناء - كفيل مستثمر أو شريك	', 'each', 'D', '4011', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '586.75', '1111', '3.15', '0', '45', '0', '0', '0', '0', '0'),
('272', '8', '0', '1', 'IMMIGRATION FINE', '	المخالفة	', 'each', 'D', '4015', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '0', '1111', '0', '0', '45', '0', '0', '0', '0', '0'),
('273', '6', '7', '1', 'Residence - New - Wife And Children - Resident Sponsor Working In Government (Normal)', '	طلبات الإقامة - جديد - زوجة وأبناء - كفيل مقيم يعمل في الجهات الحكومية	', 'each', 'D', '4011', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '456.75', '1111', '3.15', '0', '45', '0', '0', '0', '0', '0'),
('280', '6', '7', '1', 'Residence - New - Wife And Children - Resident Sponsor Working In Government (Urgent)', '	طلبات الإقامة - جديد - زوجة وأبناء - كفيل مقيم يعمل في الجهات الحكومية	', 'each', 'D', '4011', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '535.75', '1111', '3', '0.15', '45', '0', '0', '0', '0', '0'),
('282', '6', '7', '1', 'Residence - New - Work - Private Sector or Free Zone', '	طلبات الإقامة - جديد - عمل - القطاع الخاص أو منطقة حرة	', 'each', 'D', '4011', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '493.5', '1111', '3.15', '0', '45', '0', '0', '0', '0', '0'),
('283', '6', '6', '1', 'Residence - Renew - Investor or Partner (Urgent)', '	طلبات الإقامة - تجديد - مستثمر أو شريك	', 'each', 'D', '4011', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '822.5', '1111', '3.15', '0', '45', '0', '0', '0', '0', '0'),
('284', '6', '6', '1', 'Residence - Renew - Investor or Partner (Normal)', '	طلبات الإقامة - تجديد - مستثمر أو شريك	', 'each', 'D', '4011', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '742.5', '1111', '3.15', '0', '45', '0', '0', '0', '0', '0'),
('285', '6', '6', '1', 'Residence - Renew - Parents - Investor or Partner Sponsor (Normal)', '	طلبات الإقامة - تجديد - والدين - كفيل مستثمر أو شريك	', 'each', 'D', '4011', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '5347.75', '1111', '3.15', '0', '45', '0', '0', '0', '0', '0'),
('288', '6', '6', '1', 'Residence - Renew - Parents - Investor or Partner Sponsor (Urgent)', '	طلبات الإقامة - تجديد - والدين - كفيل مستثمر أو شريك	', 'each', 'D', '4011', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '5427.75', '1111', '3.15', '0', '45', '0', '0', '0', '0', '0'),
('290', '6', '6', '1', 'Residence - Renew - Parents - National Sponsor (Normal)', '	طلبات الإقامة - تجديد - والدين - كفيل مواطن	', 'each', 'D', '4011', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0000-00-00', '0000-00-00', '', '0', '285.75', '1111', '3.15', '45', '0', '0', '0', '0', '0', '0'),
('300', '6', '3', '1', 'Entry Permit / New / Job Seekers', 'أذونات الدخول / جديد / الباحثين عن عمل', 'each', 'D', '4011', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '535.75', '1111', '3', '0.15', '0', '0', '0', '0', '0', '0'),
('301', '6', '3', '1', 'Entry Permit / New / Job Seekers', 'Entry Permit / New / Job Seekers', 'each', 'D', '4011', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '0', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('3012', '6', '3', '1', 'Entry Permit / New / Job Seekers', 'أذونات الدخول / جديد / الباحثين عن عمل', 'each', 'D', '4011', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '0', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('303', '6', '6', '1', 'Residence - Renew - Parents - National Sponsor (urgent)', '	طلبات الإقامة - تجديد - والدين - كفيل مواطن	', 'each', 'D', '4011', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0000-00-00', '0000-00-00', '', '0', '365.75', '1111', '3.15', '0', '0', '0', '0', '0', '0', '0'),
('305', '6', '8', '1', 'Residence / Sponsorship Transfer / Transfer Plus Residence - Work - Government - From Same Emirate (Urgent)', 'طلبات القامة / نقل كفالة / نقل زائد إقامة - عمل - الجهات الحكومية - من نفس المارة', 'each', 'D', '4011', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '872', '1111', '3', '0.15', '0', '0', '0', '0', '0', '0'),
('311', '6', '6', '1', 'Residence - Renew - Parents - Resident Sponsor Working In Government (Normal)', '	طلبات الإقامة - تجديد - والدين - كفيل مقيم يعمل في الجهات الحكومية	', 'each', 'D', '4011', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '5316.75', '1111', '3.15', '0', '0', '0', '0', '0', '0', '0'),
('315', '6', '7', '1', 'Residence / New / Wife And Children - Resident Sponsor Working In Private Sector or Free Zone (Normal)', 'طلبات القامة / جديد / زوجة وأبناء - كفيل مقيم يعمل ف القطاع الخاص أو منطقة حرة', 'each', 'D', '4011', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '0', '1111', '0', '0', '0', '0', '0', '0', '0', '0'),
('316', '6', '4', '1', 'Residence - Cancel - Cancel Visa After Entering (Company)', 'طلبات الإقامة - إلغاء - إلغاء التأشيرة بعد الدخول', 'each', 'D', '4011', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '122.5', '1111', '3', '0.15', '0', '0', '0', '0', '0', '0'),
('317', '6', '7', '1', 'Residence / New / Wife And Children - Resident Sponsor Working In Private Sector or Free Zone (Normal)', 'طلبات القامة / جديد / زوجة وأبناء - كفيل مقيم يعمل ف القطاع الخاص أو منطقة حرة', 'each', 'D', '4011', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '356.75', '1111', '3', '0.15', '0', '0', '0', '0', '0', '0'),
('319', '6', '7', '1', 'Residence / New / Wife And Children - Resident Sponsor Working In Private Sector or Free Zone (Urgent))', 'طلبات القامة / جديد / زوجة وأبناء - كفيل مقيم يعمل ف القطاع الخاص أو منطقة حرة', 'each', 'D', '4011', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '456.75', '1111', '3', '0.15', '0', '0', '0', '0', '0', '0'),
('320', '6', '6', '1', 'Residence - Renew - Parents - Resident Sponsor Working In Government (Urgent)', '	طلبات الإقامة - تجديد - والدين - كفيل مقيم يعمل في الجهات الحكومية	', 'each', 'D', '4011', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '5396.75', '1111', '3.15', '0', '0', '0', '0', '0', '0', '0'),
('322', '6', '7', '1', 'Residence / New / Wife And Children - Resident Sponsor Working In Private Sector or Free Zone  (Urgent)	', 'طلبات القامة / جديد / زوجة وأبناء - كفيل مقيم يعمل ف القطاع الخاص أو منطقة حرة', 'each', 'D', '4011', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '435.75', '1111', '3', '0.15', '0', '0', '0', '0', '0', '0'),
('325', '6', '7', '1', 'Residence - New - Work - Private Sector or Free Zone (urgent) ', 'طلبات الإقامة - جديد - عمل - القطاع الخاص أو منطقة حرة', 'each', 'D', '4011', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '572.5', '1111', '3', '0.15', '0', '0', '0', '0', '0', '0'),
('326', '6', '7', '1', 'Residence / New / Wife And Children - Resident Sponsor Working In Private Sector or Free Zone', 'طلبات القامة / جديد / زوجة وأبناء - كفيل مقيم يعمل ف القطاع الخاص أو منطقة حرة', 'each', 'D', '4011', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '535.75', '1111', '3.15', '0', '0', '0', '0', '0', '0', '0'),
('328', '6', '7', '1', 'Residence - New - Wife And Children - Resident Sponsor Working In Government 1 year  (Normal)', 'طلبات الإقامة - جديد - زوجة وأبناء - كفيل مقيم يعمل في الجهات الحكومية	', 'each', 'D', '4011', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '256.75', '1111', '3', '0.15', '0', '0', '0', '0', '0', '0'),
('329', '6', '6', '1', 'Residence / New / New Born Baby - National Sponsor', 'طلبات القامة / جديد / مولود جديد - كفيل مواطن', 'each', 'D', '4011', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '406.75', '1111', '3.15', '0', '0', '0', '0', '0', '0', '0'),
('330', '6', '6', '1', 'Residence / Renew / Wife And Children - Resident Sponsor Working In Private Sector or Free Zone 1 year (Nomal)', 'طلبات القامة / تجديد / زوجة وأبناء - كفيل مقيم يعمل ف القطاع الخاص أو منطقة حرة', 'each', 'D', '4011', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '256.75', '1111', '3', '0.15', '0', '0', '0', '0', '0', '0'),
('344', '6', '7', '1', 'Residence / New / Wife And Children - Resident Sponsor Working In Private Sector or Free Zone', 'طلبات القامة / جديد / زوجة وأبناء - كفيل مقيم يعمل ف القطاع الخاص أو منطقة حرة', 'each', 'D', '4011', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '356.75', '1111', '3.15', '0', '0', '0', '0', '0', '0', '0'),
('358', '6', '6', '1', 'Residence / Renew / Wife And Children - Resident Sponsor Working In Private Sector or Free Zone 1 year (Urgentl) ', 'طلبات القامة / تجديد / زوجة وأبناء - كفيل مقيم يعمل ف القطاع الخاص أو منطقة حرة\r\n\r\n', 'each', 'D', '4011', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '335.75', '1111', '3.15', '0', '0', '0', '0', '0', '0', '0'),
('401', '5', '0', '1', '24 HOURS MEDICAL', ' (فحص طبي مستعجل (24 ساعة )', 'each', 'D', '4013', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '3.16', '480', '1112', '0', '0', '0', '0', '0', '0', '0', '0'),
('5000', '4', '0', '1', 'INSURANCE', '', 'each', 'D', '4012', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '0', '1116', '0', '0', '0', '0', '0', '0', '0', '0'),
('501', '6', '6', '1', 'Residence - Renew - Parents - Resident Sponsor Working In Private Sector or Free Zone (Normal)', '	طلبات الإقامة - تجديد - والدين - كفيل مقيم يعمل في القطاع الخاص أو منطقة حرة	', 'each', 'D', '4011', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '5316.75', '1111', '3.15', '0', '0', '0', '0', '0', '0', '0'),
('502', '6', '6', '1', 'Residence - Renew - Parents - Resident Sponsor Working In Private Sector or Free Zone (Urgent)', '	طلبات الإقامة - تجديد - والدين - كفيل مقيم يعمل في القطاع الخاص أو منطقة حرة	', 'each', 'D', '4011', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '385.75', '1111', '3.15', '0', '0', '0', '0', '0', '0', '0'),
('503', '6', '6', '1', 'Residence - Renew - Son Older Than 21 Years Old - Investor or Partner Sponsor', '	طلبات الإقامة - تجديد - ابن أكبر من 21 سنة - كفيل مستثمر أو شريك	', 'each', 'D', '4011', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '0', '1111', '3.15', '0', '0', '0', '0', '0', '0', '0'),
('504', '6', '6', '1', 'Residence - Renew - Son Older Than 21 Years Old - Resident Sponsor Working In Government', '	طلبات الإقامة - تجديد - ابن أكبر من 21 سنة - كفيل مقيم يعمل في الجهات الحكومية	', 'each', 'D', '4011', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '0', '1111', '3.15', '0', '0', '0', '0', '0', '0', '0'),
('505', '6', '6', '1', 'Residence - Renew - Son Older Than 21 Years Old - Resident Sponsor Working In Private Sector or Free Zone', '	طلبات الإقامة - تجديد - ابن أكبر من 21 سنة - كفيل مقيم يعمل في القطاع الخاص أو منطقة حرة	', 'each', 'D', '4011', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '5396.75', '1111', '3.15', '0', '0', '0', '0', '0', '0', '0'),
('506', '6', '6', '1', 'Residence - Renew - Wife And Children - Investor or Partner Sponsor (Normal)', '	طلبات الإقامة - تجديد - زوجة وأبناء - كفيل مستثمر أو شريك	', 'each', 'D', '4011', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '3567.75', '1111', '3.15', '0', '0', '0', '0', '0', '0', '0'),
('507', '6', '6', '1', 'Residence - Renew - Wife And Children - Investor or Partner Sponsor (Urgent)', '	طلبات الإقامة - تجديد - زوجة وأبناء - كفيل مستثمر أو شريك	', 'each', 'D', '4011', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '3647.75', '1111', '3.15', '0', '0', '0', '0', '0', '0', '0'),
('508', '6', '6', '1', 'Residence - Renew - Wife And Children - National Sponsor (Normal)', '	طلبات الإقامة - تجديد - زوجة وأبناء - كفيل مواطن	', 'each', 'D', '4011', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0000-00-00', '0000-00-00', '', '0', '406.75', '1111', '3.15', '0', '0', '0', '0', '0', '0', '0'),
('509', '6', '6', '1', 'Residence - Renew - Wife And Children - National Sponsor (urgent)', '	طلبات الإقامة - تجديد - زوجة وأبناء - كفيل مواطن	', 'each', 'D', '4011', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0000-00-00', '0000-00-00', '', '0', '565.75', '1111', '3.15', '0', '0', '0', '0', '0', '0', '0'),
('510', '6', '6', '1', 'Residence - Renew - Wife And Children - Resident Sponsor Working In Government (Urgent)', '	طلبات الإقامة - تجديد - زوجة وأبناء - كفيل مقيم يعمل في الجهات الحكومية	', 'each', 'D', '4011', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '535.75', '1111', '3.15', '0', '0', '0', '0', '0', '0', '0'),
('511', '6', '6', '1', 'Residence - Renew - Wife And Children - Resident Sponsor Working In Government (Normal)', '	طلبات الإقامة - تجديد - زوجة وأبناء - كفيل مقيم يعمل في الجهات الحكومية	', 'each', 'D', '4011', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '456.75', '1111', '3.15', '0', '0', '0', '0', '0', '0', '0'),
('512', '6', '6', '1', 'Residence - Renew - Wife And Children - Resident Sponsor Working In Private Sector or Free Zone 2 year (Normal)', '	طلبات الإقامة - تجديد - زوجة وأبناء - كفيل مقيم يعمل في القطاع الخاص أو منطقة حرة	', 'each', 'D', '4011', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '356.75', '1111', '3.15', '0', '0', '0', '0', '0', '0', '0'),
('513', '6', '6', '1', 'Residence - Renew - Wife And Children - Resident Sponsor Working In Private Sector or Free Zone 2 Year(Urgent)', '	طلبات الإقامة - تجديد - زوجة وأبناء - كفيل مقيم يعمل في القطاع الخاص أو منطقة حرة	', 'each', 'D', '4011', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '435.75', '1111', '3.15', '0', '0', '0', '0', '0', '0', '0'),
('514', '6', '6', '1', 'Residence - Renew - Wife And Children - Resident Sponsor Working In Private Sector or Free Zone 3 Year(Urgent)', '	طلبات الإقامة - تجديد - زوجة وأبناء - كفيل مقيم يعمل في القطاع الخاص أو منطقة حرة	', 'each', 'D', '4011', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '535.75', '1111', '3.15', '0', '0', '0', '0', '0', '0', '0'),
('515', '6', '6', '1', 'Residence - Renew - Wife And Children - Resident Sponsor Working In Private Sector or Free Zone 3 Year(Normal)', '	طلبات الإقامة - تجديد - زوجة وأبناء - كفيل مقيم يعمل في القطاع الخاص أو منطقة حرة	', 'each', 'D', '4011', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '456.75', '1111', '3.15', '0', '0', '0', '0', '0', '0', '0'),
('516', '6', '6', '1', 'Residence - Renew - Work - Government (Urgent)', '	طلبات الإقامة - تجديد - عمل - الجهات الحكومية	', 'each', 'D', '4011', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '672.5', '1111', '3.15', '0', '0', '0', '0', '0', '0', '0'),
('517', '6', '6', '1', 'Residence - Renew - Work - Government (Normal)', '	طلبات الإقامة - تجديد - عمل - الجهات الحكومية	', 'each', 'D', '4011', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '592.5', '1111', '3.15', '0', '0', '0', '0', '0', '0', '0'),
('518', '6', '6', '1', 'Residence - Renew - Work - Private Sector or Free Zone (Urgent)', '	طلبات الإقامة - تجديد - عمل - القطاع الخاص أو منطقة حرة	', 'each', 'D', '4011', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '572.5', '1111', '3.15', '0', '0', '0', '0', '0', '0', '0'),
('519', '6', '6', '1', 'Residence - Renew - Work - Private Sector or Free Zone (Normal)', '	طلبات الإقامة - تجديد - عمل - القطاع الخاص أو منطقة حرة	', 'each', 'D', '4011', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '493.5', '1111', '3.15', '0', '0', '0', '0', '0', '0', '0'),
('520', '6', '8', '1', 'Residence - Sponsorship Transfer - Transfer Plus Residence - Family - Resident Sponsor Working In Private Sector or Free Zone - From Same Emirate', '	طلبات الإقامة - نقل كفالة - نقل زائد إقامة - أسرة - كفيل مقيم يعمل في القطاع الخاص أو منطقة حرة - من نفس الإمارة	', 'each', 'D', '4011', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '456.75', '1111', '3.15', '0', '0', '0', '0', '0', '0', '0'),
('521', '6', '8', '1', 'Residence - Sponsorship Transfer - Transfer Plus Residence - Investor or Partner? - From Same Emirate', '	طلبات الإقامة - نقل كفالة - نقل زائد إقامة - مستثمر أو شريك - من نفس الإمارة	', 'each', 'D', '4011', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '1322.5', '1111', '3.15', '0', '0', '0', '0', '0', '0', '0'),
('522', '6', '8', '1', 'Residence - Sponsorship Transfer - Transfer Plus Residence - Work - Private Sector or Free Zone - From Same Emirate', '	طلبات الإقامة - نقل كفالة - نقل زائد إقامة - عمل - القطاع الخاص أو منطقة حرة - من نفس الإمارة	', 'each', 'D', '4011', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '1172.5', '1111', '3.15', '0', '0', '0', '0', '0', '0', '0'),
('523', '6', '9', '1', 'Residence - Transfer Residence To New Passport - Due To Passport Lost', '	طلبات الإقامة - نقل الإقامة إلى جواز سفر جديد - بسبب ضياع جواز السفر	', 'each', 'D', '4011', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '243.5', '1111', '3.15', '0', '0', '0', '0', '0', '0', '0'),
('524', '6', '9', '1', 'Residence - Transfer Residence To New Passport - From Another Passport', '	طلبات الإقامة - نقل الإقامة إلى جواز سفر جديد - من جواز سفر آخر	', 'each', 'D', '4011', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '156.75', '1111', '3.15', '0', '0', '0', '0', '0', '0', '0'),
('525', '6', '10', '1', 'Services - Change Status - (Family)', '	خدمات - تعديل وضع - إتحادي	', 'each', 'D', '4011', '5010', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0000-00-00', '0000-00-00', '', '0', '535.75', '1111', '3.15', '0', '0', '0', '0', '0', '0', '0'),
('526', '6', '10', '1', 'Services - Change Status -(Company)', '	خدمات - تعديل وضع - محلي	', 'each', 'D', '4011', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '572.5', '1111', '3.15', '0', '0', '0', '0', '0', '0', '0'),
('527', '6', '10', '1', 'Services - FIS - Temporary Closure - Absconding ', '	خدمات - المتابعة والتحقيق - إغلاق مؤقت - بلاغ هروب	', 'each', 'D', '4011', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '393.5', '1111', '3.15', '0', '0', '0', '0', '0', '0', '0'),
('528', '6', '10', '1', 'Services - FIS - Temporary Closure - Absconding ', '	خدمات - المتابعة والتحقيق - إغلاق مؤقت - بلاغ هروب	', 'each', 'D', '4011', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '393.5', '1111', '3.15', '0', '0', '0', '0', '0', '0', '0'),
('529', '6', '10', '1', 'Services - Work Permit - Work - MOL (Inside)', '	خدمات - تصريح عمل - عمل - وزارة العمل	', 'each', 'D', '4011', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '1092.5', '1111', '3.15', '0', '0', '0', '0', '0', '0', '0'),
('530', '6', '10', '1', 'Services - Work Permit - Work - MOL (Outside)', '	خدمات - تصريح عمل - عمل - وزارة العمل	', 'each', 'D', '4011', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '422.5', '1111', '3.15', '0', '0', '0', '0', '0', '0', '0'),
('6000', '8', '0', '1', 'LETTER TYPING', '', 'each', 'D', '4015', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '0', '11121', '0', '0', '0', '0', '0', '0', '0', '0'),
('701', '8', '0', '1', 'PRO SERVICES', '', 'each', 'D', '4016', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '0', '11121', '0', '0', '0', '0', '0', '0', '0', '0'),
('801', '5', '0', '1', '24 HOURS MEDICAL SERVANT', '(فحص طبي مستعجل خادم (24 ساعة', 'each', 'D', '4013', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '3.16', '530', '1112', '0', '0', '0', '0', '0', '0', '0', '0'),
('803', '5', '0', '1', '48 HOURS MEDICAL', ' (فحص طبي مستعجل (48ساعة', 'each', 'D', '4013', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '3.16', '380', '1112', '0', '0', '0', '0', '0', '0', '0', '0'),
('804', '5', '0', '1', '48 HOURS MEDICAL SERVANT', '(فحص طبي مستعجل خادم  (48 ساعة )', 'each', 'D', '4013', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '3.16', '430', '1112', '0', '0', '0', '0', '0', '0', '0', '0'),
('805', '5', '0', '1', 'MEDICAL VIP', 'فحص طبى مستعجل كبار الشخضيات ', 'each', 'D', '4013', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '3.16', '700', '1112', '0', '0', '0', '0', '0', '0', '0', '0'),
('806', '5', '0', '1', 'NORMAL MEDICAL', 'فحص طبى عادى ', 'each', 'D', '4013', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '3.16', '270', '1112', '0', '0', '0', '0', '0', '0', '0', '0'),
('807', '5', '0', '1', 'NORMAL MEDICAL SERVANT', 'فحص طبى عادى خادم ', 'each', 'D', '4013', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '3.16', '320', '1112', '0', '0', '0', '0', '0', '0', '0', '0'),
('CBD', '7', '0', '1', 'Long Term Visit', '', 'each', 'D', '4012', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '0', '1112', '0', '0', '0', '0', '0', '0', '0', '0'),
('ED2', '7', '0', '1', 'EMIRATES ID FORM/ TWOYEARS', '', 'each', 'D', '4012', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '2.63', '240', '1112', '0', '0', '0', '0', '0', '0', '0', '0'),
('EID1', '7', '0', '1', 'EMIRATES ID FORM/ ONE YEAR', 'طلب الهوية الإماراتية/ سنة واحدة', 'each', 'D', '4012', '5010', '1510', '5040', '1530', '0', '0', '500', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '2.63', '140', '1112', '0', '0', '0', '0', '0', '0', '0', '0'),
('EID10', '7', '0', '1', 'EMIRATES ID FOR CITIZEN FOR 10 YEARS', 'طلب الهوية للمواطنين /10 سنوات\r\n', 'each', 'D', '4012', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '2.63', '240', '1112', '0', '0', '0', '0', '0', '0', '0', '0'),
('EID123', '7', '0', '1', 'EMIRATES ID FINE REMOVAL REQUEST', '', 'each', 'D', '4012', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '0', '1112', '0', '0', '0', '0', '0', '0', '0', '0'),
('EID2', '7', '0', '1', 'EMIRATES ID FORM/ TWO YEARS', 'طلب الهوية الإماراتية/ سنة واحدة', 'each', 'D', '4012', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '2.63', '240', '1112', '0', '0', '0', '0', '0', '0', '0', '0'),
('EID3', '7', '0', '1', 'EMIRATES ID FORM/ THREE YEARS', 'طلب الهوية الإماراتية/ 3 سنوات', 'each', 'D', '4012', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '2.63', '340', '1112', '0', '0', '0', '0', '0', '0', '0', '0'),
('EID5', '7', '0', '1', 'EMIRATES ID FORM/ FIVE YEARS (GCC)', 'طلب الهوية الإماراتية/ 5 سنوات', 'each', 'D', '4012', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '2.63', '140', '1112', '0', '0', '0', '0', '0', '0', '0', '0'),
('EIDFINE', '8', '0', '1', 'FINE IN EMIRATES ID', 'الغرامة-الهوية', 'each', 'D', '4012', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '0', '1112', '0', '0', '0', '0', '0', '0', '0', '0'),
('EIDRE', '7', '0', '1', 'EID RESCANNING', 'مسح نواقص للهوية', 'each', 'D', '4012', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '0', '0', '1112', '0', '0', '0', '0', '0', '0', '0', '0'),
('EIDREPL', '7', '0', '1', 'EMIRATES ID FORM/ REPLACEMENT', 'طلب الهوية الإماراتية/ استبدال', 'each', 'D', '4012', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '2.63', '340', '1112', '0', '0', '0', '0', '0', '0', '0', '0'),
('ET24', '5', '0', '1', 'MEDICALTOPUP  ( NORMAL TO 24 HRS )', '', 'each', 'D', '4012', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '3.16', '210', '1112', '0', '0', '0', '0', '0', '0', '0', '0'),
('M24', '5', '0', '1', '24 HOURS MEDICAL', '24 HOURS MEDICAL', 'each', 'D', '4013', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '3.16', '480', '1112', '0', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `0_stock_master` VALUES
('MT1', '5', '0', '1', 'MEDICALTOPUP  ( NORMAL TO 48 HRS )', '', 'each', 'D', '4013', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '3.16', '110', '1112', '0', '0', '110', '0', '0', '0', '0', '0'),
('MV', '5', '0', '1', 'MEDICALTOPUP  ( NORMAL TO VIP  )', '', 'each', 'D', '4013', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '3.16', '430', '1112', '0', '0', '0', '0', '0', '0', '0', '0'),
('TOPUP', '7', '0', '1', 'EMIRATES ID FEE TOP UP', 'رسوم إضافي لطلب الهوية\r\n', 'each', 'D', '4012', '5010', '1510', '5040', '1530', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0000-00-00', '0000-00-00', '', '2.63', '0', '1112', '0', '0', '0', '0', '0', '0', '0', '0');

### Structure of table `0_stock_moves` ###

DROP TABLE IF EXISTS `0_stock_moves`;

CREATE TABLE `0_stock_moves` (
  `trans_id` int(11) NOT NULL AUTO_INCREMENT,
  `trans_no` int(11) NOT NULL DEFAULT '0',
  `stock_id` char(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `type` smallint(6) NOT NULL DEFAULT '0',
  `loc_code` char(5) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `tran_date` date NOT NULL DEFAULT '0000-00-00',
  `price` double NOT NULL DEFAULT '0',
  `reference` char(40) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `qty` double NOT NULL DEFAULT '1',
  `standard_cost` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`trans_id`),
  KEY `type` (`type`,`trans_no`),
  KEY `Move` (`stock_id`,`loc_code`,`tran_date`)
) ENGINE=InnoDB AUTO_INCREMENT=330 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

### Data of table `0_stock_moves` ###

INSERT INTO `0_stock_moves` VALUES
('323', '1', '108', '13', 'DEF', '2018-10-17', '80', 'auto', '-1', '0'),
('324', '2', 'EIDRE', '13', 'DEF', '2018-10-17', '10', 'auto', '-1', '0'),
('326', '4', '102', '13', 'DEF', '2018-10-17', '36.989', 'auto', '-1', '0'),
('327', '5', '105', '13', 'DEF', '2018-10-17', '70', 'auto', '-1', '0'),
('328', '6', 'EID5', '13', 'DEF', '2018-10-17', '30', 'auto', '-1', '0'),
('329', '7', '11083', '13', 'DEF', '2018-10-18', '80', 'auto', '-1', '0');

### Structure of table `0_supp_allocations` ###

DROP TABLE IF EXISTS `0_supp_allocations`;

CREATE TABLE `0_supp_allocations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `person_id` int(11) DEFAULT NULL,
  `amt` double unsigned DEFAULT NULL,
  `date_alloc` date NOT NULL DEFAULT '0000-00-00',
  `trans_no_from` int(11) DEFAULT NULL,
  `trans_type_from` int(11) DEFAULT NULL,
  `trans_no_to` int(11) DEFAULT NULL,
  `trans_type_to` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `trans_type_from` (`person_id`,`trans_type_from`,`trans_no_from`,`trans_type_to`,`trans_no_to`),
  KEY `From` (`trans_type_from`,`trans_no_from`),
  KEY `To` (`trans_type_to`,`trans_no_to`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

### Data of table `0_supp_allocations` ###


### Structure of table `0_supp_invoice_items` ###

DROP TABLE IF EXISTS `0_supp_invoice_items`;

CREATE TABLE `0_supp_invoice_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `supp_trans_no` int(11) DEFAULT NULL,
  `supp_trans_type` int(11) DEFAULT NULL,
  `gl_code` varchar(15) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `grn_item_id` int(11) DEFAULT NULL,
  `po_detail_item_id` int(11) DEFAULT NULL,
  `stock_id` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `description` tinytext COLLATE utf8_unicode_ci,
  `quantity` double NOT NULL DEFAULT '0',
  `unit_price` double NOT NULL DEFAULT '0',
  `unit_tax` double NOT NULL DEFAULT '0',
  `memo_` tinytext COLLATE utf8_unicode_ci,
  `dimension_id` int(11) NOT NULL DEFAULT '0',
  `dimension2_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `Transaction` (`supp_trans_type`,`supp_trans_no`,`stock_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

### Data of table `0_supp_invoice_items` ###


### Structure of table `0_supp_trans` ###

DROP TABLE IF EXISTS `0_supp_trans`;

CREATE TABLE `0_supp_trans` (
  `trans_no` int(11) unsigned NOT NULL DEFAULT '0',
  `type` smallint(6) unsigned NOT NULL DEFAULT '0',
  `supplier_id` int(11) unsigned NOT NULL,
  `reference` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `supp_reference` varchar(60) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `tran_date` date NOT NULL DEFAULT '0000-00-00',
  `due_date` date NOT NULL DEFAULT '0000-00-00',
  `ov_amount` double NOT NULL DEFAULT '0',
  `ov_discount` double NOT NULL DEFAULT '0',
  `ov_gst` double NOT NULL DEFAULT '0',
  `rate` double NOT NULL DEFAULT '1',
  `alloc` double NOT NULL DEFAULT '0',
  `tax_included` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`type`,`trans_no`,`supplier_id`),
  KEY `supplier_id` (`supplier_id`),
  KEY `tran_date` (`tran_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

### Data of table `0_supp_trans` ###


### Structure of table `0_suppliers` ###

DROP TABLE IF EXISTS `0_suppliers`;

CREATE TABLE `0_suppliers` (
  `supplier_id` int(11) NOT NULL AUTO_INCREMENT,
  `supp_name` varchar(60) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `supp_ref` varchar(30) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `address` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `supp_address` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `gst_no` varchar(25) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `contact` varchar(60) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `supp_account_no` varchar(40) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `website` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `bank_account` varchar(60) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `curr_code` char(3) COLLATE utf8_unicode_ci DEFAULT NULL,
  `payment_terms` int(11) DEFAULT NULL,
  `tax_included` tinyint(1) NOT NULL DEFAULT '0',
  `dimension_id` int(11) DEFAULT '0',
  `dimension2_id` int(11) DEFAULT '0',
  `tax_group_id` int(11) DEFAULT NULL,
  `credit_limit` double NOT NULL DEFAULT '0',
  `purchase_account` varchar(15) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `payable_account` varchar(15) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `payment_discount_account` varchar(15) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `notes` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `inactive` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`supplier_id`),
  KEY `supp_ref` (`supp_ref`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

### Data of table `0_suppliers` ###


### Structure of table `0_sys_prefs` ###

DROP TABLE IF EXISTS `0_sys_prefs`;

CREATE TABLE `0_sys_prefs` (
  `name` varchar(35) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `category` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
  `type` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `length` smallint(6) DEFAULT NULL,
  `value` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`name`),
  KEY `category` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

### Data of table `0_sys_prefs` ###

INSERT INTO `0_sys_prefs` VALUES
('accounts_alpha', 'glsetup.general', 'tinyint', '1', '0'),
('accumulate_shipping', 'glsetup.customer', 'tinyint', '1', ''),
('add_pct', 'setup.company', 'int', '5', '-1'),
('allow_negative_prices', 'glsetup.inventory', 'tinyint', '1', '1'),
('allow_negative_stock', 'glsetup.inventory', 'tinyint', '1', ''),
('alternative_tax_include_on_docs', 'setup.company', 'tinyint', '1', ''),
('auto_curr_reval', 'setup.company', 'smallint', '6', '1'),
('bank_charge_act', 'glsetup.general', 'varchar', '15', '5690'),
('barcodes_on_stock', 'setup.company', 'tinyint', '1', '1'),
('base_sales', 'setup.company', 'int', '11', '1'),
('bcc_email', 'setup.company', 'varchar', '100', ''),
('company_logo_report', 'setup.company', 'tinyint', '1', ''),
('coy_logo', 'setup.company', 'varchar', '100', 'header-top.jpg'),
('coy_name', 'setup.company', 'varchar', '60', 'EPIC CENTER GOVERNMENT TRANSACTIONS'),
('coy_no', 'setup.company', 'varchar', '25', ''),
('creditors_act', 'glsetup.purchase', 'varchar', '15', '2100'),
('curr_default', 'setup.company', 'char', '3', 'AED'),
('debtors_act', 'glsetup.sales', 'varchar', '15', '1200'),
('default_adj_act', 'glsetup.items', 'varchar', '15', '5040'),
('default_cogs_act', 'glsetup.items', 'varchar', '15', '5010'),
('default_credit_limit', 'glsetup.customer', 'int', '11', '1000'),
('default_delivery_required', 'glsetup.sales', 'smallint', '6', '1'),
('default_dim_required', 'glsetup.dims', 'int', '11', '20'),
('default_inv_sales_act', 'glsetup.items', 'varchar', '15', '4010'),
('default_inventory_act', 'glsetup.items', 'varchar', '15', '1510'),
('default_loss_on_asset_disposal_act', 'glsetup.items', 'varchar', '15', '5660'),
('default_prompt_payment_act', 'glsetup.sales', 'varchar', '15', '4500'),
('default_quote_valid_days', 'glsetup.sales', 'smallint', '6', '30'),
('default_receival_required', 'glsetup.purchase', 'smallint', '6', '10'),
('default_sales_act', 'glsetup.sales', 'varchar', '15', '4010'),
('default_sales_discount_act', 'glsetup.sales', 'varchar', '15', '4510'),
('default_wip_act', 'glsetup.items', 'varchar', '15', '1530'),
('default_workorder_required', 'glsetup.manuf', 'int', '11', '20'),
('deferred_income_act', 'glsetup.sales', 'varchar', '15', '2105'),
('depreciation_period', 'glsetup.company', 'tinyint', '1', '1'),
('domicile', 'setup.company', 'varchar', '55', ''),
('email', 'setup.company', 'varchar', '100', ''),
('exchange_diff_act', 'glsetup.general', 'varchar', '15', '4450'),
('f_year', 'setup.company', 'int', '11', '3'),
('fax', 'setup.company', 'varchar', '30', ''),
('freight_act', 'glsetup.customer', 'varchar', '15', '4430'),
('gl_closing_date', 'setup.closing_date', 'date', '8', '2016-12-31'),
('grn_clearing_act', 'glsetup.purchase', 'varchar', '15', '1550'),
('gst_no', 'setup.company', 'varchar', '25', ''),
('legal_text', 'glsetup.customer', 'tinytext', '0', ''),
('loc_notification', 'glsetup.inventory', 'tinyint', '1', ''),
('login_tout', 'setup.company', 'smallint', '6', '43200'),
('no_customer_list', 'setup.company', 'tinyint', '1', ''),
('no_item_list', 'setup.company', 'tinyint', '1', ''),
('no_supplier_list', 'setup.company', 'tinyint', '1', ''),
('no_zero_lines_amount', 'glsetup.sales', 'tinyint', '1', '1'),
('past_due_days', 'glsetup.general', 'int', '11', '30'),
('payroll_deductleave_act', NULL, 'int', NULL, '5410'),
('payroll_month_work_days', NULL, 'float', NULL, '30'),
('payroll_overtime_act', NULL, 'int', NULL, '5420'),
('payroll_payable_act', NULL, 'int', NULL, '2100'),
('payroll_work_hours', NULL, 'float', NULL, '10'),
('phone', 'setup.company', 'varchar', '30', ''),
('po_over_charge', 'glsetup.purchase', 'int', '11', '10'),
('po_over_receive', 'glsetup.purchase', 'int', '11', '10'),
('postal_address', 'setup.company', 'tinytext', '0', 'Dubai'),
('print_invoice_no', 'glsetup.sales', 'tinyint', '1', '0'),
('print_item_images_on_quote', 'glsetup.inventory', 'tinyint', '1', ''),
('profit_loss_year_act', 'glsetup.general', 'varchar', '15', '9990'),
('pyt_discount_act', 'glsetup.purchase', 'varchar', '15', '5060'),
('retained_earnings_act', 'glsetup.general', 'varchar', '15', '3590'),
('round_to', 'setup.company', 'int', '5', '1'),
('shortname_name_in_list', 'setup.company', 'tinyint', '1', ''),
('show_po_item_codes', 'glsetup.purchase', 'tinyint', '1', ''),
('suppress_tax_rates', 'setup.company', 'tinyint', '1', ''),
('tax_algorithm', 'glsetup.customer', 'tinyint', '1', '1'),
('tax_last', 'setup.company', 'int', '11', '1'),
('tax_prd', 'setup.company', 'int', '11', '1'),
('time_zone', 'setup.company', 'tinyint', '1', ''),
('use_dimension', 'setup.company', 'tinyint', '1', '0'),
('use_fixed_assets', 'setup.company', 'tinyint', '1', '1'),
('use_manufacturing', 'setup.company', 'tinyint', '1', '1'),
('version_id', 'system', 'varchar', '11', '2.4.1');

### Structure of table `0_tag_associations` ###

DROP TABLE IF EXISTS `0_tag_associations`;

CREATE TABLE `0_tag_associations` (
  `record_id` varchar(15) COLLATE utf8_unicode_ci NOT NULL,
  `tag_id` int(11) NOT NULL,
  UNIQUE KEY `record_id` (`record_id`,`tag_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

### Data of table `0_tag_associations` ###


### Structure of table `0_tags` ###

DROP TABLE IF EXISTS `0_tags`;

CREATE TABLE `0_tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` smallint(6) NOT NULL,
  `name` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(60) COLLATE utf8_unicode_ci DEFAULT NULL,
  `inactive` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `type` (`type`,`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

### Data of table `0_tags` ###


### Structure of table `0_tax_group_items` ###

DROP TABLE IF EXISTS `0_tax_group_items`;

CREATE TABLE `0_tax_group_items` (
  `tax_group_id` int(11) NOT NULL DEFAULT '0',
  `tax_type_id` int(11) NOT NULL DEFAULT '0',
  `tax_shipping` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`tax_group_id`,`tax_type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

### Data of table `0_tax_group_items` ###

INSERT INTO `0_tax_group_items` VALUES
('1', '1', '1');

### Structure of table `0_tax_groups` ###

DROP TABLE IF EXISTS `0_tax_groups`;

CREATE TABLE `0_tax_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(60) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `inactive` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

### Data of table `0_tax_groups` ###

INSERT INTO `0_tax_groups` VALUES
('1', 'Tax', '0'),
('2', 'Tax Exempt', '0');

### Structure of table `0_tax_types` ###

DROP TABLE IF EXISTS `0_tax_types`;

CREATE TABLE `0_tax_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rate` double NOT NULL DEFAULT '0',
  `sales_gl_code` varchar(15) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `purchasing_gl_code` varchar(15) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `name` varchar(60) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `inactive` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

### Data of table `0_tax_types` ###

INSERT INTO `0_tax_types` VALUES
('1', '5', '2150', '2150', 'Tax', '0');

### Structure of table `0_trans_tax_details` ###

DROP TABLE IF EXISTS `0_trans_tax_details`;

CREATE TABLE `0_trans_tax_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `trans_type` smallint(6) DEFAULT NULL,
  `trans_no` int(11) DEFAULT NULL,
  `tran_date` date NOT NULL,
  `tax_type_id` int(11) NOT NULL DEFAULT '0',
  `rate` double NOT NULL DEFAULT '0',
  `ex_rate` double NOT NULL DEFAULT '1',
  `included_in_price` tinyint(1) NOT NULL DEFAULT '0',
  `net_amount` double NOT NULL DEFAULT '0',
  `amount` double NOT NULL DEFAULT '0',
  `memo` tinytext COLLATE utf8_unicode_ci,
  `reg_type` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `Type_and_Number` (`trans_type`,`trans_no`),
  KEY `tran_date` (`tran_date`)
) ENGINE=InnoDB AUTO_INCREMENT=398 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

### Data of table `0_trans_tax_details` ###

INSERT INTO `0_trans_tax_details` VALUES
('382', '13', '1', '2018-10-17', '1', '5', '1', '0', '80', '4', 'auto', NULL),
('383', '10', '1', '2018-10-17', '1', '5', '1', '0', '0', '0', '000001', '0'),
('384', '13', '2', '2018-10-17', '1', '5', '1', '0', '10', '0.5', 'auto', NULL),
('385', '10', '2', '2018-10-17', '1', '5', '1', '0', '0', '0', '000002', '0'),
('386', '13', '3', '2018-10-17', '1', '5', '1', '0', '0', '0', 'auto', NULL),
('387', '10', '3', '2018-10-17', '1', '5', '1', '0', '0', '0', '000003', '0'),
('388', '13', '4', '2018-10-17', '1', '5', '1', '0', '47', '2.35', 'auto', NULL),
('389', '10', '4', '2018-10-17', '1', '5', '1', '0', '47', '2.35', '000004', '0'),
('390', '13', '5', '2018-10-17', '1', '5', '1', '0', '80', '4', 'auto', NULL),
('391', '10', '5', '2018-10-17', '1', '5', '1', '0', '80', '4', '000005', '0'),
('392', '13', '6', '2018-10-17', '1', '5', '1', '0', '30', '1.5', 'auto', NULL),
('393', '10', '6', '2018-10-17', '1', '5', '1', '0', '30', '1.5', '000003', '0'),
('394', '10', '1', '2018-10-17', '1', '5', '1', '0', '80', '4', '000001', '0'),
('395', '10', '2', '2018-10-17', '1', '5', '1', '0', '10', '0.5', '000002', '0'),
('396', '13', '7', '2018-10-18', '0', '0', '1', '0', '80', '0', 'auto', NULL),
('397', '10', '7', '2018-10-18', '0', '0', '1', '0', '80', '0', '000006', '0');

### Structure of table `0_useronline` ###

DROP TABLE IF EXISTS `0_useronline`;

CREATE TABLE `0_useronline` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `timestamp` int(15) NOT NULL DEFAULT '0',
  `ip` varchar(40) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `file` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `timestamp` (`timestamp`),
  KEY `ip` (`ip`)
) ENGINE=InnoDB AUTO_INCREMENT=10978 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

### Data of table `0_useronline` ###

INSERT INTO `0_useronline` VALUES
('10961', '1539863821', '::1', '/project/axispro/v1/admin/fiscalyears.php'),
('10962', '1539863856', '::1', '/project/axispro/v1/index.php'),
('10963', '1539863860', '::1', '/project/axispro/v1/admin/company_preferences.php'),
('10964', '1539863866', '::1', '/project/axispro/v1/admin/company_preferences.php'),
('10965', '1539863867', '::1', '/project/axispro/v1/index.php'),
('10966', '1539863875', '::1', '/project/axispro/v1/admin/fiscalyears.php'),
('10967', '1539863885', '::1', '/project/axispro/v1/admin/fiscalyears.php'),
('10968', '1539863898', '::1', '/project/axispro/v1/admin/fiscalyears.php'),
('10969', '1539863905', '::1', '/project/axispro/v1/index.php'),
('10970', '1539863908', '::1', '/project/axispro/v1/admin/company_preferences.php'),
('10971', '1539863912', '::1', '/project/axispro/v1/admin/company_preferences.php'),
('10972', '1539863914', '::1', '/project/axispro/v1/index.php'),
('10973', '1539863918', '::1', '/project/axispro/v1/admin/fiscalyears.php'),
('10974', '1539863921', '::1', '/project/axispro/v1/admin/fiscalyears.php'),
('10975', '1539863929', '::1', '/project/axispro/v1/admin/fiscalyears.php'),
('10976', '1539863932', '::1', '/project/axispro/v1/admin/fiscalyears.php'),
('10977', '1539863934', '::1', '/project/axispro/v1/admin/fiscalyears.php');

### Structure of table `0_users` ###

DROP TABLE IF EXISTS `0_users`;

CREATE TABLE `0_users` (
  `id` smallint(6) NOT NULL AUTO_INCREMENT,
  `user_id` varchar(60) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `employee_id` int(11) NOT NULL,
  `password` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `real_name` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `role_id` int(11) NOT NULL DEFAULT '1',
  `phone` varchar(30) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `email` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `language` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `date_format` tinyint(1) NOT NULL DEFAULT '0',
  `date_sep` tinyint(1) NOT NULL DEFAULT '0',
  `tho_sep` tinyint(1) NOT NULL DEFAULT '0',
  `dec_sep` tinyint(1) NOT NULL DEFAULT '0',
  `theme` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'default',
  `page_size` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'A4',
  `prices_dec` smallint(6) NOT NULL DEFAULT '2',
  `qty_dec` smallint(6) NOT NULL DEFAULT '2',
  `rates_dec` smallint(6) NOT NULL DEFAULT '4',
  `percent_dec` smallint(6) NOT NULL DEFAULT '1',
  `show_gl` tinyint(1) NOT NULL DEFAULT '1',
  `show_codes` tinyint(1) NOT NULL DEFAULT '0',
  `show_hints` tinyint(1) NOT NULL DEFAULT '0',
  `last_visit_date` datetime DEFAULT NULL,
  `query_size` tinyint(1) unsigned NOT NULL DEFAULT '10',
  `graphic_links` tinyint(1) DEFAULT '1',
  `pos` smallint(6) DEFAULT '1',
  `print_profile` varchar(30) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `rep_popup` tinyint(1) DEFAULT '1',
  `sticky_doc_date` tinyint(1) DEFAULT '0',
  `startup_tab` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `transaction_days` smallint(6) NOT NULL DEFAULT '30',
  `save_report_selections` smallint(6) NOT NULL DEFAULT '0',
  `use_date_picker` tinyint(1) NOT NULL DEFAULT '1',
  `def_print_destination` tinyint(1) NOT NULL DEFAULT '0',
  `def_print_orientation` tinyint(1) NOT NULL DEFAULT '0',
  `inactive` tinyint(1) NOT NULL DEFAULT '0',
  `is_local` int(11) DEFAULT '0' COMMENT '1- Local UAE Nationality employee, 0 - Non Local Employee',
  `cashier_account` varchar(50) COLLATE utf8_unicode_ci DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=85 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

### Data of table `0_users` ###

INSERT INTO `0_users` VALUES
('1', 'admin', '0', '21232f297a57a5a743894a0e4a801fc3', 'Administrator', '2', '', 'adm@example.com', 'C', '4', '2', '0', '0', 'daxis', 'Letter', '2', '2', '4', '1', '1', '1', '1', '2018-10-18 11:48:50', '10', '1', '1', '', '1', '0', 'orders', '30', '0', '1', '0', '0', '0', '1', '5'),
('65', 'irshad', '0', '81dc9bdb52d04dc20036dbd8313ed055', 'IRSHAD', '2', '', NULL, 'C', '4', '2', '0', '0', 'daxis', 'Letter', '2', '2', '4', '1', '1', '1', '1', '2018-10-13 10:06:09', '10', '1', '1', '', '1', '0', 'orders', '30', '0', '1', '0', '0', '0', '0', '7'),
('66', 'hamda', '0', 'd41d8cd98f00b204e9800998ecf8427e', 'HAMDA HUSAIN', '3', '', NULL, 'C', '4', '2', '0', '0', 'daxis', 'Letter', '2', '2', '4', '1', '1', '1', '1', '2018-09-30 17:23:03', '10', '1', '1', '', '1', '0', 'orders', '30', '0', '1', '0', '0', '0', '1', '7'),
('67', 'SHAIKHA', '0', 'd41d8cd98f00b204e9800998ecf8427e', 'SHAIKHA', '3', '', NULL, 'C', '4', '2', '0', '0', 'daxis', 'Letter', '2', '2', '4', '1', '1', '1', '1', '2018-09-30 17:49:31', '10', '1', '1', '', '1', '0', 'orders', '30', '0', '1', '0', '0', '0', '1', '7'),
('68', 'AFRA', '0', 'd41d8cd98f00b204e9800998ecf8427e', 'AFRA', '3', '', NULL, 'C', '4', '2', '0', '0', 'daxis', 'Letter', '2', '2', '4', '1', '1', '1', '1', '2018-09-30 18:17:30', '10', '1', '1', '', '1', '0', 'orders', '30', '0', '1', '0', '0', '0', '1', '7'),
('69', 'AMNA', '0', 'd41d8cd98f00b204e9800998ecf8427e', 'AMNA ALI', '3', '', NULL, 'C', '4', '2', '0', '0', 'daxis', 'Letter', '2', '2', '4', '1', '1', '1', '1', NULL, '10', '1', '1', '', '1', '0', 'orders', '30', '0', '1', '0', '0', '0', '1', '7'),
('70', 'JIRSHAD', '0', 'd41d8cd98f00b204e9800998ecf8427e', 'JIRSHAD', '3', '', NULL, 'C', '4', '2', '0', '0', 'daxis', 'Letter', '2', '2', '4', '1', '1', '1', '1', NULL, '10', '1', '1', '', '1', '0', 'orders', '30', '0', '1', '0', '0', '0', '0', '7'),
('71', 'MIRA', '0', 'd41d8cd98f00b204e9800998ecf8427e', 'MIRA', '3', '', NULL, 'C', '4', '2', '0', '0', 'daxis', 'Letter', '2', '2', '4', '1', '1', '1', '1', NULL, '10', '1', '1', '', '1', '0', 'orders', '30', '0', '1', '0', '0', '0', '1', '7'),
('72', 'AMAL', '0', 'd41d8cd98f00b204e9800998ecf8427e', 'AMAL', '3', '', NULL, 'C', '4', '2', '0', '0', 'daxis', 'Letter', '2', '2', '4', '1', '1', '1', '1', NULL, '10', '1', '1', '', '1', '0', 'orders', '30', '0', '1', '0', '0', '0', '1', '7'),
('73', 'BADRIA', '0', 'd41d8cd98f00b204e9800998ecf8427e', 'BADRIA', '3', '', NULL, 'C', '4', '2', '0', '0', 'daxis', 'Letter', '2', '2', '4', '1', '1', '1', '1', NULL, '10', '1', '1', '', '1', '0', 'orders', '30', '0', '1', '0', '0', '0', '1', '7'),
('74', 'samshuddeen', '0', 'e6e02ec44411688a5769a9f516d61489', 'SAMSHUDDEEN', '2', '', NULL, 'C', '4', '2', '0', '0', 'daxis', 'Letter', '2', '2', '4', '1', '1', '1', '1', '2018-10-13 10:10:34', '10', '1', '1', '', '1', '0', 'orders', '30', '0', '1', '0', '0', '0', '0', '7'),
('75', 'ALISAEED', '0', 'd41d8cd98f00b204e9800998ecf8427e', '', '3', '', NULL, 'C', '4', '2', '0', '0', 'daxis', 'Letter', '2', '2', '4', '1', '1', '1', '1', '2018-09-30 18:55:52', '10', '1', '1', '', '1', '0', 'orders', '30', '0', '1', '0', '0', '0', '0', '7'),
('76', 'NOUFAL', '0', 'd41d8cd98f00b204e9800998ecf8427e', 'NOUFAL', '3', '', NULL, 'C', '4', '2', '0', '0', 'daxis', 'Letter', '2', '2', '4', '1', '1', '1', '1', '2018-10-16 15:39:04', '10', '1', '1', '', '1', '0', 'orders', '30', '0', '1', '0', '0', '0', '0', '7'),
('77', 'YASEEN', '0', 'd41d8cd98f00b204e9800998ecf8427e', 'MOHAMMED YASEEN', '3', '', NULL, 'C', '4', '2', '0', '0', 'daxis', 'Letter', '2', '2', '4', '1', '1', '1', '1', NULL, '10', '1', '1', '', '1', '0', 'orders', '30', '0', '1', '0', '0', '0', '0', '7'),
('78', 'SATHAR', '0', 'd41d8cd98f00b204e9800998ecf8427e', 'SATHAR NALAKATH', '3', '', NULL, 'C', '4', '2', '0', '0', 'daxis', 'Letter', '2', '2', '4', '1', '1', '1', '1', '2018-10-01 09:40:19', '10', '1', '1', '', '1', '0', 'orders', '30', '0', '1', '0', '0', '0', '0', '7'),
('79', 'SAMAD', '0', 'd41d8cd98f00b204e9800998ecf8427e', 'NOUFAL SAMAD', '3', '', NULL, 'C', '4', '2', '0', '0', 'daxis', 'Letter', '2', '2', '4', '1', '1', '1', '1', NULL, '10', '1', '1', '', '1', '0', 'orders', '30', '0', '1', '0', '0', '0', '0', '7'),
('80', 'BASITH', '0', 'd41d8cd98f00b204e9800998ecf8427e', 'BASITH V C', '3', '', NULL, 'C', '4', '2', '0', '0', 'daxis', 'Letter', '2', '2', '4', '1', '1', '1', '1', NULL, '10', '1', '1', '', '1', '0', 'orders', '30', '0', '1', '0', '0', '0', '0', '7'),
('81', 'MAHIR', '0', 'd41d8cd98f00b204e9800998ecf8427e', 'MAMMU MAHIR', '3', '', NULL, 'C', '4', '2', '0', '0', 'daxis', 'Letter', '2', '2', '4', '1', '1', '1', '1', NULL, '10', '1', '1', '', '1', '0', 'orders', '30', '0', '1', '0', '0', '0', '0', '7'),
('82', 'SHAFEEQ', '0', 'd41d8cd98f00b204e9800998ecf8427e', 'SHAFEEQ M H', '3', '', NULL, 'C', '4', '2', '0', '0', 'daxis', 'Letter', '2', '2', '4', '1', '1', '1', '1', NULL, '10', '1', '1', '', '1', '0', 'orders', '30', '0', '1', '0', '0', '0', '0', '7'),
('83', 'AFSAL', '0', 'd41d8cd98f00b204e9800998ecf8427e', 'MUHAMMED AFSAL', '3', '', NULL, 'C', '4', '2', '0', '0', 'daxis', 'Letter', '2', '2', '4', '1', '1', '1', '1', NULL, '10', '1', '1', '', '1', '0', 'orders', '30', '0', '1', '0', '0', '0', '0', '7'),
('84', 'SAJIR', '0', 'd41d8cd98f00b204e9800998ecf8427e', 'SAJIR POYIL', '3', '', NULL, 'C', '4', '2', '0', '0', 'daxis', 'Letter', '2', '2', '4', '1', '1', '1', '1', NULL, '10', '1', '1', '', '1', '0', 'orders', '30', '0', '1', '0', '0', '0', '0', '7');

### Structure of table `0_voided` ###

DROP TABLE IF EXISTS `0_voided`;

CREATE TABLE `0_voided` (
  `type` int(11) NOT NULL DEFAULT '0',
  `id` int(11) NOT NULL DEFAULT '0',
  `date_` date NOT NULL DEFAULT '0000-00-00',
  `memo_` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `trans_date` date DEFAULT NULL,
  `amount` double DEFAULT NULL,
  `customer` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `transaction_created_by` int(11) DEFAULT NULL,
  UNIQUE KEY `id` (`type`,`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

### Data of table `0_voided` ###

INSERT INTO `0_voided` VALUES
('10', '3', '2018-10-17', 'EDITED_INVOICE', '2018-10-17', '314.13', NULL, '1', '1'),
('13', '3', '2018-10-17', '', '2018-10-17', '314.13', NULL, '1', '1');

### Structure of table `0_wo_costing` ###

DROP TABLE IF EXISTS `0_wo_costing`;

CREATE TABLE `0_wo_costing` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `workorder_id` int(11) NOT NULL DEFAULT '0',
  `cost_type` tinyint(1) NOT NULL DEFAULT '0',
  `trans_type` int(11) NOT NULL DEFAULT '0',
  `trans_no` int(11) NOT NULL DEFAULT '0',
  `factor` double NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

### Data of table `0_wo_costing` ###


### Structure of table `0_wo_issue_items` ###

DROP TABLE IF EXISTS `0_wo_issue_items`;

CREATE TABLE `0_wo_issue_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `stock_id` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  `issue_id` int(11) DEFAULT NULL,
  `qty_issued` double DEFAULT NULL,
  `unit_cost` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

### Data of table `0_wo_issue_items` ###


### Structure of table `0_wo_issues` ###

DROP TABLE IF EXISTS `0_wo_issues`;

CREATE TABLE `0_wo_issues` (
  `issue_no` int(11) NOT NULL AUTO_INCREMENT,
  `workorder_id` int(11) NOT NULL DEFAULT '0',
  `reference` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `issue_date` date DEFAULT NULL,
  `loc_code` varchar(5) COLLATE utf8_unicode_ci DEFAULT NULL,
  `workcentre_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`issue_no`),
  KEY `workorder_id` (`workorder_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

### Data of table `0_wo_issues` ###


### Structure of table `0_wo_manufacture` ###

DROP TABLE IF EXISTS `0_wo_manufacture`;

CREATE TABLE `0_wo_manufacture` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reference` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `workorder_id` int(11) NOT NULL DEFAULT '0',
  `quantity` double NOT NULL DEFAULT '0',
  `date_` date NOT NULL DEFAULT '0000-00-00',
  PRIMARY KEY (`id`),
  KEY `workorder_id` (`workorder_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

### Data of table `0_wo_manufacture` ###


### Structure of table `0_wo_requirements` ###

DROP TABLE IF EXISTS `0_wo_requirements`;

CREATE TABLE `0_wo_requirements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `workorder_id` int(11) NOT NULL DEFAULT '0',
  `stock_id` char(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `workcentre` int(11) NOT NULL DEFAULT '0',
  `units_req` double NOT NULL DEFAULT '1',
  `unit_cost` double NOT NULL DEFAULT '0',
  `loc_code` char(5) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `units_issued` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `workorder_id` (`workorder_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

### Data of table `0_wo_requirements` ###


### Structure of table `0_workcentres` ###

DROP TABLE IF EXISTS `0_workcentres`;

CREATE TABLE `0_workcentres` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` char(40) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `description` char(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `inactive` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

### Data of table `0_workcentres` ###

INSERT INTO `0_workcentres` VALUES
('1', 'Work Centre', '', '0');

### Structure of table `0_workorders` ###

DROP TABLE IF EXISTS `0_workorders`;

CREATE TABLE `0_workorders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `wo_ref` varchar(60) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `loc_code` varchar(5) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `units_reqd` double NOT NULL DEFAULT '1',
  `stock_id` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `date_` date NOT NULL DEFAULT '0000-00-00',
  `type` tinyint(4) NOT NULL DEFAULT '0',
  `required_by` date NOT NULL DEFAULT '0000-00-00',
  `released_date` date NOT NULL DEFAULT '0000-00-00',
  `units_issued` double NOT NULL DEFAULT '0',
  `closed` tinyint(1) NOT NULL DEFAULT '0',
  `released` tinyint(1) NOT NULL DEFAULT '0',
  `additional_costs` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `wo_ref` (`wo_ref`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

### Data of table `0_workorders` ###

INSERT INTO `0_workorders` VALUES
('1', '001/2017', 'DEF', '2', '201', '2017-05-05', '0', '2017-05-05', '2017-05-05', '2', '1', '1', '0'),
('2', '002/2017', 'DEF', '5', '201', '2017-05-07', '2', '2017-05-27', '2017-05-07', '0', '0', '1', '0'),
('3', '003/2017', 'DEF', '5', '201', '2017-05-07', '2', '2017-05-27', '0000-00-00', '0', '0', '0', '0');

### Structure of table `cust_list` ###

DROP TABLE IF EXISTS `cust_list`;

CREATE TABLE `cust_list` (
  `CustomerID` int(11) NOT NULL AUTO_INCREMENT,
  `CustName` varchar(60) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `BranchName` varchar(60) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `Contact` varchar(60) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `ShippingAddress` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `BillingAddress` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `phone` varchar(30) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `phone2` varchar(30) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `fax` varchar(30) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `email` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `sales_account` varchar(15) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `payment_terms` int(11) NOT NULL DEFAULT '1',
  `credit_limit` float NOT NULL DEFAULT '0',
  `TaxNum` varchar(55) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `TaxExempt` tinyint(1) NOT NULL DEFAULT '0',
  `Pricing` int(11) NOT NULL DEFAULT '1',
  `NeedPO` tinyint(1) NOT NULL DEFAULT '0',
  `IsMB` tinyint(1) NOT NULL DEFAULT '0',
  `debtor_no` int(11) DEFAULT NULL,
  `debtor_ref` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
  `branch_code` int(11) DEFAULT NULL,
  `branch_ref` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`CustomerID`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

### Data of table `cust_list` ###

INSERT INTO `cust_list` VALUES
('1', 'Customer 1', 'Customer 1\r', '', '', '', '', '', '', '', '', '0', '0', '', '0', '0', '0', '0', '151', NULL, '151', NULL),
('2', 'Customer 2', 'Customer 2\r', '', '', '', '', '', '', '', '', '0', '0', '', '0', '0', '0', '0', '152', NULL, '152', NULL),
('3', 'Customer 3', 'Customer 3\r', '', '', '', '', '', '', '', '', '0', '0', '', '0', '0', '0', '0', '153', NULL, '153', NULL),
('4', 'Customer 4', 'Customer 4\r', '', '', '', '', '', '', '', '', '0', '0', '', '0', '0', '0', '0', '154', NULL, '154', NULL);

### Structure of table `customer_discount_items` ###

DROP TABLE IF EXISTS `customer_discount_items`;

CREATE TABLE `customer_discount_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) DEFAULT NULL,
  `item_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `discount` double DEFAULT '0' COMMENT 'in percentage',
  `customer_commission` double DEFAULT '0',
  `reward_point` double DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

### Data of table `customer_discount_items` ###

INSERT INTO `customer_discount_items` VALUES
('12', '1', '7', '0', '0', '0'),
('13', '16', '4', '0', '0', '0'),
('14', '19', '6', '10', '0', '0');

### Structure of table `customer_rewards` ###

DROP TABLE IF EXISTS `customer_rewards`;

CREATE TABLE `customer_rewards` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `trans_no` int(11) DEFAULT '0',
  `trans_type` int(11) DEFAULT '0',
  `tran_date` date DEFAULT NULL,
  `stock_id` varchar(256) DEFAULT '0',
  `reward_type` int(11) DEFAULT '1' COMMENT '1 - in, 2 - Out',
  `customer_id` int(11) DEFAULT '0',
  `qty` int(11) DEFAULT '1',
  `conversion_rate` double DEFAULT '0',
  `reward_point` double DEFAULT '0',
  `reward_amount` double DEFAULT '0',
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=latin1 ;

### Data of table `customer_rewards` ###

INSERT INTO `customer_rewards` VALUES
('5', '10', '12', '2018-10-04', '0', '2', '1', '1', '0', '0', '0', '1', '2018-10-04 10:45:59'),
('6', '11', '12', '2018-10-04', '0', '2', '1', '1', '0', '0', '0', '1', '2018-10-04 10:47:28'),
('7', '12', '12', '2018-10-04', '0', '2', '1', '1', '0', '0', '3', '1', '2018-10-04 10:51:19'),
('8', '14', '12', '2018-10-04', '0', '2', '1', '1', '0', '0', '2.9', '1', '2018-10-04 10:54:28'),
('9', '15', '12', '2018-10-04', '0', '2', '1', '1', '0', '0', '3', '1', '2018-10-04 11:02:45');

### Structure of table `other_charges_master` ###

DROP TABLE IF EXISTS `other_charges_master`;

CREATE TABLE `other_charges_master` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `stock_id` varchar(256) DEFAULT NULL,
  `sales_type_id` int(11) DEFAULT NULL,
  `account_code` varchar(128) DEFAULT NULL,
  `description` varchar(256) DEFAULT NULL,
  `amount` double DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=270 DEFAULT CHARSET=latin1 ;

### Data of table `other_charges_master` ###

INSERT INTO `other_charges_master` VALUES
('146', '11001', '1', '4321', 'TASHEEL CHARGES', '0'),
('147', '11002', '1', '4321', 'TASHEEL CHARGES', '0'),
('148', '11003', '1', '4321', 'TASHEEL CHARGES', '9'),
('149', '11004', '1', '4321', 'TASHEEL CHARGES', '9'),
('150', '11005', '1', '4321', 'TASHEEL CHARGES', '9'),
('151', '11006', '1', '4321', 'TASHEEL CHARGES', '9'),
('152', '11007', '1', '4321', 'TASHEEL CHARGES', '9'),
('153', '11008', '1', '4321', 'TASHEEL CHARGES', '9'),
('154', '11009', '1', '4321', 'TASHEEL CHARGES', '9'),
('155', '11010', '1', '4321', 'TASHEEL CHARGES', '9'),
('156', '11011', '1', '4321', 'TASHEEL CHARGES', '9'),
('157', '11012', '1', '4321', 'TASHEEL CHARGES', '9'),
('158', '11013', '1', '4321', 'TASHEEL CHARGES', '9'),
('159', '11014', '1', '4321', 'TASHEEL CHARGES', '9'),
('160', '11015', '1', '4321', 'TASHEEL CHARGES', '9'),
('161', '11016', '1', '4321', 'TASHEEL CHARGES', '9'),
('162', '11017', '1', '4321', 'TASHEEL CHARGES', '9'),
('163', '11018', '1', '4321', 'TASHEEL CHARGES', '9'),
('164', '11019', '1', '4321', 'TASHEEL CHARGES', '9'),
('165', '11020', '1', '4321', 'TASHEEL CHARGES', '9'),
('166', '11021', '1', '4321', 'TASHEEL CHARGES', '9'),
('167', '11022', '1', '4321', 'TASHEEL CHARGES', '9'),
('168', '11023', '1', '4321', 'TASHEEL CHARGES', '9'),
('169', '11024', '1', '4321', 'TASHEEL CHARGES', '23'),
('170', '11025', '1', '4321', 'TASHEEL CHARGES', '23'),
('171', '11026', '1', '4321', 'TASHEEL CHARGES', '23'),
('172', '11027', '1', '4321', 'TASHEEL CHARGES', '23'),
('173', '11028', '1', '4321', 'TASHEEL CHARGES', '23'),
('174', '11029', '1', '4321', 'TASHEEL CHARGES', '23'),
('175', '11030', '1', '4321', 'TASHEEL CHARGES', '23'),
('176', '11031', '1', '4321', 'TASHEEL CHARGES', '23'),
('177', '11032', '1', '4321', 'TASHEEL CHARGES', '23'),
('178', '11033', '1', '4321', 'TASHEEL CHARGES', '23'),
('179', '11034', '1', '4321', 'TASHEEL CHARGES', '23'),
('180', '11035', '1', '4321', 'TASHEEL CHARGES', '23'),
('181', '11036', '1', '4321', 'TASHEEL CHARGES', '23'),
('182', '11037', '1', '4321', 'TASHEEL CHARGES', '23'),
('183', '11038', '1', '4321', 'TASHEEL CHARGES', '23'),
('184', '11039', '1', '4321', 'TASHEEL CHARGES', '23'),
('185', '11040', '1', '4321', 'TASHEEL CHARGES', '23'),
('186', '11041', '1', '4321', 'TASHEEL CHARGES', '23'),
('187', '11042', '1', '4321', 'TASHEEL CHARGES', '23'),
('188', '11043', '1', '4321', 'TASHEEL CHARGES', '23'),
('189', '11044', '1', '4321', 'TASHEEL CHARGES', '23'),
('190', '11045', '1', '4321', 'TASHEEL CHARGES', '23'),
('191', '11046', '1', '4321', 'TASHEEL CHARGES', '23'),
('192', '11047', '1', '4321', 'TASHEEL CHARGES', '23'),
('193', '11048', '1', '4321', 'TASHEEL CHARGES', '23'),
('194', '11049', '1', '4321', 'TASHEEL CHARGES', '23'),
('195', '11050', '1', '4321', 'TASHEEL CHARGES', '23'),
('196', '11051', '1', '4321', 'TASHEEL CHARGES', '23'),
('197', '11052', '1', '4321', 'TASHEEL CHARGES', '23'),
('198', '11053', '1', '4321', 'TASHEEL CHARGES', '23'),
('199', '11054', '1', '4321', 'TASHEEL CHARGES', '23'),
('200', '11055', '1', '4321', 'TASHEEL CHARGES', '23'),
('201', '11056', '1', '4321', 'TASHEEL CHARGES', '23'),
('202', '11057', '1', '4321', 'TASHEEL CHARGES', '23'),
('203', '11058', '1', '4321', 'TASHEEL CHARGES', '23'),
('204', '11059', '1', '4321', 'TASHEEL CHARGES', '23'),
('205', '11060', '1', '4321', 'TASHEEL CHARGES', '23'),
('206', '11061', '1', '4321', 'TASHEEL CHARGES', '23'),
('207', '11062', '1', '4321', 'TASHEEL CHARGES', '23'),
('208', '11063', '1', '4321', 'TASHEEL CHARGES', '23'),
('209', '11064', '1', '4321', 'TASHEEL CHARGES', '23'),
('210', '11065', '1', '4321', 'TASHEEL CHARGES', '23'),
('211', '11066', '1', '4321', 'TASHEEL CHARGES', '23'),
('212', '11067', '1', '4321', 'TASHEEL CHARGES', '23'),
('213', '11068', '1', '4321', 'TASHEEL CHARGES', '23'),
('214', '11069', '1', '4321', 'TASHEEL CHARGES', '23'),
('215', '11070', '1', '4321', 'TASHEEL CHARGES', '23'),
('216', '11071', '1', '4321', 'TASHEEL CHARGES', '23'),
('217', '11072', '1', '4321', 'TASHEEL CHARGES', '23'),
('218', '11073', '1', '4321', 'TASHEEL CHARGES', '23'),
('219', '11074', '1', '4321', 'TASHEEL CHARGES', '23'),
('220', '11075', '1', '4321', 'TASHEEL CHARGES', '23'),
('221', '11076', '1', '4321', 'TASHEEL CHARGES', '23'),
('222', '11077', '1', '4321', 'TASHEEL CHARGES', '23'),
('223', '11078', '1', '4321', 'TASHEEL CHARGES', '23'),
('224', '11079', '1', '4321', 'TASHEEL CHARGES', '23'),
('225', '11080', '1', '4321', 'TASHEEL CHARGES', '23'),
('226', '11081', '1', '4321', 'TASHEEL CHARGES', '23'),
('227', '11082', '1', '4321', 'TASHEEL CHARGES', '23'),
('228', '11083', '1', '4321', 'TASHEEL CHARGES', '23'),
('229', '11084', '1', '4321', 'TASHEEL CHARGES', '123'),
('230', '11085', '1', '4321', 'TASHEEL CHARGES', '123'),
('231', '11086', '1', '4321', 'TASHEEL CHARGES', '123'),
('232', '11087', '1', '4321', 'TASHEEL CHARGES', '123'),
('233', '11088', '1', '4321', 'TASHEEL CHARGES', '223'),
('234', '11089', '1', '4321', 'TASHEEL CHARGES', '223'),
('235', '11090', '1', '4321', 'TASHEEL CHARGES', '223'),
('236', '11091', '1', '4321', 'TASHEEL CHARGES', '223'),
('237', '11092', '1', '4321', 'TASHEEL CHARGES', '223'),
('238', '11093', '1', '4321', 'TASHEEL CHARGES', '323'),
('239', '11094', '1', '4321', 'TASHEEL CHARGES', '323'),
('240', '11095', '1', '4321', 'TASHEEL CHARGES', '523'),
('241', '11096', '1', '4321', 'TASHEEL CHARGES', '523'),
('242', '11097', '1', '4321', 'TASHEEL CHARGES', '523'),
('243', '11098', '1', '4321', 'TASHEEL CHARGES', '523'),
('244', '11099', '1', '4321', 'TASHEEL CHARGES', '523'),
('245', '11100', '1', '4321', 'TASHEEL CHARGES', '623'),
('246', '11101', '1', '4321', 'TASHEEL CHARGES', '623'),
('247', '11102', '1', '4321', 'TASHEEL CHARGES', '623'),
('248', '11103', '1', '4321', 'TASHEEL CHARGES', '823'),
('249', '11104', '1', '4321', 'TASHEEL CHARGES', '1023'),
('250', '11105', '1', '4321', 'TASHEEL CHARGES', '1523'),
('251', '11106', '1', '4321', 'TASHEEL CHARGES', '1523'),
('252', '11107', '1', '4321', 'TASHEEL CHARGES', '1523'),
('253', '11108', '1', '4321', 'TASHEEL CHARGES', '1523'),
('254', '11109', '1', '4321', 'TASHEEL CHARGES', '1723'),
('255', '11110', '1', '4321', 'TASHEEL CHARGES', '2023'),
('256', '11111', '1', '4321', 'TASHEEL CHARGES', '2023'),
('257', '11112', '1', '4321', 'TASHEEL CHARGES', '2023'),
('258', '11113', '1', '4321', 'TASHEEL CHARGES', '2023'),
('259', '11114', '1', '4321', 'TASHEEL CHARGES', '2223'),
('260', '11115', '1', '4321', 'TASHEEL CHARGES', '2523'),
('261', '11116', '1', '4321', 'TASHEEL CHARGES', '5023'),
('262', '11117', '1', '4321', 'TASHEEL CHARGES', '5023'),
('263', '11118', '1', '4321', 'TASHEEL CHARGES', '5023'),
('264', '11119', '1', '4321', 'TASHEEL CHARGES', '5223'),
('265', '11120', '1', '4321', 'TASHEEL CHARGES', '10023'),
('266', '11121', '1', '4321', 'TASHEEL CHARGES', '25023'),
('267', '11122', '1', '4321', 'TASHEEL CHARGES', '50023'),
('268', '11123', '1', '4321', 'TASHEEL CHARGES', '50023'),
('269', '1T1', '1', '4321', 'TASHEEL-CHARGES', '10');

### Structure of table `saai_theme_option` ###

DROP TABLE IF EXISTS `saai_theme_option`;

CREATE TABLE `saai_theme_option` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `option_name` varchar(60) NOT NULL,
  `option_value` varchar(60) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=latin1 ;

### Data of table `saai_theme_option` ###

INSERT INTO `saai_theme_option` VALUES
('1', 'hide_version', '0'),
('2', 'hide_help_link', '0'),
('3', 'hide_dashboard', '0'),
('4', 'powered_name', 'Direct Axis Technology L.L.C'),
('5', 'powered_url', 'http://directaxistech.com'),
('6', 'theme', 'daxis'),
('7', 'color_scheme', 'green'),
('8', 'logo', 'kv_logo.png');

### Structure of table `temp_bank_statements` ###

DROP TABLE IF EXISTS `temp_bank_statements`;

CREATE TABLE `temp_bank_statements` (
  `transaction_` varchar(256) DEFAULT '',
  `debit` varchar(256) DEFAULT '0',
  `credit` varchar(256) DEFAULT '0',
  `reconciled` int(11) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ;

### Data of table `temp_bank_statements` ###

INSERT INTO `temp_bank_statements` VALUES
('1234', '-89.5', '0\r', '1'),
('noqodi Charges*  Ref. 20198120180014568 20198120180014568 Vision eForm Card Charges hessa', '140', '0\r', '0'),
('5% VAT on noqodi charges  Ref. 20198120180014568 20198120180014568 Vision eForm Card Charges hessa', '0.15', '0\r', '0'),
('eForm Vision Charges  Ref. 20198120180014570 20198120180014570 Vision eForm Card Charges hessa', '356.75', '0\r', '0'),
('noqodi Charges*  Ref. 20198120180014570 20198120180014570 Vision eForm Card Charges hessa', '3', '0\r', '0'),
('5% VAT on noqodi charges  Ref. 20198120180014570 20198120180014570 Vision eForm Card Charges hessa', '0.15', '0\r', '0'),
('eForm Vision Charges  Ref. 20132020180280368 20132020180280368 Vision eForm Card Charges', '406.75', '0\r', '0'),
('noqodi Charges*  Ref. 20132020180280368 20132020180280368 Vision eForm Card Charges', '3', '0\r', '0'),
('5% VAT on noqodi charges  Ref. 20132020180280368 20132020180280368 Vision eForm Card Charges', '0.15', '0\r', '0'),
('eForm Vision Charges  Ref. 20133320180465390 20133320180465390 Vision eForm Card Charges hind', '456.75', '0\r', '0'),
('noqodi Charges*  Ref. 20133320180465390 20133320180465390 Vision eForm Card Charges hind', '3', '0\r', '0'),
('5% VAT on noqodi charges  Ref. 20133320180465390 20133320180465390 Vision eForm Card Charges hind', '0.15', '0\r', '0'),
('eForm Vision Charges  Ref. 20147220180091050 20147220180091050 Vision eForm Card Charges Ameera', '955.75', '0\r', '0'),
('noqodi Charges*  Ref. 20147220180091050 20147220180091050 Vision eForm Card Charges Ameera', '3', '0\r', '0'),
('5% VAT on noqodi charges  Ref. 20147220180091050 20147220180091050 Vision eForm Card Charges Ameera', '0.15', '0\r', '0'),
('eForm Vision Charges  Ref. 20147220180091060 20147220180091060 Vision eForm Card Charges Ameera', '955.75', '0\r', '0'),
('noqodi Charges*  Ref. 20147220180091060 20147220180091060 Vision eForm Card Charges Ameera', '3', '0\r', '0'),
('5% VAT on noqodi charges  Ref. 20147220180091060 20147220180091060 Vision eForm Card Charges Ameera', '0.15', '0\r', '0'),
('eForm Vision Charges  Ref. 201227320180174860 201227320180174860 Vision eForm Card Charges maitha', '375', '0\r', '0'),
('noqodi Charges*  Ref. 201227320180174860 201227320180174860 Vision eForm Card Charges maitha', '3', '0\r', '0'),
('5% VAT on noqodi charges  Ref. 201227320180174860 201227320180174860 Vision eForm Card Charges maitha', '0.15', '0\r', '0'),
('eForm Vision Charges  Ref. 20149820180149272 20149820180149272 Vision eForm Card Charges maitha', '535.75', '0\r', '0'),
('noqodi Charges*  Ref. 20149820180149272 20149820180149272 Vision eForm Card Charges maitha', '3', '0\r', '0'),
('5% VAT on noqodi charges  Ref. 20149820180149272 20149820180149272 Vision eForm Card Charges maitha', '0.15', '0\r', '0'),
('eForm Vision Charges  Ref. 201227320180174878 201227320180174878 Vision eForm Card Charges maitha', '375', '0\r', '0'),
('noqodi Charges*  Ref. 201227320180174878 201227320180174878 Vision eForm Card Charges maitha', '3', '0\r', '0'),
('5% VAT on noqodi charges  Ref. 201227320180174878 201227320180174878 Vision eForm Card Charges maitha', '0.15', '0\r', '0');

### Structure of table `xx_reports` ###

DROP TABLE IF EXISTS `xx_reports`;

CREATE TABLE `xx_reports` (
  `id` varchar(10) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `typ` varchar(10) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `attrib` text COLLATE utf8_unicode_ci NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

### Data of table `xx_reports` ###

INSERT INTO `xx_reports` VALUES
('6', 'item', 'Line|PH|1|1|1|1|1'),
('F2', 'funct', 'atime|2007-09-21|Bauer|Actual Time|function atime() {return date(&quot;h:i a&quot;);}'),
('F1', 'funct', 'RepDate|2007-09-26|Joe|Date|function RepDate() {return today().&quot; &quot;.now();}'),
('9', 'select', 'select `a`.`stock_id` AS `stock_id`,`a`.`description` AS `item_description`,`a`.`long_description` AS `long_description`,`a`.`category_id` AS `category_id`,`c`.`price` AS `service_charge`,`a`.`govt_fee` AS `govt_fee`,`a`.`pf_amount` AS `pf_amount`,`a`.`bank_service_charge` AS `bank_service_charge`,`a`.`bank_service_charge_vat` AS `bank_service_charge_vat`,`a`.`commission_loc_user` AS `commission_loc_user`,`a`.`commission_non_loc_user` AS `commission_non_loc_user` from ((`0_stock_master` `a` left join `0_stock_category` `b` on((`b`.`category_id` = `a`.`category_id`))) left join `0_prices` `c` on(((`c`.`stock_id` = `a`.`stock_id`) and (`c`.`sales_type_id` = 1)))) '),
('8', 'select', 'select `a`.`stock_id` AS `stock_id`,`a`.`description` AS `item_description`,`a`.`long_description` AS `long_description`,`a`.`category_id` AS `category_id`,`c`.`price` AS `service_charge`,`a`.`govt_fee` AS `govt_fee`,`a`.`pf_amount` AS `pf_amount`,`a`.`bank_service_charge` AS `bank_service_charge`,`a`.`bank_service_charge_vat` AS `bank_service_charge_vat`,`a`.`commission_loc_user` AS `commission_loc_user`,`a`.`commission_non_loc_user` AS `commission_non_loc_user` from ((`0_stock_master` `a` left join `0_stock_category` `b` on((`b`.`category_id` = `a`.`category_id`))) left join `0_prices` `c` on(((`c`.`stock_id` = `a`.`stock_id`) and (`c`.`sales_type_id` = 1)))) '),
('B1', 'block', 'block1|2007-09-27|Bauer|Block 1'),
('6', 'item', 'Line|GF|1|1|1|400|1'),
('6', 'item', 'DB|DE|Helvetica|9|6|50|0|account_code||||||||'),
('6', 'item', 'Term|GH|Helvetica|9|50|50|0|Newgroup||||||||'),
('6', 'item', 'String|RF|Helvetica|9|20|50|0|Gran Total||||||||'),
('6', 'item', 'String|GF|Helvetica|9|20|50|0|Sum:||||||||'),
('6', 'item', 'DB|DE|Helvetica|9|30|300|0|name||||||||'),
('6', 'item', 'DB|DE|Helvetica|9|20|400|0|class_name||||||||'),
('6', 'item', 'DB|DE|Helvetica|9|50|100|0|account_name||||||||'),
('6', 'select', 'select * from 0_chart_master,0_chart_types,0_chart_class where account_type=id and class_id=cid order by account_code'),
('6', 'item', 'Line|GH|1|1|1|1|1'),
('6', 'item', 'Line|RH|1|1|1|1|1'),
('6', 'item', 'Line|RF|1|1|1|1|1'),
('6', 'item', 'Term|RH|Helvetica-Bold|16|50|50|0|RepTitle||||||||'),
('6', 'item', 'String|RH|Helvetica|8|30|50|-12|Print Out Date:||||||||'),
('6', 'item', 'Term|RH|Helvetica|8|30|120|-12|RepDate||||||||'),
('6', 'item', 'String|RH|Helvetica|8|30|50|-24|Fiscal Year:||||||||'),
('6', 'item', 'String|RH|Helvetica|8|30|50|-36|Select:||||||||'),
('6', 'item', 'Term|GF|Helvetica|9|4r|200|0|subcount||||||||'),
('6', 'item', 'Term|RF|Helvetica|9|4r|200|0|rec_count||||||||'),
('6', 'item', 'String|PH|Helvetica-Bold|9|20|50|0|Account||||||||'),
('6', 'item', 'String|PH|Helvetica-Bold|9|50|100|0|Account Name||||||||'),
('6', 'item', 'String|PH|Helvetica-Bold|9|20|300|0|Type||||||||'),
('6', 'item', 'String|PH|Helvetica-Bold|9|20l|400|0|Class||||||||'),
('6', 'item', 'Term|RH|Helvetica|9|50|400|0|Company||||||||'),
('6', 'item', 'Term|RH|Helvetica|9|50l|400|-12|Username||||||||'),
('6', 'item', 'Term|RH|Helvetica|9|50|400|-36|PageNo||||||||'),
('B1', 'item', 'String|PH|Helvetica|7|20|100|0|Stringitem||||||||'),
('6', 'item', 'Term|RH|Helvetica|8|50|400|-24|Host||||||||'),
('F13', 'funct', 'Host|2007-09-26|Hunt|Host name|function Host(){return $_SERVER[&#039;SERVER_NAME&#039;];}'),
('6', 'group', 'name|nopage'),
('6', 'item', 'Term|RH|Helvetica|8|50|120|-24|FiscalYear||||||||'),
('F12', 'funct', 'FiscalYear|2007-09-26|Hunt|Get current Fiscal Year|function FiscalYear(){$y=get_current_fiscalyear();return sql2date($y[&#039;begin&#039;]) . &quot; - &quot; . sql2date($y[&#039;end&#039;]);}'),
('F11', 'funct', 'Username|2007-09-26|Hunt|Get Username|function Username(){return $_SESSION[&quot;wa_current_user&quot;]-&gt;name;}'),
('F10', 'funct', 'Company|2007-09-26|Hunt|Company Name|function Company(){ return get_company_pref(&#039;coy_name&#039;); }'),
('6', 'info', 'Accounts|2012-11-10|Hunt|Accounts List (example report)|portrait|a4|class'),
('F1', 'funct', 'Cp of PageNo|2018-05-29|Joe|Page Number|function PageNo($it){return &quot;Page   &quot;.$it-&gt;pdf-&gt;getNumPages();}'),
('F1', 'funct', 'Cp of PageNo|2018-05-29|Joe|Page Number|function PageNo($it){return &quot;Page   &quot;.$it-&gt;pdf-&gt;getNumPages();}'),
('F1', 'funct', 'Cp of PageNo|2018-05-29|Joe|Page Number|function PageNo($it){return &quot;Page   &quot;.$it-&gt;pdf-&gt;getNumPages();}'),
('7', 'info', 'dadsasdad|2018-07-31|||portrait|letter|single'),
('7', 'select', 'select `a`.`stock_id` AS `stock_id`,`a`.`description` AS `item_description`,`a`.`long_description` AS `long_description`,`a`.`category_id` AS `category_id`,`c`.`price` AS `service_charge`,`a`.`govt_fee` AS `govt_fee`,`a`.`pf_amount` AS `pf_amount`,`a`.`bank_service_charge` AS `bank_service_charge`,`a`.`bank_service_charge_vat` AS `bank_service_charge_vat`,`a`.`commission_loc_user` AS `commission_loc_user`,`a`.`commission_non_loc_user` AS `commission_non_loc_user` from ((`0_stock_master` `a` left join `0_stock_category` `b` on((`b`.`category_id` = `a`.`category_id`))) left join `0_prices` `c` on(((`c`.`stock_id` = `a`.`stock_id`) and (`c`.`sales_type_id` = 1)))) '),
('7', 'group', '|nopage'),
('8', 'info', 'DX|2018-07-31|DXS|DAXIS|portrait|letter|classtable'),
('8', 'group', '|nopage'),
('9', 'info', '44|2018-07-31|4|4|portrait|letter|single'),
('9', 'group', '|nopage');

### Structure of table `0_subcategories` ###

DROP TABLE IF EXISTS `0_subcategories`;

CREATE TABLE `0_subcategories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `description` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `main_cat_id` int(11) DEFAULT NULL,
  `parent_sub_cat_id` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `FK_0_subcategories_0_stock_category` (`main_cat_id`),
  CONSTRAINT `FK_0_subcategories_0_stock_category` FOREIGN KEY (`main_cat_id`) REFERENCES `0_stock_category` (`category_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

### Data of table `0_subcategories` ###

INSERT INTO `0_subcategories` VALUES
('1', 'Entry Permit - Cancel', '6', '0'),
('2', 'Entry Permit - Extend', '6', '0'),
('3', 'Entry Permit - New', '6', '0'),
('4', 'Residence - Cancel', '6', '0'),
('5', 'Residence - Data Modification', '6', '0'),
('6', 'Residence - Renew', '6', '0'),
('7', 'Residence - New', '6', '0'),
('8', 'Residence - Sponsorship Transfer', '6', '0'),
('9', 'Residence - Transfer Residence To New Passport', '6', '0'),
('10', 'Services', '6', '0');

### Structure of table `commission_report_view` ###

DROP VIEW IF EXISTS `commission_report_view`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `commission_report_view` AS select `b`.`reference` AS `invoice_no`,`a`.`stock_id` AS `stock_id`,`a`.`description` AS `description`,`a`.`unit_price` AS `unit_price`,`a`.`unit_tax` AS `unit_tax`,`a`.`quantity` AS `quantity`,((((((`a`.`unit_price` + `a`.`govt_fee`) + `a`.`bank_service_charge`) + `a`.`bank_service_charge_vat`) + `a`.`unit_tax`) * `a`.`quantity`) - (`a`.`discount_amount` * `a`.`quantity`)) AS `invoice_amount`,(`a`.`discount_percent` * 100) AS `discount_percent`,`a`.`discount_amount` AS `discount_amount`,`a`.`govt_fee` AS `govt_fee`,`a`.`bank_service_charge` AS `bank_service_charge`,`a`.`bank_service_charge_vat` AS `bank_service_charge_vat`,`a`.`pf_amount` AS `pf_amount`,`a`.`transaction_id` AS `transaction_id`,`a`.`user_commission` AS `user_commission`,(`a`.`user_commission` * `a`.`quantity`) AS `total_commission`,`a`.`created_by` AS `created_by`,`a`.`updated_by` AS `updated_by`,`c`.`name` AS `customer_name`,`b`.`display_customer` AS `reference_customer`,`0_users`.`user_id` AS `created_employee`,`b`.`tran_date` AS `transaction_date` from (((`0_debtor_trans_details` `a` left join `0_debtor_trans` `b` on((`b`.`trans_no` = `a`.`debtor_trans_no`))) left join `0_debtors_master` `c` on((`c`.`debtor_no` = `b`.`debtor_no`))) left join `0_users` on((`0_users`.`id` = `a`.`created_by`))) where ((`a`.`debtor_trans_type` = 10) and (`b`.`reference` <> 'auto') and (`b`.`type` = 10) and (`a`.`quantity` <> 0)) group by `b`.`reference`,`a`.`stock_id` order by `b`.`reference` ;



### Structure of table `customer_commission_report_view` ###

DROP VIEW IF EXISTS `customer_commission_report_view`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `customer_commission_report_view` AS select ifnull(((`a`.`unit_price` * `e`.`customer_commission`) / 100),0) AS `customer_commission`,ifnull((((`a`.`unit_price` * `e`.`customer_commission`) / 100) * `a`.`quantity`),0) AS `total_customer_commission`,`b`.`reference` AS `invoice_no`,`a`.`stock_id` AS `stock_id`,`a`.`description` AS `description`,`a`.`unit_price` AS `unit_price`,`a`.`unit_tax` AS `unit_tax`,`a`.`quantity` AS `quantity`,((((((`a`.`unit_price` + `a`.`govt_fee`) + `a`.`bank_service_charge`) + `a`.`bank_service_charge_vat`) + `a`.`unit_tax`) * `a`.`quantity`) - (`a`.`discount_amount` * `a`.`quantity`)) AS `invoice_amount`,(`a`.`discount_percent` * 100) AS `discount_percent`,`a`.`discount_amount` AS `discount_amount`,`a`.`govt_fee` AS `govt_fee`,`a`.`bank_service_charge` AS `bank_service_charge`,`a`.`bank_service_charge_vat` AS `bank_service_charge_vat`,`a`.`pf_amount` AS `pf_amount`,`a`.`transaction_id` AS `transaction_id`,`a`.`user_commission` AS `user_commission`,(`a`.`user_commission` * `a`.`quantity`) AS `total_commission`,`a`.`created_by` AS `created_by`,`a`.`updated_by` AS `updated_by`,`c`.`name` AS `customer_name`,`b`.`display_customer` AS `reference_customer`,`0_users`.`user_id` AS `created_employee`,`b`.`tran_date` AS `transaction_date` from (((((`0_debtor_trans_details` `a` left join `0_debtor_trans` `b` on((`b`.`trans_no` = `a`.`debtor_trans_no`))) left join `0_debtors_master` `c` on((`c`.`debtor_no` = `b`.`debtor_no`))) left join `0_users` on((`0_users`.`id` = `a`.`created_by`))) left join `0_stock_master` `d` on((`d`.`stock_id` = `a`.`stock_id`))) join `customer_discount_items` `e` on(((`e`.`item_id` = `d`.`category_id`) and (`c`.`debtor_no` = `e`.`customer_id`)))) where ((`a`.`debtor_trans_type` = 10) and (`b`.`reference` <> 'auto') and (`b`.`type` = 10) and (`a`.`quantity` <> 0)) group by `b`.`reference`,`a`.`stock_id` order by `a`.`id` desc ;



### Structure of table `daily_report_view` ###

DROP VIEW IF EXISTS `daily_report_view`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `daily_report_view` AS select (select count(0) from `0_debtor_trans` where ((`0_debtor_trans`.`type` = 10) and (`0_debtor_trans`.`tran_date` = `b`.`tran_date`))) AS `invoice_count`,`b`.`tran_date` AS `tran_date`,(select count(`0_debtor_trans_details`.`id`) from (`0_debtor_trans` left join `0_debtor_trans_details` on((`0_debtor_trans`.`trans_no` = `0_debtor_trans_details`.`debtor_trans_no`))) where ((`0_debtor_trans_details`.`debtor_trans_type` = 10) and (`0_debtor_trans`.`type` = 10) and (`0_debtor_trans`.`tran_date` = `b`.`tran_date`))) AS `total_service_count`,(select sum((`0_debtor_trans`.`ov_amount` + `0_debtor_trans`.`ov_gst`)) from `0_debtor_trans` where ((`0_debtor_trans`.`type` = 10) and (`0_debtor_trans`.`tran_date` = `b`.`tran_date`))) AS `total_invoice_amount`,(select sum(`0_debtor_trans`.`alloc`) from `0_debtor_trans` where ((`0_debtor_trans`.`type` = 10) and (`0_debtor_trans`.`tran_date` = `b`.`tran_date`))) AS `total_amount_recieved`,(select (sum((`0_debtor_trans`.`ov_amount` + `0_debtor_trans`.`ov_gst`)) - sum(`0_debtor_trans`.`alloc`)) from `0_debtor_trans` where ((`0_debtor_trans`.`type` = 10) and (`0_debtor_trans`.`tran_date` = `b`.`tran_date`))) AS `pending_amount`,(select sum((`0_debtor_trans_details`.`quantity` * `0_debtor_trans_details`.`unit_price`)) from (`0_debtor_trans` left join `0_debtor_trans_details` on((`0_debtor_trans`.`trans_no` = `0_debtor_trans_details`.`debtor_trans_no`))) where ((`0_debtor_trans_details`.`debtor_trans_type` = 10) and (`0_debtor_trans`.`type` = 10) and (`0_debtor_trans`.`tran_date` = `b`.`tran_date`))) AS `total_service_charge`,(select sum((`0_debtor_trans_details`.`quantity` * `0_debtor_trans_details`.`user_commission`)) from (`0_debtor_trans` left join `0_debtor_trans_details` on((`0_debtor_trans`.`trans_no` = `0_debtor_trans_details`.`debtor_trans_no`))) where ((`0_debtor_trans_details`.`debtor_trans_type` = 10) and (`0_debtor_trans`.`type` = 10) and (`0_debtor_trans`.`tran_date` = `b`.`tran_date`))) AS `total_commission`,(select sum(abs(`0_gl_trans`.`amount`)) from `0_gl_trans` where ((`0_gl_trans`.`type` = 12) and (`0_gl_trans`.`account` = 1200) and (`b`.`tran_date` = `0_gl_trans`.`tran_date`))) AS `total_collection` from ((`0_debtor_trans` `a` join `0_gl_trans` `b` on((`a`.`trans_no` = `b`.`type_no`))) join `0_debtor_trans_details` `c` on((`a`.`trans_no` = `c`.`debtor_trans_no`))) where ((`c`.`debtor_trans_type` = 10) and (`a`.`type` = 10)) group by `b`.`tran_date` order by `b`.`tran_date` ;



### Structure of table `discount_report_view` ###

DROP VIEW IF EXISTS `discount_report_view`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `discount_report_view` AS select `b`.`reference` AS `invoice_no`,`a`.`stock_id` AS `stock_id`,`a`.`description` AS `description`,`a`.`unit_price` AS `unit_price`,`a`.`unit_tax` AS `unit_tax`,`a`.`quantity` AS `quantity`,((((((`a`.`unit_price` + `a`.`govt_fee`) + `a`.`bank_service_charge`) + `a`.`bank_service_charge_vat`) + `a`.`unit_tax`) * `a`.`quantity`) - (`a`.`discount_amount` * `a`.`quantity`)) AS `invoice_amount`,(`a`.`discount_percent` * 100) AS `discount_percent`,`a`.`discount_amount` AS `discount_amount`,(`a`.`discount_amount` * `a`.`quantity`) AS `total_discount`,`a`.`govt_fee` AS `govt_fee`,`a`.`bank_service_charge` AS `bank_service_charge`,`a`.`bank_service_charge_vat` AS `bank_service_charge_vat`,`a`.`pf_amount` AS `pf_amount`,`a`.`transaction_id` AS `transaction_id`,`a`.`user_commission` AS `user_commission`,`a`.`created_by` AS `created_by`,`a`.`updated_by` AS `updated_by`,`c`.`name` AS `customer_name`,`b`.`display_customer` AS `reference_customer`,`0_users`.`user_id` AS `created_employee`,`b`.`tran_date` AS `transaction_date` from (((`0_debtor_trans_details` `a` left join `0_debtor_trans` `b` on((`b`.`trans_no` = `a`.`debtor_trans_no`))) left join `0_debtors_master` `c` on((`c`.`debtor_no` = `b`.`debtor_no`))) left join `0_users` on((`0_users`.`id` = `a`.`created_by`))) where ((`a`.`debtor_trans_type` = 10) and (`b`.`reference` <> 'auto') and (`b`.`type` = 10) and (`a`.`quantity` <> 0)) group by `b`.`reference`,`a`.`stock_id` order by `b`.`reference` ;



### Structure of table `invoice_payment_report` ###

DROP VIEW IF EXISTS `invoice_payment_report`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `invoice_payment_report` AS select `b`.`tran_date` AS `date_alloc`,`a`.`person_id` AS `person_id`,`a`.`amt` AS `amt`,`b`.`reference` AS `invoice_number`,ifnull((select `0_debtor_trans`.`reference` from `0_debtor_trans` where ((`0_debtor_trans`.`type` = 12) and (`0_debtor_trans`.`trans_no` = `a`.`trans_no_from`))),'User Payment') AS `payment_ref`,`c`.`name` AS `customer`,`d`.`user` AS `user` from (((`0_cust_allocations` `a` left join `0_debtor_trans` `b` on((`b`.`trans_no` = `a`.`trans_no_to`))) left join `0_debtors_master` `c` on((`c`.`debtor_no` = `a`.`person_id`))) left join `0_audit_trail` `d` on(((`d`.`trans_no` = `a`.`trans_no_from`) and (`d`.`type` = 12)))) where (`b`.`type` = 10) ;



### Structure of table `invoice_report_detail_view` ###

DROP VIEW IF EXISTS `invoice_report_detail_view`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `invoice_report_detail_view` AS select ifnull(`e`.`reward_amount`,0) AS `reward_amount`,(case when (`b`.`payment_flag` = 2) then 'CustomerCard' else `payment`.`payment_method` end) AS `payment_method`,ifnull((((`a`.`unit_price` * `f`.`customer_commission`) / 100) * `a`.`quantity`),0) AS `total_customer_commission`,`b`.`reference` AS `invoice_no`,`a`.`stock_id` AS `stock_id`,`d`.`category_id` AS `category_id`,left(`a`.`description`,60) AS `description`,`d`.`description` AS `service_eng_name`,`a`.`unit_price` AS `unit_price`,(`a`.`unit_price` * `a`.`quantity`) AS `total_price`,(`a`.`unit_tax` * `a`.`quantity`) AS `total_tax`,(`a`.`govt_fee` * `a`.`quantity`) AS `total_govt_fee`,`a`.`unit_tax` AS `unit_tax`,`a`.`quantity` AS `quantity`,((((((`a`.`unit_price` + `a`.`govt_fee`) + `a`.`bank_service_charge`) + `a`.`bank_service_charge_vat`) + `a`.`unit_tax`) * `a`.`quantity`) - (`a`.`discount_amount` * `a`.`quantity`)) AS `invoice_amount`,(((((`a`.`unit_price` * `a`.`quantity`) - (`a`.`discount_amount` * `a`.`quantity`)) - ifnull(`e`.`reward_amount`,0)) - ifnull((((`a`.`unit_price` * `f`.`customer_commission`) / 100) * `a`.`quantity`),0)) - (`a`.`pf_amount` * `a`.`quantity`)) AS `net_service_charge`,(`a`.`discount_percent` * 100) AS `discount_percent`,`a`.`discount_amount` AS `discount_amount`,`a`.`govt_fee` AS `govt_fee`,`a`.`bank_service_charge` AS `bank_service_charge`,`a`.`bank_service_charge_vat` AS `bank_service_charge_vat`,`a`.`pf_amount` AS `pf_amount`,if((`a`.`transaction_id` <> ''),concat('"',`a`.`transaction_id`,'"'),NULL) AS `transaction_id`,`a`.`user_commission` AS `user_commission`,`a`.`created_by` AS `created_by`,`a`.`updated_by` AS `updated_by`,`c`.`name` AS `customer_name`,`b`.`display_customer` AS `reference_customer`,`0_users`.`user_id` AS `created_employee`,`b`.`tran_date` AS `transaction_date`,(case when (`b`.`alloc` >= (`b`.`ov_amount` + `b`.`ov_gst`)) then '1' when (`b`.`alloc` = 0) then '2' when (`b`.`alloc` < (`b`.`ov_amount` + `b`.`ov_gst`)) then '3' end) AS `payment_status` from (((((((`0_debtor_trans_details` `a` left join `0_debtor_trans` `b` on((`b`.`trans_no` = `a`.`debtor_trans_no`))) left join `0_debtors_master` `c` on((`c`.`debtor_no` = `b`.`debtor_no`))) left join `0_users` on((`0_users`.`id` = `a`.`created_by`))) left join `0_stock_master` `d` on((`d`.`stock_id` = `a`.`stock_id`))) left join `customer_rewards` `e` on(((`e`.`trans_no` = `b`.`trans_no`) and (`e`.`trans_type` = 10)))) left join `customer_discount_items` `f` on(((`f`.`item_id` = `d`.`category_id`) and (`c`.`debtor_no` = `f`.`customer_id`)))) left join `0_debtor_trans` `payment` on(((`payment`.`trans_no` = (select `0_cust_allocations`.`trans_no_from` from `0_cust_allocations` where (`0_cust_allocations`.`trans_no_to` = `b`.`trans_no`) order by `0_cust_allocations`.`id` limit 1)) and (`payment`.`type` = 12)))) where ((`a`.`debtor_trans_type` = 10) and (`b`.`reference` <> 'auto') and (`b`.`type` = 10) and (`a`.`quantity` <> 0)) group by `b`.`reference`,`a`.`stock_id`,`a`.`id` order by `a`.`id` desc ;



### Structure of table `invoice_report_for_ref_view` ###

DROP VIEW IF EXISTS `invoice_report_for_ref_view`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `invoice_report_for_ref_view` AS select ifnull(`e`.`reward_amount`,0) AS `reward_amount`,ifnull((((`a`.`unit_price` * `f`.`customer_commission`) / 100) * `a`.`quantity`),0) AS `total_customer_commission`,`b`.`reference` AS `invoice_no`,`a`.`stock_id` AS `stock_id`,`d`.`category_id` AS `category_id`,left(`a`.`description`,60) AS `description`,`d`.`description` AS `service_eng_name`,`a`.`unit_price` AS `unit_price`,(`a`.`unit_price` * `a`.`quantity`) AS `total_price`,(`a`.`unit_tax` * `a`.`quantity`) AS `total_tax`,(`a`.`govt_fee` * `a`.`quantity`) AS `total_govt_fee`,`a`.`unit_tax` AS `unit_tax`,`a`.`quantity` AS `quantity`,(`b`.`ov_amount` + `b`.`ov_gst`) AS `invoice_amount`,(((((`a`.`unit_price` * `a`.`quantity`) - (`a`.`discount_amount` * `a`.`quantity`)) - ifnull(`e`.`reward_amount`,0)) - ifnull((((`a`.`unit_price` * `f`.`customer_commission`) / 100) * `a`.`quantity`),0)) - (`a`.`pf_amount` * `a`.`quantity`)) AS `net_service_charge`,(`a`.`discount_percent` * 100) AS `discount_percent`,`a`.`discount_amount` AS `discount_amount`,`a`.`govt_fee` AS `govt_fee`,`a`.`bank_service_charge` AS `bank_service_charge`,`a`.`bank_service_charge_vat` AS `bank_service_charge_vat`,`a`.`pf_amount` AS `pf_amount`,`a`.`transaction_id` AS `transaction_id`,`a`.`user_commission` AS `user_commission`,`a`.`created_by` AS `created_by`,`a`.`updated_by` AS `updated_by`,`c`.`name` AS `customer_name`,`b`.`display_customer` AS `reference_customer`,`0_users`.`user_id` AS `created_employee`,`b`.`tran_date` AS `transaction_date`,(case when (`b`.`alloc` >= (`b`.`ov_amount` + `b`.`ov_gst`)) then '1' when (`b`.`alloc` = 0) then '2' when (`b`.`alloc` < (`b`.`ov_amount` + `b`.`ov_gst`)) then '3' end) AS `payment_status`,(select `0_audit_trail`.`gl_date` from (`0_cust_allocations` left join `0_audit_trail` on(((`0_audit_trail`.`trans_no` = `0_cust_allocations`.`trans_no_from`) and (`0_audit_trail`.`type` = 12)))) where (`0_cust_allocations`.`trans_no_to` = `b`.`trans_no`) limit 1) AS `payment_date`,((((((((`a`.`unit_price` + `a`.`pf_amount`) + `a`.`govt_fee`) + `a`.`bank_service_charge`) + `a`.`bank_service_charge_vat`) + `a`.`unit_tax`) * `a`.`quantity`) - (`a`.`discount_amount` * `a`.`quantity`)) - ifnull(`e`.`reward_amount`,0)) AS `paid_line_total` from ((((((`0_debtor_trans_details` `a` left join `0_debtor_trans` `b` on((`b`.`trans_no` = `a`.`debtor_trans_no`))) left join `0_debtors_master` `c` on((`c`.`debtor_no` = `b`.`debtor_no`))) left join `0_users` on((`0_users`.`id` = `a`.`created_by`))) left join `0_stock_master` `d` on((`d`.`stock_id` = `a`.`stock_id`))) left join `customer_rewards` `e` on(((`e`.`trans_no` = `b`.`trans_no`) and (convert(`e`.`stock_id` using utf32) = convert(`a`.`stock_id` using utf32)) and (`e`.`trans_type` = 10)))) left join `customer_discount_items` `f` on(((`f`.`item_id` = `d`.`category_id`) and (`c`.`debtor_no` = `f`.`customer_id`)))) where ((`a`.`debtor_trans_type` = 10) and (`b`.`reference` <> 'auto') and (`b`.`type` = 10) and (`a`.`quantity` <> 0)) group by `b`.`reference`,`a`.`stock_id`,`a`.`id` order by `a`.`id` desc ;



### Structure of table `invoice_report_view` ###

DROP VIEW IF EXISTS `invoice_report_view`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `invoice_report_view` AS select `b`.`reference` AS `invoice_no`,`a`.`stock_id` AS `stock_id`,`a`.`description` AS `description`,`a`.`unit_price` AS `unit_price`,`a`.`unit_tax` AS `unit_tax`,`a`.`quantity` AS `quantity`,(`b`.`ov_amount` + `b`.`ov_gst`) AS `invoice_amount`,`a`.`discount_percent` AS `discount_percent`,`a`.`discount_amount` AS `discount_amount`,`a`.`govt_fee` AS `govt_fee`,`a`.`bank_service_charge` AS `bank_service_charge`,`a`.`bank_service_charge_vat` AS `bank_service_charge_vat`,`a`.`pf_amount` AS `pf_amount`,`a`.`transaction_id` AS `transaction_id`,`a`.`user_commission` AS `user_commission`,`a`.`created_by` AS `created_by`,`a`.`updated_by` AS `updated_by`,`c`.`name` AS `customer_name`,`c`.`debtor_no` AS `debtor_no`,`b`.`display_customer` AS `reference_customer`,`0_users`.`user_id` AS `created_employee`,`b`.`tran_date` AS `transaction_date`,(case when (`b`.`alloc` >= (`b`.`ov_amount` + `b`.`ov_gst`)) then '1' when (`b`.`alloc` = 0) then '2' when (`b`.`alloc` < (`b`.`ov_amount` + `b`.`ov_gst`)) then '3' end) AS `payment_status` from (((`0_debtor_trans_details` `a` left join `0_debtor_trans` `b` on((`b`.`trans_no` = `a`.`debtor_trans_no`))) left join `0_debtors_master` `c` on((`c`.`debtor_no` = `b`.`debtor_no`))) left join `0_users` on((`0_users`.`id` = `a`.`created_by`))) where ((`a`.`debtor_trans_type` = 10) and (`b`.`reference` <> 'auto') and (`b`.`type` = 10) and (`a`.`quantity` <> 0)) group by `b`.`reference` order by `b`.`trans_no` desc ;



### Structure of table `items_report_view` ###

DROP VIEW IF EXISTS `items_report_view`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `items_report_view` AS select `a`.`stock_id` AS `stock_id`,`a`.`description` AS `item_description`,`a`.`long_description` AS `long_description`,`a`.`category_id` AS `category_id`,`c`.`price` AS `service_charge`,`a`.`govt_fee` AS `govt_fee`,`a`.`pf_amount` AS `pf_amount`,`a`.`bank_service_charge` AS `bank_service_charge`,`a`.`bank_service_charge_vat` AS `bank_service_charge_vat`,`a`.`commission_loc_user` AS `commission_loc_user`,`a`.`commission_non_loc_user` AS `commission_non_loc_user` from ((`0_stock_master` `a` left join `0_stock_category` `b` on((`b`.`category_id` = `a`.`category_id`))) left join `0_prices` `c` on(((`c`.`stock_id` = `a`.`stock_id`) and (`c`.`sales_type_id` = 1)))) ;



### Structure of table `payment_receipt_report_view` ###

DROP VIEW IF EXISTS `payment_receipt_report_view`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `payment_receipt_report_view` AS select `d`.`gl_date` AS `date_alloc`,`g`.`id` AS `bank_account`,`a`.`person_id` AS `person_id`,`h`.`user_id` AS `user_id`,(`f`.`amount` + ifnull(`e`.`reward_amount`,0)) AS `collected_amount`,`b`.`reference` AS `invoice_number`,`e`.`reward_amount` AS `reward_amount`,`g`.`bank_account_name` AS `bank_account_name`,(select `0_debtor_trans`.`reference` from `0_debtor_trans` where ((`0_debtor_trans`.`type` = 12) and (`0_debtor_trans`.`trans_no` = `a`.`trans_no_from`))) AS `payment_ref`,`c`.`name` AS `customer`,`c`.`debtor_no` AS `customer_id`,`d`.`user` AS `user` from (((((((`0_cust_allocations` `a` left join `0_debtor_trans` `b` on((`b`.`trans_no` = `a`.`trans_no_to`))) left join `0_debtors_master` `c` on((`c`.`debtor_no` = `a`.`person_id`))) left join `0_audit_trail` `d` on(((`d`.`trans_no` = `a`.`trans_no_from`) and (`d`.`type` = 12)))) left join `0_bank_trans` `f` on(((`f`.`trans_no` = `d`.`trans_no`) and (`f`.`type` = 12)))) left join `0_bank_accounts` `g` on((`g`.`id` = `f`.`bank_act`))) left join `customer_rewards` `e` on(((`e`.`trans_no` = `d`.`trans_no`) and (`e`.`trans_type` = 12)))) left join `0_users` `h` on((`h`.`id` = `d`.`user`))) where (`b`.`type` = 10) ;



### Structure of table `periodical_report_view` ###

DROP VIEW IF EXISTS `periodical_report_view`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `periodical_report_view` AS select (select count(0) from `0_debtor_trans` where ((`0_debtor_trans`.`type` = 10) and (`0_debtor_trans`.`tran_date` = `b`.`tran_date`) and (`0_debtor_trans`.`ov_amount` <> 0))) AS `invoice_count`,`b`.`tran_date` AS `tran_date`,(select count(`0_debtor_trans_details`.`id`) from (`0_debtor_trans` left join `0_debtor_trans_details` on((`0_debtor_trans`.`trans_no` = `0_debtor_trans_details`.`debtor_trans_no`))) where ((`0_debtor_trans_details`.`debtor_trans_type` = 10) and (`0_debtor_trans`.`type` = 10) and (`0_debtor_trans`.`tran_date` = `b`.`tran_date`))) AS `total_service_count`,(select sum((`0_debtor_trans`.`ov_amount` + `0_debtor_trans`.`ov_gst`)) from `0_debtor_trans` where ((`0_debtor_trans`.`type` = 10) and (`0_debtor_trans`.`tran_date` = `b`.`tran_date`))) AS `total_invoice_amount`,(select sum(`0_debtor_trans`.`alloc`) from `0_debtor_trans` where ((`0_debtor_trans`.`type` = 10) and (`0_debtor_trans`.`tran_date` = `b`.`tran_date`))) AS `total_amount_recieved`,(select (sum((`0_debtor_trans`.`ov_amount` + `0_debtor_trans`.`ov_gst`)) - sum(`0_debtor_trans`.`alloc`)) from `0_debtor_trans` where ((`0_debtor_trans`.`type` = 10) and (`0_debtor_trans`.`tran_date` = `b`.`tran_date`))) AS `pending_amount`,(select (sum((`0_debtor_trans_details`.`quantity` * `0_debtor_trans_details`.`unit_price`)) - sum((`0_debtor_trans_details`.`quantity` * `0_debtor_trans_details`.`discount_amount`))) from (`0_debtor_trans` left join `0_debtor_trans_details` on((`0_debtor_trans`.`trans_no` = `0_debtor_trans_details`.`debtor_trans_no`))) where ((`0_debtor_trans_details`.`debtor_trans_type` = 10) and (`0_debtor_trans`.`type` = 10) and (`0_debtor_trans`.`tran_date` = `b`.`tran_date`))) AS `total_service_charge`,(select sum((`0_debtor_trans_details`.`quantity` * `0_debtor_trans_details`.`user_commission`)) from (`0_debtor_trans` left join `0_debtor_trans_details` on((`0_debtor_trans`.`trans_no` = `0_debtor_trans_details`.`debtor_trans_no`))) where ((`0_debtor_trans_details`.`debtor_trans_type` = 10) and (`0_debtor_trans`.`type` = 10) and (`0_debtor_trans`.`tran_date` = `b`.`tran_date`))) AS `total_commission`,(select sum(abs(`0_gl_trans`.`amount`)) from `0_gl_trans` where ((`0_gl_trans`.`type` = 12) and (`0_gl_trans`.`account` = 1200) and (`b`.`tran_date` = `0_gl_trans`.`tran_date`))) AS `total_collection` from ((`0_debtor_trans` `a` join `0_gl_trans` `b` on((`a`.`trans_no` = `b`.`type_no`))) join `0_debtor_trans_details` `c` on((`a`.`trans_no` = `c`.`debtor_trans_no`))) where ((`c`.`debtor_trans_type` = 10) and (`a`.`type` = 10)) group by `b`.`tran_date` order by `b`.`tran_date` ;



### Structure of table `single_user_transaction_report_view` ###

DROP VIEW IF EXISTS `single_user_transaction_report_view`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `single_user_transaction_report_view` AS select `0_chart_master`.`account_code` AS `account_code`,`0_chart_master`.`account_name` AS `account_name`,`0_gl_trans`.`tran_date` AS `tran_date`,`0_gl_trans`.`amount` AS `amount`,(select `0_audit_trail`.`user` AS `user_id` from `0_audit_trail` where (`0_audit_trail`.`trans_no` = `0_gl_trans`.`type_no`) limit 1) AS `user_id`,`0_gl_trans`.`type_no` AS `type_no` from (`0_chart_master` join `0_gl_trans` on((`0_gl_trans`.`account` = `0_chart_master`.`account_code`))) where ((`0_gl_trans`.`type` in (12,0,10)) and (`0_gl_trans`.`amount` <> 0)) order by `0_gl_trans`.`counter` desc ;



### Structure of table `test_view` ###

DROP VIEW IF EXISTS `test_view`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `test_view` AS select (select count(0) from `0_debtor_trans` where ((`0_debtor_trans`.`type` = 10) and (`0_debtor_trans`.`tran_date` = `b`.`tran_date`))) AS `invoice_count`,`b`.`tran_date` AS `tran_date`,(select count(`0_debtor_trans_details`.`id`) from (`0_debtor_trans` left join `0_debtor_trans_details` on((`0_debtor_trans`.`trans_no` = `0_debtor_trans_details`.`debtor_trans_no`))) where ((`0_debtor_trans_details`.`debtor_trans_type` = 10) and (`0_debtor_trans`.`type` = 10) and (`0_debtor_trans`.`tran_date` = `b`.`tran_date`))) AS `total_service_count`,(select sum((`0_debtor_trans`.`ov_amount` + `0_debtor_trans`.`ov_gst`)) from `0_debtor_trans` where ((`0_debtor_trans`.`type` = 10) and (`0_debtor_trans`.`tran_date` = `b`.`tran_date`))) AS `total_invoice_amount`,(select sum(`0_debtor_trans`.`alloc`) from `0_debtor_trans` where ((`0_debtor_trans`.`type` = 10) and (`0_debtor_trans`.`tran_date` = `b`.`tran_date`))) AS `total_amount_recieved`,(select (sum((`0_debtor_trans`.`ov_amount` + `0_debtor_trans`.`ov_gst`)) - sum(`0_debtor_trans`.`alloc`)) from `0_debtor_trans` where ((`0_debtor_trans`.`type` = 10) and (`0_debtor_trans`.`tran_date` = `b`.`tran_date`))) AS `pending_amount`,(select sum((`0_debtor_trans_details`.`quantity` * `0_debtor_trans_details`.`unit_price`)) from (`0_debtor_trans` left join `0_debtor_trans_details` on((`0_debtor_trans`.`trans_no` = `0_debtor_trans_details`.`debtor_trans_no`))) where ((`0_debtor_trans_details`.`debtor_trans_type` = 10) and (`0_debtor_trans`.`type` = 10) and (`0_debtor_trans`.`tran_date` = `b`.`tran_date`))) AS `total_service_charge`,(select sum((`0_debtor_trans_details`.`quantity` * `0_debtor_trans_details`.`user_commission`)) from (`0_debtor_trans` left join `0_debtor_trans_details` on((`0_debtor_trans`.`trans_no` = `0_debtor_trans_details`.`debtor_trans_no`))) where ((`0_debtor_trans_details`.`debtor_trans_type` = 10) and (`0_debtor_trans`.`type` = 10) and (`0_debtor_trans`.`tran_date` = `b`.`tran_date`))) AS `total_commission`,(select sum((`0_debtor_trans_details`.`quantity` * `0_debtor_trans_details`.`discount_amount`)) from (`0_debtor_trans` left join `0_debtor_trans_details` on((`0_debtor_trans`.`trans_no` = `0_debtor_trans_details`.`debtor_trans_no`))) where ((`0_debtor_trans_details`.`debtor_trans_type` = 10) and (`0_debtor_trans`.`type` = 10) and (`0_debtor_trans`.`tran_date` = `b`.`tran_date`))) AS `total_discount`,(select sum((`0_debtor_trans_details`.`quantity` * `0_debtor_trans_details`.`govt_fee`)) from (`0_debtor_trans` left join `0_debtor_trans_details` on((`0_debtor_trans`.`trans_no` = `0_debtor_trans_details`.`debtor_trans_no`))) where ((`0_debtor_trans_details`.`debtor_trans_type` = 10) and (`0_debtor_trans`.`type` = 10) and (`0_debtor_trans`.`tran_date` = `b`.`tran_date`))) AS `total_govt_fee`,(select sum(abs(`0_gl_trans`.`amount`)) from `0_gl_trans` where ((`0_gl_trans`.`type` = 12) and (`0_gl_trans`.`account` = 1200) and (`a`.`tran_date` = `0_gl_trans`.`tran_date`))) AS `total_collection` from ((`0_debtor_trans` `a` join `0_gl_trans` `b` on((`a`.`trans_no` = `b`.`type_no`))) join `0_debtor_trans_details` `c` on((`a`.`trans_no` = `c`.`debtor_trans_no`))) where ((`c`.`debtor_trans_type` = 10) and (`a`.`type` = 10)) group by `b`.`tran_date` ;



### Structure of table `transaction_report_view` ###

DROP VIEW IF EXISTS `transaction_report_view`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `transaction_report_view` AS select `0_chart_master`.`account_code` AS `account_code`,`0_bank_accounts`.`id` AS `bank_account_id`,`0_chart_master`.`account_name` AS `account_name`,`0_gl_trans`.`tran_date` AS `tran_date`,`0_gl_trans`.`amount` AS `amount`,(select `0_audit_trail`.`user` AS `user_id` from `0_audit_trail` where ((`0_audit_trail`.`trans_no` = `0_gl_trans`.`type_no`) and (`0_audit_trail`.`type` = `0_gl_trans`.`type`)) limit 1) AS `user_id`,`0_gl_trans`.`type_no` AS `type_no` from ((`0_chart_master` join `0_gl_trans` on((`0_gl_trans`.`account` = `0_chart_master`.`account_code`))) left join `0_bank_accounts` on((`0_bank_accounts`.`account_code` = `0_gl_trans`.`account`))) where ((`0_gl_trans`.`type` in (12,0,10)) and (`0_gl_trans`.`amount` <> 0)) order by `0_gl_trans`.`counter` desc ;



### Structure of table `voided_trans_reprt_view` ###

DROP VIEW IF EXISTS `voided_trans_reprt_view`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `voided_trans_reprt_view` AS select `c`.`reference` AS `reference`,`a`.`date_` AS `voided_date`,`a`.`trans_date` AS `trans_date`,`a`.`amount` AS `amount`,`a`.`memo_` AS `memo_`,`b`.`user_id` AS `voided_by`,`d`.`user_id` AS `transaction_done_by`,(case when (`a`.`type` = 10) then 'Sales Invoice' when (`a`.`type` = 12) then 'Customer Payment' when (`a`.`type` = 0) then 'Journal' end) AS `type` from (((`0_voided` `a` left join `0_users` `b` on((`b`.`id` = `a`.`created_by`))) left join `0_refs` `c` on(((`c`.`id` = `a`.`id`) and (`c`.`type` = `a`.`type`)))) left join `0_users` `d` on((`d`.`id` = `a`.`transaction_created_by`))) where (`a`.`type` in (1,0,10,12)) ;

