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

function add_customer_item_discounts(
    $item_id,
    $discount,
    $customer_commission = 0,
    $reward_point=0,
    $entity = null,
    $cust_comm_emp_share=0,
    $cust_comm_center_share=0,
    $comm_calc_method=null,
    $comm_emp_sh_calc_method=null,
    $comm_emp_sh_percent_of=null,
    $customer_commission2 = 0,
    $comm2_calc_method = 0
)
{

    $sql = "INSERT INTO 0_customer_discount_items (
        item_id,
        discount,
        customer_commission,
        comm_calc_method,
        reward_point,
        customer_id,
        cust_comm_emp_share,
        comm_emp_sh_calc_method,
        comm_emp_sh_percent_of,
        cust_comm_center_share,
        customer_commission2,
        comm2_calc_method
    ) VALUES ("
        . db_escape($item_id) . ","
        . db_escape($discount) . ","
        . db_escape($customer_commission) . ","
        . db_escape($comm_calc_method, true) . ","
        . db_escape($reward_point) . ","
        . db_escape($entity) . ","
        . db_escape($cust_comm_emp_share) . ","
        . db_escape($comm_emp_sh_calc_method, true) . ","
        . db_escape($comm_emp_sh_percent_of, true) . ","
        . db_escape($cust_comm_center_share) .","
        . db_escape($customer_commission2) . ","
        . db_escape($comm2_calc_method, true)
    . ")";
    begin_transaction();
    db_query($sql, "Can't insert customer_discount_items");
    $id = db_insert_id();
    commit_transaction();

    app('activityLogger')
        ->info(
            "Added customer discount {discount_id}",
            ['discount_id' => $id]
        );

    return $id;
}

function update_customer_item_discounts(
    $id,
    $item_id,
    $customer_commission,
    $discount,
    $reward_point=0,
    $cust_comm_emp_share=0,
    $cust_comm_center_share=0,
    $comm_calc_method=null,
    $comm_emp_sh_calc_method=null,
    $comm_emp_sh_percent_of=null,
    $customer_commission2 =0,
    $comm2_calc_method=null
)
{
    $sql = "UPDATE 0_customer_discount_items SET "
        . "item_id=" . db_escape($item_id) . ", "
        . "customer_commission=" . db_escape($customer_commission) . ", "
        . "comm_calc_method=" . db_escape($comm_calc_method, true) . ", "
        . "discount=" . db_escape($discount).", "
        . "reward_point=" . db_escape($reward_point).", "
        . "cust_comm_emp_share=".db_escape($cust_comm_emp_share).", "
        . "comm_emp_sh_calc_method=".db_escape($comm_emp_sh_calc_method, true).", "
        . "comm_emp_sh_percent_of=".db_escape($comm_emp_sh_percent_of, true).", "
        . "cust_comm_center_share=".db_escape($cust_comm_center_share).","
        . "customer_commission2=" . db_escape($customer_commission2) . ", "
        . "comm2_calc_method=" . db_escape($comm2_calc_method, true)
        . " WHERE id = " . db_escape($id);

    begin_transaction();

    db_query($sql, "Can't update customer_discount_items");
    commit_transaction();

    app('activityLogger')
        ->info(
            "Updated customer discount {discount_id}",
            [
                'discount_id' => $id,
                'item_id' => $item_id,
                'discount' => $discount
            ]
        );
    return $id;
}


function delete_customer_item_discount($id)
{

    $sql = "DELETE FROM 0_customer_discount_items WHERE id=" . db_escape($id);
    $mysqli_result = db_query($sql, "Can't delete customer_discount_items");

    app('activityLogger')
        ->info(
            "Removed customer discount {discount_id}",
            ['discount_id' => $id]
        );

    return $mysqli_result;
}

