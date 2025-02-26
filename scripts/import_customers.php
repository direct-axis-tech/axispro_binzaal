<?php

use App\Models\Accounting\JournalTransaction;
use App\Models\Accounting\Ledger;
use Symfony\Component\Console\Application as ConsoleApplication;
use App\Models\MetaReference;
use App\Models\Purchase\Supplier;
use App\Models\Sales\Customer;

$path_to_root = "../ERP";

require_once __DIR__ . "/../ERP/includes/console_session.inc";
require_once __DIR__ . "/../ERP/includes/ui/ui_globals.inc";

try {
    $inputs = _validateArguments();
    
    $data = _importCustomers(_parseCsv($inputs['filePath']));
    _storeImportedCustomers($data);
    return;
}

catch (Throwable $e) {
    (new ConsoleApplication)->renderThrowable($e, new Symfony\Component\Console\Output\ConsoleOutput());
}

/*
 |--------------------------------------------------------------------------------------
 |--------------------------------------------------------------------------------------
 */

/**
 * Return error to the console
 *
 * @param string $msg
 * @return void
 */
function _err($msg) {
    echo "Err: " . $msg;
    exit(1);
}

/**
 * Check if the file is a csv and is readable text
 *
 * @param string $filePath
 * @return boolean
 */
function _isFileImportable(string $filePath): bool
{
    // Check for .csv extension
    $fileInfo = pathinfo($filePath);
    if (strtolower($fileInfo['extension']) !== 'csv') {
        return false;
    }

    // Open the file in read-only binary mode
    $handle = fopen($filePath, 'rb');
    if (!$handle) {
        return false; // Error opening the file
    }

    // Read the header portion of the file
    $header = fgetcsv($handle, 1000);
    fclose($handle);

    if (!$header || !is_array($header)) {
        return false;
    }

    $expected = [
        'Sl No.',
        'Name',
        'Bal'
    ];
    $header = array_flip(_clean_header($header));

    foreach ($expected as $k) {
        if (!isset($header[$k])) {
            echo "Could not find the header: {$k} in the csv\n";
            return false;
        }
    }

    return true;
}

/**
 * Parses the csv file and return the array
 *
 * @param string $filePath
 * @return array
 */
function _parseCsv(string $filePath): array
{
    // detect the line endings automatically
    ini_set('auto_detect_line_endings', TRUE);
    
    $data = [];
    $header = [];
    // read the csv
    if (($handle = fopen($filePath, "r")) !== false) {
        while (($row = fgetcsv($handle, 1000)) !== false) {
            if (!$header) {
                $header = _clean_header($row);
                continue;
            }

            $_row = [];
            foreach ($header as $i => $k) {
                $_row[$k] = $row[$i];
            }

            $data[] = $_row;
        }

        fclose($handle);
    }

    return $data;
}

/**
 * Import the customers
 *
 * @param array $data
 * @return void
 */
function _importCustomers($data)
{
    $customer = Customer::find(Customer::WALK_IN_CUSTOMER);
    $branch = $customer->default_branch;

    array_walk($data, function (&$row) use ($customer, $branch) {
        $nextRef = Customer::getNextCustomerRef();

        $new = $customer->replicate();
        $new->setRelations([]);
        $new->customer_type = Customer::TYPE_CASH_CUSTOMER;
        $new->name = $row['Name'];
        $new->contact_person = '';
        $new->tax_id = '';
        $new->debtor_ref = $nextRef;
        $new->credit_limit = pref('gl.customer.default_credit_limit') ?: 0;
        $new->balance = $row['Bal'];
        $new->mobile = '';
        $new->debtor_email = '';
        $new->iban_no = '';
        $new->cr_lmt_warning_lvl = pref('customer.dflt_cr_lmt_warning_lvl');
        $new->cr_lmt_notice_lvl = pref('customer.dflt_cr_lmt_notice_lvl');

        $newBranch = $branch->replicate();
        $newBranch->debtor_no = null;
        $newBranch->branch_ref = $nextRef;
        $newBranch->br_name = $row['Name'];
        $newBranch->receivables_account = pref('gl.sales.walkin_receivable_act');

        DB::transaction(function() use($new, $newBranch) {
            $new->save();
            $new->branches()->save($newBranch);

            $person_id = DB::table('0_crm_persons')->insertGetId([
                'ref' => $new->debtor_ref,
                'name' => $new->name,
                'name2' => '',
                'address' => $newBranch->br_address,
                'phone' => $new->mobile,
                'phone2' => '',
                'fax' => '',
                'email' => $new->debtor_email,
                'lang' => '',
                'notes' => ''
            ]);

            DB::table('0_crm_contacts')->insert([
                'type' => 'cust_branch',
                'action' => 'general',
                'entity_id' => $new->default_branch->branch_code,
                'person_id' => $person_id
            ]);
            
            DB::table('0_crm_contacts')->insert([
                'type' => 'customer',
                'action' => 'general',
                'entity_id' => $new->debtor_no,
                'person_id' => $person_id
            ]);
        });

        $row['debtor_ref'] = $new->debtor_ref;
        $row['debtor_no'] = $new->debtor_no;
    });

    return $data;
}

/**
 * Store the imported customers
 *
 * @param array $data
 * @return void
 */
function _storeImportedCustomers($data)
{
    $filePath = storage_path("logs/imported_customers.csv");
    $handle = fopen($filePath, 'wb');

    if (!$handle) {
        return _err("Error opening the file for writing");
    }

    fputcsv($handle, array_keys($data[0]));

    foreach ($data as $row) {
        fputcsv($handle, $row);
    }

    fclose($handle);
}

/**
 * Validate the command line arguments
 *
 * @return array
 */
function _validateArguments()
{
    global $argv;

    // Check if there's at least one argument passed (excluding the script name)
    if (!isset($argv[1])) {
        return _err("No arguments were passed.\n Argument 1: file path is required");
    }

    $filePath = $argv[1];

    if (!file_exists($filePath)) {
        return _err("The file path '{$filePath}' does not exists");
    }

    if (!is_readable($filePath)) {
        return _err("Access denied while trying to read the file path: '{$filePath}'");
    }

    if (!_isFileImportable($filePath)) {
        return _err("The file '{$filePath}' must be a valid csv file");
    }

    return [
        'filePath' => $filePath
    ];
}

function _clean_header($header) {
    return array_map(
        function ($str) {
            $bom = pack('H*','EFBBBF');
            $str = preg_replace("/^$bom/", '', $str);
            return $str;
        },
        $header
    );
}