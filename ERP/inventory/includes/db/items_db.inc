<?php

use App\Exceptions\BusinessLogicException;
use App\Models\Inventory\StockCategory;
use App\Models\Sales\AutofetchedTransaction;

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
function update_item($stock_id, $description, $long_description, $category_id, 
	$tax_type_id, $units, $mb_flag, $sales_account, $inventory_account, 
	$cogs_account, 	$adjustment_account, $wip_account, $dimension_id, 
	$dimension2_id, $no_sale, $editable, $no_purchase,
	$depreciation_method = 'D', $depreciation_rate=100, $depreciation_factor=1,
	$depreciation_start=null, $fa_class_id=null, $nationality=null, $costing_method=null)
{



	$sql = "UPDATE ".TB_PREF."stock_master SET long_description=".db_escape($long_description).",
		description=".db_escape($description).",
		category_id=".db_escape($category_id).",
		sales_account=".db_escape($sales_account).",
		inventory_account=".db_escape($inventory_account).",
		cogs_account=".db_escape($cogs_account).",
		adjustment_account=".db_escape($adjustment_account).",
		wip_account=".db_escape($wip_account).",
		dimension_id=".db_escape($dimension_id).",
		dimension2_id=".db_escape($dimension2_id).",
		tax_type_id=".db_escape($tax_type_id).",
		no_sale=".db_escape($no_sale).",
		no_purchase=".db_escape($no_purchase).",
		editable=".db_escape($editable).",
		depreciation_method=".db_escape($depreciation_method).",
		depreciation_rate=".db_escape($depreciation_rate).",
		depreciation_factor=".db_escape($depreciation_factor).",
		depreciation_factor=".db_escape($depreciation_factor).",
		sub_category_id=".db_escape($_POST['sub_category_id']).",
		fa_class_id=".db_escape($fa_class_id).",
		nationality=".db_escape($nationality, true).",
		costing_method=".db_escape($costing_method, true).",
		updated_date=".quote(date('Y-m-d H:i:s'));

	if ($units != '')
		$sql .= ", units=".db_escape($units);

	if ($mb_flag != '')
		$sql .= ", mb_flag=".db_escape($mb_flag);

	if (isset($depreciation_start)) {
		$sql .= ", depreciation_start='".date2sql($depreciation_start)."'"
			.", depreciation_date='".date2sql($depreciation_start)."'";
	}

	$sql .= " WHERE stock_id=".db_escape($stock_id);

	db_query($sql, "The item could not be updated");

	update_item_code(-1, $stock_id, $stock_id, $description, $category_id, 1, 0);
}

/**
 * Retrieves the items details to be added to cart for tasheel from the post variable
 * 
 * @return array
 */
