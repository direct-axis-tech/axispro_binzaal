<?php
/**********************************************
Author: Joe Hunt
Name: Import of CSV formatted customers
Free software under GNU GPL
***********************************************/
$page_security = 'SA_CUSTOMER';
$path_to_root="../..";

include($path_to_root . "/includes/session.inc");

page("Import of CSV formatted Customers");

include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/includes/data_checks.inc");

include_once($path_to_root . "/sales/includes/db/branches_db.inc");
include_once($path_to_root . "/sales/includes/db/customers_db.inc");

//error_reporting(E_ALL);
//ini_set("display_errors", "on");

if (isset($_POST['import']))
{
	if (isset($_FILES['imp']) && $_FILES['imp']['name'] != '')
	{
		$filename = $_FILES['imp']['tmp_name'];
		$sep = $_POST['sep'];

		$fp = @fopen($filename, "r");
		if (!$fp)
			die("can not open file $filename");

		$lines = $i = $j = 0;
		// id; name; address1; address2; address3; address4; area; phone; fax; email; contact; tax_id; currency;
		while ($data = fgetcsv($fp, 4096, $sep))
		{
			if ($lines++ == 0)
				continue;
			list($id, $name, $addr1, $addr2, $addr3, $addr4, $area, $phone, $fax, $email, $contact, $tax_id,
				$currency) = $data;
			$name = db_escape($name);
			$addr = "";
			if ($addr1 != "")
				$addr .= "$addr1\n";
			if ($addr2 != "")
				$addr .= "$addr2\n";
			if ($addr3 != "")
				$addr .= "$addr3\n";
			if ($addr4 != "")
				$addr .= "$addr4\n";
			$addr = db_escape($addr);
			$sql = "SELECT area_code, description FROM ".TB_PREF."areas WHERE description='$area'";

			$result = db_query($sql, "could not get area");

			$row = db_fetch_row($result);
			if (!$row)
			{
    			$sql = "INSERT INTO ".TB_PREF."areas (description) VALUES ('$area')";
    			db_query($sql,"The sales area could not be added");
				$area_code = db_insert_id();
			}
			else
				$area_code = $row[0];
			if ($currency == "")
				$currency = get_company_pref("curr_default");
			else
			{
				$row = get_currency($currency);
				if (!$row)
					add_currency($currency, "", "", "", "");
			}
			if ($id != "")
				$sql = "SELECT debtor_no FROM ".TB_PREF."debtors_master WHERE debtor_no=$id";
			else
				$sql = "SELECT debtor_no FROM ".TB_PREF."debtors_master WHERE name='$name'";
			$result = db_query($sql,"customer could not be retreived");
			$row = db_fetch_row($result);
			if (!$row)
			{
				if ($id != "")
				{
					$sql = "INSERT INTO ".TB_PREF."debtors_master (debtor_no, name, address, email,
						tax_id, curr_code, sales_type, payment_terms, credit_status)
						VALUES ($id, '$name', '$addr', '$email', '$tax_id', '$currency', {$_POST['sales_type']},
						{$_POST['payment_terms']}, 1)";

				}
				else
				{
					$sql = "INSERT INTO ".TB_PREF."debtors_master (name, address, email,
						tax_id, curr_code, sales_type, payment_terms, credit_status)
						VALUES ('$name', '$addr', '$email', '$tax_id', '$currency', {$_POST['sales_type']},
						{$_POST['payment_terms']}, 1)";
				}

				display_error($sql);

				db_query($sql, "The customer could not be added");
				if ($id == "")
					$id = db_insert_id();
				$sql = "INSERT INTO ".TB_PREF."cust_branch (debtor_no, br_name, br_address, area, salesman,
					phone, fax, contact_name, email, default_location, tax_group_id, sales_account,
					sales_discount_account, receivables_account, payment_discount_account, br_post_address)
					VALUES ($id, '$name', '$addr', '$area_code', '{$_POST['salesman']}', '$phone', '$fax',
					'$contact', '$email', '{$_POST['default_location']}', {$_POST['tax_group_id']}, '{$_POST['sales_account']}',
					'{$_POST['sales_discount_account']}', '{$_POST['receivables_account']}',
					'{$_POST['payment_discount_account']}', '$addr')";
				db_query($sql, "The customer branch could not be added");

				$i++;
			}
			else
			{
				$sql = "UPDATE ".TB_PREF."debtors_master SET address='$addr',
					email='$email',
					tax_id='$tax_id',
					curr_code='$currency',
					sales_type={$_POST['sales_type']},
					payment_terms={$_POST['payment_terms']}
					WHERE name='$name'";

				db_query($sql, "The customer could not be updated");

				$j++;
			}
		}
		@fclose($fp);

		display_notification("$i customer posts created, $j customer posts updated.");

	}
	else
		display_error("No CSV file selected");
}

start_form(true);

start_table("$table_style2 width=40%");

table_section_title("Default GL Accounts");

$company_record = get_company_prefs();

if (!isset($_POST['sales_account']) || $_POST['sales_account'] == "")
   	$_POST['sales_account'] = $company_record["default_sales_act"];

if (!isset($_POST['sales_discount_account']) || $_POST['sales_discount_account'] == "")
   	$_POST['sales_discount_account'] = $company_record["default_sales_discount_act"];

if (!isset($_POST['receivables_account']) || $_POST['receivables_account'] == "")
	$_POST['receivables_account'] = $company_record["debtors_act"];

if (!isset($_POST['payment_discount_account']) || $_POST['payment_discount_account'] == "")
	$_POST['payment_discount_account'] = $company_record["default_prompt_payment_act"];

if (!isset($_POST['sep']))
	$_POST['sep'] = ";";

gl_all_accounts_list_row("Sales Account:", 'sales_account', $_POST['sales_account']);
gl_all_accounts_list_row("Sales Discount Account:", 'sales_discount_account', $_POST['sales_discount_account']);
gl_all_accounts_list_row("Receivables Account:", 'receivables_account', $_POST['receivables_account']);
gl_all_accounts_list_row("Payment Discount Account:", 'payment_discount_account', $_POST['payment_discount_account']);

table_section_title("Separator, Location, Tax Type, Sales Type, Sales Person and Payment Terms");
text_row("Field separator:", 'sep', $_POST['sep'], 2, 1);
locations_list_row("Location:", 'default_location', null);
item_tax_types_list_row("Item Tax Type:", 'tax_group_id', null);
sales_types_list_row("Sales Type:", 'sales_type', null);
sales_persons_list_row("Sales Person", 'salesman', null);
payment_terms_list_row("Payment Terms", 'payment_terms', null);
label_row("CSV Import File:", "<input type='file' id='imp' name='imp'>");

end_table(1);

submit_center('import', "Import CSV File");

end_form();

end_page();

?>