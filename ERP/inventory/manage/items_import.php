<?php
/**********************************************************************
Copyright (C) FrontAccounting, LLC.
Released under the terms of the GNU General Public License, GPL, 
as published by the Free Software Foundation, either version 3 
of the License, or (at your option) any later version.
This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  
See the License here <http://www.gnu.org/licenses/gpl-3.0.html>.
***********************************************************************/
$page_security = 'SA_ASSET_IMPORT';
$path_to_root = "../..";

include($path_to_root . "/includes/session.inc");
include($path_to_root . "/reporting/includes/tcpdf.php");


$js = "";
if (user_use_date_picker())
	$js .= get_js_date_picker();

page(trans("Fixed Asset Import"), false, false, "", $js);

include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/fixed_assets/includes/fixed_assets_db.inc");
include_once($path_to_root . "/inventory/includes/inventory_db.inc");

$user_comp = user_company();
$_POST['fixed_asset']  = 1;


//------------------------------------------------------------------------------------


if (get_post('fixed_asset')) {

	check_db_has_fixed_asset_categories(trans("There are no fixed asset categories defined in the system. At least one fixed asset category is required to add a fixed asset."));
	check_db_has_fixed_asset_classes(trans("There are no fixed asset classes defined in the system. At least one fixed asset class is required to add a fixed asset."));
} else{
	check_db_has_stock_categories(trans("There are no item categories defined in the system. At least one item category is required to add a item."));

	check_db_has_item_tax_types(trans("There are no item tax types defined in the system. At least one item tax type is required to add a item."));
}

//------------------------------------------------------------------------------------


if (isset($_POST["ADD_ITEM"]) && isset($_FILES["assets_import_file"])) {

    $file = $_FILES["assets_import_file"]["tmp_name"];

    // Check if a file was uploaded successfully
    if (is_uploaded_file($file)) {

        // Open the CSV file for reading
        if (($handle = fopen($file, "r")) !== FALSE) {
            // Flag to skip the first line (header)
            $skipHeader = true;
            $rowCount   = 2;
			$importedData = []; // Array to store validated data
            $validationErrors = [];

            // Loop through each row in the CSV file
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {

                // Skip the first line (header)
                if ($skipHeader) {
                    $skipHeader = false;
                    continue;
                }
                $columnCount = 0;
                
                // Validate and sanitize data
                $inputData = array(
                    'itemCode' 				  => trim($data[$columnCount++]),
                    'name' 					  => trim($data[$columnCount++]),
                    'description' 			  => trim($data[$columnCount++]),
                    'category' 				  => trim($data[$columnCount++]),
                    'itemTaxType' 			  => trim($data[$columnCount++]),
                    'unitsOfMeasure' 		  => trim($data[$columnCount++]),
                    'fixedAssetClass' 		  => trim($data[$columnCount++]),
					'assetLocation' 		  => trim($data[$columnCount++]),
                    'depreciationMethod'	  => trim($data[$columnCount++]),
                    'depreciationRate'		  => trim($data[$columnCount++]),
                    'depreciationDate'		  => date('Y-m-d',strtotime(trim($data[$columnCount++]))),
                    'salesAccount'  		  => trim($data[$columnCount++]),
                    'assetAccount' 			  => trim($data[$columnCount++]),
                    'depreciationCostAccount' => trim($data[$columnCount++]),
                    'disposalAccount'		  => trim($data[$columnCount++]),
                    'itemStatus' 			  => trim($data[$columnCount++]),
					'purchaseDate' 			  => trim($data[$columnCount++]),
					'purchaseCost' 			  => trim($data[$columnCount++]),
					'currentValue' 			  => trim($data[$columnCount++]),
					'supplierReferenceNo'     => trim($data[$columnCount++]),
					'generatedDate' 		  => date('Y-m-d',strtotime(trim($data[$columnCount++]))),
                    'wipAccount'			  => 1530,
                    'depreciationFactor'	  => 0,
                );

				$validationResult = validate_data($inputData);

				if ($validationResult['valid']) {

                	$inputData['category'] = get_item_category_by_name($inputData['category'])['category_id'];
                	$inputData['itemTaxType'] = get_item_tax_type_by_name($inputData['itemTaxType'])['id'];
                	$inputData['unitsOfMeasure'] = get_item_unit($inputData['unitsOfMeasure'])['abbr'];
					$inputData['supplierId'] = get_supplier_by_refno($inputData['supplierReferenceNo'])['supplier_id'];

					if($inputData['depreciationRate'] > 100 ) {
						$inputData['depreciationRate'] = 100;
					} elseif ($inputData['depreciationRate'] < 0 ) {
						$inputData['depreciationRate'] = 0;
					}

                    $importedData[] = $inputData;

                } else {
                    // If validation fails, store the error message
                    $validationErrors[] = "Error importing at row ". $rowCount .' :- '. implode(' , ', $validationResult['errors']);
                }

                $rowCount++;
            }

            fclose($handle);

            // Check if there are validation errors
            if (!empty($validationErrors)) {
                // Display validation errors and do not proceed with insertion
                foreach ($validationErrors as $error) {
                    display_error( trans($error));
                }

            } else {
                // All rows are valid, proceed with insertion
                foreach ($importedData as $inputData) {
                    // Insert each row into the database using your insert function
					add_items_import($inputData);
                }
                $importedCount = count($importedData);
                display_notification(trans($importedCount . " Items have been imported."));
                
            }

            clear_data();
        } else {
            display_error(trans('Error opening the file.'));
        }
    } else {
        display_error(trans('Select an attachment file.'));
    }
}