function check_duplicate_customer_item_discount($customer_id, $item_id, $id = null)
{

    $sql = "SELECT * FROM 0_customer_discount_items WHERE customer_id=$customer_id AND item_id='$item_id'";

    if ($id != null) {
        $sql .= " AND id != $id";
    }
    return db_query($sql, "Can't check duplicate customer_discount_items");
}


function add_crm_person($ref, $name, $name2, $address, $phone, $phone2, $fax, $email, $lang, $notes,
                        $cat_ids = null, $entity = null)
{
    $sql = "INSERT INTO " . TB_PREF . "crm_persons (ref, name, name2, address,
		phone, phone2, fax, email, lang, notes)
		VALUES ("
        . db_escape($ref) . ", "
        . db_escape($name) . ", "
        . db_escape($name2) . ", "
        . db_escape($address) . ", "
        . db_escape($phone) . ", "
        . db_escape($phone2) . ", "
        . db_escape($fax) . ", "
        . db_escape($email) . ", "
        . db_escape($lang) . ", "
        . db_escape($notes)
        . ")";

    begin_transaction();

    $ret = db_query($sql, "Can't insert crm person");
    $id = db_insert_id();
    if ($ret && $cat_ids) {
        if (!update_person_contacts($id, $cat_ids, $entity))
            return null;
    }
    commit_transaction();
    return $id;
}

function update_crm_person($id, $ref, $name, $name2, $address, $phone, $phone2, $fax, $email,
                           $lang, $notes, $cat_ids, $entity = null, $type = null)
{
    $sql = "UPDATE " . TB_PREF . "crm_persons SET "
        . "ref=" . db_escape($ref) . ", "
        . "name=" . db_escape($name) . ", "
        . "name2=" . db_escape($name2) . ", "
        . "address=" . db_escape($address) . ", "
        . "phone=" . db_escape($phone) . ", "
        . "phone2=" . db_escape($phone2) . ", "
        . "fax=" . db_escape($fax) . ", "
        . "email=" . db_escape($email) . ", "
        . "lang=" . db_escape($lang) . ", "
        . "notes=" . db_escape($notes)
        . " WHERE id = " . db_escape($id);

    begin_transaction();

    $ret = db_query($sql, "Can't update crm person");
    if ($ret) {
        if (!update_person_contacts($id, $cat_ids, $entity, $type))
            return null;
    }
    commit_transaction();
    return $id;
}

function delete_crm_person($person, $with_contacts = false)
{
    begin_transaction();

    if ($with_contacts) {
        $sql = "DELETE FROM " . TB_PREF . "crm_contacts WHERE person_id=" . db_escape($person);
        db_query($sql, "Can't delete crm contacts");
    }
    $sql = "DELETE FROM " . TB_PREF . "crm_persons WHERE id=" . db_escape($person);
    $ret = db_query($sql, "Can't delete crm person");

    commit_transaction();
    return $ret;
}

/*
	Retrieve full contact data from database for selected type/action/entity or person
*/
function get_crm_persons($type = null, $action = null, $entity = null, $person = null, $unique = false)
{
    $sql = "SELECT 
        CONCAT(p.ref, ' - ', p.name) formatted_name, 
        t.*, 
        p.*, 
        r.id as contact_id 
    FROM " . TB_PREF . "crm_persons p,"
        . TB_PREF . "crm_categories t, "
        . TB_PREF . "crm_contacts r 
    WHERE ";
    $sel = array('r.type=t.type', 'r.action=t.action', 'r.person_id=p.id');

    if ($type)
        $sel[] = 't.type=' . db_escape($type);

    if ($action)
        $sel[] = 't.action=' . db_escape($action);

    if ($entity)
        $sel[] = 'r.entity_id=' . db_escape($entity);

    if ($person)
        $sel[] = 'r.person_id=' . db_escape($person);

    $sql .= implode(" AND ", $sel);

    if ($unique)
        $sql .= " GROUP BY person_id";
    else
        $sql .= " ORDER BY contact_id";

    $result = db_query($sql, "Can't get crm persons");
    // fallback to general contacts
    if (!db_num_rows($result) && $action && $action != 'general')
        return get_crm_persons($type, 'general', $entity, $person, $unique);
    else
        return $result;
}

