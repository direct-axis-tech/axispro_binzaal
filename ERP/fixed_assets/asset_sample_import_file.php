<?php

if(isset($_POST['sampleFile'])) {

    // Clear the output buffer
    ob_clean();

    // Set the response headers for CSV download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="AssetsImport.csv"');

    // Disable caching
    header('Cache-Control: no-store, no-cache');

    // Create a file handle to write CSV data
    $output = fopen('php://output', 'w');

    // Define the CSV header with the specified fields
    $csvHeader = array(
        'Item Code',
        'Name',
        'Description',
        'Category',
        'Item Tax Type',
        'Units Of Measure',
        'Fixed Asset Class',
        'Location',
        'Depreciation Method',
        'Depreciation Rate',
        'Date Of Last Depreciation',
        'Sales Account',
        'Asset Account',
        'Depreciation Cost Account',
        'Depreciation/Disposal Account',
        'Item Status',
        'Purchase Date',
        'Purchase Cost',
        'Current Value',
        'Supplier Reference No',
        'Generated Date'
    );

    // Write the header to the CSV
    fputcsv($output, $csvHeader);

    // Exit to prevent any additional output
    exit;
}

?>