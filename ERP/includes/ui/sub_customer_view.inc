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
include_once($path_to_root . '/includes/ui/simple_crud_class.inc');
include_once($path_to_root . "/includes/db/crm_contacts_db.inc");

/*
	View/Edit class for contacts
*/

class sub_customer extends simple_crud
{
    var $id;
    var $entity;
    var $sub_class;
    var $class;

    function __construct($name, $id, $class, $subclass = null)
    {
        $fields = array('customer_id', 'name');

        parent::__construct($name, $fields);
        $this->class = $class;
        $this->subclass = $subclass;
        $this->entity = $id;
    }

    function list_view()
    {
        br();


        $sql = "SELECT * FROM 0_sub_customers WHERE customer_id = $this->entity";

        $sub_customers = db_query($sql);

//        $item_discounts = get_customer_discount_items($this->entity);
        start_table(TABLESTYLE, "width='80%'");
        $th = array(trans('Name'), trans('Email'), trans('TRN No'), '', "&nbsp;");
        table_header($th);
        $k = 0;

        $last = $myrow = db_fetch($sub_customers);

        while ($last) {

            if ($myrow['id'] != $last['id']) {
                alt_table_row_color($k);
                label_cell($last['name'], "style='text-align:center;'");
                label_cell($last['email'], "style='text-align:center;'");
                label_cell($last['trn'], "style='text-align:center;'");

                edit_button_cell("{$this->name}Edit[{$last['id']}]", trans("Edit"));

                // delete button removes only category unless this is the last contact for this person
                delete_button_cell("{$this->name}Delete[{$last['id']}]", trans("Delete"));

//                submit_js_confirm("contactsDelete[2]", trans('You are about to void this Document.\nDo you want to continue?'));


                end_row();
                $description = array();
                $last = $myrow;
            }

            if ($myrow) {
                $description[] = $myrow['name'];
                $myrow = db_fetch($sub_customers);
            }
        }

        //inactive_control_row($th);
        end_table(1);
    }

    function editor_view()
    {
        br();

        start_outer_table(TABLESTYLE);

        table_section(1);
        table_section_title(trans("Enter Sub Customers"));
        text_row(trans("Sub Customer Name").":", 'name', @$this->data['name'], 35, 40);
        text_row(trans("TRN No").":", 'trn_no', @$this->data['trn'], 35, 40);
        text_row(trans("Sub Customer Email").":", 'email', @$this->data['email'], 35, 40);

        end_outer_table(1);
    }

    function db_insert()
    {
        $dat = $this->data;

        return db_insert('0_sub_customers',['customer_id' => $this->entity,'name' => db_escape($_POST['name']), 'trn' => db_escape($_POST['trn_no']), 'email' => db_escape($_POST['email'])]);

    }

    function db_update()
    {


        return db_update('0_sub_customers',
        [
            'name' => db_escape($_POST['name']),
            'trn' => db_escape($_POST['trn_no']),
            'email' => db_escape($_POST['email'])
        ],
        ["id=$this->selected_id"]
    );
//        return update_customer_item_discounts($this->selected_id, $_POST['item_id'], $_POST['customer_commission'],
//            $_POST['discount'],$_POST['reward_point']);
    }

    function insert_check()
    {
        if (strlen($_POST['name']) == 0) {
            display_error(trans("Sub Customer Name cannot be empty"));
            set_focus('item_id');
            return false;
        }
//        if (strlen($_POST['reward_point']) == 0) {
//            display_error(trans("Reward Point cannot be empty."));
//            set_focus('discount');
//            return false;
//        }

//        if (db_num_rows(check_duplicate_customer_item_discount($this->entity, $_POST['item_id'], $this->selected_id))) {
//            display_error(trans("Customer has already assigned a reward point for this item"));
//            set_focus('item_id');
//            return false;
//        }

        return true;
    }

    function db_read()
    {


        $sql = "SELECT * FROM 0_sub_customers WHERE id = $this->selected_id";
        $sub_customers = db_query($sql, "Can't get customer item discounts");

        return db_fetch($sub_customers);

//        return get_customer_discount_item($this->selected_id);
    }

    function delete_check()
    {
        return true;
    }
    //
    //	Delete all contacts for person in current class/entity
    //
    function db_delete()
    {

        $sql = "DELETE FROM 0_sub_customers WHERE id=" . db_escape($this->selected_id);
        return db_query($sql, "Can't delete customer_discount_items");
//        return delete_customer_item_discount($this->selected_id);

    }
}