function getAutoFetchedItems($selected_item_ids) {
    $normalize = function ($name) {
        return strtolower(trim($name));
    };

	// We have made sure that the description for each service in any category is unique so we can find the stock id
	$stocks = collect(app('api')->get_all_items('array'))
		->groupBy('category_id')
		->map(function ($items) use ($normalize) {
			return [
				'en' => $items
					->keyBy(function ($item) use ($normalize) {
						return $normalize($item['stock_name']);
					})
					->toArray(),
				'ar' => $items
					->keyBy(function ($item) use ($normalize) {
						return $normalize($item['stock_name_ar']) ?: $normalize($item['stock_name']);
					})
					->toArray()
			];
		})
		->toArray();

	$configs = [
		AIT_TASHEEL => [
			'defaults' => [
				'category_id' => pref('autofetch.tasheel.ts_auto_stock_category'),
				'govt_bank_account' => pref('autofetch.tasheel.ts_auto_govt_bank_acc'),
				'returnable_to' => pref('autofetch.tasheel.ts_auto_returnable_to'),
				'17.96' => pref('axispro.tas_17_1_cat'),
				'37.80' => pref('axispro.tas_36_cat'),
				'75.60' => pref('axispro.tas_72_cat'),
				'151.20' => pref('axispro.twj_144_cat'),
				'226.80' => pref('axispro.tas_216_cat'),
			],
			'commissions' => [
				'37.80' => 15,
				'75.60' => 35,
				'151.20' => 35,
				'226.80' => 105,
			],
			'next_no_col' => 'ts_next_auto_stock_no',
			'stock_prefix' => 'TSWA',
			'item_tax_type' => ITT_REGULAR,
			'sales_type' => SALES_TYPE_TAX_INCLUDED
		],
		AIT_IMM_POST_DXB => [
			'defaults' => [
				'category_id' => pref('autofetch.immigration.imm_auto_stock_category'),
				'govt_bank_account' => pref('autofetch.immigration.imm_auto_govt_bank_acc'),
				'80.00' => pref('axispro.imm_80_cat'),
			],
			'commissions' => [
				'80.00' => 45
			],
			'next_no_col' => 'imm_next_auto_stock_no',
			'stock_prefix' => 'IMGA',
			'item_tax_type' => ITT_NO_TAX,
			'sales_type' => SALES_TYPE_TAX_EXCLUDED
		]
	];

	$configs[AIT_TASHEEL]['getPricing'] = function ($item, $stockItem = null) use ($configs) {
        $dec = user_price_dec();
		$prices = [
			'govt_fee' => $item['total'],
			'unit_price' => 0,
			'returnable_amt' => $item['srv_chg'],
			'bank_service_charge' => null,
			'bank_service_charge_vat' => null,
			'commission_loc_user' => $configs[AIT_TASHEEL]['commissions'][$item['srv_chg']] ?? 0,
			'commission_non_loc_user' => null,
			'total' => $item['total'],
		];

        // Update the returnable amount if there is any change for tasheel transactions
        if (
            $stockItem
            && !empty($prices['returnable_amt'])
            && is_numeric($prices['returnable_amt'])
            && round2($prices['returnable_amt'], $dec) != round2($stockItem['returnable_amt'], $dec)
        ) {
            $commission = $configs[AIT_TASHEEL]['commissions'][$prices['returnable_amt']] ?? 0;
            db_query(
                "UPDATE `0_stock_master` stock
                SET stock.returnable_amt = '{$prices['returnable_amt']}',
                stock.commission_loc_user = '{$commission}'
                WHERE stock.stock_id = '{$stockItem['stock_id']}'",
                "Could not update the service charge"
            );
        }

        return $prices;
	};

	$configs[AIT_IMM_POST_DXB]['getPricing'] = function ($item, $stockItem = null) use ($configs) {
        /* 
         |---------------------------------------------------------------------------
         | Note:
         |---------------------------------------------------------------------------
         | When auto fetching the emigration transactions, We have 2 stages.
         | 1. Before the payment page
         | 2. After the payment page
         | 
         | If we only encountered either stages alone: like, Before with no After
         | or After with no Before then the service charge is hardcoded to be 80
         | in the extension itself. In such cases the service charge could be wrong.
         | 
         | So we should prioritize the service charge configured in the server.
         | This gives the system a pass to override the values fetched from autofetch
         |---------------------------------------------------------------------------
         */
		$prices = [
            'govt_fee' => $item['total'] - $item['srv_chg'],
			'unit_price' => data_get($stockItem, 'stock_price') ?? $item['srv_chg'],
			'returnable_amt' => 0,
			'bank_service_charge' => null,
			'bank_service_charge_vat' => null,
			'commission_loc_user' => data_get($stockItem, 'commission_loc_user'),
			'commission_non_loc_user' => data_get($stockItem, 'commission_non_loc_user'),
		];

        $prices['total'] = $prices['govt_fee'] + $prices['unit_price'];

        if (is_null($prices['commission_loc_user'])) {
            $prices['commission_loc_user'] = $configs[AIT_IMM_POST_DXB]['commissions'][$prices['unit_price']] ?? 0;
        }

        return $prices;
	};

	$categories = StockCategory::whereIn('category_id', [
		$configs[AIT_TASHEEL]['defaults']['category_id'],
		$configs[AIT_TASHEEL]['defaults']['17.96'],
		$configs[AIT_TASHEEL]['defaults']['37.80'],
		$configs[AIT_TASHEEL]['defaults']['75.60'],
		$configs[AIT_TASHEEL]['defaults']['151.20'],
		$configs[AIT_TASHEEL]['defaults']['226.80'],
		$configs[AIT_IMM_POST_DXB]['defaults']['category_id'],
		$configs[AIT_IMM_POST_DXB]['defaults']['80.00'],
	])->get()->keyBy('category_id');

    $configs[AIT_TASHEEL]['getCategoryId'] = function ($item) use ($configs) {
        /*
         |---------------------------------------------------------------------------
         | Note:
         |---------------------------------------------------------------------------
         | Category ID is determined based on service charge only.
         |---------------------------------------------------------------------------
         */
        return $configs[AIT_TASHEEL]['defaults'][$item['srv_chg']] ?? $configs[AIT_TASHEEL]['defaults']['category_id'];
    };

    $configs[AIT_IMM_POST_DXB]['getCategoryId'] = function ($item) use ($configs, $stocks, $normalize) {
        $name = $normalize($item['name_en']);
        $default_category_id = $configs[AIT_IMM_POST_DXB]['defaults']['category_id'];
        $category_id = $configs[AIT_IMM_POST_DXB]['defaults'][$item['srv_chg']];

        /*
         |---------------------------------------------------------------------------
         | Note:
         |---------------------------------------------------------------------------
         | Category ID is determined based on service charge as well as name.
         | 
         | The item must exist in the database within the found category.
         | If an item with the name does not exist in the category found,
         | we need to check if there is any item with the same name in the
         | default category and use that item instead.
         | If the item with the same name is not present in either categories, then
         | item is considered non existent.
         |---------------------------------------------------------------------------
         */
        if ($category_id) {
            if (!empty($stocks[$category_id]['en'][$name]) || !empty($stocks[$category_id]['ar'][$name])) {
                return $category_id;
            }

            if (!empty($stocks[$default_category_id]['en'][$name]) || !empty($stocks[$default_category_id]['ar'][$name])) {
                return $default_category_id;
            }

            return $category_id;
        }

        return $default_category_id;
    };

	$auto_items = [];
	$default_currency = get_company_pref("curr_default");
	$selected_items = AutofetchedTransaction::query()
		->select(
			'service_en as name_en',
			DB::raw("if (`service_ar` = ' ' OR `service_ar` IS NULL, `service_en`, `service_ar`) as name_ar"),
			DB::raw("if (`type` = '".AIT_IMM_PRE_DXB."', '".AIT_IMM_POST_DXB."', `type`) as `type`"),
			'service_chg as srv_chg',
			'transaction_id',
			'application_id',
			'webuser_id',
			'total'
		)
		->whereIn('id', $selected_item_ids)
		->get();
	
	// Validate that the configuration values are all set
	foreach ($selected_items->unique('type') as $item) {
		if (count($configs[$item->type]['defaults']) != count(array_filter($configs[$item->type]['defaults']))) {
			throw new BusinessLogicException('Autofetch is not configured in your system. Please configure them before proceeding.');
		}
	}

	foreach ($selected_items as $item) {
		$item = $item->toArray();
		$name = $normalize($item['name_en']);
		$type = $item['type'];
		$category_id = $configs[$type]['getCategoryId']($item);

		// if there is already stock then use it else create one stock for us to use
        if (!empty($stocks[$category_id]['en'][$name]) || !empty($stocks[$category_id]['ar'][$name])) {
            $stockItem = $stocks[$category_id]['en'][$name] ?? $stocks[$category_id]['ar'][$name];
            $prices = $configs[$type]['getPricing']($item, $stockItem);

            $auto_items[] = array_merge(
                $item,
                [
                    'stock_id' => $stockItem['stock_id'],
                    'returnable_to' => $stockItem['returnable_to'],
                    'unit_price'  => $prices['unit_price'],
					'govt_fee' => $prices['govt_fee'],
					'returnable_amt' => $prices['returnable_amt'],
                    'govt_bank_account' => $stockItem['govt_bank_account'],
                    'commission_loc_user' => $prices['commission_loc_user'],
                    'commission_non_loc_user' => $prices['commission_non_loc_user'] ?? $stockItem['commission_non_loc_user'],
					'bank_service_charge' => $prices['bank_service_charge'] ?? $stockItem['bank_service_charge'],
					'bank_service_charge_vat' => $prices['bank_service_charge_vat'] ?? $stockItem['bank_service_charge_vat'],
                    'category_id' => $category_id,
					'type' => $type
                ]
            );
            continue;
        }

	  	$prices = $configs[$type]['getPricing']($item);
		$stock_prefix = $configs[$type]['stock_prefix'];
		$next_no_col = $configs[$type]['next_no_col'];
		
		begin_transaction();
		// increment the next stock no. This will also help acquire the write lock to avoid inconsistencies.
		$max = "(SELECT max(cast(replace(stock_id, '{$stock_prefix}', '') AS UNSIGNED)) FROM 0_stock_master WHERE stock_id REGEXP '^{$stock_prefix}[[:digit:]]+' )";
		db_query(
			"UPDATE `0_sys_prefs` SET `value` = IFNULL($max, 0) + 2 WHERE `name` = '".$next_no_col."'",
			"Could not update the next stock no"
		);

		// get the previous stock no. That would be our stock no.
		$stock_no = db_query(
			"SELECT (cast(`value` AS UNSIGNED) - 1) stock_no FROM `0_sys_prefs` WHERE `name` = '".$next_no_col."'",
			"Could not retrieve the stock no"
		)->fetch_assoc()['stock_no'];

		$stock_id = $stock_prefix . str_pad($stock_no, 3, "0", STR_PAD_LEFT);
		$category = $categories->get($category_id);

		add_item(
			$stock_id,
			$item['name_en'],
			$item['name_ar'],
			$category_id,
			$configs[$type]['item_tax_type'],
			'each',
			'D',
			$category->dflt_sales_act,
			'',
			$category->dflt_cogs_act,
			'',
			'',
			0,
			0,
			0,
			0,
			1,
			'D',
			100,
			1,
			null,
			null,
			0,
			$prices['govt_fee'],
			$configs[$type]['defaults']['govt_bank_account'],
			$prices['bank_service_charge'] ?? 0,
			$prices['bank_service_charge_vat'] ?? 0,
			$prices['commission_loc_user'] ?? 0,
			$prices['commission_non_loc_user'] ?? 0,
			0,
			$prices['returnable_amt'],
			$configs[$type]['defaults']['returnable_to'] ?? null,
			0.00,
			null,
			0.00,
			null,
			null,
			COSTING_METHOD_NORMAL
		);

		if ($prices['unit_price']) {
			add_item_price(
				$stock_id,
				$configs[$type]['sales_type'],
				$default_currency,
				$prices['unit_price']
			);
		}
		commit_transaction();

		$_item = array_merge(
			$item,
			[
				'stock_id' => $stock_id,
				'returnable_to' => $configs[$type]['defaults']['returnable_to'] ?? null,
				'unit_price'  => $prices['unit_price'],
				'govt_fee' => $prices['govt_fee'],
				'returnable_amt' => $prices['returnable_amt'],
				'govt_bank_account' => $configs[$type]['defaults']['govt_bank_account'],
				'commission_loc_user' => $prices['commission_loc_user'],
				'commission_non_loc_user' => $prices['commission_non_loc_user'] ?? 0,
				'bank_service_charge' => $prices['bank_service_charge'] ?? 0,
				'bank_service_charge_vat' => $prices['bank_service_charge_vat'] ?? 0,
				'category_id' => $category_id,
				'type' => $type
			]
		);
		$stocks[$category_id]['en'][$name] = $_item;
		$stocks[$category_id]['ar'][$name] = $_item;
		$auto_items[] = $_item;
	}

	return $auto_items;
}

