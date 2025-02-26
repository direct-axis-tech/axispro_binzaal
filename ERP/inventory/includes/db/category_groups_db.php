<?php

/**
 * Store category group in the database
 *
 * @param string $desc
 * @return string
 */
function add_category_group($desc)
{
    $desc = db_escape($desc);
    $sql = "INSERT INTO `0_category_groups` (`desc`) VALUES ($desc)";
    db_query($sql, "category group could not be added");

    return db_insert_id();
}

/**
 * Update category group
 *
 * @param string $id
 * @param string $desc
 * @return string
 */
function update_category_group($id, $desc)
{
    $desc = db_escape($desc);
    $id = db_escape($id);

    $sql = ("UPDATE `0_category_groups` SET `desc` = {$desc} WHERE id = {$id}");

    db_query($sql, "category group could not be updated");

    return db_num_affected_rows();
}

/**
 * Delete category groupo
 *
 * @param string $id
 * @return string
 */
function delete_category_group($id)
{
    $sql = "DELETE FROM `0_category_groups` WHERE id = " . db_escape($id);

    db_query($sql, "a category group could not be deleted");

    return db_num_affected_rows();
}

/**
 * Get all the category groups
 *
 * @return mysqli_result
 */
function get_category_groups()
{
    $sql = "SELECT * FROM `0_category_groups`";

    return db_query($sql, "could not category groups");
}

/**
 * Retrieve the category groups keyed by ID
 *
 * @return array
 */
function get_category_groups_keyed_by_id()
{
    $groups = [];
    $mysqli_result = get_category_groups();
    while ($g = $mysqli_result->fetch_assoc()) {
        $groups[$g['id']] = $g;
    }

    return $groups;
}

/**
 * Retrive one category ID given the ID
 *
 * @param string $id
 * @return array
 */
function get_category_group($id)
{
    $sql = "SELECT * FROM `0_category_groups` WHERE id =" . db_escape($id);

    $result = db_query($sql, "a category group could not be retrieved");

    return db_fetch($result);
}