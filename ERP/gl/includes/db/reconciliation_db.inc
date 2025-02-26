<?php


/**
 * @param $where
 * @return string
 */
function get_sql_for_reconcile_result($where)
{
    $sql = "select sw_date,bank_date,transaction_,sw_amount,bank_amount 
            from 0_reconcile_result WHERE 1=1 $where ";

    return $sql;
}

