<?php

/**
 * Retrieve all the supplier discount items for the supplier
 *
 * @param string $supplier_id
 * @return mysqli_result
 */
function get_supplier_discount_items($supplier_id)
{
    $sql = (
        "SELECT
            cat.description,
            disc.*
        FROM 0_supplier_discount_items as disc
        LEFT JOIN 0_stock_category as cat ON
           cat.category_id = disc.category_id
        WHERE disc.supplier_id = ".db_escape($supplier_id)
    );

    return db_query($sql, "Can't get supplier discount items for supplier");
}

/**
 * Retrieve a supplier discount item from db
 *
 * @param string $id
 * @return array|null
 */
function get_supplier_discount_item($id)
{
    return db_query(
        "SELECT * FROM 0_supplier_discount_items WHERE id = $id",
        "Can't get supplier discount item"
    )->fetch_assoc();
}

/**
 * Add supplier commission to database
 *
 * @param string $supplier_id
 * @param string $category_id
 * @param double $commission
 * @return string|false
 */
function add_supplier_item_discounts(
    $supplier_id,
    $category_id,
    $commission=0,
    $comm_calc_method=null
)
{
    $sql = (
        "INSERT INTO 0_supplier_discount_items (
            supplier_id,
            category_id,
            commission,
            comm_calc_method
        )
        VALUES ("
                  . db_escape($supplier_id, true)
            . "," . db_escape($category_id, true)
            . "," . db_escape($commission)
            . "," . db_escape($comm_calc_method, null)
        . ")"
    );

    db_query($sql, "Can't insert supplier discount item");
    $id = db_insert_id();

    app('activityLogger')
        ->info(
            "Added supplier discount {id}",
            compact(
                'id',
                'supplier_id',
                'category_id',
                'commission',
                'comm_calc_method'
            )
        );

    return $id;
}

/**
 * Update supplier discount item
 *
 * @param string $id
 * @param string $category_id
 * @param double $commission
 * @return bool
 */
function update_supplier_item_discounts(
    $id,
    $category_id,
    $commission=0,
    $comm_calc_method=null
)
{
    $sql = (
        "UPDATE 0_supplier_discount_items"
        . " SET "
            .   "category_id = ".db_escape($category_id, true)
            . ", commission = ".db_escape($commission)
            . ", comm_calc_method = ".db_escape($comm_calc_method, true)
        . " WHERE id = " . db_escape($id)
    );

    $result = db_query($sql, "Can't update supplier discount item");

    app('activityLogger')
        ->info(
            "Updated supplier discount {id}",
            compact(
                'id',
                'category_id',
                'commission',
                'comm_calc_method'
            )
        );

    return $result;
}

/**
 * Deletes a supplier discount item from database
 *
 * @param string $id
 * @return bool
 */
function delete_supplier_item_discount($id)
{
    $data = get_supplier_discount_item($id);
    
    $result = db_query(
        "DELETE FROM 0_supplier_discount_items WHERE id = " . db_escape($id),
        "Can't delete supplier discount item"
    );

    app('activityLogger')->info("Removed supplier discount {id}", $data);

    return $result;
}

/**
 * Check if there is already a discount item against this category
 *
 * @param string $supplier_id
 * @param string $category_id
 * @param string $id
 * @return boolean
 */
function is_duplicate_supplier_item_discount($supplier_id, $category_id, $id = null)
{
    $sql = (
        "SELECT
            1 as cnt
        FROM 0_supplier_discount_items
        WHERE
            supplier_id = ".db_escape($supplier_id)
            . " AND category_id = ".db_escape($category_id)
    );

    if ($id) {
        $sql .= " AND id != $id";
    }

    $result = db_query($sql, "Can't check for duplicate supplier discount item")->fetch_assoc();

    return data_get($result, 'cnt') == 1;
}