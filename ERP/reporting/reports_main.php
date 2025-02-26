<?php
/**********************************************************************
    Direct Axis Technology L.L.C.
	Released under the terms of the GNU General Public License, GPL,
	as published by the Free Software Foundation, either version 3
	of the License, or (at your option) any later version.
    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
    See the License here <http://www.gnu.org/licenses/gpl-3.0.html>.
***********************************************************************/
$path_to_root="..";
$page_security = 'SA_OPEN';
include_once($path_to_root . "/includes/session.inc");

include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/data_checks.inc");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/reporting/includes/reports_classes.inc");
$js = "";
if ($SysPrefs->use_popup_windows && $SysPrefs->use_popup_search)
	$js .= get_js_open_window(900, 500);
if (user_use_date_picker())
	$js .= get_js_date_picker();

add_js_file('reports.js');

page(trans($help_context = "Reports and Analysis"), false, false, "", $js);

$reports = new BoxReports;

$dim = get_company_pref('use_dimension');


//Added for AMER
//$reports->addReportClass(trans('AMER Reports'), RC_AMER_REPORT);
//$reports->addReport(RC_AMER_REPORT, 1000, trans('Commission Report'), array());
//$reports->addReport(RC_AMER_REPORT, 1001, trans('Discount Report'), array());



$reports->addReportClass(trans('Customer'), RC_CUSTOMER);
$reports->addReport(RC_CUSTOMER, 101, trans('Customer &Balances'),
	array(	trans('Start Date') => 'DATEBEGIN',
			trans('End Date') => 'DATEENDM',
			trans('Customer') => 'CUSTOMERS',
			trans('Show Balance') => 'YES_NO',
			trans('Currency Filter') => 'CURRENCY',
			trans('Suppress Zeros') => 'YES_NO',
			trans('Comments') => 'TEXTBOX',
			trans('Orientation') => 'ORIENTATION',
			trans('Destination') => 'DESTINATION'));


$reports->addReport(RC_CUSTOMER, 1080, trans('Customer Report(Service Wise)'),
    array(	trans('Start Date') => 'DATEBEGIN',
        trans('End Date') => 'DATEENDM',
        trans('Customer') => 'CUSTOMERS',
        trans('Destination') => 'DESTINATION'));


$reports->addReport(RC_CUSTOMER, 102, trans('&Aged Customer Analysis'),
	array(	trans('End Date') => 'DATE',
			trans('Customer') => 'CUSTOMERS_NO_FILTER',
			trans('Currency Filter') => 'CURRENCY',
			trans('Show Also Allocated') => 'YES_NO',
			trans('Summary Only') => 'YES_NO',
			trans('Suppress Zeros') => 'YES_NO',
			trans('Graphics') => 'GRAPHIC',
			trans('Comments') => 'TEXTBOX',
			trans('Orientation') => 'ORIENTATION',
			trans('Destination') => 'DESTINATION'));
$reports->addReport(RC_CUSTOMER, 115, trans('Customer Trial Balance'),
    array(  trans('Start Date') => 'DATEBEGIN',
            trans('End Date') => 'DATEENDM',
            trans('Customer') => 'CUSTOMERS_NO_FILTER',
            trans('Sales Areas') => 'AREAS',
            trans('Sales Folk') => 'SALESMEN',
            trans('Currency Filter') => 'CURRENCY',
            trans('Suppress Zeros') => 'YES_NO',
            trans('Comments') => 'TEXTBOX',
            trans('Orientation') => 'ORIENTATION',
            trans('Destination') => 'DESTINATION'));
$reports->addReport(RC_CUSTOMER, 103, trans('Customer &Detail Listing'),
	array(	trans('Activity Since') => 'DATEBEGIN',
			trans('Sales Areas') => 'AREAS',
			trans('Sales Folk') => 'SALESMEN',
			trans('Activity Greater Than') => 'TEXT',
			trans('Activity Less Than') => 'TEXT',
			trans('Comments') => 'TEXTBOX',
			trans('Orientation') => 'ORIENTATION',
			trans('Destination') => 'DESTINATION'));
