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
    
    return _postOpeningGl(_parseCsv($inputs['filePath']), $inputs['openingDate']);
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
        'account',
        'person_type_id',
        'person_id',
        'amount',
        'memo'
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

    if (!isset($argv[2])) {
        return _err("No arguments were passed.\n Argument 2: The opening entry date");
    }

    $date = $argv[2];

    if (DateTime::createFromFormat(DB_DATE_FORMAT, $date)->format(DB_DATE_FORMAT) != $date) {
        return _err("The opening entry date must be in the given format. eg: " . date(DB_DATE_FORMAT));
    }

    return [
        'filePath' => $filePath,
        'openingDate' => $date
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

/**
 * Post the opening GL Entry
 *
 * @param array $data
 * @param string $transDate in the format 2004-01-01
 * @return void
 */
function _postOpeningGl($data, $transDate)
{
    begin_transaction();
    $transType = JournalTransaction::JOURNAL;
    $transDate = sql2date($transDate);

    $cart = new items_cart($transType);
    $cart->tran_date = $cart->doc_date = $cart->event_date = $transDate;
    $cart->reference = MetaReference::getNext($transType, null, $cart->tran_date, true);
    $cart->memo_ = "Opening Balances";

    $chart = Ledger::all()->keyBy('account_code');
    $debtors = Customer::all()->keyBy('debtor_no');
    $suppliers = Supplier::all()->keyBy('supplier_id');
    
    $taxAccounts = [];
    foreach (DB::table('0_tax_types')->get() as $taxType) {
        $taxAccounts[$taxType->sales_gl_code] = true;
        $taxAccounts[$taxType->purchasing_gl_code] = true;
    }

    $shouldIncludeInTaxRegister = false;

    foreach ($data as $i => $row) {
        if ($row['amount'] == 0) {
            continue;
        }

        if (!$chart->get($row['account'])) {
            return _err("At Line: ".($i + 2).", The account does not exist");
        }

        if (isset($taxAccounts[$row['account']])) {
            $shouldIncludeInTaxRegister = true;
        }

        if (
            $row['person_type_id'] == PT_SUPPLIER
            && !$suppliers->get($row['person_id'])
        ) {
            return _err("At Line: ".($i + 2).", The supplier does not exist");
        }
        
        if (
            $row['person_type_id'] == PT_CUSTOMER
            && !$debtors->get($row['person_id'])
        ) {
            return _err("At Line: ".($i + 2).", The Customer does not exist");
        }

        $cart->add_gl_item(
            $row['account'],
            0,
            0,
            $row['amount'],
            $row['memo'],
            null,
            $row['person_id']
        );
    }
    
    if (abs($cart->gl_items_total_debit() + $cart->gl_items_total_credit()) > 0.5) {
        return _err("The total debit and credit value does not match");
    }

    if ($shouldIncludeInTaxRegister) {
        $cart->tax_info = $cart->collect_tax_info();
    }

    write_journal_entries($cart);
    commit_transaction();

    echo "Opening Balance import successful. Journal Reference: ".$cart->reference;
    exit(0);
}

