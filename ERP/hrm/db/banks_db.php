<?php

/**
 * Retrieve the list of all banks
 * 
 * @return mysqli_result
 */
function getBanks() : mysqli_result {
    return db_query("SELECT * FROM `0_banks` ORDER BY `name`", "Could not retrieve the list of banks");
}

/**
 * Retrieves the list of all banks and key it by the id
 * 
 * @return array
 */
function getBanksKeyedById(): array {
    $mysqliResult = getBanks();

    $banks = [];
    while ($bank = $mysqliResult->fetch_assoc()) {
        $banks[$bank['id']] = $bank;
    }

    return $banks;
}