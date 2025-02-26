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
function add_item_price($stock_id, $sales_type_id, $curr_abrev, $price)
{
	$sql = "INSERT INTO ".TB_PREF."prices (stock_id, sales_type_id, curr_abrev, price) 
		VALUES (".db_escape($stock_id).", ".db_escape($sales_type_id)
		.", ".db_escape($curr_abrev).", ".db_escape($price).")";

	db_query($sql,"an item price could not be added");
}

function update_item_price($price_id, $sales_type_id, $curr_abrev, $price)
{
	$sql = "UPDATE ".TB_PREF."prices SET sales_type_id=".db_escape($sales_type_id).", 
		curr_abrev=".db_escape($curr_abrev).", 
		price=".db_escape($price)." WHERE id=".db_escape($price_id);

	db_query($sql,"an item price could not be updated");
}

function delete_item_price($price_id)
{
	$sql="DELETE FROM ".TB_PREF."prices WHERE id= ".db_escape($price_id);
	db_query($sql,"an item price could not be deleted");
}

function get_prices($stock_id)
{
	$sql = "SELECT pricelist.sales_type, price.* 
		FROM ".TB_PREF."prices price, "
			.TB_PREF."sales_types pricelist
		WHERE price.sales_type_id = pricelist.id
		AND stock_id=".db_escape($stock_id)
		." ORDER BY curr_abrev, sales_type_id";

	return db_query($sql,"item prices could not be retreived");
}

function get_stock_price($price_id)
{
	$sql = "SELECT ".TB_PREF."prices.*,
	".TB_PREF."stock_category.is_tasheel,
	".TB_PREF."stock_master.govt_fee,
	".TB_PREF."stock_master.customer_commission,
	".TB_PREF."stock_master.pf_amount,
	".TB_PREF."stock_master.govt_bank_account,
	".TB_PREF."stock_master.use_own_govt_bank_account,
	".TB_PREF."stock_master.bank_service_charge,
	".TB_PREF."stock_master.bank_service_charge_vat,
	".TB_PREF."stock_master.commission_loc_user,
	".TB_PREF."stock_master.commission_non_loc_user 
	 FROM ".TB_PREF."prices 
	 LEFT JOIN ".TB_PREF."stock_master ON ".TB_PREF."stock_master.stock_id=".TB_PREF."prices.stock_id 
	 LEFT JOIN ".TB_PREF."stock_category ON ".TB_PREF."stock_category.category_id=".TB_PREF."stock_master.category_id  
	 WHERE ".TB_PREF."prices.id=".db_escape($price_id);

	$result = db_query($sql,"price could not be retreived");

	return db_fetch($result);
}

function get_stock_price_type_currency($stock_id, $type, $currency)
{
	$sql = "SELECT * FROM ".TB_PREF."prices WHERE stock_id=".db_escape($stock_id)."
		AND sales_type_id=".db_escape($type)."
		AND curr_abrev=".db_escape($currency);

	$result = db_query($sql,"price could not be retreived");

	return db_fetch($result);
}

/**
 * Updates additional params of stock items.
 * 
 * @param string $stock_id
 * @param float $govt_fee
 * @param string $govt_bank_acc
 * @param float $bank_service_charge
 * @param float $bank_service_charge_vat
 * @param float $commission_loc_user
 * @param float $commission_non_loc_user
 */
function update_item_additional_charges_info(
    $stock_id,
    $govt_fee,
    $govt_bank_acc,
    $bank_service_charge,
    $bank_service_charge_vat,
    $commission_loc_user,
    $commission_non_loc_user,
    $pf_amount,
    $returnable_amt = 0.00,
    $returnable_to = null,
    $split_govt_fee_amt = 0.00,
    $split_govt_fee_acc = null,
    $extra_srv_chg = 0.00
) {
    $use_own_govt_bank_account = $_POST['use_own_govt_bank_account'];
    $sql = (
        "UPDATE ".TB_PREF."stock_master SET 
            govt_fee = ".db_escape($govt_fee).", 
            pf_amount = ".db_escape($pf_amount).", 
		    govt_bank_account = ".db_escape($govt_bank_acc).", 
		    bank_service_charge = ".db_escape($bank_service_charge).", 
		    bank_service_charge_vat = ".db_escape($bank_service_charge_vat).", 
		    commission_loc_user = ".db_escape($commission_loc_user).", 
		    use_own_govt_bank_account = ".db_escape($use_own_govt_bank_account).", 
		    commission_non_loc_user = ".db_escape($commission_non_loc_user).",
            returnable_amt = ".db_escape($returnable_amt).",
            returnable_to = ".db_escape($returnable_to, true).",
            split_govt_fee_amt = ".db_escape($split_govt_fee_amt).",
            split_govt_fee_acc = ".db_escape($split_govt_fee_acc).",
            extra_srv_chg = ".db_escape($extra_srv_chg)."
        WHERE stock_id = ".db_escape($stock_id)
    );


    db_query($sql,"an item additional price information could not be updated");

}

function update_item_service_charge($price,$stock_id)
{
    $sql="UPDATE 0_prices set price='".$price."' where stock_id='".$stock_id."'";
    db_query($sql,"an item additional price information could not be updated");
}


function get_other_charges_info($stock_id,$sales_type_id) {

    if(empty($sales_type_id))
        $sales_type_id=-1;

    $sql = "select * from `0_other_charges_master` where stock_id='$stock_id' and sales_type_id=$sales_type_id";
    return db_query($sql,"item other charges info could not be retreived");

}


function add_other_charge_info($stock_id,$sales_type_id,$charges) {

    $sql = "DELETE FROM `0_other_charges_master` WHERE stock_id='$stock_id' and sales_type_id=$sales_type_id";
    db_query($sql,"Other fee info could not be deleted");

    begin_transaction();

    if(!empty($charges)) {

        $sql = "INSERT INTO `0_other_charges_master` (stock_id,sales_type_id,account_code,description,amount) 
                  VALUES ";
        $batch_values_array = [];
        foreach ($charges as $row) {

            if($row->acc_amount) {

                array_push($batch_values_array,
                    "('$stock_id',$sales_type_id,'$row->acc_code','$row->acc_desc',$row->acc_amount)");
            }
        }

        $sql = $sql." ".implode(",",$batch_values_array);

        if(!empty($batch_values_array)) {
            db_query($sql,"Other fee info could not be added");
        }

    }

    commit_transaction();

}