function get_crm_person($id)
{
    $sql = "SELECT * FROM " . TB_PREF . "crm_persons WHERE id=" . db_escape($id);

    $res = db_query($sql, "Can't get crm persons");

    $person = db_fetch($res);
    $person['contacts'] = get_person_contacts($id);
//	$person['discounts'] = get_customer_discount_items($id);

    return $person;
}

/*
	Returns all contacts for given person id
*/
function get_person_contacts($id)
{
    $sql = "SELECT t.id FROM "
        . TB_PREF . "crm_categories t, "
        . TB_PREF . "crm_contacts r WHERE r.type=t.type AND r.action=t.action 
			AND r.person_id=" . db_escape($id);

    $contacts = array();
    $ret = db_query($sql, "Can't get crm person contacts");
    while ($cont = db_fetch_row($ret))
        $contacts[] = $cont[0];

//    display_error(print_r($contacts ,true));

    return $contacts;
}


/** Added for AMER */

function get_customer_discount_items($id)
{
    $sql = "SELECT " . TB_PREF . "stock_category.description, 0_customer_discount_items.* FROM 0_customer_discount_items 
      LEFT JOIN " . TB_PREF . "stock_category on " . TB_PREF . "stock_category.category_id=0_customer_discount_items.item_id
       WHERE 0_customer_discount_items.customer_id = $id";
    $discounts = db_query($sql, "Can't get customer item discounts");
    return $discounts;

}


function get_customer_discount_item($id)
{
    $sql = "SELECT * FROM 0_customer_discount_items WHERE id = $id";
    $discounts = db_query($sql, "Can't get customer item discounts");

    return db_fetch($discounts);

}


function update_person_contacts($id, $cat_ids, $entity_id = null, $type = null)
{
    $sql = "DELETE FROM " . TB_PREF . "crm_contacts WHERE person_id=" . db_escape($id);
    if ($type)
        $sql .= " AND type=" . db_escape($type);

    begin_transaction();

    $ret = db_query($sql, "Can't delete person contacts");

    foreach ($cat_ids as $n => $cid)
        $cat_ids[$n] = db_escape($cid);

    if ($ret && count($cat_ids)) {
        array_walk($cat_ids, 'db_escape');
        $sql = "INSERT INTO " . TB_PREF . "crm_contacts (person_id,type,action,entity_id)
			SELECT " . db_escape($id) . ",t.type, t.action," . db_escape($entity_id, true) . "
			FROM " . TB_PREF . "crm_categories t WHERE t.id=" . implode(' OR t.id=', $cat_ids);
        if ($type)
            $sql .= " AND t.type=" . db_escape($type);
        $ret = db_query($sql, "Can't update person contacts");
    }
    commit_transaction();
    return $ret;
}

function delete_entity_contacts($class, $entity)
{
    delete_crm_contacts(null, $class, $entity);
    // cleanup
    $res = get_crm_persons($class, null, $entity, null, true);
    while ($person = db_fetch($res)) {
        $rels = get_person_contacts($person['id']);
        if (count($rels) == 0) {
            delete_crm_person($person['id']);
        }
    }
}

//-----------------------------------------------------------------------------------------------

