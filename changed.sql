-- phpMyAdmin SQL Dump
-- version 5.0.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 29, 2020 at 01:41 PM
-- Server version: 10.4.11-MariaDB
-- PHP Version: 7.2.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `egfm_db`
--

-- --------------------------------------------------------
-- 30-12-2020
--
-- Table structure for table `0_voided_bank_trans`
--

CREATE TABLE IF NOT EXISTS `0_voided_bank_trans` (
  `id` int(11) NOT NULL,
  `type` smallint(6) DEFAULT NULL,
  `trans_no` int(11) DEFAULT NULL,
  `bank_act` varchar(15) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `ref` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  `trans_date` date DEFAULT NULL,
  `amount` double DEFAULT NULL,
  `dimension_id` int(11) NOT NULL DEFAULT 0,
  `dimension2_id` int(11) NOT NULL DEFAULT 0,
  `person_type_id` int(11) NOT NULL DEFAULT 0,
  `person_id` tinyblob DEFAULT NULL,
  `reconciled` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `0_voided_customer_rewards`
--

CREATE TABLE IF NOT EXISTS `0_voided_customer_rewards` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `trans_no` int(11) DEFAULT 0,
  `detail_id` int(11) DEFAULT 0,
  `trans_type` int(11) DEFAULT 0,
  `tran_date` date DEFAULT NULL,
  `stock_id` varchar(256) DEFAULT '0',
  `reward_type` int(11) DEFAULT 1 COMMENT '1 - in, 2 - Out',
  `customer_id` int(11) DEFAULT 0,
  `qty` int(11) DEFAULT 1,
  `conversion_rate` double DEFAULT 0,
  `reward_point` double DEFAULT 0,
  `reward_amount` double DEFAULT 0,
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `trans_no` (`trans_no`),
  KEY `trans_type` (`trans_type`),
  KEY `tran_date` (`tran_date`),
  KEY `stock_id` (`stock_id`),
  KEY `reward_type` (`reward_type`),
  KEY `customer_id` (`customer_id`),
  KEY `created_by` (`created_by`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `0_voided_cust_allocations`
--

CREATE TABLE IF NOT EXISTS `0_voided_cust_allocations` (
  `id` int(11) NOT NULL,
  `person_id` int(11) DEFAULT NULL,
  `amt` double UNSIGNED DEFAULT NULL,
  `date_alloc` date DEFAULT NULL,
  `trans_no_from` int(11) DEFAULT NULL,
  `trans_type_from` int(11) DEFAULT NULL,
  `trans_no_to` int(11) DEFAULT NULL,
  `trans_type_to` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `0_voided_debtor_trans`
--

CREATE TABLE IF NOT EXISTS `0_voided_debtor_trans` (
  `trans_no` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `type` smallint(6) UNSIGNED NOT NULL DEFAULT 0,
  `version` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `debtor_no` int(11) UNSIGNED NOT NULL,
  `qms_token` varchar(128) COLLATE utf8_unicode_ci DEFAULT '',
  `qms_token_done` int(11) DEFAULT 0,
  `branch_code` int(11) NOT NULL DEFAULT -1,
  `tran_date` date DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `reference` varchar(60) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `barcode` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `tpe` int(11) NOT NULL DEFAULT 0,
  `order_` int(11) NOT NULL DEFAULT 0,
  `ov_amount` double NOT NULL DEFAULT 0,
  `ov_gst` double NOT NULL DEFAULT 0,
  `ov_freight` double NOT NULL DEFAULT 0,
  `ov_freight_tax` double NOT NULL DEFAULT 0,
  `ov_discount` double NOT NULL DEFAULT 0,
  `alloc` double NOT NULL DEFAULT 0,
  `prep_amount` double NOT NULL DEFAULT 0,
  `rate` double NOT NULL DEFAULT 1,
  `ship_via` int(11) DEFAULT NULL,
  `dimension_id` int(11) NOT NULL DEFAULT 0,
  `dimension2_id` int(11) NOT NULL DEFAULT 0,
  `payment_terms` int(11) DEFAULT NULL,
  `tax_included` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `display_customer` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `customer_trn` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `customer_mobile` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `customer_email` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `customer_ref` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `payment_method` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `credit_card_charge` varchar(255) COLLATE utf8_unicode_ci DEFAULT '0',
  `show_bank_charge` int(11) DEFAULT 0,
  `payment_flag` int(11) DEFAULT 0 COMMENT '0-Amer,1-Tasheel Edirham card, 2-Tasheel Customer Card,3-Amer Customer Card',
  `cust_emp_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `cust_emp_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `invoice_type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `inv_total` double DEFAULT 0,
  `customer_card_amount` double DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `0_voided_debtor_trans_details`
--

CREATE TABLE IF NOT EXISTS `0_voided_debtor_trans_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `debtor_trans_no` int(11) DEFAULT NULL,
  `debtor_trans_type` int(11) DEFAULT NULL,
  `stock_id` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `description` tinytext COLLATE utf8_unicode_ci DEFAULT NULL,
  `unit_price` double NOT NULL DEFAULT 0,
  `unit_tax` double NOT NULL DEFAULT 0,
  `quantity` double NOT NULL DEFAULT 0,
  `discount_percent` double NOT NULL DEFAULT 0,
  `discount_amount` double DEFAULT 0,
  `standard_cost` double NOT NULL DEFAULT 0,
  `qty_done` double NOT NULL DEFAULT 0,
  `src_id` int(11) NOT NULL,
  `govt_fee` double NOT NULL DEFAULT 0,
  `govt_bank_account` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `bank_service_charge` double NOT NULL DEFAULT 0,
  `bank_service_charge_vat` double NOT NULL DEFAULT 0,
  `pf_amount` double NOT NULL DEFAULT 0,
  `transaction_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ed_transaction_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `application_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `user_commission` double DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `ref_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `transaction_id_updated_at` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `Transaction` (`debtor_trans_type`,`debtor_trans_no`),
  KEY `src_id` (`src_id`),
  KEY `stock_id` (`stock_id`),
  KEY `quantity` (`quantity`),
  KEY `qty_done` (`qty_done`),
  KEY `transaction_id` (`transaction_id`),
  KEY `created_by` (`created_by`),
  KEY `updated_by` (`updated_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `0_voided_gl_trans`
--

CREATE TABLE IF NOT EXISTS `0_voided_gl_trans` (
  `counter` int(11) NOT NULL,
  `type` smallint(6) NOT NULL DEFAULT 0,
  `type_no` int(11) NOT NULL DEFAULT 0,
  `tran_date` date DEFAULT NULL,
  `account` varchar(15) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `axispro_subledger_code` varchar(15) COLLATE utf8_unicode_ci DEFAULT '0',
  `memo_` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `amount` double NOT NULL DEFAULT 0,
  `transaction_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `dimension_id` int(11) NOT NULL DEFAULT 0,
  `dimension2_id` int(11) NOT NULL DEFAULT 0,
  `person_type_id` int(11) DEFAULT NULL,
  `person_id` tinyblob DEFAULT NULL,
  `reconciled` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `0_voided_journal`
--

CREATE TABLE IF NOT EXISTS `0_voided_journal` (
  `type` smallint(6) NOT NULL DEFAULT 0,
  `trans_no` int(11) NOT NULL DEFAULT 0,
  `tran_date` date DEFAULT NULL,
  `reference` varchar(60) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `source_ref` varchar(60) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `event_date` date DEFAULT NULL,
  `doc_date` date DEFAULT NULL,
  `currency` char(3) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `amount` double NOT NULL DEFAULT 0,
  `rate` double NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `0_voided_purch_orders`
--

CREATE TABLE IF NOT EXISTS `0_voided_purch_orders` (
  `order_no` int(11) NOT NULL,
  `supplier_id` int(11) NOT NULL DEFAULT 0,
  `comments` tinytext COLLATE utf8_unicode_ci DEFAULT NULL,
  `ord_date` date DEFAULT NULL,
  `reference` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `requisition_no` tinytext COLLATE utf8_unicode_ci DEFAULT NULL,
  `into_stock_location` varchar(5) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `delivery_address` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `total` double NOT NULL DEFAULT 0,
  `prep_amount` double NOT NULL DEFAULT 0,
  `alloc` double NOT NULL DEFAULT 0,
  `tax_included` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `0_voided_sales_orders`
--

CREATE TABLE IF NOT EXISTS `0_voided_sales_orders` (
  `order_no` int(11) NOT NULL,
  `trans_type` smallint(6) NOT NULL DEFAULT 30,
  `version` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `type` tinyint(1) NOT NULL DEFAULT 0,
  `debtor_no` int(11) NOT NULL DEFAULT 0,
  `branch_code` int(11) NOT NULL DEFAULT 0,
  `reference` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `customer_ref` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `comments` tinytext COLLATE utf8_unicode_ci DEFAULT NULL,
  `ord_date` date DEFAULT NULL,
  `order_type` int(11) NOT NULL DEFAULT 0,
  `ship_via` int(11) NOT NULL DEFAULT 0,
  `delivery_address` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `contact_phone` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
  `contact_email` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `deliver_to` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `freight_cost` double NOT NULL DEFAULT 0,
  `from_stk_loc` varchar(5) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `delivery_date` date DEFAULT NULL,
  `payment_terms` int(11) DEFAULT NULL,
  `total` double NOT NULL DEFAULT 0,
  `prep_amount` double NOT NULL DEFAULT 0,
  `alloc` double NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `0_voided_sales_order_details`
--

CREATE TABLE IF NOT EXISTS `0_voided_sales_order_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_no` int(11) NOT NULL DEFAULT 0,
  `trans_type` smallint(6) NOT NULL DEFAULT 30,
  `stk_code` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `description` tinytext COLLATE utf8_unicode_ci DEFAULT NULL,
  `qty_sent` double NOT NULL DEFAULT 0,
  `unit_price` double NOT NULL DEFAULT 0,
  `quantity` double NOT NULL DEFAULT 0,
  `invoiced` double NOT NULL DEFAULT 0,
  `discount_percent` double NOT NULL DEFAULT 0,
  `discount_amount` double DEFAULT 0,
  `govt_fee` double DEFAULT 0,
  `bank_service_charge` double DEFAULT 0,
  `bank_service_charge_vat` double DEFAULT 0,
  `pf_amount` double DEFAULT 0,
  `transaction_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ed_transaction_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `application_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `govt_bank_account` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ref_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sorder` (`trans_type`,`order_no`),
  KEY `stkcode` (`stk_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `0_voided_stock_moves`
--

CREATE TABLE IF NOT EXISTS `0_voided_stock_moves` (
  `trans_id` int(11) NOT NULL,
  `trans_no` int(11) NOT NULL DEFAULT 0,
  `stock_id` char(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `type` smallint(6) NOT NULL DEFAULT 0,
  `loc_code` char(5) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `tran_date` date DEFAULT NULL,
  `price` double NOT NULL DEFAULT 0,
  `reference` char(40) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `qty` double NOT NULL DEFAULT 1,
  `standard_cost` double NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `0_voided_supp_allocations`
--

CREATE TABLE IF NOT EXISTS `0_voided_supp_allocations` (
  `id` int(11) NOT NULL,
  `person_id` int(11) DEFAULT NULL,
  `amt` double UNSIGNED DEFAULT NULL,
  `date_alloc` date DEFAULT NULL,
  `trans_no_from` int(11) DEFAULT NULL,
  `trans_type_from` int(11) DEFAULT NULL,
  `trans_no_to` int(11) DEFAULT NULL,
  `trans_type_to` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `0_voided_supp_trans`
--

CREATE TABLE IF NOT EXISTS `0_voided_supp_trans` (
  `trans_no` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `type` smallint(6) UNSIGNED NOT NULL DEFAULT 0,
  `supplier_id` int(11) UNSIGNED NOT NULL,
  `reference` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `supp_reference` varchar(60) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `tran_date` date DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `ov_amount` double NOT NULL DEFAULT 0,
  `ov_discount` double NOT NULL DEFAULT 0,
  `ov_gst` double NOT NULL DEFAULT 0,
  `rate` double NOT NULL DEFAULT 1,
  `alloc` double NOT NULL DEFAULT 0,
  `tax_included` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `0_voided_trans_tax_details`
--

CREATE TABLE IF NOT EXISTS `0_voided_trans_tax_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `trans_type` smallint(6) DEFAULT NULL,
  `trans_no` int(11) DEFAULT NULL,
  `tran_date` date NOT NULL,
  `tax_type_id` int(11) NOT NULL DEFAULT 0,
  `rate` double NOT NULL DEFAULT 0,
  `ex_rate` double NOT NULL DEFAULT 1,
  `included_in_price` tinyint(1) NOT NULL DEFAULT 0,
  `net_amount` double NOT NULL DEFAULT 0,
  `amount` double NOT NULL DEFAULT 0,
  `memo` tinytext COLLATE utf8_unicode_ci DEFAULT NULL,
  `reg_type` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `Type_and_Number` (`trans_type`,`trans_no`),
  KEY `tran_date` (`tran_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------
--
-- Table structure for table `0_voided_customer_rewards`
--

CREATE TABLE IF NOT EXISTS `0_voided_customer_rewards` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `trans_no` int(11) DEFAULT 0,
  `detail_id` int(11) DEFAULT 0,
  `trans_type` int(11) DEFAULT 0,
  `tran_date` date DEFAULT NULL,
  `stock_id` varchar(256) DEFAULT '0',
  `reward_type` int(11) DEFAULT 1 COMMENT '1 - in, 2 - Out',
  `customer_id` int(11) DEFAULT 0,
  `qty` int(11) DEFAULT 1,
  `conversion_rate` double DEFAULT 0,
  `reward_point` double DEFAULT 0,
  `reward_amount` double DEFAULT 0,
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `trans_no` (`trans_no`),
  KEY `trans_type` (`trans_type`),
  KEY `tran_date` (`tran_date`),
  KEY `stock_id` (`stock_id`),
  KEY `reward_type` (`reward_type`),
  KEY `customer_id` (`customer_id`),
  KEY `created_by` (`created_by`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `0_voided_sales_order_details`
--

CREATE TABLE IF NOT EXISTS `0_voided_sales_order_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_no` int(11) NOT NULL DEFAULT 0,
  `trans_type` smallint(6) NOT NULL DEFAULT 30,
  `stk_code` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `description` tinytext COLLATE utf8_unicode_ci DEFAULT NULL,
  `qty_sent` double NOT NULL DEFAULT 0,
  `unit_price` double NOT NULL DEFAULT 0,
  `quantity` double NOT NULL DEFAULT 0,
  `invoiced` double NOT NULL DEFAULT 0,
  `discount_percent` double NOT NULL DEFAULT 0,
  `discount_amount` double DEFAULT 0,
  `govt_fee` double DEFAULT 0,
  `bank_service_charge` double DEFAULT 0,
  `bank_service_charge_vat` double DEFAULT 0,
  `pf_amount` double DEFAULT 0,
  `transaction_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ed_transaction_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `application_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `govt_bank_account` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ref_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sorder` (`trans_type`,`order_no`),
  KEY `stkcode` (`stk_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `0_voided_stock_moves`
--

CREATE TABLE IF NOT EXISTS `0_voided_stock_moves` (
  `trans_id` int(11) NOT NULL,
  `trans_no` int(11) NOT NULL DEFAULT 0,
  `stock_id` char(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `type` smallint(6) NOT NULL DEFAULT 0,
  `loc_code` char(5) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `tran_date` date NOT NULL,
  `price` double NOT NULL DEFAULT 0,
  `reference` char(40) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `qty` double NOT NULL DEFAULT 1,
  `standard_cost` double NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
-- --------------------------------------------------------

--
-- Table structure for table `0_voided_supp_invoice_items`
--

CREATE TABLE IF NOT EXISTS `0_voided_supp_invoice_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `supp_trans_no` int(11) DEFAULT NULL,
  `supp_trans_type` int(11) DEFAULT NULL,
  `gl_code` varchar(15) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `grn_item_id` int(11) DEFAULT NULL,
  `po_detail_item_id` int(11) DEFAULT NULL,
  `stock_id` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `description` tinytext COLLATE utf8_unicode_ci DEFAULT NULL,
  `quantity` double NOT NULL DEFAULT 0,
  `unit_price` double NOT NULL DEFAULT 0,
  `unit_tax` double NOT NULL DEFAULT 0,
  `memo_` tinytext COLLATE utf8_unicode_ci DEFAULT NULL,
  `dimension_id` int(11) NOT NULL DEFAULT 0,
  `dimension2_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `Transaction` (`supp_trans_type`,`supp_trans_no`,`stock_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
-- --------------------------------------------------------

--
-- Table structure for table `0_voided_purch_order_details`
--

CREATE TABLE IF NOT EXISTS `0_voided_purch_order_details` (
  `po_detail_item` int(11) NOT NULL,
  `order_no` int(11) NOT NULL DEFAULT 0,
  `item_code` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `description` tinytext COLLATE utf8_unicode_ci DEFAULT NULL,
  `delivery_date` date NOT NULL,
  `qty_invoiced` double NOT NULL DEFAULT 0,
  `unit_price` double NOT NULL DEFAULT 0,
  `act_price` double NOT NULL DEFAULT 0,
  `std_cost_unit` double NOT NULL DEFAULT 0,
  `quantity_ordered` double NOT NULL DEFAULT 0,
  `quantity_received` double NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
-- --------------------------------------------------------

--
-- Table structure for table `0_voided_grn_items`
--

CREATE TABLE IF NOT EXISTS `0_voided_grn_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `grn_batch_id` int(11) DEFAULT NULL,
  `po_detail_item` int(11) NOT NULL DEFAULT 0,
  `item_code` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `description` tinytext COLLATE utf8_unicode_ci DEFAULT NULL,
  `qty_recd` double NOT NULL DEFAULT 0,
  `quantity_inv` double NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `grn_batch_id` (`grn_batch_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `0_voided_debtor_trans` ADD UNIQUE KEY (trans_no,type);
ALTER TABLE `0_voided_supp_trans` ADD UNIQUE KEY (type,trans_no,supplier_id);
ALTER TABLE `0_voided_purch_orders` ADD UNIQUE(`order_no`);
ALTER TABLE `0_voided_sales_orders` ADD UNIQUE KEY (trans_type,order_no);
ALTER TABLE `0_voided_supp_allocations` ADD UNIQUE(`id`);
ALTER TABLE `0_voided_supp_allocations` ADD UNIQUE KEY (person_id,trans_type_from,trans_no_from,trans_type_to,trans_no_to);
ALTER TABLE `0_voided_debtor_trans` ADD UNIQUE KEY (type,trans_no,debtor_no);
ALTER TABLE `0_voided_bank_trans` ADD UNIQUE(`id`);
ALTER TABLE `0_voided_customer_rewards` CHANGE `id` `id` INT(11) NOT NULL;
ALTER TABLE `0_voided_cust_allocations` ADD UNIQUE(`id`);
ALTER TABLE `0_voided_cust_allocations` ADD UNIQUE KEY (person_id,trans_type_from,trans_no_from,trans_type_to,trans_no_to);
ALTER TABLE `0_voided_debtor_trans_details` CHANGE `id` `id` INT(11) NOT NULL;
ALTER TABLE `0_voided_gl_trans` ADD UNIQUE(`counter`);
ALTER TABLE `0_voided_grn_items` CHANGE `id` `id` INT(11) NOT NULL;
ALTER TABLE `0_voided_journal` ADD UNIQUE KEY (type,trans_no);
ALTER TABLE `0_voided_purch_order_details` ADD UNIQUE(`po_detail_item`);
ALTER TABLE `0_voided_sales_order_details` CHANGE `id` `id` INT(11) NOT NULL;
ALTER TABLE `0_voided_stock_moves` ADD UNIQUE(`trans_id`);
ALTER TABLE `0_voided_supp_invoice_items` CHANGE `id` `id` INT(11) NOT NULL;
ALTER TABLE `0_voided_trans_tax_details` CHANGE `id` `id` INT(11) NOT NULL;

-- YBC Specific
ALTER TABLE `0_voided_bank_trans` ADD `payment_type` INT NULL AFTER `reconciled`, ADD `cheq_no` VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL AFTER `payment_type`, ADD `cheq_date` DATE NULL AFTER `cheq_no`, ADD `created_by` INT NULL AFTER `cheq_date`;
ALTER TABLE `0_voided_debtor_trans` ADD `created_by` INT(11) NULL AFTER `customer_card_amount`, ADD `payment_type` INT(11) NULL AFTER `created_by`, ADD `cheq_no` VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL AFTER `payment_type`, ADD `cheq_date` DATE NOT NULL AFTER `cheq_no`;

ALTER TABLE `0_voided_purch_orders` ADD `pay_terms` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL AFTER `tax_included`, ADD `quote_file` VARCHAR(200) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '0' AFTER `pay_terms`, ADD `terms_and_cond` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL AFTER `quote_file`;
ALTER TABLE `0_voided_gl_trans` ADD `created_by` INT NULL AFTER `reconciled`;
ALTER TABLE `0_voided_debtor_trans` ADD `contact_person` VARCHAR(80) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL AFTER `cheq_date`, ADD `round_of_amount` DOUBLE NOT NULL DEFAULT '0' AFTER `contact_person`;
ALTER TABLE `0_voided_debtor_trans` DROP `qms_token`, DROP `qms_token_done`;
ALTER TABLE `0_voided_debtor_trans` CHANGE `round_of_amount` `round_of_amount` DOUBLE NOT NULL DEFAULT '0' AFTER `prep_amount`, CHANGE `contact_person` `contact_person` VARCHAR(80) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL AFTER `display_customer`;
ALTER TABLE `0_voided_purch_orders` CHANGE `terms_and_cond` `terms_and_cond` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL AFTER `comments`, CHANGE `pay_terms` `pay_terms` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL AFTER `terms_and_cond`, CHANGE `quote_file` `quote_file` VARCHAR(200) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '\'0\'' AFTER `reference`;
ALTER TABLE `0_voided_debtor_trans` DROP INDEX `trans_no`;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;