function add_item(
    $stock_id,
    $description,
    $long_description,
    $category_id, 
	$tax_type_id,
    $units,
    $mb_flag,
    $sales_account,
    $inventory_account,
	$cogs_account,
    $adjustment_account,
    $wip_account,
    $dimension_id,
	$dimension2_id,
    $no_sale,
    $editable,
    $no_purchase,
	$depreciation_method='D',
    $depreciation_rate=100,
    $depreciation_factor=1,
    $depreciation_start='1971-01-01',
	$fa_class_id=null,
    $sub_category_id = 0,
    $govt_fee = 0,
    $govt_bank_account = '',
    $bank_service_charge = 0,
    $bank_service_charge_vat = 0,
    $commission_loc_user = 0,
    $commission_non_loc_user = 0,
    $pf_amount = 0,
    $returnable_amt = 0,
    $returnable_to = null,
    $split_govt_fee_amt = 0.00,
    $split_govt_fee_acc = null,
	$receivable_commission_amount = 0.00,
	$receivable_commission_account = null,
	$nationality = null,
	$costing_method = null
) {
	$sql = (
        "INSERT INTO ".TB_PREF."stock_master ("
            . " stock_id,"
            . " description,"
            . " long_description,"
            . " category_id,"
            . " tax_type_id,"
            . " units,"
            . " mb_flag,"
            . " costing_method,"
            . " sales_account,"
            . " inventory_account,"
            . " cogs_account,"
            . " adjustment_account,"
            . " wip_account,"
            . " dimension_id,"
            . " dimension2_id,"
            . " no_sale,"
            . " no_purchase,"
            . " editable,"
            . " depreciation_method,"
            . " depreciation_rate,"
            . " depreciation_factor,"
			. (
				isset($depreciation_start)
					? (
						" depreciation_start,"
						. " depreciation_date,"
						. " fa_class_id,"
					) : ""
			)
            . " sub_category_id,"
            . " govt_fee," 
            . " pf_amount," 
		    . " govt_bank_account," 
		    . " bank_service_charge," 
		    . " bank_service_charge_vat," 
		    . " commission_loc_user," 
		    . " commission_non_loc_user,"
            . " returnable_amt,"
            . " returnable_to,"
            . " split_govt_fee_amt,"
            . " split_govt_fee_acc,"
			. " created_date,"
			. " receivable_commission_amount,"
			. " receivable_commission_account,"
			. " nationality"
        . ") VALUES ("
				   . db_escape($stock_id)
			. ", " . db_escape($description)
			. ", " . db_escape($long_description)
			. ", " . db_escape($category_id)
			. ", " . db_escape($tax_type_id)
			. ", " . db_escape($units)
			. ", " . db_escape($mb_flag)
			. ", " . db_escape($costing_method, true)
			. ", " . db_escape($sales_account)
			. ", " . db_escape($inventory_account)
			. ", " . db_escape($cogs_account)
			. ", " . db_escape($adjustment_account)
			. ", " . db_escape($wip_account)
			. ", " . db_escape($dimension_id)
			. ", " . db_escape($dimension2_id)
			. ", " . db_escape($no_sale)
			. ", " . db_escape($no_purchase)
			. ", " . db_escape($editable)
			. ", " . db_escape($depreciation_method)
			. ", " . db_escape($depreciation_rate)
			. ", " . db_escape($depreciation_factor)
			. (
				isset($depreciation_start)
					? (
						  ", " . quote(date2sql($depreciation_start))
						. ", " . quote(date2sql($depreciation_start))
						. ", " . db_escape($fa_class_id)
					) : ''
			)
			. ", " . db_escape($sub_category_id)
			. ", " . db_escape($govt_fee)
			. ", " . db_escape($pf_amount)
			. ", " . db_escape($govt_bank_account)
			. ", " . db_escape($bank_service_charge)
			. ", " . db_escape($bank_service_charge_vat)
			. ", " . db_escape($commission_loc_user)
			. ", " . db_escape($commission_non_loc_user)
			. ", " . db_escape($returnable_amt)
			. ", " . db_escape($returnable_to, true)
			. ", " . db_escape($split_govt_fee_amt)
			. ", " . db_escape($split_govt_fee_acc, true)
			. ", " . quote(date('Y-m-d H:i:s'))
			. ", " . db_escape($receivable_commission_amount)
			. ", " . db_escape($receivable_commission_account, true)
			. ", " . db_escape($nationality, true)
			. "
        )"
    );

	db_query($sql, "The item could not be added");

	$sql = "INSERT INTO ".TB_PREF."loc_stock (loc_code, stock_id)
		SELECT loc_code, ".db_escape($stock_id)
		." FROM ".TB_PREF."locations";

	db_query($sql, "The item locstock could not be added");

	add_item_code($stock_id, $stock_id, $description, $category_id, 1, 0);
}