function add_crm_category($type, $action, $name, $description)
{
    $sql = "INSERT INTO " . TB_PREF . "crm_categories (type, action, name, description)
		VALUES (" . db_escape($type) . ", "
        . db_escape($action) . ", "
        . db_escape($name) . ", "
        . db_escape($description)
        . ")";
    db_query($sql, "The insert of the crm category failed");
}

function update_crm_category($selected_id, $type, $action, $name, $description)
{
    $sql = "UPDATE " . TB_PREF . "crm_categories SET ";
    if ($type)
        $sql .= "type=" . db_escape($type) . ",";
    if ($action)
        $sql .= "action=" . db_escape($action) . ",";
    $sql .= "name=" . db_escape($name) . ","
        . "description=" . db_escape($description)
        . " WHERE id = " . db_escape($selected_id);
    db_query($sql, "The update of the crm category failed");
}

function delete_crm_category($selected_id)
{
    // preserve system categories
    $sql = "DELETE FROM " . TB_PREF . "crm_categories WHERE system=0 AND id=" . db_escape($selected_id);
    db_query($sql, "could not delete crm category");
}

function get_crm_categories($show_inactive)
{
    $sql = "SELECT * FROM " . TB_PREF . "crm_categories";
    if (!$show_inactive) $sql .= " WHERE !inactive";
    $sql .= " ORDER BY type, action";
    return db_query($sql, "could not get areas");
}

function get_crm_category($selected_id)
{
    $sql = "SELECT * FROM " . TB_PREF . "crm_categories WHERE id=" . db_escape($selected_id);

    $result = db_query($sql, "could not get crm category");
    return db_fetch($result);
}

function get_crm_category_name($id)
{
    $sql = "SELECT name FROM " . TB_PREF . "crm_categories WHERE id=" . db_escape($id);

    $result = db_query($sql, "could not get sales type");

    $row = db_fetch_row($result);
	return is_array($row) ? $row[0] : false;
}

//----------------------------------------------------------------------------------------
//	Contact is relation between person and entity in some category
//
function add_crm_contact($type, $action, $entity_id, $person_id)
{
    $sql = "INSERT INTO " . TB_PREF . "crm_contacts (person_id, type, action, entity_id) VALUES ("
        . db_escape($person_id) . ","
        . db_escape($type) . ","
        . db_escape($action) . ","
        . db_escape($entity_id) . ")";
    return db_query($sql, "Can't insert crm contact");
}

/*
	Delete contact selected by unique id.
*/
function delete_crm_contact($id)
{
    $sql = "DELETE FROM " . TB_PREF . "crm_contacts WHERE id=" . db_escape($id);

    return db_query($sql, "Can't delete crm contact");
}

/*
	Delete selected contacts for given person
*/
function delete_crm_contacts($person_id = null, $type = null, $entity_id = null, $action = null)
{
    $sql = "DELETE FROM " . TB_PREF . "crm_contacts WHERE ";

    if ($person_id)
        $where[] = 'person_id=' . db_escape($person_id);
    if ($type)
        $where[] = 'type=' . db_escape($type);
    if ($entity_id)
        $where[] = 'entity_id=' . db_escape($entity_id);
    if ($action)
        $where[] = 'action=' . db_escape($action);

    return db_query($sql . implode(' AND ', $where), "Can't delete crm contact");
}

/*
	Returns person data for given contact id
*/
function get_crm_contact($id)
{
    $sql = "SELECT t.type, t.action, p.*, r.person_id, r.id  FROM " . TB_PREF . "crm_persons p,"
        . TB_PREF . "crm_categories t, "
        . TB_PREF . "crm_contacts r WHERE r.type=t.type AND r.action=t.action AND r.person_id=p.id"
        . " AND r.id=" . db_escape($id);
    $ret = db_query($sql, "Can't get crm contact");
    if ($ret)
        return db_fetch($ret, "Can't fetch contact data");
    return $ret;
}

/*
	 Check for whether category is used in contacts.
*/
function is_crm_category_used($id)
{
    $row = get_crm_category($id);
    $sql = "SELECT COUNT(*) FROM " . TB_PREF . "crm_contacts WHERE type='" . $row['type'] . "' AND action='" . $row['action'] . "'";
    $result = db_query($sql, "check relations for crm_contacts failed");
    $contacts = db_fetch($result);
    return $contacts[0];
}