$reports->addReport(RC_CUSTOMER, 114, trans('Sales &Summary Report'),
	array(	trans('Start Date') => 'DATEBEGINTAX',
			trans('End Date') => 'DATEENDTAX',
			trans('Tax Id Only') => 'YES_NO',
			trans('Comments') => 'TEXTBOX',
			trans('Orientation') => 'ORIENTATION',
			trans('Destination') => 'DESTINATION'));
$reports->addReport(RC_CUSTOMER, 104, trans('&Price Listing'),
	array(	trans('Currency Filter') => 'CURRENCY',
			trans('Inventory Category') => 'CATEGORIES',
			trans('Sales Types') => 'SALESTYPES',
			trans('Show Pictures') => 'YES_NO',
			trans('Show GP %') => 'YES_NO',
			trans('Comments') => 'TEXTBOX',
			trans('Orientation') => 'ORIENTATION',
			trans('Destination') => 'DESTINATION'));
$reports->addReport(RC_CUSTOMER, 105, trans('&Order Status Listing'),
	array(	trans('Start Date') => 'DATEBEGINM',
			trans('End Date') => 'DATEENDM',
			trans('Inventory Category') => 'CATEGORIES',
			trans('Stock Location') => 'LOCATIONS',
			trans('Back Orders Only') => 'YES_NO',
			trans('Comments') => 'TEXTBOX',
			trans('Orientation') => 'ORIENTATION',
			trans('Destination') => 'DESTINATION'));
$reports->addReport(RC_CUSTOMER, 106, trans('&Salesman Listing'),
	array(	trans('Start Date') => 'DATEBEGINM',
			trans('End Date') => 'DATEENDM',
			trans('Summary Only') => 'YES_NO',
			trans('Comments') => 'TEXTBOX',
			trans('Orientation') => 'ORIENTATION',
			trans('Destination') => 'DESTINATION'));
$reports->addReport(RC_CUSTOMER, 107, trans('Print &Invoices'),
	array(	trans('From') => 'INVOICE',
			trans('To') => 'INVOICE',
			trans('Currency Filter') => 'CURRENCY',
			trans('email Customers') => 'YES_NO',
			trans('Payment Link') => 'PAYMENT_LINK',
			trans('Comments') => 'TEXTBOX',
			trans('Customer') => 'CUSTOMERS_NO_FILTER',
			trans('Orientation') => 'ORIENTATION'
));
$reports->addReport(RC_CUSTOMER, 113, trans('Print &Credit Notes'),
	array(	trans('From') => 'CREDIT',
			trans('To') => 'CREDIT',
			trans('Currency Filter') => 'CURRENCY',
			trans('email Customers') => 'YES_NO',
			trans('Payment Link') => 'PAYMENT_LINK',
			trans('Comments') => 'TEXTBOX',
			trans('Orientation') => 'ORIENTATION'));
$reports->addReport(RC_CUSTOMER, 110, trans('Print &Deliveries'),
	array(	trans('From') => 'DELIVERY',
			trans('To') => 'DELIVERY',
			trans('email Customers') => 'YES_NO',
			trans('Print as Packing Slip') => 'YES_NO',
			trans('Comments') => 'TEXTBOX',
			trans('Orientation') => 'ORIENTATION'));
$reports->addReport(RC_CUSTOMER, 108, trans('Print &Statements'),
	array(	trans('Customer') => 'CUSTOMERS_NO_FILTER',
			trans('Currency Filter') => 'CURRENCY',
			trans('Show Also Allocated') => 'YES_NO',
			trans('Email Customers') => 'YES_NO',
			trans('Comments') => 'TEXTBOX',
			trans('Orientation') => 'ORIENTATION'));
$reports->addReport(RC_CUSTOMER, 109, trans('&Print Sales Orders'),
	array(	trans('From') => 'ORDERS',
			trans('To') => 'ORDERS',
			trans('Currency Filter') => 'CURRENCY',
			trans('Email Customers') => 'YES_NO',
			trans('Print as Quote') => 'YES_NO',
			trans('Comments') => 'TEXTBOX',
			trans('Orientation') => 'ORIENTATION'));