function clear_data()
{
	unset($_POST['ADD_ITEM']);
	unset($_FILES['assets_import_file']);
}

function validate_data($data)
{
	$validationResult = array();
	$validateFlag     = true;
	$validationResult['valid'] = false;

	# Validate Item Code
	if(empty($data['itemCode'])) {

		$validationResult['errors'][] = 'Item Code is required.';
		$validateFlag = false;

	} elseif(strstr($data['itemCode'], " ") || strstr($data['itemCode'],"'") || 
		strstr($data['itemCode'], "+") || strstr($data['itemCode'], "\"") || 
		strstr($data['itemCode'], "&") || strstr($data['itemCode'], "\t")) {

		$validationResult['errors'][] = 'The item code cannot contain any of the following characters -  & + OR a space OR quotes.';
		$validateFlag = false;

	} elseif(db_num_rows(get_item_kit($data['itemCode']))) {

		$validationResult['errors'][] = 'This item code is already assigned to stock item or sale kit.';
		$validateFlag = false;
	}

	# Validate Asset Name
	if(empty($data['name'])) {

		$validationResult['errors'][] = 'Name is required.';
		$validateFlag = false;
	}

	# Validate Asset Category
	if(empty($data['category'])) {

		$validationResult['errors'][] = 'Category is required.';
	    $validateFlag = false;
	} elseif(!(get_item_category_by_name($data['category']))) {

		$validationResult['errors'][] = 'The category is not found in category master.';
		$validateFlag = false;
	}

	# Validate Asset Tax Type
	if(empty($data['itemTaxType'])) {

		$validationResult['errors'][] = 'Item Tax Type is required.';
	    $validateFlag = false;
	} elseif(!(get_item_tax_type_by_name($data['itemTaxType']))) {

		$validationResult['errors'][] = 'The asset tax type is not found in tax master.';
	    $validateFlag = false;
	}

	# Validate Asset Unit
	if(empty($data['unitsOfMeasure'])) {

		$validationResult['errors'][] = 'Units Of Measure is required.';
	    $validateFlag = false;
	} elseif(!(get_item_unit($data['unitsOfMeasure']))) {

		$validationResult['errors'][] = 'The asset unit of measure is not found in master.';
	    $validateFlag = false;
	}

	# Validate Asset Class
	if(empty($data['fixedAssetClass'])) {

		$validationResult['errors'][] = 'Fixed Asset Class is required.';
	    $validateFlag = false;
	} elseif(!(get_fixed_asset_class($data['fixedAssetClass']))) {

		$validationResult['errors'][] = 'The asset class is not found in asset class master.';
	    $validateFlag = false;
	}

	# Validate Asset Location
	if(empty($data['assetLocation'])) {

		$validationResult['errors'][] = 'Asset Location is required.';
	    $validateFlag = false;
	} elseif(!(get_item_location($data['assetLocation']))) {

		$validationResult['errors'][] = 'The asset location is not found in asset location master.';
	    $validateFlag = false;
	}

	# Validate Depreciation Method
	if(empty($data['depreciationMethod'])) {

		$validationResult['errors'][] = 'Depreciation Method is required.';
		$validateFlag = false;
	} elseif(!in_array($data['depreciationMethod'], array('S'))) {

		$validationResult['errors'][] = 'The depreciation method is not found.';
		$validateFlag = false;
	}

	# Validate Depreciation Rate
	if(empty($data['depreciationRate'])) {

		$validationResult['errors'][] = 'Depreciation Rate is required.';
		$validateFlag = false;
	} elseif(!is_numeric($data['depreciationRate'])) {

		$validationResult['errors'][] = 'Depreciation rate must be numeric.';
		$validateFlag = false;
	}

	# Validate Depreciation Date
	if(empty($data['depreciationDate'])) {

		$validationResult['errors'][] = 'Depreciation date is required.';
		$validateFlag = false;
	} elseif(date('Y-m-d', strtotime($data['depreciationDate'])) == '1970-01-01') {

		$validationResult['errors'][] = 'Enter a valid depreciation date.';
		$validateFlag = false;
	}

	# Validate Sales Account
	if(empty($data['salesAccount'])) {

		$validationResult['errors'][] = 'Sales Account is required.';
		$validateFlag = false;
	} elseif(!(get_gl_account($data['salesAccount']))) {
		
		$validationResult['errors'][] = 'Enter a valid sales account details.';
		$validateFlag = false;
	}

	# Validate Asset Account
	if(empty($data['assetAccount'])) {

		$validationResult['errors'][] = 'Asset Account is required.';
		$validateFlag = false;
	} elseif(!(get_gl_account($data['assetAccount']))) {
		
		$validationResult['errors'][] = 'Enter a valid asset account details.';
		$validateFlag = false;
	}

	# Validate Depreciation Cost Account
	if(empty($data['depreciationCostAccount'])) {

		$validationResult['errors'][] = 'Depreciation Cost Account is required.';
		$validateFlag = false;
	} elseif(!(get_gl_account($data['depreciationCostAccount']))) {
		
		$validationResult['errors'][] = 'Enter a valid depreciation cost account details.';
		$validateFlag = false;
	}

	# Validate Disposal Account
	if(empty($data['disposalAccount'])) {

		$validationResult['errors'][] = 'Disposal Account is required.';
		$validateFlag = false;
	} elseif(!(get_gl_account($data['disposalAccount']))) {
		
		$validationResult['errors'][] = 'Enter a valid disposal account details.';
		$validateFlag = false;
	}

	# Validate Item Status
	if(strlen($data['itemStatus']) == 0) {

		$validationResult['errors'][] = 'Item Status is required.';
		$validateFlag = false;
	} elseif(!in_array($data['itemStatus'], array(0,1)) ) {

		$validationResult['errors'][] = 'Item Status must be 0 or 1.';
		$validateFlag = false;
	}

	# Validate Purchase Date
	if(empty($data['purchaseDate'])) {

		$validationResult['errors'][] = 'Item purchase date is required.';
		$validateFlag = false;
	} elseif(date('Y-m-d', strtotime($data['purchaseDate'])) == '1970-01-01') {

		$validationResult['errors'][] = 'Enter a valid purchase date.';
		$validateFlag = false;
	}

	# Validate Purchase Cost
	if(empty($data['purchaseCost'])) {

		$validationResult['errors'][] = 'Item purchase cost is required.';
		$validateFlag = false;
	} elseif(!is_numeric($data['purchaseCost'])) {

		$validationResult['errors'][] = 'Enter a valid purchase cost.';
		$validateFlag = false;
	}

	# Validate Current Value
	if(empty($data['currentValue'])) {

		$validationResult['errors'][] = 'current value is required.';
		$validateFlag = false;
	} elseif(!is_numeric($data['currentValue'])) {

		$validationResult['errors'][] = 'Enter a valid current value.';
		$validateFlag = false;
	}

	# Validate Supplier Reference No
	if(empty($data['supplierReferenceNo'])) {

		$validationResult['errors'][] = 'Supplier Reference No is required.';
		$validateFlag = false;
	} elseif(!(get_supplier_by_refno($data['supplierReferenceNo']))) {

		$validationResult['errors'][] = 'Enter a valid supplier reference number.';
		$validateFlag = false;
	}

	# Validate Report Generated Date
	if(empty($data['generatedDate'])) {

		$validationResult['errors'][] = 'Generated date is required.';
		$validateFlag = false;
	} elseif(date('Y-m-d', strtotime($data['generatedDate'])) == '1970-01-01') {

		$validationResult['errors'][] = 'Enter a valid generated date.';
		$validateFlag = false;
	}

	# Validation Success
	if($validateFlag) {
		$validationResult['valid'] = true;
	}

	return $validationResult;

}