function delete_item($stock_id)
{
	$sql="DELETE FROM ".TB_PREF."stock_master WHERE stock_id=".db_escape($stock_id);
	db_query($sql, "could not delete stock item");

	/*and cascade deletes in loc_stock */
	$sql ="DELETE FROM ".TB_PREF."loc_stock WHERE stock_id=".db_escape($stock_id);
	db_query($sql, "could not delete stock item loc stock");

	/*and cascade deletes in purch_data */
	$sql ="DELETE FROM ".TB_PREF."purch_data WHERE stock_id=".db_escape($stock_id);
	db_query($sql, "could not delete stock item purch data");

	/*and cascade deletes in prices */
	$sql ="DELETE FROM ".TB_PREF."prices WHERE stock_id=".db_escape($stock_id);
	db_query($sql, "could not delete stock item prices");

	/*and cascade delete the bill of material if any */
	$sql = "DELETE FROM ".TB_PREF."bom WHERE parent=".db_escape($stock_id);
	db_query($sql, "could not delete stock item bom");

	delete_item_kit($stock_id);
}

function get_item($stock_id)
{
	$sql = "SELECT item.*, taxtype.name AS tax_type_name
		FROM ".TB_PREF."stock_master item,"
			.TB_PREF."item_tax_types taxtype
		WHERE taxtype.id=item.tax_type_id
		AND stock_id=".db_escape($stock_id);
	$result = db_query($sql,"an item could not be retreived");

	return db_fetch_assoc($result);
}