$reports->addReport(RC_CUSTOMER, 111, trans('&Print Sales Quotations'),
	array(	trans('From') => 'QUOTATIONS',
			trans('To') => 'QUOTATIONS',
			trans('Currency Filter') => 'CURRENCY',
			trans('Email Customers') => 'YES_NO',
			trans('Comments') => 'TEXTBOX',
			trans('Orientation') => 'ORIENTATION'));
$reports->addReport(RC_CUSTOMER, 112, trans('Print Receipts'),
	array(	trans('From') => 'RECEIPT',
			trans('To') => 'RECEIPT',
			trans('Currency Filter') => 'CURRENCY',
			trans('Email Customers') => 'YES_NO',
			trans('Comments') => 'TEXTBOX',
			trans('Orientation') => 'ORIENTATION'));

$reports->addReportClass(trans('Supplier'), RC_SUPPLIER);
$reports->addReport(RC_SUPPLIER, 201, trans('Supplier &Balances'),
	array(	trans('Start Date') => 'DATEBEGIN',
			trans('End Date') => 'DATEENDM',
			trans('Supplier') => 'SUPPLIERS_NO_FILTER',
			trans('Show Balance') => 'YES_NO',
			trans('Currency Filter') => 'CURRENCY',
			trans('Suppress Zeros') => 'YES_NO',
			trans('Comments') => 'TEXTBOX',
			trans('Orientation') => 'ORIENTATION',
			trans('Destination') => 'DESTINATION'));
$reports->addReport(RC_SUPPLIER, 202, trans('&Aged Supplier Analyses'),
	array(	trans('End Date') => 'DATE',
			trans('Supplier') => 'SUPPLIERS_NO_FILTER',
			trans('Currency Filter') => 'CURRENCY',
			trans('Show Also Allocated') => 'YES_NO',
			trans('Summary Only') => 'YES_NO',
			trans('Suppress Zeros') => 'YES_NO',
			trans('Graphics') => 'GRAPHIC',
			trans('Comments') => 'TEXTBOX',
			trans('Orientation') => 'ORIENTATION',
			trans('Destination') => 'DESTINATION'));
$reports->addReport(RC_SUPPLIER, 206, trans('Supplier &Trial Balances'),
    array(  trans('Start Date') => 'DATEBEGIN',
            trans('End Date') => 'DATEENDM',
            trans('Supplier') => 'SUPPLIERS_NO_FILTER',
            trans('Currency Filter') => 'CURRENCY',
            trans('Suppress Zeros') => 'YES_NO',
            trans('Comments') => 'TEXTBOX',
            trans('Orientation') => 'ORIENTATION',
            trans('Destination') => 'DESTINATION'));
$reports->addReport(RC_SUPPLIER, 203, trans('&Payment Report'),
	array(	trans('End Date') => 'DATE',
			trans('Supplier') => 'SUPPLIERS_NO_FILTER',
			trans('Currency Filter') => 'CURRENCY',
			trans('Suppress Zeros') => 'YES_NO',
			trans('Comments') => 'TEXTBOX',
			trans('Orientation') => 'ORIENTATION',
			trans('Destination') => 'DESTINATION'));
$reports->addReport(RC_SUPPLIER, 204, trans('Outstanding &GRNs Report'),
	array(	trans('Supplier') => 'SUPPLIERS_NO_FILTER',
			trans('Comments') => 'TEXTBOX',
			trans('Orientation') => 'ORIENTATION',
			trans('Destination') => 'DESTINATION'));
$reports->addReport(RC_SUPPLIER, 205, trans('Supplier &Detail Listing'),
	array(	trans('Activity Since') => 'DATEBEGIN',
			trans('Activity Greater Than') => 'TEXT',
			trans('Activity Less Than') => 'TEXT',
			trans('Comments') => 'TEXTBOX',
			trans('Orientation') => 'ORIENTATION',
			trans('Destination') => 'DESTINATION'));
