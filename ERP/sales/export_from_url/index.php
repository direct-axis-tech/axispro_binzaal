<?php
$path_to_root = "../..";
include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/data_checks.inc");
include_once($path_to_root . "/sales/includes/sales_db.inc");
include_once($path_to_root . "/sales/includes/sales_ui.inc");
include_once($path_to_root . "/reporting/includes/reporting.inc");
include_once($path_to_root . "/taxes/tax_calc.inc");
include_once($path_to_root . "/admin/db/shipping_db.inc");


$directory_path = getcwd();
$time_stamp = strtotime('now');
$file_name = "URL_EXPORT_";

if ($_GET['customer_discount_list'] == 1) {
    exportCustomerDiscountInfo();
    exit;
}


function exportCustomerDiscountInfo()
{
    global $file_name;

    $file_name .= "CUSTOMER_DISCOUNT_INFO";
    deleteExistingFiles($file_name);
    $csv_path = generatePathToFile($file_name);

    $sql = "(SELECT 'Customer Name','Category','Discount','Commission','Reward')
        UNION 
        SELECT b.name AS CustomerName,c.description AS Category,a.discount AS Discount,
        a.customer_commission AS Commission,a.reward_point AS RewardPOINT
        FROM 0_customer_discount_items a
        LEFT JOIN 0_debtors_master b ON b.debtor_no=a.customer_id
        LEFT JOIN 0_stock_category c ON c.category_id=a.item_id 
        INTO OUTFILE '$csv_path'
        FIELDS TERMINATED BY ','
        ENCLOSED BY '\"'
        LINES TERMINATED BY '\\n'";

    db_query($sql);

    downloadFile();

}

function deleteExistingFiles($file_name)
{
    $mask = $file_name . '*.*';
    array_map('unlink', glob($mask));
}

function generatePathToFile($file_name)
{
    global $time_stamp, $directory_path,$file_name;
    $file_name = $file_name . $time_stamp;
    $csv_path = $directory_path . "/" . $file_name . ".csv";
    return str_replace('\\', '/', $csv_path);
}

function downloadFile()
{
    global $path_to_root, $file_name;
    $download_path = $path_to_root . "/sales/export_from_url/" . $file_name . ".csv";
    echo '<script>window.open("' . $download_path . '","_blank")</script>';
}






