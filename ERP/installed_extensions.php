<?php

/* List of installed additional extensions. If extensions are added to the list manually
	make sure they have unique and so far never used extension_ids as a keys,
	and $next_extension_id is also updated. More about format of this file yo will find in 
	FA extension system documentation.
*/

$next_extension_id = 16; // unique id for next installed extension
$installed_extensions = [];
// $installed_extensions = array (
//   1 => 
//   array (
//     'name' => 'Bank Statement w/ Reconcile',
//     'package' => 'rep_statement_reconcile',
//     'version' => '2.4.0-1',
//     'type' => 'extension',
//     'active' => false,
//     'path' => 'modules/rep_statement_reconcile',
//   ),
//   2 => 
//   array (
//     'name' => 'Report Generator',
//     'package' => 'repgen',
//     'version' => '2.4.0-4',
//     'type' => 'extension',
//     'active' => false,
//     'path' => 'modules/repgen',
//   ),
//   3 => 
//   array (
//     'name' => 'Theme Exclusive for Dashboard',
//     'package' => 'exclusive_db',
//     'version' => '2.4.0-1',
//     'type' => 'theme',
//     'active' => false,
//     'path' => 'themes/exclusive_db',
//   ),
//   4 => 
//   array (
//     'name' => 'Theme Fancy',
//     'package' => 'fancy',
//     'version' => '2.4.0-1',
//     'type' => 'theme',
//     'active' => false,
//     'path' => 'themes/fancy',
//   ),
//   6 => 
//   array (
//     'package' => 'Charts',
//     'name' => 'Charts',
//     'version' => '-',
//     'available' => '',
//     'type' => 'extension',
//     'path' => 'modules/Charts',
//     'active' => false,
//   ),
//   8 => 
//   array (
//     'package' => 'dashboard',
//     'name' => 'Company Dashboard',
//     'version' => '2.4.0-1',
//     'available' => '',
//     'type' => 'extension',
//     'path' => 'modules/dashboard',
//     'active' => false,
//   ),
//   9 => 
//   array (
//     'name' => 'Theme Exclusive',
//     'package' => 'exclusive',
//     'version' => '2.4.0-1',
//     'type' => 'theme',
//     'active' => false,
//     'path' => 'themes/exclusive',
//   ),
//   10 => 
//   array (
//     'package' => 'premium',
//     'name' => 'premium',
//     'version' => '-',
//     'available' => '',
//     'type' => 'extension',
//     'path' => 'modules/premium',
//     'active' => false,
//   ),
//   11 => 
//   array (
//     'name' => 'Sales Summary Report',
//     'package' => 'rep_sales_summary',
//     'version' => '2.4.0-1',
//     'type' => 'extension',
//     'active' => false,
//     'path' => 'modules/rep_sales_summary',
//   ),
//   12 => 
//   array (
//     'name' => 'Cash Flow Statement Report',
//     'package' => 'rep_cash_flow_statement',
//     'version' => '2.4.0-1',
//     'type' => 'extension',
//     'active' => false,
//     'path' => 'modules/rep_cash_flow_statement',
//   ),
//   14 => 
//   array (
//     'package' => 'rep_customer_ledger',
//     'name' => 'Customer Ledger Report',
//     'version' => '2.4.1-2',
//     'available' => '',
//     'type' => 'extension',
//     'path' => 'modules/rep_customer_ledger',
//     'active' => false,
//   ),
// );