$reports->addReport(RC_SUPPLIER, 209, trans('Print Purchase &Orders'),
	array(	trans('From') => 'PO',
			trans('To') => 'PO',
			trans('Currency Filter') => 'CURRENCY',
			trans('Email Suppliers') => 'YES_NO',
			trans('Comments') => 'TEXTBOX',
			trans('Orientation') => 'ORIENTATION'));
$reports->addReport(RC_SUPPLIER, 210, trans('Print Remi&ttances'),
	array(	trans('From') => 'REMITTANCE',
			trans('To') => 'REMITTANCE',
			trans('Currency Filter') => 'CURRENCY',
			trans('Email Suppliers') => 'YES_NO',
			trans('Comments') => 'TEXTBOX',
			trans('Orientation') => 'ORIENTATION'));

$reports->addReportClass(trans('Inventory'), RC_INVENTORY);
$reports->addReport(RC_INVENTORY,  301, trans('Inventory &Valuation Report'),
	array(	trans('End Date') => 'DATE',
			trans('Inventory Category') => 'CATEGORIES',
			trans('Location') => 'LOCATIONS',
			trans('Summary Only') => 'YES_NO',
			trans('Comments') => 'TEXTBOX',
			trans('Orientation') => 'ORIENTATION',
			trans('Destination') => 'DESTINATION'));
$reports->addReport(RC_INVENTORY,  302, trans('Inventory &Planning Report'),
	array(	trans('Inventory Category') => 'CATEGORIES',
			trans('Location') => 'LOCATIONS',
			trans('Comments') => 'TEXTBOX',
			trans('Orientation') => 'ORIENTATION',
			trans('Destination') => 'DESTINATION'));
$reports->addReport(RC_INVENTORY, 303, trans('Stock &Check Sheets'),
	array(	trans('Inventory Category') => 'CATEGORIES',
			trans('Location') => 'LOCATIONS',
			trans('Show Pictures') => 'YES_NO',
			trans('Inventory Column') => 'YES_NO',
			trans('Show Only Shortages') => 'YES_NO',
			trans('Suppress Zeros') => 'YES_NO',
			trans('Item Like') => 'TEXT',
			trans('Comments') => 'TEXTBOX',
			trans('Orientation') => 'ORIENTATION',
			trans('Destination') => 'DESTINATION'));
$reports->addReport(RC_INVENTORY, 304, trans('Inventory &Sales Report'),
	array(	trans('Start Date') => 'DATEBEGINM',
			trans('End Date') => 'DATEENDM',
			trans('Inventory Category') => 'CATEGORIES',
			trans('Location') => 'LOCATIONS',
			trans('Customer') => 'CUSTOMERS_NO_FILTER',
			trans('Show Service Items') => 'YES_NO',
			trans('Comments') => 'TEXTBOX',
			trans('Orientation') => 'ORIENTATION',
			trans('Destination') => 'DESTINATION'));
$reports->addReport(RC_INVENTORY, 305, trans('&GRN Valuation Report'),
	array(	trans('Start Date') => 'DATEBEGINM',
			trans('End Date') => 'DATEENDM',
			trans('Comments') => 'TEXTBOX',
			trans('Orientation') => 'ORIENTATION',
			trans('Destination') => 'DESTINATION'));
$reports->addReport(RC_INVENTORY, 306, trans('Inventory P&urchasing Report'),
	array(	trans('Start Date') => 'DATEBEGINM',
			trans('End Date') => 'DATEENDM',
			trans('Inventory Category') => 'CATEGORIES',
			trans('Location') => 'LOCATIONS',
			trans('Supplier') => 'SUPPLIERS_NO_FILTER',
			trans('Items') => 'ITEMS_P',
			trans('Comments') => 'TEXTBOX',
			trans('Orientation') => 'ORIENTATION',
			trans('Destination') => 'DESTINATION'));