//------------------------------------------------------------------------------------


br();


start_form(true, '', $path_to_root. '/fixed_assets/asset_sample_import_file.php', 'sample_import_form');
submit_center('download_csv', trans("Click here to download sample CSV file"), true, 'Download sample CSV file', false, '');
hidden('sampleFile', 1);
end_form();

br(3);

start_form(true);
start_table(TABLESTYLE2,"style='width:25%'");
file_row(trans("Attached File (.csv)") . ":", 'assets_import_file', 'assets_import_file');
end_table(1);
submit_center('ADD_ITEM', trans("Import File"), true, 'Import', false, 'add.png');
end_form();

br(2);

?>


    <style>
        /* CSS styles to highlight instructions */
        .instructions {
            background-color: #f0f0f0;
            border: 1px solid #ddd;
            padding: 10px 10px 10px 20px;
        }

        .instructions h4 {
            color: #333;
        }
        
        .instructions span {
            color: red;
        }
    </style>

    <div class="instructions">
        <h4>CSV File Import Instructions</h4>
        <p>Your CSV file should have the following columns:</p>
        <ul>
            <li>Item Code :- <span>Must be unique, No special characters and spaces included. </span> </li>
            <li>Name :- <span>Name of asset. </span> </li>
            <li>Description :- <span>Description of the asset. </span> </li>
            <li>Category :- <span>Category Name from category master </span> </li>
            <li>Item Tax Type :- <span>Tax type name from Item Tax Type Master </span> </li>
            <li>Units Of Measure :- <span>Unit name from Units of Measure Master </span> </li>
            <li>Fixed Asset Class :- <span>Class name from Fixed Asset Class Master. </span> </li>
			<li>Location :- <span>Location code from Fixed Asset Location Master. </span> </li>
            <li>Depreciation Method :- <span>S  (S - Straight Line) </span> </li>
            <li>Depreciation Rate :- <span>Yearly depreciation rate. </span> </li>
			<li>Date Of Last Depreciation :- <span>Date of last depreciation occurs, (Y-m-d Format) </span> </li>
            <li>Sales Account :- <span>Account Code of sales account </span> </li>
            <li>Asset Account :- <span>Account Code of asset account </span> </li>
            <li>Depreciation Cost Account :- <span>Account Code of depreciation cost account </span> </li>
            <li>Depreciation/Disposal Account :- <span>Account Code of depreciation/disposal account </span> </li>
            <li>Item Status :- <span>0 or 1  (0 - Active, 1 - Inactive) </span> </li>
			<li>Purchase Date :- <span>Date of purchase, (Y-m-d Format) </span> </li>
			<li>Purchase Cost :- <span>Cost of asset when purchased </span> </li>
			<li>Current Value :- <span>Cost of asset when its imported </span> </li>
			<li>Supplier Reference No :- <span>Reference Number Of The Supplier </span> </li>
			<li>Generated Date :- <span>Up to Date, (Y-m-d Format) </span> </li>
        </ul>

    </div>

<?php




// //------------------------------------------------------------------------------------

br(2);

end_page();

?>




