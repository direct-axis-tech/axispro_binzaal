<?php

namespace App\Models;

use App\Models\Accounting\BankTransaction;
use App\Models\Accounting\JournalTransaction;
use App\Models\Inventory\StockMove;
use App\Models\Inventory\StockReplacement;
use App\Models\Labour\Contract;
use App\Models\Sales\CustomerTransaction;
use App\Models\Sales\SalesOrder;
use App\Models\Sales\SalesOrderDetail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use PDO;

class MetaTransaction extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = '0_meta_transactions';

    /**
     * Indicates if the query should be run for laravel env
     *
     * @var boolean
     */
    private static $isInLaravel = false; 

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Calculates the next trans no
     * 
     * **Note**: This function only works for frontaccounting core.  
     * Since the write operation needs to be done inside a transaction,
     * we cannot use laravel DB functions because - laravel and 
     * frontaccounting both maintain their own connections. Laravel
     * uses PDO while frontaccounting use mysqli, so re-writing is
     * a time consuming task. Hence this bit of function is not usable
     * if we re-write any transaction to use laravel. Then we need to have
     * a different write operation.
     * 
     * @param int $type
     * @return int
     * 
     * @throws InvalidArgumentException
     */

    private static function nextTransNo($type){
        return self::runQuery(
            "SELECT next_trans_no FROM 0_meta_transactions WHERE id = {$type}",
            "Could not get next transaction number"
        )['next_trans_no'];
    }

    private static function incrementTransNo($type){
        return self::runQuery(
            "UPDATE `0_meta_transactions` SET next_trans_no = next_trans_no + 1 WHERE id = {$type}",
            "Could not get next transaction number"
        );
    }

    private static function existsTransNo($transNo,$type){
        $tableData = SELF::getTableDetails($type);

        $conditions = "{$tableData['col_trans_no']} = {$transNo}";
        if (!empty($tableData['col_type'])) {
            $conditions .= " AND `{$tableData['col_type']}` = {$type}";
        }

        $count =  self::runQuery(
            "SELECT COUNT(*) as cnt FROM {$tableData['table']} WHERE {$conditions}",
            "Could not check if trans_no already exists in the table"
        )['cnt'];

        return $count > 0;
    }

    private static function getTableDetails ($type){
        return self::runQuery(
            "SELECT `table`,col_trans_no,col_type FROM 0_meta_transactions WHERE id = {$type}",
            'Could not get table name'
        );
    }

    public static function getNextTransNo($type) {
        if (!static::canGetNextTransNo($type)) {
            throw new InvalidArgumentException("$type is not yet implemented");
        }

        // Lets ensure first that no other transaction can read the
        // next_trans_no until we finsh this transaction
        do {
            SELF::incrementTransNo($type);
            $nextTransNo = SELF::nextTransNo($type);

            // $nextTransNo = 1 means it was never initialized before. So initialize
            if ($nextTransNo == 1) {
                $nextTransNo = static::initializeNextTransNo($type);
            }
        } while (SELF::existsTransNo($nextTransNo - 1, $type));

        return --$nextTransNo;
    }

    /**
     * Determines if the next transaction number can be obtained without a full table scan
     *
     * @param int $type
     * @return boolean
     */
    public static function canGetNextTransNo($type) {
        return in_array($type, static::getImplemented());
    }

    /**
     * Get all the implemented transactions
     * 
     * Retrieves all the transactions that we have implemented
     * so far: where we can get the `next_trans_no` from
     * `meta_transactions` table itself - avoiding the need to
     * do a buggy `max(trans_no)` SQL operation, that could give
     * us the same `max(trans_no)` in certain scenarios.
     * Hence leading us to a duplicate
     */
    public static function getImplemented() {
        return [
            CustomerTransaction::INVOICE,
            CustomerTransaction::CREDIT,
            CustomerTransaction::PAYMENT,
            CustomerTransaction::DELIVERY,
            CustomerTransaction::REFUND,
            BankTransaction::CREDIT,
            BankTransaction::DEBIT,
            BankTransaction::TRANSFER,
            SalesOrder::ORDER,
            SalesOrder::QUOTE,
            Contract::TEMPORARY_CONTRACT,
            Contract::CONTRACT,
            StockMove::STOCK_RETURN,
            StockReplacement::STOCK_REPLACEMENT,
            JournalTransaction::JOURNAL,
            JournalTransaction::PAYROLL,
            SalesOrderDetail::ORDER_LINE_ITEM
        ];
    }

    /**
     * Initialize the next_trans_no for the given type and return it
     * 
     * Idealy we would need to lock the tables to perform this operation
     * However, this function is only being called from the `getNextTransNo`,
     * which is already inside a transaction.
     * By that time we already have the lock aquired on `meta_transactions`
     * table. So with that we be sure that there will not be concurrent
     * write operation of the same type.
     * If we were to perform a `lock tables` it would implicitly commit the
     * transaction which is not what we want.
     *
     * @param int $type
     * @return int
     */
    private static function initializeNextTransNo($type) {
        // Retrieve the metadata about this type of transaction
        [
            'table' => $table,
            'col_type' => $typeCol,
            'col_trans_no' => $transNoCol
        ] = self::runQuery(
            "SELECT * FROM `0_meta_transactions` WHERE id = {$type}",
            "Could not get the metadata about the transaction {$type}"
        );

        // Build the conditions. Some of them does not have an type stored in: like workorder
        $conditions = '1 = 1';
        if (!empty($typeCol)) {
            $conditions .= " AND `{$typeCol}` = {$type}";
        }

        // Retrieve the current maximum
        $nextTransNo = self::runQuery(
            "SELECT max(`{$transNoCol}`) as max_trans_no FROM `{$table}` WHERE {$conditions}",
            "Could not get the next_trans_no"
        )['max_trans_no'];

        // The next_trans_no is actualy +1 which is happening at this point.
        // So initalise it to +2 for the coming transaction
        $nextTransNo = $nextTransNo + 2;

        // Save
        self::runQuery(
            "UPDATE `0_meta_transactions` SET next_trans_no = {$nextTransNo} WHERE id = {$type}",
            "Could not save the next_trans_no"
        );

        return $nextTransNo;
    }

    /**
     * Set the mode to is in laravel
     *
     * @param boolean $isInLaravel
     * @return void
     */
    public static function inLaravel($isInLaravel = true) {
        self::$isInLaravel = $isInLaravel;
    }

    /**
     * Get the mode if its in laravel
     *
     * @return void
     */
    public static function isInLaravel() {
        return self::$isInLaravel;
    }

    /**
     * Run query depending on the environment
     *
     * @param string $qry
     * @param string $msg
     * @return array|bool
     */
    private static function runQuery($qry, $msg = null) {
        if (self::$isInLaravel) {
            $conn = DB::connection()->getPdo();

            $stmt = $conn->query($qry);

            if ($stmt->columnCount() > 0) {
                return $stmt->fetch(PDO::FETCH_ASSOC);
            }

            return true;
        }

        $result = db_query($qry, $msg);
        
        if (gettype($result) == "boolean") {
            return $result;
        }

        return $result->fetch_assoc();
    }
}
