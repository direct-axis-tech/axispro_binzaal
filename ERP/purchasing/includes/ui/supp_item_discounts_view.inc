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
include_once($path_to_root . "/purchasing/includes/db/supp_item_discounts_db.inc");

/*
	View/Edit class for contacts
*/
class supplier_discount_items extends simple_crud
{
    var $id;
    var $supplier_id;

    function __construct($name, $id)
    {
        parent::__construct($name, [
            'category_id',
            'commission',
            'comm_calc_method'
        ]);
        $this->supplier_id = $id;
    }

    function list_view()
    {
        br();
        $comm_calc_methods = commission_calculation_methods();
        $item_discounts = get_supplier_discount_items($this->supplier_id);
        start_table(TABLESTYLE, "width='80%'");
        $th = array(
            trans('Item Category Name'),
            trans("Customer Commission"),
            trans("Comm Calc Method"),
            '&nbsp;',
            "&nbsp;"
        );
        table_header($th);
        
        $k = 0;
        while ($myrow = db_fetch($item_discounts)) {
            alt_table_row_color($k);
            label_cell($myrow['description'], "style='text-align:center;'");
            label_cell($myrow['commission'], "style='text-align:center;'");
            label_cell($comm_calc_methods[$myrow['comm_calc_method']] ?? '--', "style='text-align:center;'");
            edit_button_cell("{$this->name}Edit[{$myrow['id']}]", trans("Edit"));
            delete_button_cell("{$this->name}Delete[{$myrow['id']}]", trans("Delete"));
            end_row();
        }

        end_table(1);
    }

    function editor_view()
    {
        br();
        start_outer_table(TABLESTYLE);
        table_section(1);
        table_section_title(trans("Enter Item & Discount"));
        stock_categories_list_row(trans("Item Category:"), 'category_id', @$this->data['category_id'], false);
        amount_row(trans("Commission:"), 'commission', @$this->data['commission']);
        array_selector_row(
            trans('Commission Calculation Method'),
            'comm_calc_method',
            @$this->data['comm_calc_method'],
            commission_calculation_methods(),
            [
                'spec_option' => '-- select --',
                'spec_id' => '',
            ]
        );
        end_outer_table(1);
    }

    function db_insert()
    {
        return add_supplier_item_discounts(
            $this->supplier_id,
            get_post('category_id'),
            input_num('commission'),
            get_post('comm_calc_method')
        );
    }

    function db_update()
    {
        return update_supplier_item_discounts(
            $this->selected_id,
            get_post('category_id'),
            input_num('commission'),
            get_post('comm_calc_method')
        );
    }

    function insert_check()
    {
        if (!get_post('category_id')) {
            display_error(trans("Item Category cannot be empty"));
            set_focus('category_id');
            return false;
        }
        
        if (input_num('commission') == 0) {
            display_error(trans("Commission cannot be zero"));
            set_focus('commission');
            return false;
        }

        if (input_num('commission') != 0 && !get_post('comm_calc_method')) {
            display_error(trans("Commission calculation method cannot be empty"));
            set_focus('commission');
            return false;
        }

        if (is_duplicate_supplier_item_discount($this->supplier_id, $_POST['category_id'], $this->selected_id)) {
            display_error(trans("Duplicate supplier item discount"));
            set_focus('category_id');
            return false;
        }

        return true;
    }

    function db_read()
    {
        return get_supplier_discount_item($this->selected_id);
    }

    function delete_check()
    {
        return true;
    }

    function db_delete()
    {
        return delete_supplier_item_discount($this->selected_id);

    }
}