$reports->addReport(RC_INVENTORY, 307, trans('Inventory &Movement Report'),
	array(	trans('Start Date') => 'DATEBEGINM',
			trans('End Date') => 'DATEENDM',
			trans('Inventory Category') => 'CATEGORIES',
			trans('Location') => 'LOCATIONS',
			trans('Comments') => 'TEXTBOX',
			trans('Orientation') => 'ORIENTATION',
			trans('Destination') => 'DESTINATION'));
$reports->addReport(RC_INVENTORY, 308, trans('C&osted Inventory Movement Report'),
	array(	trans('Start Date') => 'DATEBEGINM',
			trans('End Date') => 'DATEENDM',
			trans('Inventory Category') => 'CATEGORIES',
			trans('Location') => 'LOCATIONS',
			trans('Comments') => 'TEXTBOX',
			trans('Orientation') => 'ORIENTATION',
			trans('Destination') => 'DESTINATION'));
$reports->addReport(RC_INVENTORY, 309,trans('Item &Sales Summary Report'),
	array(	trans('Start Date') => 'DATEBEGINM',
			trans('End Date') => 'DATEENDM',
			trans('Inventory Category') => 'CATEGORIES',
			trans('Comments') => 'TEXTBOX',
			trans('Orientation') => 'ORIENTATION',
			trans('Destination') => 'DESTINATION'));
$reports->addReport(RC_INVENTORY, 310, trans('Inventory Purchasing - &Transaction Based'),
	array(	trans('Start Date') => 'DATEBEGINM',
			trans('End Date') => 'DATEENDM',
			trans('Inventory Category') => 'CATEGORIES',
			trans('Location') => 'LOCATIONS',
			trans('Supplier') => 'SUPPLIERS_NO_FILTER',
			trans('Items') => 'ITEMS_P',
			trans('Comments') => 'TEXTBOX',
			trans('Orientation') => 'ORIENTATION',
			trans('Destination') => 'DESTINATION'));
if (get_company_pref('use_manufacturing'))
{
	$reports->addReportClass(trans('Manufacturing'), RC_MANUFACTURE);
	$reports->addReport(RC_MANUFACTURE, 401, trans('&Bill of Material Listing'),
		array(	trans('From product') => 'ITEMS',
				trans('To product') => 'ITEMS',
				trans('Comments') => 'TEXTBOX',
				trans('Orientation') => 'ORIENTATION',
				trans('Destination') => 'DESTINATION'));
	$reports->addReport(RC_MANUFACTURE, 402, trans('Work Order &Listing'),
		array(	trans('Items') => 'ITEMS_ALL',
				trans('Location') => 'LOCATIONS',
				trans('Outstanding Only') => 'YES_NO',
				trans('Show GL Rows') => 'YES_NO',
				trans('Comments') => 'TEXTBOX',
				trans('Orientation') => 'ORIENTATION',
				trans('Destination') => 'DESTINATION'));
	$reports->addReport(RC_MANUFACTURE, 409, trans('Print &Work Orders'),
		array(	trans('From') => 'WORKORDER',
				trans('To') => 'WORKORDER',
				trans('Email Locations') => 'YES_NO',
				trans('Comments') => 'TEXTBOX',
				trans('Orientation') => 'ORIENTATION'));
}
if (get_company_pref('use_fixed_assets'))
{
	$reports->addReportClass(trans('Fixed Assets'), RC_FIXEDASSETS);
	$reports->addReport(RC_FIXEDASSETS, 451, trans('&Fixed Assets Valuation'),
		array(	trans('End Date') => 'DATE',
				trans('Fixed Assets Class') => 'FCLASS',
				trans('Fixed Assets Location') => 'FLOCATIONS',
				trans('Summary Only') => 'YES_NO',
				trans('Comments') => 'TEXTBOX',
				trans('Orientation') => 'ORIENTATION',
				trans('Destination') => 'DESTINATION'));

	$reports->addReport(RC_FIXEDASSETS, 452, trans('&Fixed Assets Depreciation Report'),
		array(	trans('End Date') => 'DATE',
				trans('Fixed Assets Category') => 'FCATEGORIES',
				trans('Orientation') => 'ORIENTATION',
				trans('Destination') => 'DESTINATION'));

	$reports->addReport(RC_FIXEDASSETS, 453, trans('&Fixed Assets Allocation Report'),
		array(	trans('Fixed Assets Category') => 'FCATEGORIES',
				trans('Orientation') => 'ORIENTATION',
				trans('Destination') => 'DESTINATION'));
}				
$reports->addReportClass(trans('Dimensions'), RC_DIMENSIONS);
if ($dim > 0)
{
	$reports->addReport(RC_DIMENSIONS, 501, trans('Dimension &Summary'),
	array(	trans('From Dimension') => 'DIMENSION',
			trans('To Dimension') => 'DIMENSION',
			trans('Show Balance') => 'YES_NO',
			trans('Comments') => 'TEXTBOX',
			trans('Orientation') => 'ORIENTATION',
			trans('Destination') => 'DESTINATION'));
}
$reports->addReportClass(trans('Banking'), RC_BANKING);
//	$reports->addReport(RC_BANKING,  601, trans('Bank &Statement'),
//	array(	trans('Bank Accounts') => 'BANK_ACCOUNTS_NO_FILTER',
//			trans('Start Date') => 'DATEBEGINM',
//			trans('End Date') => 'DATEENDM',
//			trans('Zero values') => 'YES_NO',
//			trans('Comments') => 'TEXTBOX',
//			trans('Orientation') => 'ORIENTATION',
//			trans('Destination') => 'DESTINATION'));
	$reports->addReport(RC_BANKING,  602, trans('Bank Statement w/ &Reconcile'),
	array(	trans('Bank Accounts') => 'BANK_ACCOUNTS',
			trans('Start Date') => 'DATEBEGINM',
			trans('End Date') => 'DATEENDM',
			trans('Comments') => 'TEXTBOX',
			trans('Destination') => 'DESTINATION'));

