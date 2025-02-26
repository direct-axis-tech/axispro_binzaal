<?php

/**
 * Retrieve the pay element specified by either 'id', 'for' or both
 * 
 * @param int $id The ID of the pay_element
 * @param bool $searchInactive Whether to search inactive pay_elements
 * 
 * @return null|array
 */
function getPayElement($id, $searchInactive = false) {
    $where = "elem.id = {$id}";
    if (!$searchInactive)   { $where .= " AND elem.inactive = 0";  }

    $sql = "SELECT * FROM `0_pay_elements` WHERE {$where}";
    return db_query($sql, "Could not retrieve the pay_element")->fetch_assoc();
}

/**
 * Retrieve the pay elements
 * 
 * @param array $filters An optional array of filters.
 * @return mysqli_result
 */
function getPayElements($filters = []) {
    $where = "1 = 1";

    if (!isset($filters['inactive'])) {
        $where .= " AND elem.inactive = 0";
    }

    if (isset($filters['is_fixed'])) {
        $filters['is_fixed'] = intval(boolval($filters['is_fixed']));
        $where .= " AND elem.is_fixed = {$filters['is_fixed']}";
    }

    $sql = (
        "SELECT
            *
        FROM `0_pay_elements` elem
        WHERE {$where}
        ORDER BY 
            elem.type DESC,
            elem.is_fixed DESC,
            IF(elem.is_fixed, elem.id, 99),
            elem.name"
    );

    return db_query($sql, "Could not retrieve the pay elements");
}


/**
 * Retrieve the pay elements and key it by id
 * 
 * @param array $filters An optional array of filters.
 * @return array
 */
function getPayElementsKeyedById($filters = []) {
    $elements = [];

    $mysqliResult = getPayElements($filters);
    while ($el = $mysqliResult->fetch_assoc()) {
        $elements[$el['id']] = $el;
    }

    return $elements;
}