function get_items($fixed_asset = 0)
{
	$sql = "SELECT * FROM ".TB_PREF."stock_master WHERE fixed_asset=".db_escape($fixed_asset);
	return db_query($sql,"items could not be retreived");
}

function check_usage($stock_id, $dispmsg = true)
{
    $msg = item_in_foreign_codes($stock_id);

    if ($msg != '') {
        if ($dispmsg) display_error($msg);
        return false;
    }
    return true;
}

function item_in_foreign_codes($stock_id)
{
	$sqls=  array(
	"SELECT COUNT(*) FROM "
		.TB_PREF."stock_moves WHERE stock_id=".db_escape($stock_id) =>
	 trans('Cannot delete this item because there are stock movements that refer to this item.'),
	"SELECT COUNT(*) FROM "
		.TB_PREF."bom WHERE component=".db_escape($stock_id)=>
	 trans('Cannot delete this item record because there are bills of material that require this part as a component.'),
	"SELECT COUNT(*) FROM "
		.TB_PREF."sales_order_details WHERE stk_code=".db_escape($stock_id) =>
	 trans('Cannot delete this item because there are existing sales order items for it.'),
	"SELECT COUNT(*) FROM "
		.TB_PREF."purch_order_details WHERE item_code=".db_escape($stock_id)=>
	 trans('Cannot delete this item because there are existing purchase order items for it.')
	);

	$msg = '';

	foreach($sqls as $sql=>$err) {
		$result = db_query($sql, "could not query stock usage");
		$myrow = db_fetch_row($result);
		if ($myrow[0] > 0) 
		{
			$msg = $err; break;
		}
	}
	if ($msg == '') {

		$kits = get_where_used($stock_id);
		$num_kits = db_num_rows($kits);
		if ($num_kits) {
			$msg = trans("This item cannot be deleted because some code aliases 
				or foreign codes was entered for it, or there are kits defined 
				using this item as component")
				.':<br>';

			while($num_kits--) {
				$kit = db_fetch($kits);
				$msg .= "'".$kit[0]."'";
				if ($num_kits) $msg .= ',';
			}

		}
	}
	return $msg;
}

function
get_items_search($description, $type,$cat_id=null,$featured_stock_ids = [])
{
	global $SysPrefs;

	$sql = "SELECT COUNT(i.item_code) AS kit, i.item_code, i.description, c.description category,s.long_description
          ,s.govt_fee,s.bank_service_charge,s.bank_service_charge_vat,s.pf_amount,c.is_tasheel,pp.price,s.category_id    
 		FROM ".TB_PREF."stock_master s, ".TB_PREF."item_codes i
			INNER JOIN ".TB_PREF."stock_category c ON i.category_id=c.category_id 
			INNER JOIN 0_prices pp on pp.stock_id=i.stock_id AND pp.sales_type_id=1 
		WHERE i.stock_id=s.stock_id
			AND !i.inactive AND !s.inactive
			AND (  i.item_code LIKE " . db_escape("%" . $description. "%") . " OR 
				i.description LIKE " . db_escape("%" . get_post("description"). "%") . " OR 
				s.long_description LIKE " . db_escape("%" . get_post("description"). "%") . " OR 
				c.description LIKE " . db_escape("%" . get_post("description"). "%") . ") ";

	switch ($type) {
		case "sales":
			$sql .= " AND !s.no_sale AND mb_flag <> '".STOCK_TYPE_FIXED_ASSET."'";
			break;
		case "manufactured":
			$sql .= " AND mb_flag = '".STOCK_TYPE_MANUFACTURED."'";
			break;
    	case "purchasable":
    		$sql .= " AND NOT no_purchase AND mb_flag <> '".STOCK_TYPE_FIXED_ASSET."' AND i.item_code=i.stock_id";
    		break;
		case "costable":
			$sql .= " AND mb_flag <> '".STOCK_TYPE_SERVICE."' AND mb_flag <> '".STOCK_TYPE_FIXED_ASSET."' AND  i.item_code=i.stock_id";
			break;
		case "component":
			$parent = $_GET['parent'];
			$sql .= " AND  i.item_code=i.stock_id AND i.stock_id <> '$parent' AND mb_flag <> '".STOCK_TYPE_FIXED_ASSET."' ";
			break;
		case "kits":
			$sql .= " AND !i.is_foreign AND i.item_code!=i.stock_id AND mb_flag <> '".STOCK_TYPE_FIXED_ASSET."'";
			break;
		case "all":
			$sql .= " AND mb_flag <> '".STOCK_TYPE_FIXED_ASSET."' AND i.item_code=i.stock_id";
			break;
	}

	if($cat_id && $cat_id != -1) {
        $sql .= " AND s.category_id = $cat_id";
    }


    if(isset($_POST['sub_filter'])&&!empty($_POST['sub_filter'])) {
        $sub_filter = $_POST['sub_filter'];
        $sql .= " AND s.description LIKE ".db_escape("%".$sub_filter."%");
    }

    //SUB_CAT_FILTER
    if(isset($_POST['subcategory_1']) && !empty($_POST['subcategory_1'])) {
	    $sub_cat_1 = $_POST['subcategory_1'];
	    $sql .= " AND (s.sub_category_id=$sub_cat_1";
	    $sql .= " OR s.sub_category_id IN (select id from 0_subcategories where parent_sub_cat_id=$sub_cat_1)) ";
    }
    if(isset($_POST['subcategory_2']) && !empty($_POST['subcategory_2'])) {
        $sub_cat_2 = $_POST['subcategory_2'];
        $sql .= " AND s.sub_category_id=$sub_cat_2";
    }
    //SUB_CAT_FILTER -- END


    if(!empty($featured_stock_ids)) {


        $featured_stock_ids = implode(",",$featured_stock_ids);
	    $sql .= " AND s.stock_id in ($featured_stock_ids) ";
    }

	if (isset($SysPrefs->max_rows_in_search))
		$limit = $SysPrefs->max_rows_in_search;
	else
		$limit = 10;

	$sql .= " GROUP BY i.item_code ORDER BY i.description LIMIT 0,".(int)($limit);


//	display_error($sql);

	return db_query($sql, "Failed in retreiving item list.");
}

function get_subcategory($parent_sub_cat_id, $main_cat_id)
{

    if(empty($parent_sub_cat_id)) $parent_sub_cat_id = 0;

    $sql = "SELECT * FROM 0_subcategories WHERE parent_sub_cat_id='$parent_sub_cat_id' AND main_cat_id='$main_cat_id'";

    $result = db_query($sql, "an item could not be retreived");
    $return_array = [];
    $return_array[0] = "--";
    while ($row = db_fetch($result)) {
        $return_array[$row['id']] = array($row['description'],$row['arabic_name']);
    }

    return $return_array;

}


function get_item_by_category($category_id = 0, $deprMonth)
{
	$cond = "";
	if ($category_id > 0) {
		$cond = " AND m.category_id = " . $category_id;
	}
	$sql = "SELECT m.*, ROUND(SUM(IFNULL(dt.depreciation_amount, 0)), 2) AS accumulated_depr_amount
			FROM " . TB_PREF . "stock_master m   
			LEFT JOIN " . TB_PREF . "stock_depreciation_details dt ON dt.item_id = m.stock_id AND dt.is_active = 1                                 
			WHERE m.mb_flag = '".STOCK_TYPE_FIXED_ASSET."' AND m.inactive = 0 AND m.depreciation_date < '" . $deprMonth . "' " . $cond . "
			GROUP BY m.stock_id";

	return db_query($sql, "No Items Were Found");
}

function add_items_import($data)
{
	global $Refs;
	begin_transaction();

	$insertArray = array(
        'stock_id'              =>  db_escape($data['itemCode']),
        'description'           =>  db_escape($data['name']),
        'long_description'      =>  db_escape($data['description']),
        'category_id'           =>  db_escape($data['category']),
        'tax_type_id'           =>  db_escape($data['itemTaxType']),
        'units'                 =>  db_escape($data['unitsOfMeasure']),
        'mb_flag'               =>  db_escape(STOCK_TYPE_FIXED_ASSET),
        'sales_account'         =>  db_escape($data['salesAccount']),
        'inventory_account'     =>  db_escape($data['assetAccount']),
        'cogs_account'          =>  db_escape($data['depreciationCostAccount']),
        'adjustment_account'    =>  db_escape($data['disposalAccount']),
        'wip_account'           =>  db_escape($data['wipAccount']),
        'depreciation_method'   =>  db_escape($data['depreciationMethod']),
        'depreciation_rate'     =>  db_escape($data['depreciationRate']),
        'depreciation_factor'   =>  db_escape($data['depreciationFactor']),
		'purchase_date'         =>  quote(date('Y-m-d', strtotime($data['purchaseDate']))),
        'depreciation_start'    =>  quote(date('Y-m-d', strtotime($data['purchaseDate']))),
        'depreciation_date'     =>  quote(date('Y-m-d', strtotime($data['depreciationDate']))),
        'fa_class_id'           =>  db_escape($data['fixedAssetClass']),
        'purchase_cost'         =>  db_escape($data['purchaseCost']),
        'material_cost'         =>  db_escape($data['currentValue']),
        'current_value'         =>  db_escape($data['currentValue']),
        'up_to_date'            =>  db_escape($data['generatedDate']),
        'created_date'          =>  quote(date('Y-m-d H:i:s')),
        'inactive'              =>  db_escape($data['itemStatus']),
		'dimension_id'			=>	db_escape(0),
		'dimension2_id'			=>	db_escape(0),
		'no_sale'				=>	db_escape(0),
		'no_purchase'			=>	db_escape(0),
		'editable'				=> 	db_escape(0),
	);

	$grnReference = $Refs->get_next(ST_SUPPRECEIVE, null, array('supplier' => $data['supplierId'], 'date' => Today()));

	$grnId = add_grn_batch(null, $data['supplierId'], $grnReference, $data['assetLocation'], date(dateformat()), 1);

	$openingData = array (
		'stock_id'	 => $data['itemCode'],
		'description'=> $data['name'],
		'date'       => date('Y-m-d', strtotime($data['purchaseDate'])),
		'value'  	 => ($data['purchaseCost'] - $data['currentValue']),
		'trans_no' 	 => $grnId,
		'trans_date' => date('Y-m-d'),
		'trans_type' => ST_SUPPRECEIVE,
		'memo'       => "Opening Balance"
	);

	// Create a list of column names and  values
	$columns = implode(', ', array_keys($insertArray));
	$values  = implode(', ', array_values($insertArray));

	$sql = "INSERT INTO ".TB_PREF."stock_master ($columns) VALUES ($values)";
	db_query($sql, "The item could not be added");

	$sql = "INSERT INTO ".TB_PREF."loc_stock (loc_code, stock_id)
		SELECT loc_code, ". db_escape($data['itemCode']) ." FROM ". TB_PREF ."locations";
	db_query($sql, "The item locstock could not be added");

	add_item_code($data['itemCode'], $data['itemCode'], $data['name'], $data['category'], 1, 0);

	add_gr_details($grnId, 0, $data['itemCode'], $data['name'], 1);	

	add_stock_moves($grnId, $data);

	add_item_depreciations([$openingData]);

	$Refs->save(ST_SUPPRECEIVE, $grnId, $grnReference);

	add_audit_trail(ST_SUPPRECEIVE, $grnId, date(dateformat()));

	commit_transaction();

}

function add_stock_moves($trans_no, $data)
{
	$sql = "INSERT INTO ".TB_PREF."stock_moves
	(trans_id, trans_no, stock_id, `type`, loc_code, tran_date, price, reference, qty, standard_cost)
	VALUES (NULL, ". $trans_no .", ". db_escape($data['itemCode']) .", ". ST_SUPPRECEIVE .", ". db_escape($data['assetLocation']) .", ". quote(date('Y-m-d')) .", ". db_escape($data['purchaseCost']) .", 'Imported', 1, ". db_escape($data['currentValue']) .")";

	db_query($sql, "The sock moves could not be added");
}

function add_gr_details($grn_batch_id, $po_detail_item = 0, $item_code, $description, $quantity_received = 1)
{
	$sql = "INSERT INTO " . TB_PREF . "grn_items (grn_batch_id, po_detail_item, item_code, description, qty_recd, quantity_inv)
	VALUES (" . db_escape($grn_batch_id) . ", ". db_escape($po_detail_item) . ", " . db_escape($item_code) . ", " . db_escape($description). ", " . db_escape($quantity_received) . ", " . db_escape($quantity_received) . ")";

	db_query($sql, "A GRN detail item could not be inserted.");
}