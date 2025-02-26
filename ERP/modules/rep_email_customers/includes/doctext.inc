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
/*****
*
*	Set document type dependent elements of common page layout.
*
*/
	$Addr1 = array(
			'title' => trans("Charge To"),
			'name' => @$this->formData['br_name'] ? $this->formData['br_name'] : @$this->formData['DebtorName'],
			'address' => @$this->formData['br_address'] ? $this->formData['br_address'] : @$this->formData['address']
	);
	$Addr2 = array(
			'title' => trans("Delivered To"),
			'name' => @$this->formData['deliver_to'],
			'address' => @$this->formData['delivery_address']
	);

	// default item column headers
	$this->headers = array(trans("Item Code"), trans("Item Description"), trans("Quantity"),
		trans("Unit"), trans("Price"), trans("Discount %"), trans("Total"));

	// for links use 'text' => 'url'
	$Footer[0] = trans("All amounts stated in") . " - " . @$this->formData['curr_code'];

	switch ($this->formData['doctype'])
	{
		case ST_SALESQUOTE:
			$this->title = trans("SALES QUOTATION");
			$this->formData['document_name'] =trans("Quotation No.");
			$this->formData['document_date'] = $this->formData['ord_date'];
			$this->formData['document_number'] = $print_invoice_no == 0 && isset($this->formData['reference'])
				? $this->formData['reference'] : $this->formData['order_no'];
			$aux_info = array(
				trans("Customer's Reference") => $this->formData["customer_ref"],
				trans("Sales Person") => get_salesman_name($this->formData['salesman']),
				trans("Your VAT no.") => $this->formData['tax_id'],
				trans("Our Quotation No") => $this->formData['order_no'],
				trans("Valid until") => sql2date($this->formData['delivery_date']),
			);
			break;

		case ST_SALESORDER:
			$this->title = ($print_as_quote==1 ? trans("PROFORMA INVOICE") : trans("SALES ORDER"));
			$this->formData['document_name'] =trans("Order No.");
			$this->formData['document_date'] = $this->formData['ord_date'];
			$this->formData['document_number'] = $print_invoice_no == 0 && isset($this->formData['reference'])
				? $this->formData['reference'] : $this->formData['order_no'];
			$this->formData['document_amount'] = $this->formData['order_no'];

			$aux_info = array(
				trans("Customer's Reference") => $this->formData["customer_ref"],
				trans("Sales Person") => get_salesman_name($this->formData['salesman']),
				trans("Your VAT no.") => $this->formData['tax_id'],
				trans("Our Order No") => $this->formData['order_no'],
				trans("Delivery Date") => sql2date($this->formData['delivery_date']),
			);
			break;

		case ST_CUSTDELIVERY:
/* Packing slip corresponding to a invoic, not 'delivery'.
 * Therefore the code is similar to ST_SALESINVOICES 
 */
			$this->title = trans("PACKING SLIP");
				$this->formData['document_name'] =trans("Invoice No.");
			$this->formData['domicile'] = $this->company['domicile'];
			//$Footer[0] = trans("Please quote Invoice no. when paying. All amounts stated in"). " - " . $this->formData['curr_code'];

			$deliveries = get_sales_parent_numbers(ST_SALESINVOICE, $this->formData['trans_no']);
			if ($print_invoice_no == 0)
			{
				foreach($deliveries as $n => $delivery) {
					$delivery_transaction = get_customer_trans($delivery, ST_CUSTDELIVERY);
					$order = get_reference(ST_SALESORDER, $delivery_transaction['order_']);
					if($order) $deliveries[$n] = $order;
				}
			}
			$aux_info = array(
				trans("Customer's Reference") => $this->formData["customer_ref"],
				trans("Sales Person") => get_salesman_name($this->formData['salesman']),
				trans("Your VAT no.") => $this->formData['tax_id'],
				trans("Order No.") => implode(',', $deliveries),
				trans("Due Date") => sql2date($this->formData['due_date']),
			);
			break;

		case ST_CUSTCREDIT:
			$this->title = trans("CREDIT NOTE");
			$this->formData['document_name'] =trans("Credit No.");
			$Footer[0] = trans("Please quote Credit no. when paying. All amounts stated in") . " - " . $this->formData['curr_code'];

			$aux_info = array(
				trans("Customer's Reference") => @$this->formData["customer_ref"],
				trans("Sales Person") => get_salesman_name($this->formData['salesman']),
				trans("Your VAT no.") => $this->formData['tax_id'],
				trans("Our Order No") => $this->formData['order_'],
				trans("Due Date") => '',
			);
			break;

		case ST_SALESINVOICE:
			if($print_as_quote) {
				$this->title =  trans("PROFORMA INVOICE");
				$this->formData['document_name'] =trans("Proforma No.");
			}
			else {
				$this->title =  trans("INVOICE");
				$this->formData['document_name'] =trans("Invoice No.");
			}
			$this->formData['domicile'] = $this->company['domicile'];
			$Footer[0] = trans("Please quote Invoice no. when paying. All amounts stated in"). " - " . $this->formData['curr_code'];

			$deliveries = get_sales_parent_numbers(ST_SALESINVOICE, $this->formData['trans_no']);
			if ($print_invoice_no == 0)
			{
				foreach($deliveries as $n => $delivery) {
					$deliveries[$n] = get_reference(ST_CUSTDELIVERY, $delivery);
				}
			}
			$aux_info = array(
				trans("Customer's Reference") => $this->formData["customer_ref"],
				trans("Sales Person") => get_salesman_name($this->formData['salesman']),
				trans("Your VAT no.") => $this->formData['tax_id'],
				trans("Delivery Note No.") => implode(',', $deliveries),
				trans("Due Date") => sql2date($this->formData['due_date']),
			);
			break;

		case ST_SUPPAYMENT:
			global $systypes_array;

			$this->title = trans("REMITTANCE");
			$this->formData['document_name'] =trans("Remittance No.");
			$Addr1['title'] = trans("Order To");
			$Addr1['name'] = $this->formData['supp_name'];
			$Addr1['address'] = $this->formData['address'];
			$Addr2['title'] = trans("Charge To");
			$Addr2['name'] = '';
			$Addr2['address'] = '';

			$aux_info = array(
				trans("Customer's Reference") => $this->formData['supp_account_no'],
				trans("Type") =>$systypes_array[$this->formData["type"]],
				trans("Your VAT no.") => $this->formData['tax_id'],
				trans("Supplier's Reference") => '',
				trans("Due Date") => sql2date($this->formData['tran_date']),
			);
			$this->headers = array(trans("Transaction"), trans("#"), trans("Date"), trans("Due Date"), trans("Total Amount"), trans("Left to Allocate"), trans("This Allocation"));
			break;

		case ST_PURCHORDER:
			$this->title = trans("PURCHASE ORDER");
			$this->formData['document_name'] =trans("Purchase Order No.");
			$Addr1['title'] = trans("Order To");
			$Addr1['name'] = $this->formData['supp_name'];
			$Addr1['address'] = $this->formData['address'];
			$Addr2['title'] = trans("Deliver To");
			$Addr2['name'] = $this->company['coy_name'];
			//$Addr2['address'] = $this->company['postal_address']; No, don't destroy delivery address!
			$this->formData['document_date'] = $this->formData['ord_date'];
			$this->formData['document_number'] = $print_invoice_no == 0 && isset($this->formData['reference'])
				? $this->formData['reference'] : $this->formData['order_no'];

			$aux_info = array(
				trans("Customer's Reference") => $this->formData['supp_account_no'],
				trans("Sales Person") => $this->formData['contact'],
				trans("Your VAT no.") => $this->formData['tax_id'],
				trans("Supplier's Reference") => $this->formData['requisition_no'],
				trans("Order Date") => sql2date($this->formData['document_date']),
			);

			$this->headers = array(trans("Item Code"), trans("Item Description"),
				trans("Delivery Date"), trans("Quantity"), 	trans("Unit"), trans("Price"), trans("Total"));
			break;

		case ST_CUSTPAYMENT:
			global $systypes_array;

			$this->title = trans("RECEIPT");
			$this->formData['document_name'] =trans("Receipt No.");
			$Addr1['title'] = trans("With thanks from");
			$aux_info = array(
				trans("Customer's Reference") => $this->formData["debtor_ref"],
				trans("Type") =>$systypes_array[$this->formData["type"]],
				trans("Your VAT no.") => $this->formData['tax_id'],
				trans("Our Order No") => $this->formData['order_'],
				trans("Due Date") => sql2date($this->formData['tran_date']),
			);
			$this->headers = array(trans("Trans Type"), trans("#"), trans("Date"), trans("Due Date"), trans("Total Amount"), trans("Left to Allocate"), trans("This Allocation"));
			break;

		case ST_WORKORDER:
			global $wo_types_array;

			$this->title = trans("WORK ORDER");
			$this->formData['document_name'] =trans("Work Order No.");
			$this->formData['document_date'] = $this->formData['date_'];
			$this->formData['document_number'] = $this->formData['id'];
			$Addr1['name'] = $this->formData['location_name'];
			$Addr1['address'] = $this->formData['delivery_address'];
			$aux_info = array(
				trans("Reference") => $this->formData['wo_ref'],
				trans("Type") => $wo_types_array[$this->formData["type"]],
				trans("Manufactured Item") => $this->formData["StockItemName"],
				trans("Into Location") => $this->formData["location_name"],
				trans("Quantity") => $this->formData["units_issued"],
			);
			$Payment_Terms = trans("Required By").": ".sql2date($this->formData["required_by"]);
			$this->headers = array(trans("Item Code"), trans("Item Description"),
				trans("From Location"), trans("Work Centre"), 	trans("Unit Quantity"), trans("Total Quantity"), trans("Units Issued"));
			unset($Footer[0]);
			break;


		case ST_STATEMENT:
			$this->formData['document_name'] = '';
			$this->formData['domicile'] = $this->company['domicile'];
			$Payment_Terms = '';
			$this->title = trans("STATEMENT");
			$aux_info = array(
				trans("Customer's Reference") => '',
				trans("Sales Person") => '',
				trans("Your VAT no.") => $this->formData['tax_id'],
				trans("Our Order No") => '',
				trans("Delivery Date") => '',
			);
			$this->headers = array(trans("Date"), trans("Trans Type"), trans("#"), trans("Issue Date"), trans("Charges"),
				trans("Credits"), trans("Balance"));
	}

	// default values
	if (!isset($this->formData['document_date']))
		$this->formData['document_date'] = $this->formData['tran_date'];

	if (!isset($this->formData['document_number']))
		$this->formData['document_number'] = $print_invoice_no == 0 && isset($this->formData['reference'])
			? $this->formData['reference'] : @$this->formData['trans_no'];

	if (!isset($Payment_Terms))
	{
		$id = $this->formData['payment_terms'];
		$sql = "SELECT terms FROM ".TB_PREF."payment_terms WHERE terms_indicator=".db_escape($id);
		$result = db_query($sql,"could not get paymentterms");
		$row = db_fetch($result);
		$Payment_Terms = trans("Payment Terms") . ': ' . $row["terms"];
	}

	// footer generic content
	if (@$this->formData['bank_name'])
		$Footer[] = trans("Bank"). ": ".$this->formData['bank_name']. ", " . trans("Bank Account") . ": " . $this->formData['bank_account_number'];

	if (@$this->formData['payment_service'])	//payment link
	{
		$amt = number_format($this->formData["ov_freight"] + $this->formData["ov_gst"] + $this->formData["ov_amount"], user_price_dec());
		$service = $this->formData['payment_service'];
		$url = payment_link($service, array(
			'company_email' => $this->company['email'],
			'amount' => $amt,
			'currency' => $this->formData['curr_code'],
			'comment' => $this->title . " " . $this->formData['reference']
			));
		$Footer[trans("You can pay through"). " $service: "] = "$url";
	}

	if ($this->formData['doctype'] == ST_CUSTPAYMENT)
 		$Footer[] = trans("* Subject to Realisation of the Cheque.");

	if ($this->params['comments'] != '')
		$Footer[] = $this->params['comments'];

	if (($this->formData['doctype'] == ST_SALESINVOICE || $this->formData['doctype'] == ST_STATEMENT) && $this->company['legal_text'] != "") 
	{
		foreach(explode("\n", $this->company['legal_text']) as $line)
			$Footer[] = $line;
	}

	$this->formData['recipient_name'] = $Addr1['name'];
?>
