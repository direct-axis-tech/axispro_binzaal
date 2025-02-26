<?php
/**********************************************************************
 * Direct Axis Technology L.L.C.
 * Released under the terms of the GNU General Public License, GPL,
 * as published by the Free Software Foundation, either version 3
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the License here <http://www.gnu.org/licenses/gpl-3.0.html>.
 ***********************************************************************/

use App\Events\TransactionEventDispatcher;

//
//	General database functions common for all modules.
//
//-------------------------------------------------------------------
// Multilevel transaction control.
//
$transaction_level = 0;

function begin_transaction()
{
    global $transaction_level;

    if (!$transaction_level) {
        db_query("BEGIN", "could not start a transaction");
    }
    $transaction_level++;
}

function commit_transaction()
{
    global $transaction_level;

    $transaction_level--;

    if (!$transaction_level) {
        db_query("COMMIT", "could not commit a transaction");
        app(TransactionEventDispatcher::class)->flushQueuedEvents();
    }
}

/*
	This function is called on end of script execution to cancel
	all aborted transactions (if any)
*/
function cancel_transaction()
{
    global $transaction_level;

    if ($transaction_level) {
        db_query("ROLLBACK", "could not cancel a transaction");
    }
    $transaction_level = 0;
}

/**
 * Checks if the execution is currently inside a db transaction
 *
 * @return boolean
 */
function is_in_transaction()
{
    global $transaction_level;

    return $transaction_level > 0;
}

//-----------------------------------------------------------------------------
//	Update record activity status.
//
function update_record_status($id, $status, $table, $key)
{
    $sql = "UPDATE " . TB_PREF . $table . " SET inactive = "
        . ((int)$status) . " WHERE $key=" . db_escape($id);

    db_query($sql, "Can't update record status");
}

//-----------------------------------------------------------------------------
//
//	Helper for sql subquery returning running totals from delta tables like stock_moves or bank_trans
//
//	$table - table name with optional WHERE clause
//  $column - delta column
//	$index  - comma delimited list of columns for total grouping and order
//  Returns running totals with respective index column
//
function running_total_sql($table, $column, $index)
{

    return "SELECT daily.$index, daily.$column, (@total:=@total+daily.$column) total 
		FROM
			(SELECT $index, sum($column) $column FROM $table GROUP BY $index ORDER BY $index) daily,
			(SELECT @total:=0) total_var";
}



function rm_comma($str='') {

    if(empty($str))
        return 0.00;

    return str_replace(",", '', $str);

}


/**
 * @param string $table
 * @param array $values_array
 * @param string $type
 * @param array $where
 * @return bool|string
 * Added on 21-03-2019
 * Bipin
 */
function generate_query($table = '', $values_array = [], $type = 'insert', $where = [])
{
    if (empty($table))
        return false;

    $sql = "";
    if ($type == 'insert') {
        $cols = array_keys($values_array);
        $values = array_values($values_array);
        $sql = "INSERT INTO $table (" . implode(',', $cols) . ") 
        VALUES (" . implode(',', $values) . ") ";
    } else if ($type == 'update') {
        $set_array = [];
        foreach ($values_array as $key => $value)
            $set_array[] = $key . "=" . $value;

        $sql = "UPDATE $table SET " . implode(',', $set_array);
        if (!empty($where))
            $sql .= " WHERE " . implode($where);

    } else if ($type == 'delete') {

        if (!empty($where))
            return false;

        $sql = "DELETE FROM $table";
        $sql .= " WHERE " . implode($where);
    }

    if (!empty($sql))
        return $sql;

    return false;

}

/**
 * @param string $table
 * @param array $values_array
 * @return bool|mysqli_result|resource
 * Added on 23-03-2019
 * Bipin
 */
function db_insert($table = '', $values_array = [])
{
    if (empty($table))
        return false;

    $cols = array_keys($values_array);
    $values = array_values($values_array);
    $sql = "INSERT INTO $table (" . implode(',', $cols) . ") 
        VALUES (" . implode(',', $values) . ") ";

//    if($table == '0_notifications') {
//        var_dump($sql);
//        die;
//    }

    return db_query($sql, "INSERT FAILED TO TABLE" . $table);

}

/**
 * @param $table
 * @param $batch_array
 * @return bool|mysqli_result|resource
 * Added on 28-11-2019
 * Bipin
 */
function db_insert_batch($table,$batch_array) {

    if (empty($table) || empty($batch_array))
        return false;

    $cols = array_keys($batch_array[0]);
    $sql = "INSERT INTO $table (" . implode(',', $cols) . ") VALUES ";

    foreach ($batch_array as $row)
        $sql  .= " (" . implode(',', $row) . "),";

    $sql= rtrim($sql,",");

    return db_query($sql, "INSERT FAILED TO TABLE - BATCH INSERT" . $table);

}

/**
 * @param string $table
 * @param array $set_array
 * @param array $where
 * @return bool|mysqli_result|resource
 * Added on 23-03-2019
 * Bipin
 */
function db_update($table = '', $set_array = [], $where = [])
{
    if (empty($table))
        return false;

    $set = [];
    foreach ($set_array as $key => $value)
        $set[] = $key . "=" . $value;

    $sql = "UPDATE $table SET " . implode(',', $set);
    if (!empty($where))
        $sql .= " WHERE " . implode($where);

//    dd($sql);

    return db_query($sql, "UPDATE FAILED WITH TABLE " . $table);
}

/**
 * @param string $table
 * @param array $where
 * @return bool|mysqli_result|resource
 * Added on 23-03-2019
 * Bipin
 */
function db_delete($table = '', $where = [])
{
    if (!empty($where) || empty($table))
        return false;

    $sql = "DELETE FROM $table";
    $sql .= " WHERE " . implode($where);
    return db_query($sql, "DELETE QUERY FAILED WITH TABLE " . $table);
}