$reports->addReportClass(trans('General Ledger'), RC_GL);
$reports->addReport(RC_GL, 701, trans('Chart of &Accounts'),
	array(	trans('Show Balances') => 'YES_NO',
			trans('Comments') => 'TEXTBOX',
			trans('Orientation') => 'ORIENTATION',
			trans('Destination') => 'DESTINATION'));
$reports->addReport(RC_GL, 702, trans('List of &Journal Entries'),
	array(	trans('Start Date') => 'DATEBEGINM',
			trans('End Date') => 'DATEENDM',
			trans('Type') => 'SYS_TYPES',
			trans('Comments') => 'TEXTBOX',
			trans('Orientation') => 'ORIENTATION',
			trans('Destination') => 'DESTINATION'));

if ($dim == 2)
{
	$reports->addReport(RC_GL, 704, trans('GL Account &Transactions'),
	array(	trans('Start Date') => 'DATEBEGINM',
			trans('End Date') => 'DATEENDM',
			trans('From Account') => 'GL_ACCOUNTS',
			trans('To Account') => 'GL_ACCOUNTS',
			trans('Dimension')." 1" =>  'DIMENSIONS1',
			trans('Dimension')." 2" =>  'DIMENSIONS2',
			trans('Comments') => 'TEXTBOX',
			trans('Orientation') => 'ORIENTATION',
			trans('Destination') => 'DESTINATION'));
	$reports->addReport(RC_GL, 705, trans('Annual &Expense Breakdown'),
	array(	trans('Year') => 'TRANS_YEARS',
			trans('Dimension')." 1" =>  'DIMENSIONS1',
			trans('Dimension')." 2" =>  'DIMENSIONS2',
			trans('Account Tags') =>  'ACCOUNTTAGS',
			trans('Comments') => 'TEXTBOX',
			trans('Orientation') => 'ORIENTATION',
			trans('Amounts in thousands') => 'YES_NO',
			trans('Destination') => 'DESTINATION'));
	$reports->addReport(RC_GL, 706, trans('&Balance Sheet'),
	array(	trans('Start Date') => 'DATEBEGIN',
			trans('End Date') => 'DATEENDM',
			trans('Dimension')." 1" => 'DIMENSIONS1',
			trans('Dimension')." 2" => 'DIMENSIONS2',
			trans('Account Tags') =>  'ACCOUNTTAGS',
			trans('Decimal values') => 'YES_NO',
			trans('Graphics') => 'GRAPHIC',
			trans('Comments') => 'TEXTBOX',
			trans('Orientation') => 'ORIENTATION',
			trans('Destination') => 'DESTINATION'));
	$reports->addReport(RC_GL, 707, trans('&Profit and Loss Statement'),
	array(	trans('Start Date') => 'DATEBEGINM',
			trans('End Date') => 'DATEENDM',
			trans('Compare to') => 'COMPARE',
			trans('Dimension')." 1" =>  'DIMENSIONS1',
			trans('Dimension')." 2" =>  'DIMENSIONS2',
			trans('Account Tags') =>  'ACCOUNTTAGS',
			trans('Decimal values') => 'YES_NO',
			trans('Graphics') => 'GRAPHIC',
			trans('Comments') => 'TEXTBOX',
			trans('Orientation') => 'ORIENTATION',
			trans('Destination') => 'DESTINATION'));
	$reports->addReport(RC_GL, 708, trans('Trial &Balance'),
	array(	trans('Start Date') => 'DATEBEGINM',
			trans('End Date') => 'DATEENDM',
			trans('Zero values') => 'YES_NO',
			trans('Only balances') => 'YES_NO',
			trans('Dimension')." 1" =>  'DIMENSIONS1',
			trans('Dimension')." 2" =>  'DIMENSIONS2',
			trans('Comments') => 'TEXTBOX',
			trans('Orientation') => 'ORIENTATION',
			trans('Destination') => 'DESTINATION'));
}
elseif ($dim == 1)
{
	$reports->addReport(RC_GL, 704, trans('GL Account &Transactions'),
	array(	trans('Start Date') => 'DATEBEGINM',
			trans('End Date') => 'DATEENDM',
			trans('From Account') => 'GL_ACCOUNTS',
			trans('To Account') => 'GL_ACCOUNTS',
			trans('Dimension') =>  'DIMENSIONS1',
			trans('Comments') => 'TEXTBOX',
			trans('Orientation') => 'ORIENTATION',
			trans('Destination') => 'DESTINATION'));
	$reports->addReport(RC_GL, 705, trans('Annual &Expense Breakdown'),
	array(	trans('Year') => 'TRANS_YEARS',
			trans('Dimension') =>  'DIMENSIONS1',
			trans('Account Tags') =>  'ACCOUNTTAGS',
			trans('Comments') => 'TEXTBOX',
			trans('Orientation') => 'ORIENTATION',
			trans('Amounts in thousands') => 'YES_NO',
			trans('Destination') => 'DESTINATION'));
	$reports->addReport(RC_GL, 706, trans('&Balance Sheet'),
	array(	trans('Start Date') => 'DATEBEGIN',
			trans('End Date') => 'DATEENDM',
			trans('Dimension') => 'DIMENSIONS1',
			trans('Account Tags') =>  'ACCOUNTTAGS',
			trans('Decimal values') => 'YES_NO',
			trans('Graphics') => 'GRAPHIC',
			trans('Comments') => 'TEXTBOX',
			trans('Orientation') => 'ORIENTATION',
			trans('Destination') => 'DESTINATION'));
	$reports->addReport(RC_GL, 707, trans('&Profit and Loss Statement'),
	array(	trans('Start Date') => 'DATEBEGINM',
			trans('End Date') => 'DATEENDM',
			trans('Compare to') => 'COMPARE',
			trans('Dimension') => 'DIMENSIONS1',
			trans('Account Tags') =>  'ACCOUNTTAGS',
			trans('Decimal values') => 'YES_NO',
			trans('Graphics') => 'GRAPHIC',
			trans('Comments') => 'TEXTBOX',
			trans('Orientation') => 'ORIENTATION',
			trans('Destination') => 'DESTINATION'));
	$reports->addReport(RC_GL, 708, trans('Trial &Balance'),
	array(	trans('Start Date') => 'DATEBEGINM',
			trans('End Date') => 'DATEENDM',
			trans('Zero values') => 'YES_NO',
			trans('Only balances') => 'YES_NO',
			trans('Dimension') => 'DIMENSIONS1',
			trans('Comments') => 'TEXTBOX',
			trans('Orientation') => 'ORIENTATION',
			trans('Destination') => 'DESTINATION'));
}
else
{
	$reports->addReport(RC_GL, 704, trans('GL Account &Transactions'),
	array(	trans('Start Date') => 'DATEBEGINM',
			trans('End Date') => 'DATEENDM',
			trans('From Account') => 'GL_ACCOUNTS',
			trans('To Account') => 'GL_ACCOUNTS',
			trans('Comments') => 'TEXTBOX',
			trans('Orientation') => 'ORIENTATION',
			trans('Destination') => 'DESTINATION'));
	$reports->addReport(RC_GL, 705, trans('Annual &Expense Breakdown'),
	array(	trans('Year') => 'TRANS_YEARS',
			trans('Account Tags') =>  'ACCOUNTTAGS',
			trans('Comments') => 'TEXTBOX',
			trans('Orientation') => 'ORIENTATION',
			trans('Amounts in thousands') => 'YES_NO',
			trans('Destination') => 'DESTINATION'));
	$reports->addReport(RC_GL, 706, trans('&Balance Sheet'),
	array(	trans('Start Date') => 'DATEBEGIN',
			trans('End Date') => 'DATEENDM',
			trans('Account Tags') =>  'ACCOUNTTAGS',
			trans('Decimal values') => 'YES_NO',
			trans('Graphics') => 'GRAPHIC',
			trans('Comments') => 'TEXTBOX',
			trans('Orientation') => 'ORIENTATION',
			trans('Destination') => 'DESTINATION'));
	$reports->addReport(RC_GL, 707, trans('&Profit and Loss Statement'),
	array(	trans('Start Date') => 'DATEBEGINM',
			trans('End Date') => 'DATEENDM',
			trans('Compare to') => 'COMPARE',
			trans('Account Tags') =>  'ACCOUNTTAGS',
			trans('Decimal values') => 'YES_NO',
			trans('Graphics') => 'GRAPHIC',
			trans('Comments') => 'TEXTBOX',
			trans('Orientation') => 'ORIENTATION',
			trans('Destination') => 'DESTINATION'));
	$reports->addReport(RC_GL, 708, trans('Trial &Balance'),
	array(	trans('Start Date') => 'DATEBEGINM',
			trans('End Date') => 'DATEENDM',
			trans('Zero values') => 'YES_NO',
			trans('Only balances') => 'YES_NO',
			trans('Comments') => 'TEXTBOX',
			trans('Orientation') => 'ORIENTATION',
			trans('Destination') => 'DESTINATION'));
}
$reports->addReport(RC_GL, 709, trans('Ta&x Report'),
	array(	trans('Start Date') => 'DATEBEGINTAX',
			trans('End Date') => 'DATEENDTAX',
			trans('Tax Type')	=> 'TAXREGTYPE',
			trans('Dimension')  => 'DIMENSIONS',
			trans('Summary Only') => 'YES_NO',
			trans('Comments') => 'TEXTBOX',
			trans('Orientation') => 'ORIENTATION',
			trans('Destination') => 'DESTINATION'));
$reports->addReport(RC_GL, 710, trans('Audit Trail'),
	array(	trans('Start Date') => 'DATEBEGINM',
			trans('End Date') => 'DATEENDM',
			trans('Type') => 'SYS_TYPES_ALL',
			trans('User') => 'USERS',
			trans('Comments') => 'TEXTBOX',
			trans('Orientation') => 'ORIENTATION',
			trans('Destination') => 'DESTINATION'));

add_custom_reports($reports);

echo $reports->getDisplay();

end_page();

?>

<script>
    $(document).ready(function(e) {

        $(document).on("click","select#PARAM_2",function() {
            $("select#PARAM_3").val($(this).val());
        });

    });
</script>
