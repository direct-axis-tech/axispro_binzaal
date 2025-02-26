<?php
/**********************************************************************
    Direct Axis Technology L.L.C.
	Released under the terms of the GNU General Public License, GPL, 
	as published by the Free Software Foundation, either version 3 
	of the License, or (at your option) any later version.
    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  
    See the License here <http://www.gnu.org/licenses/gpl-3.0.html>.
***********************************************************************/

use App\Models\EntityGroupCategory;
use App\Models\Labour\Agent;
use App\Models\Sales\Customer;

include_once($path_to_root . "/includes/banking.inc");
include_once($path_to_root . "/includes/types.inc");
include_once($path_to_root . "/includes/current_user.inc");

define('SEARCH_BUTTON', "<input %s type='submit' class='combo_submit' style='border:0;background:url($path_to_root/themes/"
	."%s/images/locate.png) no-repeat;%s' aspect='fallback' name='%s' value=' ' title='".trans("Set filter")."'> ");

define('SELECT_BUTTON', "<input %s type='submit' class='combo_select' style='border:0;background:url($path_to_root/themes/"
	."%s/images/button_ok.png) no-repeat;%s' aspect='fallback' name='%s' value=' ' title='".trans("Select")."'> ");

//----------------------------------------------------------------------------
//	Universal sql combo generator
//	$sql must return selector values and selector texts in columns 0 & 1
//	Options are merged with defaults.

function combo_input($name, $selected_id, $sql, $valfield, $namefield,
	$options=null, $type=null)
{
global $Ajax, $path_to_root, $SysPrefs, $js_lib;

$opts = array(		// default options
	'where'=> array(),		// additional constraints
	'order' => $namefield,	// list sort order
		// special option parameters
	'spec_option'=>false, 	// option text or false
	'spec_id' => 0,		// option id
		// submit on select parameters
	'default' => '', // default value when $_POST is not set
	'multi' => false,	// multiple select
	'select_submit' => false, //submit on select: true/false
	'async' => true,	// select update via ajax (true) vs _page_body reload
		// search box parameters
	'sel_hint' => null,
	'search_box' => false, 	// name or true/false
	'type' => 0,	// type of extended selector:
		// 0 - with (optional) visible search box, search by fragment inside id
		// 1 - with hidden search box, search by option text
		// 2 - with (optional) visible search box, search by fragment at the start of id
		// 3 - TODO reverse: box with hidden selector available via enter; this
		// would be convenient for optional ad hoc adding of new item
	'search_submit' => true, //search submit button: true/false
	'size' => 8,	// size and max of box tag
	'max' => 50,
	'height' => false,	// number of lines in select box
	'cells' => false,	// combo displayed as 2 <td></td> cells
	'search' => array(), // sql field names to search
	'format' => null, 	 // format functions for regular options
	'disabled' => false,
	'box_hint' => null, // box/selectors hints; null = std see below
	'category' => false, // category column name or false
	'show_inactive' => false, // show inactive records. 
	'editable' => false, // false, or length of editable entry field
	'editlink' => false, // link to entity entry/edit page (optional)
	'readonly' => false, // make the control readonly,
	'select2' => false
);
// ------ merge options with defaults ----------
	if($options != null)
		$opts = array_merge($opts, $options);
	if (!is_array($opts['where']))  $opts['where'] = array($opts['where']);

	$search_box = $opts['search_box']===true ? '_'.$name.'_edit' : $opts['search_box'];
	// select content filtered by search field:
	$search_submit = $opts['search_submit']===true ? '_'.$name.'_button' : $opts['search_submit'];
	// select set by select content field
	$search_button = $opts['editable'] ? '_'.$name.'_button' : ($search_box ? $search_submit : false);

	$select_submit =  $opts['select_submit'];
	$spec_id = $opts['spec_id'];
	$spec_option = $opts['spec_option'];
	if ($opts['type'] == 0) {
		$by_id = true;
		$class = 'combo';
	} elseif($opts['type'] == 1) {
		$by_id = false;
		$class = 'combo2';
	} else {
		$by_id = true;
		$class = 'combo3';
	}

	$disabled = $opts['disabled'] ? "disabled" : '';
	$multi = $opts['multi'];
	
	if(!count($opts['search'])) {
		$opts['search'] = array($by_id ? $valfield : $namefield);
	}
	if ($opts['sel_hint'] === null) 
		$opts['sel_hint'] = $by_id || $search_box==false ?
			'' : trans('Press Space tab for search pattern entry');

	if ($opts['box_hint'] === null)
		$opts['box_hint'] = $search_box && $search_submit != false ?
			($by_id ? trans('Enter code fragment to search or * for all')
			: trans('Enter description fragment to search or * for all')) :'';

	if ($selected_id == null) {
		$selected_id = get_post($name, (string)$opts['default']);
	}
	if(!is_array($selected_id))
		$selected_id = array((string)$selected_id); // code is generalized for multiple selection support

	if ($opts['select2']) {
		$_selector = 'select[name="' . $name . ($multi ? '[]' : '') .'"]';

		$select2Options = is_array($opts['select2']) ? $opts['select2'] : [];
		if (
			$spec_option !== false
			&& $spec_id == ''
			&& !isset($select2Options['placeholder'])
		) {
			$select2Options['placeholder'] = $spec_option;
			$select2Options['allowClear'] = $select2Options['allowClear'] ?? true;
		}
		$select2Options = json_encode($select2Options, JSON_FORCE_OBJECT);

		$js = "\nsetTimeout(() => $('{$_selector}').select2($select2Options));";
		if ($Ajax->isActive()) {
			$Ajax->addScript(true, $js);
		}
		else {
			$GLOBALS['__FOOT__'][] = "\n<script>{$js}</script>";
		}
	}

	$txt = get_post($search_box);
	$rel = '';
	$limit = '';
	if (isset($_POST['_'.$name.'_update'])) { // select list or search box change
		if ($by_id) $txt = $_POST[$name];

		if (!$opts['async'])
			$Ajax->activate('_page_body');
		else
			$Ajax->activate($name);
	}
	if (isset($_POST[$search_button])) {
		if (!$opts['async'])
			$Ajax->activate('_page_body');
		else
			$Ajax->activate($name);
	}
	if ($search_box) {
		// search related sql modifications

		$rel = "rel='$search_box'"; // set relation to list
		if ($opts['search_submit']) {
			if (isset($_POST[$search_button])) {
				$selected_id = array(); // ignore selected_id while search
				if (!$opts['async'])
					$Ajax->activate('_page_body');
				else
					$Ajax->activate($name);
			}
			if ($txt == '') {
				if ($spec_option === false && $selected_id == array())
					$limit = ' LIMIT 1';
				else
					$opts['where'][] = $valfield . "=". db_escape(get_post($name, $spec_id));
			}
			else
				if ($txt != '*') {

					foreach($opts['search'] as $i=> $s)
						$opts['search'][$i] = $s . " LIKE "
							.db_escape(($class=='combo3' ? '' : '%').$txt.'%');
					$opts['where'][] = '('. implode(' OR ', $opts['search']) . ')';
				}
		}
	}

	// sql completion
	if (count($opts['where'])) {
		$where = strpos($sql, 'WHERE')==false ? ' WHERE ':' AND ';
		$where .= '('. implode(' AND ', $opts['where']) . ')';
		$group_pos = strpos($sql, 'GROUP BY');
		if ($group_pos) {
			$group = substr($sql, $group_pos);
			$sql = substr($sql, 0, $group_pos) . $where.' '.$group;
		} else {
			$sql .= $where;
		}
	}

//    display_error(print_r($sql,true));

	if ($opts['order'] != false) {
		if (!is_array($opts['order']))
			$opts['order'] = array($opts['order']);
		$sql .= ' ORDER BY '.implode(',',$opts['order']);
	}

	$sql .= $limit;
	// ------ make selector ----------
	$selector = $first_opt = '';
	$first_id = false;
	$found = false;
	$lastcat = null;
	$edit = false;
	$pname = false;
	if (($type === "customer" || $type === "supplier") && !empty($SysPrefs->prefs['shortname_name_in_list']))
		$pname = true;
	if($result = db_query($sql)) {
		while ($contact_row = db_fetch($result)) {
			$value = $contact_row[0];


//            display_error(print_r($opts['format'] ,true));
			$descr = $opts['format']==null ?  $contact_row[1] :
				call_user_func($opts['format'], $contact_row, $pname);
			$sel = '';




 		 	if (get_post($search_button) && ($txt == $value)) {
 		 		$selected_id[] = $value;
 		 	}

 		 	if (in_array((string)$value, $selected_id, true)) {
				$sel = 'selected';
				$found = $value;
				$edit = $opts['editable'] && $contact_row['editable'] 
					&& (@$_POST[$search_box] == $value)
					? $contact_row[1] : false; // get non-formatted description
				if ($edit)
					break;	// selected field is editable - abandon list construction
			}
			// show selected option even if inactive 
			if (!$opts['show_inactive'] && @$contact_row['inactive'] && $sel==='') {
				continue;
			} else 
				$optclass = @$contact_row['inactive'] ? "class='inactive'" : '';

			if ($first_id === false) {
				$first_id = $value;
				$first_opt = $descr;
			}
			$cat = $contact_row[$opts['category']];
			if ($opts['category'] !== false && $cat != $lastcat){
				if ($lastcat!==null)
					$selector .= "</optgroup>";
				$selector .= "<optgroup label='".$cat."'>\n";
				$lastcat = $cat;
			}
            $diaply='';
			if($name=='empl_id' || $name=='report_to' || $name=='forward_to')
            {
                $exp=explode("-",$descr);
                $diaply=$exp[1].' - '.$exp[2];
            }
			else
            {
                $diaply=$descr;
            }



			$selector .= "<option $sel $optclass value='$value'>$diaply</option>\n";


//            display_error(print_r($selector ,true));
		}
		if ($lastcat!==null)
			$selector .= "</optgroup>";
		db_free_result($result);
	}

	// Prepend special option.
	if ($spec_option !== false) { // if special option used - add it
		$first_id = $spec_id;
		$first_opt = $spec_option;
		$sel = $found===false ? 'selected' : '';
		$optclass = @$contact_row['inactive'] ? "class='inactive'" : '';
		$selector = "<option $sel value='$first_id'>$first_opt</option>\n"
			. $selector;



	}

	if ($found===false) {
		$selected_id = array($first_id);
	}

	$style ='';
	if ($opts['readonly']  == true) {
        $style = 'style="pointer-events: none; background: #e8e7e7; opacity: 0.7;"';
    }
	
	$_POST[$name] = $multi ? $selected_id : $selected_id[0];

	if ($SysPrefs->use_popup_search)
		$selector = "<select {$style} id='$name' autocomplete='off' ".($multi ? "multiple" : '')
		. ($opts['height']!==false ? ' size="'.$opts['height'].'"' : '')
		. "$disabled name='$name".($multi ? '[]':'')."' class='$class' title='"
		. $opts['sel_hint']."' $rel>".$selector."</select>\n";
	else
		$selector = "<select {$style} autocomplete='off' ".($multi ? "multiple" : '')
		. ($opts['height']!==false ? ' size="'.$opts['height'].'"' : '')
		. "$disabled name='$name".($multi ? '[]':'')."' class='$class' title='"
		. $opts['sel_hint']."' $rel>".$selector."</select>\n";
	if ($by_id && ($search_box != false || $opts['editable']) ) {
		// on first display show selector list
		if (isset($_POST[$search_box]) && $opts['editable'] && $edit) {
			$selector = "<input type='hidden' name='$name' value='".$_POST[$name]."'>"
			."<input type='text' $disabled name='{$name}_text' id='{$name}_text' size='".
				$opts['editable']."' maxlength='".$opts['max']."' $rel value='$edit'>\n";
				set_focus($name.'_text'); // prevent lost focus
		} else if (get_post($search_submit ? $search_submit : "_{$name}_button"))
			set_focus($name); // prevent lost focus
		if (!$opts['editable'])
	 		$txt = $found;
		$Ajax->addUpdate($name, $search_box, $txt ? $txt : '');
	}

    $_class = $opts['readonly'] ? 'class="d-block inactive-control"' : '';

	// option to block clicking on select box;
	$selector = "<span {$_class}>".$selector."</span>";

	$Ajax->addUpdate($name, "_{$name}_sel", $selector);

	// span for select list/input field update
	$selector = "<span id='_{$name}_sel'>".$selector."</span>\n";

	 // if selectable or editable list is used - add select button
	if ($select_submit != false || $search_button) {
	// button class selects form reload/ajax selector update
		$selector .= sprintf(SELECT_BUTTON, $disabled, user_theme(),
			(fallback_mode() ? '' : 'display:none;'),
			 '_'.$name.'_update')."\n";
	}
// ------ make combo ----------
	$edit_entry = '';
	if ($search_box != false) {
		$edit_entry = "<input $disabled type='text' name='$search_box' id='$search_box' size='".
			$opts['size']."' maxlength='".$opts['max'].
			"' value='$txt' class='$class' rel='$name' autocomplete='off' title='"
			.$opts['box_hint']."'"
			.(!fallback_mode() && !$by_id ? " style=display:none;":'')
			.">\n";
		if ($search_submit != false || $opts['editable']) {
			$edit_entry .= sprintf(SEARCH_BUTTON, $disabled, user_theme(),
				(fallback_mode() ? '' : 'display:none;'),
				$search_submit ? $search_submit : "_{$name}_button")."\n";
		}
	}
	default_focus(($search_box && $by_id) ? $search_box : $name);

	$img = "";
	if ($SysPrefs->use_popup_search && (!isset($opts['fixed_asset']) || !$opts['fixed_asset']))
	{
		$img_title = "";
		$link = "";
		$image_icon_class='';
  		$id = $name;
  		if ($SysPrefs->use_popup_windows) {
    		switch (strtolower($type)) {
      			case "stock":
        			$link = $path_to_root . "/inventory/inquiry/stock_list.php?popup=1&type=all&client_id=" . $id;
        			$img_title = trans("Search items");
        			break;
      			case "stock_manufactured":
        			$link = $path_to_root . "/inventory/inquiry/stock_list.php?popup=1&type=manufactured&client_id=" . $id;
        			$img_title = trans("Search items");
        			break;
      			case "stock_purchased":
        			$link = $path_to_root . "/inventory/inquiry/stock_list.php?popup=1&type=purchasable&client_id=" . $id;
        			$img_title = trans("Search items");
        			break;
      			case "stock_sales":

                    if(isset($_REQUEST['show_items'])) {
                        $show = $_REQUEST['show_items'];
                    }
                    else if(isset($_SERVER['HTTP_REFERER'])) {
                        $get_string = strval(parse_url($_SERVER['HTTP_REFERER'],PHP_URL_QUERY));
                        parse_str($get_string, $get_array);
                        $show = isset($get_array["amp;show_items"]) ? $get_array["amp;show_items"] : "";
                    }

                    if(empty($show))
                        $show = "all";

        			$link = user_check_access('SA_SALES_ITEM_SEARCH')
						? ($path_to_root . "/inventory/inquiry/stock_list.php?popup=1&type=sales&client_id=" . $id."&show=$show")
						: '';
        			$img_title = trans("Search items");
                    $image_icon_class = 'item_list_options';
        			break;
      			case "stock_costable":
        			$link = $path_to_root . "/inventory/inquiry/stock_list.php?popup=1&type=costable&client_id=" . $id;
        			$img_title = trans("Search items");
        			break;
      			case "component":
      				$parent = $opts['parent'];
        			$link = $path_to_root . "/inventory/inquiry/stock_list.php?popup=1&type=component&parent=".$parent."&client_id=" . $id;
        			$img_title = trans("Search items");
        			break;
      			case "kits":
        			$link = $path_to_root . "/inventory/inquiry/stock_list.php?popup=1&type=kits&client_id=" . $id;
        			$img_title = trans("Search items");
        			break;
      			case "customer":
        			$link = $path_to_root . "/sales/inquiry/customers_list.php?popup=1&client_id=" . $id;
        			$img_title = trans("Search customers");
        			break;
      			case "branch":
        			$link = $path_to_root . "/sales/inquiry/customer_branches_list.php?popup=1&client_id=" . $id . "#customer_id";
        			$img_title = trans("Search branches");
        			break;
      			case "supplier":
        			$link = $path_to_root . "/purchasing/inquiry/suppliers_list.php?popup=1&client_id=" . $id;
        			$img_title = trans("Search suppliers");
        			break;
      			case "account":
      			case "account2":
      				$skip = strtolower($type) == "account" ? false : true;
        			$link = $path_to_root . "/gl/inquiry/accounts_list.php?popup=1&skip=".$skip."&client_id=" . $id;
        			$img_title = trans("Search GL accounts");
        			break;
    		}
  		}

  		if ($link !== "" && !$opts['readonly']) {
    		$theme = user_theme();
    		$img = '<img class="'.$image_icon_class.'" src="'.$path_to_root.'/themes/'.$theme.'/images/'.ICON_VIEW.
    			'" style="vertical-align:middle;width:20px;height:20px;border:0;" onclick="javascript:lookupWindow(&quot;'.
    			$link.'&quot;, &quot;&quot;);" title="' . $img_title . '" style="cursor:pointer;" />';
  		}
	}

	$Ajax->addUpdate($name, "_{$name}_img_sel", $img);
	$img = "<span {$_class} id='_{$name}_img_sel'>{$img}</span>";

	if ($opts['editlink'])
		$selector .= ' '.$opts['editlink'];

	if ($search_box && $opts['cells'])
		$str = ($edit_entry!='' ? "<td>$edit_entry</td>" : '')."<td nowrap>$selector$img</td>";
	else
		$str = $edit_entry.$selector.$img;
	return $str;
}

/*
	Helper function.
	Returns true if selector $name is subject to update.
*/
function list_updated($name)
{
	return isset($_POST['_'.$name.'_update']) || isset($_POST['_'.$name.'_button']);
}
//----------------------------------------------------------------------------------------------
//	Universal array combo generator
//	$items is array of options 'value' => 'description'
//	Options is reduced set of combo_selector options and is merged with defaults.

function array_selector($name, $selected_id, $items, $options=null)
{
	global $Ajax;

$opts = array(		// default options
	'spec_option'=>false, 	// option text or false
	'spec_id' => 0,		// option id
	'select_submit' => false, //submit on select: true/false
	'async' => true,	// select update via ajax (true) vs _page_body reload
	'default' => '', // default value when $_POST is not set
	'multi'=>false,	// multiple select
		// search box parameters
	'height' => false,	// number of lines in select box
	'sel_hint' => null,
	'disabled' => false,
	'readonly' => false,
	'select2' => false
);
// ------ merge options with defaults ----------
	if($options != null)
		$opts = array_merge($opts, $options);
	$select_submit =  $opts['select_submit'];
	$spec_id = $opts['spec_id'];
	$spec_option = $opts['spec_option'];
	$disabled = $opts['disabled'] ? "disabled" : '';
	$multi = $opts['multi'];

	if ($selected_id == null) {
		$selected_id = get_post($name, $opts['default']);
	}
	if(!is_array($selected_id))
		$selected_id = array((string)$selected_id); // code is generalized for multiple selection support

	if ($opts['select2']) {
		$_selector = 'select[name="' . $name . ($multi ? '[]' : '') .'"]';
		$select2Options = is_array($opts['select2']) ? $opts['select2'] : [];
		if (
			$spec_option !== false
			&& $spec_id == ''
			&& !isset($select2Options['placeholder'])
		) {
			$select2Options['placeholder'] = $spec_option;
			$select2Options['allowClear'] = $select2Options['allowClear'] ?? true;
		}
		$select2Options = json_encode($select2Options, JSON_FORCE_OBJECT);
		$js = "\nsetTimeout(() => $('{$_selector}').select2($select2Options));";
		if ($Ajax->isActive()) {
			$Ajax->addScript(true, $js);
		}
		else {
			$GLOBALS['__FOOT__'][] = "\n<script>{$js}</script>";
		}
	}

	if (isset($_POST[ '_'.$name.'_update'])) {
		if (!$opts['async'])
			$Ajax->activate('_page_body');
		else
			$Ajax->activate($name);
	}

	// ------ make selector ----------
	$selector = $first_opt = '';
	$first_id = false;
	$found = false;
	foreach($items as $value=>$descr) {
		$sel = '';
		if (in_array((string)$value, $selected_id, true)) {
			$sel = 'selected';
			$found = $value;
		}
		if ($first_id === false) {
			$first_id = $value;
			$first_opt = $descr;
		}
		$selector .= "<option $sel value='$value'>$descr</option>\n";
	}

	if ($first_id!==false) {
		$sel = ($found===$first_id) || ($found===false && ($spec_option===false)) ? "selected='selected'" : '';
	}
	// Prepend special option.
	if ($spec_option !== false) { // if special option used - add it
		$first_id = $spec_id;
		$first_opt = $spec_option;
		$sel = $found===false ? 'selected' : '';
		$selector = "<option $sel value='$spec_id'>$spec_option</option>\n"
			. $selector;
	}

	if ($found===false) {
		$selected_id = array($first_id);
	}
	$_POST[$name] = $multi ? $selected_id : $selected_id[0];

	$style = '';
	if ($opts['readonly']) {
        $style = 'style="pointer-events: none; background: #e8e7e7; opacity: 0.7;"';
    }

	$selector = "<select {$style} autocomplete='off' ".($multi  ? "multiple" : '')
		. ($opts['height']!==false ? ' size="'.$opts['height'].'"' : '')
		. "$disabled name='$name".($multi ? '[]' : '')."' class='combo' title='"
		. $opts['sel_hint']."'>".$selector."</select>\n";

	$_class = $opts['readonly'] ? 'class="d-block inactive-control"' : '';

	// option to block clicking on select box;
	$selector = "<span {$_class}>".$selector."</span>";
	
	$Ajax->addUpdate($name, "_{$name}_sel", $selector);

	$selector = "<span id='_{$name}_sel'>".$selector."</span>\n";

	if ($select_submit != false) { // if submit on change is used - add select button
		$selector .= sprintf(SELECT_BUTTON, $disabled, user_theme(),
			(fallback_mode() ? '' : 'display:none;'),
			 '_'.$name.'_update')."\n";
	}
	default_focus($name);

	return $selector;
}



//----------------------------------------------------------------------------------------------
function array_selector_row($label, $name, $selected_id, $items, $options=null, $readonly=false)
{
	$class = $readonly ? 'class="inactive-control"' : '';
	echo "<tr><td class='label'>$label</td>\n<td {$class}>";
	echo array_selector($name, $selected_id, $items, $options);
	echo "</td></tr>\n";
}

function array_selector_cells($label, $name, $selected_id, $items, $options=null)
{
		if ($label != null)
			echo "<td>$label</td>\n";
		echo "<td>";
		echo array_selector($name, $selected_id, $items, $options);
		echo "</td>\n";
}

//----------------------------------------------------------------------------------------------
function _format_add_curr($row, $pname=false)
{
	static $company_currency;

    if ($company_currency == null)
	{
		$company_currency = get_company_currency();
	}
	if (!$pname)
	{
		return $row[1] . ($row[2] == $company_currency ? '' : ("&nbsp;-&nbsp;" . $row[2]));
	}		
	else
	{
		return $row[1] . "&nbsp;-&nbsp;" . $row[2] . ($row[3] == $company_currency ? '' : ("&nbsp;-&nbsp;" . $row[3]));
	}		
}


function _bk_format_add_curr($row, $pname=false)
{
    static $company_currency;

    if ($company_currency == null)
    {
        $company_currency = get_company_currency();
    }
    if (!$pname)
    {
        return $row[0] . ($row[2] == $company_currency ? '' : ("&nbsp;-&nbsp;" . $row[2]));
    }
    else
    {
        return $row[0] . "&nbsp;-&nbsp;" . $row[2] . ($row[3] == $company_currency ? '' : ("&nbsp;-&nbsp;" . $row[3]));
    }
}

function add_edit_combo($type)
{
	global $path_to_root, $popup_editors, $SysPrefs;

	if (!isset($SysPrefs->use_icon_for_editkey) || $SysPrefs->use_icon_for_editkey==0)
		return "";
	// Derive theme path
	$theme_path = $path_to_root . '/themes/' . user_theme();

	$key = $popup_editors[$type][1];
	$onclick = "onclick=\"javascript:callEditor($key); return false;\"";
	$img = "<img width='12' height='12' border='0' alt='Add/Edit' title='Add/Edit' src='$theme_path/images/".ICON_EDIT."'>";
	return "<a target = '_blank' href='#' $onclick tabindex='-1'>$img</a>"; 
}

function undelivered_order_items_list(
	$name,
	$selected_id=null,
	$spec_option=true,
	$multi = false
)
{
	$placeholder = $spec_option === true ? trans("-- select --") : $spec_option;
	
	$selected_item = $selected_id ?? get_post($name);

	if (!$selected_item) {
		$selected_item = -1;
	}
	
	if (!is_array($selected_item)) {
		$selected_item = [$selected_item];
	}

	$selected_item = implode(",", array_map('db_escape', $selected_item));
	$select2Options = [
		'placeholder' => $placeholder ?: false,
		'allowClear' => (bool)($placeholder || $multi),
		'multiple' => (bool)$multi,
		'ajax' => [
			'url' => route('api.sales.orders.undeliveredItems.select2'),
            'dataType' => 'json',
            'delay' => 250,
		]
	];
	
	$sql = (
		"SELECT
			sod.line_reference as id,
			concat_ws(
				' - '
				, nullif(sod.line_reference, '')
				, nullif(so.reference, '')
				, nullif(dm.debtor_ref, '')
				, nullif(so.contact_phone, '')
				, ifnull(nullif(so.display_customer, ''), nullif(dm.name, ''))
				, nullif(sod.stk_code, '')
				, nullif(round(
					  sod.unit_price
					+ sod.returnable_amt
					+ sod.receivable_commission_amount
					, 2), ''
				)
				, nullif(round(
					  sod.govt_fee
					+ sod.bank_service_charge
					+ sod.bank_service_charge_vat
					, 2), ''
				)
				, nullif(sod.description, '')
			) as `text`
		FROM `0_sales_order_details` as sod
		LEFT JOIN `0_sales_orders` as so ON
			so.order_no = sod.order_no
			AND so.trans_type = sod.trans_type
		LEFT JOIN `0_debtors_master` as dm ON
			dm.debtor_no = so.debtor_no
		WHERE
			sod.quantity <> 0
			AND sod.qty_not_sent <> 0
			AND sod.trans_type = ".ST_SALESORDER."
			AND sod.line_reference in ({$selected_item})"
	);

	$opts = [
		'spec_option' => $placeholder,
		'spec_id' => '',
		'multi'	=> $multi,
		'select2' => $select2Options
	];

	$ret = combo_input($name, $selected_id, $sql, 'id', 'text', $opts);
	return $ret;
}

function undelivered_order_items_list_cells(
	$label,
	$name,
	$selected_id=null,
	$spec_option=true, 
	$multi=false
)
{
	if ($label != null)
		echo "<td>$label</td>\n";
	echo "<td>";	
	echo undelivered_order_items_list(
		$name,
		$selected_id,
		$spec_option,
		$multi
	);
	echo "</td>\n";
}

function undelivered_order_items_list_row(
	$label,
	$name,
	$selected_id=null,
	$spec_option=true, 
	$multi=false
)
{
	echo "<tr><td class='label'>$label</td><td>";
	echo undelivered_order_items_list(
		$name,
		$selected_id,
		$spec_option,
		$multi
	);
	echo "</td></tr>\n";
}

function maid_list($name, $selected_id=null, $spec_option=false, $submit_on_change=false,
	$all=false, $supplier_id=[], $nationality=[], $category_id=[])
{
	global $Ajax, $js_lib;

	$multi = false;
	$show_inactive = false;
	$placeholder = $spec_option === true ? trans("All Customers") : $spec_option;
	$selected_labour = isset($selected_id) ? $selected_id : (
		!empty($_POST[$name]) ? $_POST[$name]  : -1
	);

	if (!is_array($selected_labour)) {
		$selected_labour = [$selected_labour];
	}

		
	$selected_labour = implode(",", array_map('db_escape', $selected_labour));
	$selector = 'select[name="' . $name . ($multi ? '[]' : '') .'"]';
	$select2Options = json_encode([
		'placeholder' => $placeholder ?: false,
		'allowClear' => (bool)($placeholder || $multi),
		'multiple' => (bool)$multi,
		'showInactive' => (int)$show_inactive,
		'agentId' => $supplier_id,
		'nationality' => $nationality,
		'categoryId' => $category_id
	]);
	
	$js = "\nsetTimeout(() => initializeLaboursSelect2('{$selector}', $select2Options));";
	$Ajax->isActive() ? $Ajax->addScript(true, $js) : ($js_lib[] = $js);

	$sql = (
		"SELECT id, concat_ws(' - ', nullif(maid_ref, ''), `name`) as `name`
		FROM ".TB_PREF."labours
		WHERE id in ({$selected_labour})"
	);

	$ret = combo_input(
		$name,
		$selected_id,
		$sql,
		'id',
		'name',
		array(
			'order' => array('name'),
			'spec_option' => $spec_option === true ? trans("All Maids") : $spec_option,
			'spec_id' => ALL_TEXT,
			'select_submit'=> $submit_on_change,
			'async' => false,
			'sel_hint' => trans('Select maid'),
			'show_inactive'=> $all,
			'select2' => false
		)
	);

	return $ret;
}

function maid_list_cells($label, $name, $selected_id=null, $all_option=false, 
	$submit_on_change=false, $all=false, $supplier_id=[], $nationality=[], $category_id=[])
{
	if ($label != null)
		echo "<td>$label</td>\n";
	echo "<td>";	
	echo maid_list($name, $selected_id, $all_option, $submit_on_change, 
		$all, $supplier_id, $nationality, $category_id);
	echo "</td>\n";
}

function maid_list_row($label, $name, $selected_id=null, $all_option = false, 
	$submit_on_change=false, $all=false, $readonly=false, $supplier_id=[],
	$nationality=[], $category_id=[])
{
	$class = $readonly ? 'class="inactive-control"' : '';
	echo "<tr><td class='label'>$label</td><td {$class}>";
	echo maid_list($name, $selected_id, $all_option, $submit_on_change,
		$all, $supplier_id, $nationality, $category_id);
	echo "</td></tr>\n";
}

function supplier_list($name, $selected_id=null, $spec_option=false, $submit_on_change=false,
	$all=false, $editkey = false, $exclude_agents=false, $readonly=false)
{
	global $SysPrefs, $Ajax, $js_lib;
	if (!empty($SysPrefs->prefs['shortname_name_in_list']))
		$sql = "SELECT supplier_id, supp_ref, supp_name, curr_code, inactive FROM ".TB_PREF."suppliers ";
	else	
		$sql = "SELECT supplier_id, supp_ref, curr_code, inactive FROM ".TB_PREF."suppliers ";

	if ($exclude_agents) {
		$sql .= " WHERE supplier_type <> " . Agent::TYPE_AGENT . " ";
	}

	$mode = get_company_pref('no_supplier_list');

	if ($editkey)
		set_editor('supplier', $name, $editkey);

	$ret = combo_input($name, $selected_id, $sql, 'supplier_id', 'supp_name',
	array(
		'format' => '_format_add_curr',
	    'order' => array('supp_ref'),
		'search_box' => $mode!=0,
		'type' => 1,
        'search' => array("supp_ref","supp_name","gst_no"),        
		'spec_option' => $spec_option === true ? trans("All Suppliers") : $spec_option,
		'spec_id' => ALL_TEXT,
		'select_submit'=> $submit_on_change,
		'async' => false,
		'sel_hint' => $mode ? trans('Press Space tab to filter by name fragment') :
		trans('Select supplier'),
		'show_inactive'=>$all,
		'editlink' => $editkey ? add_edit_combo('supplier') : false,
		'readonly' => $readonly
		), "supplier");
	return $ret;
}

function supplier_list_cells($label, $name, $selected_id=null, $all_option=false, 
	$submit_on_change=false, $all=false, $editkey = false, $exclude_agents=false,
	$readonly=false)
{
	if ($label != null)
		echo "<td>$label</td>\n";
	echo "<td>";	
	echo supplier_list($name, $selected_id, $all_option, $submit_on_change, 
		$all, $editkey, $exclude_agents, $readonly);
	echo "</td>\n";
}

function supplier_list_row($label, $name, $selected_id=null, $all_option = false, 
	$submit_on_change=false, $all=false, $editkey = false, $exclude_agents=false,
	$readonly=false)
{
	echo "<tr><td class='label'>$label</td><td>";
	echo supplier_list($name, $selected_id, $all_option, $submit_on_change,
		$all, $editkey, $exclude_agents, $readonly);
	echo "</td></tr>\n";
}
//----------------------------------------------------------------------------------------------

function customer_list(
	$name,
	$selected_id=null,
	$spec_option=false,
	$submit_on_change=false,
	$show_inactive=false,
	$editkey = false,
	$where = '',
	$multi = false,
    $search_select = false
)
{
	global $SysPrefs, $Ajax, $js_lib;

	$placeholder = $spec_option === true ? trans("All Customers") : $spec_option;
	$selected_customer = isset($selected_id) ? $selected_id : (
		!empty($_POST[$name]) ? $_POST[$name]  : (
			($placeholder || $multi) ? -1 : Customer::WALK_IN_CUSTOMER
		)
	);
	
	if (!is_array($selected_customer)) {
		$selected_customer = [$selected_customer];
	}

	$selected_customer = implode(",", array_map('db_escape', $selected_customer));
	$selector = 'select[name="' . $name . ($multi ? '[]' : '') .'"]';
	$select2Options = json_encode([
		'placeholder' => $placeholder ?: false,
		'allowClear' => (bool)($placeholder || $multi),
		'multiple' => (bool)$multi,
		'showInactive' => (int)$show_inactive
	]);

	$js = "\nsetTimeout(() => initializeCustomersSelect2('{$selector}', $select2Options));";
	$Ajax->isActive() ? $Ajax->addScript(true, $js) : ($js_lib[] = $js);

	if (!empty($SysPrefs->prefs['shortname_name_in_list']))
		$sql = "SELECT debtor_no, debtor_ref, name, curr_code, inactive FROM ".TB_PREF."debtors_master WHERE debtor_no in ({$selected_customer})";
	else	
		$sql = "SELECT debtor_no, debtor_ref, curr_code, inactive FROM ".TB_PREF."debtors_master WHERE debtor_no in ({$selected_customer})";

	if(!empty($where))
	    $sql .= "AND $where";

	$mode = (get_company_pref('no_customer_list') != 0) || $search_select;

	if ($editkey)
		set_editor('customer', $name, $editkey);

	$opts = [
		'format' => '_format_add_curr',
	    'order' => array('CAST(debtor_ref AS UNSIGNED)'),
		'search_box' => $mode,
		'type' => 1,
		'size' => 20,
        'search' => array("debtor_ref","name","tax_id"),        
		'spec_option' => $spec_option === true ? trans("All Customers") : $spec_option,
		'spec_id' => ALL_TEXT,
		'select_submit'=> $submit_on_change,
		'async' => false,
		'sel_hint' => $mode ? trans('Press Space tab to filter by name fragment; F2 - entry new customer') :
		trans('Select customer'),
		'show_inactive' => $show_inactive,
		'multi'	=> $multi,
		'editlink' => $editkey ? add_edit_combo('customer') : false,
		'select2' => false
	];

	$ret = combo_input($name, $selected_id, $sql, 'debtor_no', 'debtor_ref', $opts, "customer" );
	return $ret;
}

function emp_list(
	$name,
	$selected_id=null,
	$spec_option=false,
	$submit_on_change=false,
	$show_inactive=false,
	$where = '',
	$multi = false
)
{
	$sql = "SELECT e.id, CONCAT(e.emp_ref, ' - ', e.name) name FROM `0_employees` e WHERE 1=1 ";

	if(!empty($where))
	    $sql .= "AND $where";

	$opts = [
	    'order' => array('e.name'),
		'size' => 20,      
		'spec_option' => $spec_option === true ? trans("All Employees") : $spec_option,
		'spec_id' => ALL_TEXT,
		'select_submit'=> $submit_on_change,
		'async' => false,
		trans('Select employee'),
		'show_inactive' => $show_inactive,
		'multi'	=> $multi
	];

	$ret = combo_input($name, $selected_id, $sql, 'id', 'name', $opts);
	return $ret;
}

function customer_list_cells(
	$label,
	$name,
	$selected_id=null,
	$all_option=false,
	$submit_on_change=false,
	$show_inactive=false,
	$editkey = false,
	$where = '',
	$multi = false,
    $search_select = false
) {
	if ($label != null)
		echo "<td>$label</td>\n";
	echo "<td nowrap>";
	echo customer_list(
        $name,
        $selected_id,
        $all_option,
        $submit_on_change,
        $show_inactive,
        $editkey,
        $where,
        $multi,
        $search_select
    );
	echo "</td>\n";
}

function emp_list_cells(
	$label,
	$name,
	$selected_id=null,
	$all_option=false,
	$submit_on_change=false,
	$show_inactive=false,
	$where = '',
	$multi = false
) {
	if ($label != null)
		echo "<td>$label</td>\n";
	echo "<td nowrap>";
	echo emp_list($name, $selected_id, $all_option, $submit_on_change, $show_inactive, $where, $multi);
	echo "</td>\n";
}

function customer_list_row(
	$label,
	$name,
	$selected_id=null,
	$all_option = false,
	$submit_on_change=false,
	$show_inactive=false,
	$editkey = false,
	$where = '',
	$row_params='',
	$multi = false,
    $search_select = false
) {
	echo "<tr $row_params><td class='label'>$label</td><td nowrap>";
	echo customer_list(
        $name,
        $selected_id,
        $all_option,
        $submit_on_change,
        $show_inactive,
        $editkey,
        $where,
        $multi,
        $search_select
    );
	echo "</td>\n</tr>\n";
}

//------------------------------------------------------------------------------------------------

function customer_branches_list($customer_id, $name, $selected_id=null,
	$spec_option = true, $enabled=true, $submit_on_change=false, $editkey = false)
{

	$sql = "SELECT branch_code, branch_ref FROM ".TB_PREF."cust_branch
		WHERE debtor_no=" . db_escape($customer_id)." ";

	if ($editkey)
		set_editor('branch', $name, $editkey);

	$where = $enabled ? array("inactive = 0") : array();
	$ret = combo_input($name, $selected_id, $sql, 'branch_code', 'branch_ref',
	array(
		'where' => $where,
		'order' => array('branch_ref'),
		'spec_option' => $spec_option === true ? trans('All branches') : $spec_option,
		'spec_id' => ALL_TEXT,
		'select_submit'=> $submit_on_change,
		'sel_hint' => trans('Select customer branch'),
		'editlink' => $editkey ? add_edit_combo('branch') : false
	), "branch" );
	return $ret;
}
//------------------------------------------------------------------------------------------------

function customer_branches_list_cells($label,$customer_id, $name, $selected_id=null, 
	$all_option = true, $enabled=true, $submit_on_change=false, $editkey = false)
{
	if ($label != null)
		echo "<td>$label</td>\n";
	echo "<td>";
	echo customer_branches_list($customer_id, $name, $selected_id, $all_option, $enabled, 
		$submit_on_change, $editkey);
	echo "</td>\n";
}

function customer_branches_list_row($label, $customer_id, $name, $selected_id=null, 
	$all_option = true, $enabled=true, $submit_on_change=false, $editkey = false)
{
	echo "<tr class='customer-branch-list-row'><td class='label'>$label</td>";
	customer_branches_list_cells(null, $customer_id, $name, $selected_id, 
		$all_option, $enabled, $submit_on_change, $editkey);
	echo "</tr>";
}

//------------------------------------------------------------------------------------------------

function locations_list($name, $selected_id=null, $all_option=false, $submit_on_change=false, $fixed_asset=false)
{

	$sql = "SELECT loc_code, location_name, inactive FROM ".TB_PREF."locations WHERE fixed_asset=".(int)$fixed_asset;

	return combo_input($name, $selected_id, $sql, 'loc_code', 'location_name',
		array(
			'spec_option' => $all_option === true ? trans("All Locations") : $all_option,
			'spec_id' => ALL_TEXT,
			'select_submit'=> $submit_on_change
		) );
}

function locations_list_cells($label, $name, $selected_id=null, $all_option=false, $submit_on_change=false, $fixed_asset=false)
{
	if ($label != null)
		echo "<td>$label</td>\n";
	echo "<td>";
	echo locations_list($name, $selected_id, $all_option, $submit_on_change, $fixed_asset);
	echo "</td>\n";
}

function locations_list_row($label, $name, $selected_id=null, $all_option=false, $submit_on_change=false, $fixed_asset=false)
{
	echo "<tr><td class='label'>$label</td>";
	locations_list_cells(null, $name, $selected_id, $all_option, $submit_on_change, $fixed_asset);
	echo "</tr>\n";
}

//-----------------------------------------------------------------------------------------------

function currencies_list($name, $selected_id=null, $submit_on_change=false, $exclude_home_curr=false)
{
	$sql = "SELECT curr_abrev, currency, inactive FROM ".TB_PREF."currencies";
	if ($exclude_home_curr)
		$sql .= " WHERE curr_abrev!='".get_company_currency()."'";

	// default to the company currency
	return combo_input($name, $selected_id, $sql, 'curr_abrev', 'currency',
		array(
			'select_submit'=> $submit_on_change,
			'default' => get_company_currency(),
			'async' => false
		) );
}

function currencies_list_cells($label, $name, $selected_id=null, $submit_on_change=false)
{
	if ($label != null)
		echo "<td>$label</td>\n";
	echo "<td>";
	echo currencies_list($name, $selected_id, $submit_on_change);
	echo "</td>\n";
}

function currencies_list_row($label, $name, $selected_id=null, $submit_on_change=false)
{
	echo "<tr><td class='label'>$label</td>";
	currencies_list_cells(null, $name, $selected_id, $submit_on_change);
	echo "</tr>\n";
}

//---------------------------------------------------------------------------------------------------

function fiscalyears_list($name, $selected_id=null, $submit_on_change=false)
{

	$sql = "SELECT * FROM ".TB_PREF."fiscal_year";

	// default to the company current fiscal year

	return combo_input($name, $selected_id, $sql, 'id', '',
		array(
			'order' => 'begin',
			'default' => get_company_pref('f_year'),
			'format' => '_format_fiscalyears',
			'select_submit'=> $submit_on_change,
			'async' => false
		) );
}

function _format_fiscalyears($row)
{
	return sql2date($row[1]) . "&nbsp;-&nbsp;" . sql2date($row[2])
	. "&nbsp;&nbsp;" . ($row[3] ? trans('Closed') : trans('Active'));
}

function fiscalyears_list_cells($label, $name, $selected_id=null)
{
	if ($label != null)
		echo "<td>$label</td>\n";
	echo "<td>";
	echo fiscalyears_list($name, $selected_id);
	echo "</td>\n";
}

function fiscalyears_list_row($label, $name, $selected_id=null)
{
	echo "<tr><td class='label'>$label</td>";
	fiscalyears_list_cells(null, $name, $selected_id);
	echo "</tr>\n";
}
//------------------------------------------------------------------------------------

function dimensions_list(
    $name,
    $selected_id=null,
    $no_option=false,
    $showname=' ',
	$submit_on_change=false,
    $showclosed=false,
    $showtype=1,
    $multi=false,
    $ids = null,
	$readonly = false
) {
//	$sql = "SELECT id, CONCAT(reference,'  ',name) as ref FROM ".TB_PREF."dimensions";
	$sql = "SELECT id, name as ref FROM ".TB_PREF."dimensions";

    if (!empty($ids)) {
        if (is_array($ids)) {
            $ids = implode(",", $ids);
        }

        $sql .= " WHERE id IN ({$ids})";
    }

	$options = array(
		'order' => 'reference',
		'spec_option'=>$no_option ? $showname : false,
		'spec_id' => 0,
		'select_submit'=> $submit_on_change,
		'async' => false,
        'multi' => $multi,
		'readonly' => $readonly
	);

	if (!$showclosed)
		$options['where'][] = "closed=0";
	if($showtype)
		$options['where'][] = "type_=".db_escape($showtype);

	return combo_input($name, $selected_id, $sql, 'id', 'ref', $options);
}

function payelements_list(
    $name,
    $selected_id=null,
    $no_option=false,
    $showname=' ',
	$submit_on_change=false,
    $hide_inactive=1,
    $multi=false,
    $ids = null,
	$type = null,
	$is_fixed = null
) {
	$sql = "SELECT id, name as ref FROM ".TB_PREF."pay_elements WHERE 1=1";

	if (!is_null($type)) {
		$sql .= " and type = ".db_escape($type);
	}

    if (!empty($ids)) {
        if (is_array($ids)) {
            $ids = implode(",", $ids);
        }

        $sql .= " and id IN ({$ids})";
    }

	if (!is_null($is_fixed)) {
		$sql .= " and is_fixed = ".db_escape($is_fixed);
	}

	$options = array(
		'order' => 'id',
		'spec_option'=>$no_option ? $showname : false,
		'spec_id' => 0,
		'select_submit'=> $submit_on_change,
		'async' => false,
        'multi' => $multi
	);

	if($hide_inactive)
		$options['where'][] = "inactive=0";

	return combo_input($name, $selected_id, $sql, 'id', 'id', $options);
}

function flow_groups_list(
    $name,
    $selected_id=null,
    $spec_option=true,
	$submit_on_change=false,
    $multi=false
) {
	$sql = (
		"SELECT id, `name`
		FROM ".TB_PREF."entity_groups
		WHERE
			category = ".db_escape(EntityGroupCategory::WORK_FLOW_RELATED)
	);

	$options = array(
		'order' => 'name',
		'spec_option' => $spec_option === true ? '-- select --' : $spec_option,
		'spec_id' => '',
		'select_submit'=> $submit_on_change,
		'async' => false,
        'multi' => $multi
	);

	return combo_input($name, $selected_id, $sql, 'id', 'name', $options);
}

function flow_groups_list_cells($label, $name, $selected_id=null, $spec_option=true,
	$submit_on_change=false, $multi=false)
{
	if ($label != null)
		echo "<td>$label</td>\n";

	echo "<td>";
	echo flow_groups_list($name, $selected_id, $spec_option, $submit_on_change, $multi);
	echo "</td>\n";
}

function flow_groups_list_row($label, $name, $selected_id=null, $spec_option=true,
		 $submit_on_change=false, $multi=false)
{
	echo "<tr><td class='label'>$label</td>";
	flow_groups_list_cells(null, $name, $selected_id, $spec_option, $submit_on_change, $multi);
	echo "</tr>\n";
}

function countries_list(
    $name,
    $selected_id=null,
    $no_option=false,
    $showname=' ',
	$submit_on_change=false,
    $multi=false
) {
	$sql = "SELECT code as id, name as ref FROM ".TB_PREF."countries";

	$options = array(
		'order' => 'code',
		'spec_option'=>$no_option ? $showname : false,
		'spec_id' => '',
		'select_submit'=> $submit_on_change,
		'async' => false,
        'multi' => $multi
	);

	return combo_input($name, $selected_id, $sql, 'code', 'code', $options);
}

function departments_list(
    $name,
    $selected_id=null,
    $no_option=false,
    $showname=' ',
	$submit_on_change=false,
    $hide_inactive=1,
    $multi=false,
    $ids = null
) {
	$sql = "SELECT id, name as ref FROM ".TB_PREF."departments";

    if (!empty($ids)) {
        if (is_array($ids)) {
            $ids = implode(",", $ids);
        }

        $sql .= " WHERE id IN ({$ids})";
    }

	$options = array(
		'order' => 'id',
		'spec_option'=>$no_option ? $showname : false,
		'spec_id' => 0,
		'select_submit'=> $submit_on_change,
		'async' => false,
        'multi' => $multi
	);

	if($hide_inactive)
		$options['where'][] = "inactive=0";

	return combo_input($name, $selected_id, $sql, 'id', 'id', $options);
}

function designations_list(
    $name,
    $selected_id=null,
    $spec_option=true,
	$submit_on_change=false,
    $hide_inactive=1,
    $multi=false
) {
	$sql = "SELECT id, `name` FROM ".TB_PREF."designations";

	$options = array(
		'order' => 'id',
		'spec_option'=> $spec_option === true ? '-- Select Designation --' : $spec_option,
		'spec_id' => '',
		'select_submit'=> $submit_on_change,
		'async' => false,
        'multi' => $multi
	);

	if($hide_inactive)
		$options['where'][] = "inactive=0";

	return combo_input($name, $selected_id, $sql, 'id', 'name', $options);
}

function shifts_list(
    $name,
    $selected_id=null,
    $no_option=false,
    $showname=' ',
	$submit_on_change=false,
    $multi=false,
    $ids = null
) {
	$sql = "SELECT id, code as ref FROM ".TB_PREF."shifts";

    if (!empty($ids)) {
        if (is_array($ids)) {
            $ids = implode(",", $ids);
        }

        $sql .= " WHERE id IN ({$ids})";
    }

	$options = array(
		'order' => 'id',
		'spec_option'=>$no_option ? $showname : false,
		'spec_id' => 0,
		'select_submit'=> $submit_on_change,
		'async' => false,
        'multi' => $multi
	);

	return combo_input($name, $selected_id, $sql, 'id', 'id', $options);
}

function dimensions_list_cells(
    $label,
    $name,
    $selected_id=null,
    $no_option=false,
    $showname=null,
	$showclosed=false,
    $showtype=0,
    $submit_on_change=false,
    $multi=false,
    $ids = null,
	$readonly = false
) {
	if ($label != null)
		echo "<td>$label</td>\n";
	echo "<td>";
	echo dimensions_list(
        $name,
        $selected_id,
        $no_option,
        $showname,
        $submit_on_change,
        $showclosed,
        $showtype,
        $multi,
        $ids,
		$readonly
    );
	echo "</td>\n";
}

function payelements_list_cells(
    $label,
    $name,
    $selected_id=null,
    $no_option=false,
    $showname=null,
    $hide_inactive=1,
    $submit_on_change=false,
    $multi=false,
    $ids=null,
	$type=null,
	$is_fixed = null
) {
	if ($label != null)
		echo "<td>$label</td>\n";
	echo "<td>";
	echo payelements_list(
        $name,
        $selected_id,
        $no_option,
        $showname,
        $submit_on_change,
        $hide_inactive,
        $multi,
        $ids,
		$type,
		$is_fixed
    );
	echo "</td>\n";
}

function countries_list_cells(
    $label,
    $name,
    $selected_id=null,
    $no_option=false,
    $showname=null,
    $submit_on_change=false,
    $multi=false
) {
	if ($label != null)
		echo "<td>$label</td>\n";
	echo "<td>";
	echo countries_list(
        $name,
        $selected_id,
        $no_option,
        $showname,
        $submit_on_change,
        $multi
    );
	echo "</td>\n";
}

function designations_list_cells(
    $label,
    $name,
    $selected_id=null,
    $spec_option=true,
    $submit_on_change=false,
	$hide_inactive=true,
    $multi=false
) {
	if ($label != null)
		echo "<td>$label</td>\n";
	echo "<td>";
	echo designations_list(
        $name,
        $selected_id,
        $spec_option,
        $submit_on_change,
		$hide_inactive,
        $multi
    );
	echo "</td>\n";
}

function departments_list_cells(
    $label,
    $name,
    $selected_id=null,
    $no_option=false,
    $showname=null,
    $hide_inactive=1,
    $submit_on_change=false,
    $multi=false,
    $ids = null
) {
	if ($label != null)
		echo "<td>$label</td>\n";
	echo "<td>";
	echo departments_list(
        $name,
        $selected_id,
        $no_option,
        $showname,
        $submit_on_change,
        $hide_inactive,
        $multi,
        $ids
    );
	echo "</td>\n";
}

function shifts_list_cells(
    $label,
    $name,
    $selected_id=null,
    $no_option=false,
    $showname=null,
    $submit_on_change=false,
    $multi=false,
    $ids = null
) {
	if ($label != null)
		echo "<td>$label</td>\n";
	echo "<td>";
	echo shifts_list(
        $name,
        $selected_id,
        $no_option,
        $showname,
        $submit_on_change,
        $multi,
        $ids
    );
	echo "</td>\n";
}

function dimensions_list_row($label, $name, $selected_id=null, $no_option=false, $showname=null,
	$showclosed=false, $showtype=0, $submit_on_change=false, $multi=false, $ids=null, $readonly=false)
{
	echo "<tr><td class='label'>$label</td>";
	dimensions_list_cells(null, $name, $selected_id, $no_option, $showname,
		$showclosed, $showtype, $submit_on_change, $multi, $ids, $readonly);
	echo "</tr>\n";
}



function payelements_list_row($label, $name, $selected_id=null, $no_option=false, $showname=null,
	 $hide_inactive=1, $submit_on_change=false, $multi=false, $ids=null, $type=null, $is_fixed = null)
{
	echo "<tr><td class='label'>$label</td>";
	payelements_list_cells(null, $name, $selected_id, $no_option, $showname,
		 $hide_inactive, $submit_on_change, $multi, $ids, $type, $is_fixed);
	echo "</tr>\n";
}

function countries_list_row($label, $name, $selected_id=null, $no_option=false, $showname=null,
		 $submit_on_change=false, $multi=false)
{
	echo "<tr><td class='label'>$label</td>";
	countries_list_cells(null, $name, $selected_id, $no_option, $showname,
		  $submit_on_change, $multi);
	echo "</tr>\n";
}

function designations_list_row(
	$label,
	$name,
	$selected_id=null,
	$spec_option=true,
	$submit_on_change=false,
	$hide_inactive=true,
	$multi=false
)
{
	echo "<tr><td class='label'>$label</td>";
	designations_list_cells(null, $name, $selected_id, $spec_option, $submit_on_change, $hide_inactive, $multi);
	echo "</tr>\n";
}

function departments_list_row($label, $name, $selected_id=null, $no_option=false, $showname=null,
	 $hide_inactive=1, $submit_on_change=false, $multi=false)
{
	echo "<tr><td class='label'>$label</td>";
	departments_list_cells(null, $name, $selected_id, $no_option, $showname,
		 $hide_inactive, $submit_on_change, $multi);
	echo "</tr>\n";
}

function shifts_list_row($label, $name, $selected_id=null, $no_option=false, $showname=null,
		 $submit_on_change=false, $multi=false)
{
	echo "<tr><td class='label'>$label</td>";
	shifts_list_cells(null, $name, $selected_id, $no_option, $showname,
		 $submit_on_change, $multi);
	echo "</tr>\n";
}


function asset_category_list_cells($label, $name, $selected_id=null, $no_option=false, $showname=null,
                               $showclosed=false, $showtype=0, $submit_on_change=false)
{
    if ($label != null)
        echo "<td>$label</td>\n";
    echo "<td>";
    echo asset_cat_list($name, $selected_id, $no_option, $showname, $submit_on_change, $showclosed, $showtype);
    echo "</td>\n";
}

function asset_category_list_row($label, $name, $selected_id=null, $no_option=false, $showname=null,
                             $showclosed=false, $showtype=0, $submit_on_change=false)
{
    echo "<tr><td class='label'>$label</td>";
    asset_category_list_cells(null, $name, $selected_id, $no_option, $showname,
        $showclosed, $showtype, $submit_on_change);
    echo "</tr>\n";
}


//---------------------------------------------------------------------------------------------------

function stock_items_list($name, $selected_id=null, $all_option=false, 
	$submit_on_change=false, $opts=array(), $editkey = false, $type = "stock")
{
	$sql = "SELECT stock_id, s.description, c.description,s.long_description, s.inactive, s.editable
			FROM ".TB_PREF."stock_master s,".TB_PREF."stock_category c WHERE s.category_id=c.category_id";

	if (isset($opts['fixed_asset']) && $opts['fixed_asset'])
		$sql .= " AND mb_flag='".STOCK_TYPE_FIXED_ASSET."'";
	else
		$sql .= " AND mb_flag!='".STOCK_TYPE_FIXED_ASSET."'";

	if ($editkey)
		set_editor('item', $name, $editkey);

	$ret = combo_input($name, $selected_id, $sql, 'stock_id', 's.description',
		array_merge(
		  array(
			'format' => '_format_stock_items',
			'spec_option' => $all_option===true ?  trans("All Items") : $all_option,
			'spec_id' => ALL_TEXT,
			'search_box' => true,
			'search' => array("stock_id", "c.description","s.description"),
			'search_submit' => get_company_pref('no_item_list')!=0 && (!isset($opts['fixed_asset']) || !$opts['fixed_asset']),
			'size'=>10,
			'select_submit'=> $submit_on_change,
			'category' => 2,
			'order' => array('c.description','stock_id'),
			'editlink' => $editkey ? add_edit_combo('item') : false,
			'editable' => false,
			'max' => 255
		  ), $opts), $type );
	return $ret;
}

function _format_stock_items($row)
{

    if(user_show_codes()) {
        return $row[3] ? $row[1]."&nbsp;-&nbsp;".$row[3] : $row[1];
    }
    return $row[1];

//	return user_show_codes() ?  $row[1]."&nbsp;-&nbsp;".$row[3] : $row[1];
//	return (user_show_codes() ?  ($row[1] . "&nbsp;-&nbsp;") : "") . $row[3];
}

function stock_items_list_cells($label, $name, $selected_id=null, $all_option=false, 
	$submit_on_change=false, $all=false, $editkey = false, $opts= array())
{
	if (isset($opts['fixed_asset']) && $opts['fixed_asset'])
		$editor_item = 'fa_item';
	else
		$editor_item = 'item';

// 	if ($editkey) ??
//		set_editor($editor_item, $name, $editkey);

	if ($label != null)
		echo "<td>$label</td>\n";

// ??
//  $opts = array_merge($options, array('cells'=>true, 'show_inactive'=>$all, 'new_icon' => $editkey ? 'item' : false));
//
//	echo stock_items_list($name, $selected_id, $all_option, $submit_on_change, $opts);

	echo stock_items_list($name, $selected_id, $all_option, $submit_on_change,
		array_merge(array('cells'=>true, 'show_inactive'=>$all), $opts), $editkey);

}
/*
function stock_items_list_row($label, $name, $selected_id=null, $all_option=false, $submit_on_change=false)
{
	echo "<tr>\n";
	stock_items_list_cells($label, $name, $selected_id, $all_option, $submit_on_change);
	echo "</tr>\n";
}
*/
//---------------------------------------------------------------------------------------------------
//
// Select item via foreign code.
//
function sales_items_list($name, $selected_id=null, $all_option=false,
	$submit_on_change=false, $type='', $opts=array(), $include_kit_items=true)
{
	// all sales codes
	$sql = (
		"SELECT
			i.item_code,
			concat_ws(
				' ',
				i.description,
				if(
					(!i.is_foreign AND i.item_code!=i.stock_id),
					NULL,
					concat('(', (s.govt_fee+s.bank_service_charge+bank_service_charge_vat), ')')
				),
				if(
					(!i.is_foreign AND i.item_code!=i.stock_id),
					NULL,
					s.long_description
				)
			) as descr,
			c.description,
			s.long_description,
			sum(!i.is_foreign AND i.item_code!=i.stock_id) > 0 as kit,
			i.inactive,
			if(sum(!i.is_foreign AND i.item_code!=i.stock_id) > 0, '0', s.editable) as editable
		FROM ".TB_PREF."stock_master s,
			".TB_PREF."item_codes i
		LEFT JOIN ".TB_PREF."stock_category c ON
			i.category_id = c.category_id
		WHERE
			i.stock_id = s.stock_id
      		AND mb_flag != '".STOCK_TYPE_FIXED_ASSET."'"
	);


    if ($type == 'local')	{ // exclude foreign codes
        $sql .=	" AND !i.is_foreign";
    } elseif ($type == 'kits') { // sales kits
        $sql .=	" AND !i.is_foreign AND i.item_code!=i.stock_id";
    }

	if (!$include_kit_items) {
		$sql .=	" AND !(!i.is_foreign AND i.item_code!=i.stock_id)";
	}
    $sql .= " AND !i.inactive AND !s.inactive AND !s.no_sale";
    $sql .= " GROUP BY i.item_code";



    return combo_input($name, $selected_id, $sql, 'i.item_code', 'c.description',
        array_merge(
            array(
                'format' => '_format_stock_items',
                'spec_option' => $all_option===true ?  trans("All Items") : $all_option,
                'spec_id' => ALL_TEXT,
                'search_box' => true,
                'search' => array("i.item_code", "c.description", "descr"),
                'search_submit' => get_company_pref('no_item_list')!=0,
                'size'=>15,
                'select_submit'=> $submit_on_change,
                'category' => 2,
                'order' => array('c.description','s.description'),
                'editable' => 30,
                'max' => 255
            ), $opts), $type == 'kits' ? $type : "stock_sales" );
}


function sales_items_no_search_box_list($name, $selected_id=null, $all_option=false,
                          $submit_on_change=false, $type='', $opts=array())
{
    // all sales codes
    $sql = "SELECT i.item_code, i.description, c.description, count(*)>1 as kit,
			 i.inactive, if(count(*)>1, '0', s.editable) as editable
			FROM
			".TB_PREF."stock_master s,
			".TB_PREF."item_codes i
			LEFT JOIN
			".TB_PREF."stock_category c
			ON i.category_id=c.category_id
			WHERE i.stock_id=s.stock_id
      AND mb_flag != '".STOCK_TYPE_FIXED_ASSET."'";


    if ($type == 'local')	{ // exclude foreign codes
        $sql .=	" AND !i.is_foreign";
    } elseif ($type == 'kits') { // sales kits
        $sql .=	" AND !i.is_foreign AND i.item_code!=i.stock_id";
    }
    $sql .= " AND !i.inactive AND !s.inactive AND !s.no_sale";
    $sql .= " GROUP BY i.item_code";

    return combo_input($name, $selected_id, $sql, 'i.item_code', 'c.description',
        array_merge(
            array(
                'format' => '_format_stock_items',
                'spec_option' => $all_option===true ?  trans("All Items") : $all_option,
                'spec_id' => ALL_TEXT,
                'search_box' => false,
                'search' => array("i.item_code", "c.description", "i.description"),
                'search_submit' => get_company_pref('no_item_list')!=0,
                'size'=>15,
                'select_submit'=> $submit_on_change,
                'category' => 2,
                'order' => array('c.description','i.item_code'),
                'editable' => 30,
                'max' => 255
            ), $opts), $type == 'kits' ? $type : "stock_sales" );
}

function sales_items_list_cells($label, $name, $selected_id=null, $all_option=false, $submit_on_change=false,
                                $editkey=false,$options=array())
{

    $opts =  array_merge(array('cells'=>true),$options);

	if ($editkey)
		set_editor('item', $name, $editkey);

	if ($label != null)
		echo "<td>$label</td>\n";

//	display_error(get_post('_stock_id_edit'));

	echo sales_items_list($name, $selected_id, $all_option, $submit_on_change,
		'', $opts);
}


function sales_items_no_search_box_list_cells($label, $name, $selected_id=null, $all_option=false, $submit_on_change=false, $editkey=false)
{
    if ($editkey)
        set_editor('item', $name, $editkey);

    if ($label != null)
        echo "<td>$label</td>\n";
	echo sales_items_no_search_box_list($name, $selected_id, $all_option, $submit_on_change,
		'', array('cells'=>true, 'max'=>50)); // maximum is set to 50 and not default 255.
}

function sales_kits_list($name, $selected_id=null, $all_option=false, $submit_on_change=false)
{
	return sales_items_list($name, $selected_id, $all_option, $submit_on_change,
		'kits', array('cells'=>false, 'editable' => false));
}

function sales_local_items_list_row($label, $name, $selected_id=null, $all_option=false, $submit_on_change=false, $include_kit_items=true)
{
	echo "<tr>";
	if ($label != null)
		echo "<td class='label'>$label</td>\n";
	echo "<td>";
	echo sales_items_list($name, $selected_id, $all_option, $submit_on_change,
		'local', array('cells'=>false, 'editable' => false), $include_kit_items);
	echo "</td></tr>";
}


function sales_local_items_no_search_box_list_row($label, $name, $selected_id=null, $all_option=false, $submit_on_change=false)
{
    echo "<tr>";
    if ($label != null)
        echo "<td class='label'>$label</td>\n";
    echo "<td>";
    echo sales_items_no_search_box_list($name, $selected_id, $all_option, $submit_on_change,
        'local', array('cells'=>false, 'editable' => false));
    echo "</td></tr>";
}

//------------------------------------------------------------------------------------

function stock_manufactured_items_list($name, $selected_id=null,
	$all_option=false, $submit_on_change=false)
{
	return stock_items_list($name, $selected_id, $all_option, $submit_on_change,
		array('where'=>array("mb_flag= '".STOCK_TYPE_MANUFACTURED."'")), false, "stock_manufactured");
}

function stock_manufactured_items_list_cells($label, $name, $selected_id=null,
				$all_option=false, $submit_on_change=false)
{
	if ($label != null)
		echo "<td>$label</td>\n";
	echo "<td>";
	echo stock_manufactured_items_list($name, $selected_id, $all_option, $submit_on_change);
	echo "</td>\n";
}

function stock_manufactured_items_list_row($label, $name, $selected_id=null,
		$all_option=false, $submit_on_change=false)
{
	echo "<tr><td class='label'>$label</td>";
	stock_manufactured_items_list_cells(null, $name, $selected_id, $all_option, $submit_on_change);
	echo "</tr>\n";
}
//------------------------------------------------------------------------------------

function stock_component_items_list($name, $parent_stock_id, $selected_id=null,
	$all_option=false, $submit_on_change=false, $editkey = false)
{
	$parent = db_escape($parent_stock_id);
	return stock_items_list($name, $selected_id, $all_option, $submit_on_change,
		array('where'=>array("stock_id != $parent"), 'parent'=> $parent_stock_id), $editkey, "component");
}


function stock_component_items_list_cells($label, $name, $parent_stock_id, 
	$selected_id=null, $all_option=false, $submit_on_change=false, $editkey = false)
{
	if ($label != null)
		echo "<td>$label</td>\n";
	$parent = db_escape($parent_stock_id);
	echo stock_items_list($name, $selected_id, $all_option, $submit_on_change,
		array('where'=>array("stock_id != '$parent_stock_id'"), 'cells'=>true, 'parent'=> $parent_stock_id), $editkey, "component");
}
//------------------------------------------------------------------------------------

function stock_costable_items_list($name, $selected_id=null,
	$all_option=false, $submit_on_change=false)
{
	return stock_items_list($name, $selected_id, $all_option, $submit_on_change,
		array('where'=>array("mb_flag!='".STOCK_TYPE_SERVICE."'")), false, "stock_costable");
}

function stock_costable_items_list_cells($label, $name, $selected_id=null, 
	$all_option=false, $submit_on_change=false)
{
	if ($label != null)
		echo "<td>$label</td>\n";
	echo stock_items_list($name, $selected_id, $all_option, $submit_on_change,
		array('where'=>array("mb_flag!='".STOCK_TYPE_SERVICE."'"), 'cells'=>true), false, "stock_costable");
}

//------------------------------------------------------------------------------------
function stock_purchasable_items_list($name, $selected_id=null,	
	$all_option=false, $submit_on_change=false, $all=false, $editkey=false)
{
	return stock_items_list($name, $selected_id, $all_option, $submit_on_change,
		array('where'=>array("NOT no_purchase"), 
			'show_inactive'=>$all), $editkey, "stock_purchased");
}
//
//	This helper is used in PO/GRN/PI entry and supports editable descriptions.
//
function stock_purchasable_items_list_cells($label, $name, $selected_id=null,
			$all_option=false, $submit_on_change=false, $editkey=false)
{
	if ($label != null)
		echo "<td>$label</td>\n";
	echo stock_items_list($name, $selected_id, $all_option, $submit_on_change,
		array('where'=>array("NOT no_purchase"), 
			 'editable' => 30,
			 'cells'=>true), $editkey);
}

//------------------------------------------------------------------------------------

function stock_item_types_list_row($label, $name, $selected_id=null, $enabled=true)
{
	global $stock_types;

	echo "<tr>";
	if ($label != null)
		echo "<td class='label'>$label</td>\n";
	echo "<td>";

	echo array_selector($name, $selected_id, $stock_types, 
		array( 
			'select_submit'=> true, 
			'disabled' => !$enabled) );
	echo "</td></tr>\n";
}

function stock_units_list_row($label, $name, $value=null, $enabled=true)
{
	$result = get_all_item_units();
	echo "<tr>";
	if ($label != null)
		echo "<td class='label'>$label</td>\n";
	echo "<td>";

	while($unit = db_fetch($result))
		$units[$unit['abbr']] = $unit['name'];

	echo array_selector($name, $value, $units, array( 'disabled' => !$enabled) );

	echo "</td></tr>\n";
}

//------------------------------------------------------------------------------------

function stock_purchasable_fa_list_cells($label, $name, $selected_id=null, $all_option=false,
	$submit_on_change=false, $all=false, $editkey = false, $exclude_items = array())
{
	// Check if a fixed asset has been bought.
	$where_opts[] = "stock_id NOT IN
    	( SELECT stock_id FROM ".TB_PREF."stock_moves WHERE type=".ST_SUPPRECEIVE." AND qty!=0 )";

	// exclude items currently on the order.
  	foreach($exclude_items as $item) {
    	$where_opts[] = "stock_id != ".db_escape($item->stock_id);
  	}
  	$where_opts[] = "mb_flag='".STOCK_TYPE_FIXED_ASSET."'";

	echo stock_items_list_cells($label, $name, $selected_id, $all_option, $submit_on_change, $all, $editkey, 
    array('fixed_asset' => true, 'where' => $where_opts));
}

function stock_disposable_fa_list($name, $selected_id=null,
	$all_option=false, $submit_on_change=false)
{
	// Check if a fixed asset has been bought....
	$where_opts[] = "stock_id IN
    	( SELECT stock_id FROM ".TB_PREF."stock_moves WHERE type=".ST_SUPPRECEIVE." AND qty!=0 )";
	// ...but has not been disposed or sold already.
  	$where_opts[] = "stock_id NOT IN
		( SELECT stock_id FROM ".TB_PREF."stock_moves WHERE (type=".ST_CUSTDELIVERY." OR type=".ST_INVADJUST.") AND qty!=0 )";

	$where_opts[] = "mb_flag='".STOCK_TYPE_FIXED_ASSET."'";

	echo stock_items_list($name, $selected_id, $all_option, $submit_on_change,
    array('fixed_asset' => true, 'where' => $where_opts));
}

function stock_disposable_fa_list_cells($label, $name, $selected_id=null,
	$all_option=false, $submit_on_change=false, $exclude_items = array())
{
	// Check if a fixed asset has been bought....
	$where_opts[] = "stock_id IN
    	( SELECT stock_id FROM ".TB_PREF."stock_moves WHERE type=".ST_SUPPRECEIVE." AND qty!=0 )";
	// ...but has not been disposed or sold already.
	$where_opts[] = "stock_id NOT IN
    	( SELECT stock_id FROM ".TB_PREF."stock_moves WHERE (type=".ST_CUSTDELIVERY." OR type=".ST_INVADJUST.") AND qty!=0 )";

	$where_opts[] = "mb_flag='".STOCK_TYPE_FIXED_ASSET."'";

	foreach($exclude_items as $item) {
    	$where_opts[] = "stock_id != ".db_escape($item->stock_id);
	}

	if ($label != null)
		echo "<td>$label</td>\n";
	echo stock_items_list($name, $selected_id, $all_option, $submit_on_change, 
    array('fixed_asset' => true, 'cells'=>true, 'where' => $where_opts));
}

function stock_depreciable_fa_list_cells($label, $name, $selected_id=null,
	$all_option=false, $submit_on_change=false)
{

	// Check if a fixed asset has been bought....
	$where_opts[] = "stock_id IN
    	( SELECT stock_id FROM ".TB_PREF."stock_moves WHERE type=".ST_SUPPRECEIVE." AND qty!=0 )";
	// ...but has not been disposed or sold already.
	$where_opts[] = "stock_id NOT IN
    	( SELECT stock_id FROM ".TB_PREF."stock_moves WHERE (type=".ST_CUSTDELIVERY." OR type=".ST_INVADJUST.") AND qty!=0 )";

	$year = get_current_fiscalyear();
	$begin = date2sql(add_months(sql2date($year['begin']), -1));
	$end = date2sql(add_months(sql2date($year['end']), -1));

	// check if current fiscal year
	$where_opts[] = "depreciation_date <= '".$end."'";
	$where_opts[] = "depreciation_date >= '".$begin."'";

	$where_opts[] = "material_cost > 0";
	$where_opts[] = "mb_flag='".STOCK_TYPE_FIXED_ASSET."'";

	if ($label != null)
		echo "<td>$label</td>\n";
	echo stock_items_list($name, $selected_id, $all_option, $submit_on_change,
		 array('fixed_asset' => true, 'where' => $where_opts, 'cells'=>true));
}

//------------------------------------------------------------------------------------

function tax_types_list($name, $selected_id=null, $none_option=false, $submit_on_change=false)
{
	$sql = "SELECT id, CONCAT(name, ' (',rate,'%)') as name FROM ".TB_PREF."tax_types";

	return combo_input($name, $selected_id, $sql, 'id', 'name',
		array(
			'spec_option' => $none_option,
			'spec_id' => ALL_NUMERIC,
			'select_submit'=> $submit_on_change,
			'async' => false,
		) );
}

function tax_types_list_cells($label, $name, $selected_id=null, $none_option=false,
	$submit_on_change=false)
{
	if ($label != null)
		echo "<td>$label</td>\n";
	echo "<td>";
	echo tax_types_list($name, $selected_id, $none_option, $submit_on_change);
	echo "</td>\n";
}

function tax_types_list_row($label, $name, $selected_id=null, $none_option=false,
	$submit_on_change=false)
{
	echo "<tr><td class='label'>$label</td>";
	tax_types_list_cells(null, $name, $selected_id, $none_option, $submit_on_change);
	echo "</tr>\n";
}

//------------------------------------------------------------------------------------

function tax_groups_list($name, $selected_id=null,
	$none_option=false, $submit_on_change=false)
{
	$sql = "SELECT id, name, inactive FROM ".TB_PREF."tax_groups";

	return combo_input($name, $selected_id, $sql, 'id', 'name',
		array(
			'order' => 'id',
			'spec_option' => $none_option,
			'spec_id' => ALL_NUMERIC,
			'select_submit'=> $submit_on_change,
			'async' => false,
		) );
}

function tax_groups_list_cells($label, $name, $selected_id=null, $none_option=false, $submit_on_change=false)
{
	if ($label != null)
		echo "<td>$label</td>\n";
	echo "<td>";
	echo tax_groups_list($name, $selected_id, $none_option, $submit_on_change);
	echo "</td>\n";
}

function tax_groups_list_row($label, $name, $selected_id=null, $none_option=false, $submit_on_change=false)
{
	echo "<tr><td class='label'>$label</td>";
	tax_groups_list_cells(null, $name, $selected_id, $none_option, $submit_on_change);
	echo "</tr>\n";
}

//------------------------------------------------------------------------------------

function item_tax_types_list($name, $selected_id=null, $show_inactive = false)
{
	$sql ="SELECT id, name FROM ".TB_PREF."item_tax_types";
	if (!$show_inactive)
		$sql .= " WHERE !inactive";

	return combo_input($name, $selected_id, $sql, 'id', 'name', array('order' => 'id') );
}

function item_tax_types_list_cells($label, $name, $selected_id=null, $show_inactive = false)
{
	if ($label != null)
		echo "<td>$label</td>\n";
	echo "<td>";
	echo item_tax_types_list($name, $selected_id, $show_inactive);
	echo "</td>\n";
}

function item_tax_types_list_row($label, $name, $selected_id=null, $show_inactive = false)
{
	echo "<tr><td class='label'>$label</td>";
	item_tax_types_list_cells(null, $name, $selected_id, $show_inactive);
	echo "</tr>\n";
}

function category_groups_list($name, $selected_id = null)
{
	$sql ="SELECT id, `desc` as descr FROM `0_category_groups`";

	return combo_input(
        $name,
        $selected_id,
        $sql,
        'id',
        'desc',
        array(
            'order' => 'descr',
            'spec_option' => '-- Select Category Group --'
        )
    );
}

function category_groups_list_cells($label, $name, $selected_id = null)
{
    if ($label != null) {
		echo "<td>$label</td>\n";
    }
	echo "<td>";
	echo category_groups_list($name, $selected_id);
	echo "</td>\n";
}

function category_groups_list_row($label, $name, $selected_id = null)
{
    echo "<tr><td class='label'>$label</td>";
	category_groups_list_cells(null, $name, $selected_id);
	echo "</tr>\n";
}

//------------------------------------------------------------------------------------

function shippers_list($name, $selected_id=null)
{
	$sql = "SELECT shipper_id, shipper_name, inactive FROM ".TB_PREF."shippers";
	return combo_input($name, $selected_id, $sql, 'shipper_id', 'shipper_name', 
		array('order'=>array('shipper_name')));
}

function shippers_list_cells($label, $name, $selected_id=null)
{
	if ($label != null)
		echo "<td>$label</td>\n";
	echo "<td>";
	echo shippers_list($name, $selected_id);
	echo "</td>\n";
}

function shippers_list_row($label, $name, $selected_id=null)
{
	echo "<tr><td class='label'>$label</td>";
	shippers_list_cells(null, $name, $selected_id);
	echo "</tr>\n";
}

//-------------------------------------------------------------------------------------

function sales_persons_list($name, $selected_id=null, $spec_opt=false, $spec_id=null)
{
	$sql = "SELECT salesman_code, salesman_name, inactive FROM ".TB_PREF."salesman";
	if (!isset($spec_id)) {
		$spec_id = ALL_NUMERIC;
	}

	return combo_input(
		$name,
		$selected_id,
		$sql,
		'salesman_code',
		'salesman_name', 
		array(
			'order' => array('salesman_name'),
			'spec_option' => $spec_opt,
			'spec_id' => $spec_id
		)
	);
}

function sales_persons_list_cells($label, $name, $selected_id=null, $spec_opt=false, $spec_id=null)
{
	if ($label != null)
		echo "<td>$label</td>\n";
	echo "<td>\n";
	echo sales_persons_list($name, $selected_id, $spec_opt, $spec_id);
	echo "</td>\n";
}

function sales_persons_list_row($label, $name, $selected_id=null, $spec_opt=false, $spec_id=null)
{
	echo "<tr><td class='label'>$label</td>";
	sales_persons_list_cells(null, $name, $selected_id, $spec_opt, $spec_id);
	echo "</tr>\n";
}

//------------------------------------------------------------------------------------

function sales_areas_list($name, $selected_id=null, $special_option=false)
{
	$sql = "SELECT area_code, description, inactive FROM ".TB_PREF."areas";
	return combo_input($name, $selected_id, $sql, 'area_code', 'description', 
		array('spec_option' => $special_option===true ? ' ' : $special_option,
		'order' => 'description', 'spec_id' => 0));
}

function sales_areas_list_cells($label, $name, $selected_id=null, $special_option=false)
{
	if ($label != null)
		echo "<td>$label</td>\n";
	echo "<td>";
	echo sales_areas_list($name, $selected_id, $special_option);
	echo "</td>\n";
}

function sales_areas_list_row($label, $name, $selected_id=null, $special_option=false)
{
	echo "<tr><td class='label'>$label</td>";
	sales_areas_list_cells(null, $name, $selected_id, $special_option);
	echo "</tr>\n";
}

//------------------------------------------------------------------------------------

function sales_groups_list($name, $selected_id=null, $special_option=false)
{
	$sql = "SELECT id, description, inactive FROM ".TB_PREF."groups";
	return combo_input($name, $selected_id, $sql, 'id', 'description', array(
		'spec_option' => $special_option===true ? ' ' : $special_option,
		'order' => 'description', 'spec_id' => 0,
	));
}

function sales_groups_list_cells($label, $name, $selected_id=null, $special_option=false)
{
	if ($label != null)
		echo "<td>$label</td>\n";
	echo "<td>";
	echo sales_groups_list($name, $selected_id, $special_option);
	echo "</td>\n";
}

function sales_groups_list_row($label, $name, $selected_id=null, $special_option=false)
{
	echo "<tr><td class='label'>$label</td>";
	sales_groups_list_cells(null, $name, $selected_id, $special_option);
	echo "</tr>\n";
}

//------------------------------------------------------------------------------------

function _format_template_items($row)
{
	return ($row[0] . "&nbsp;- &nbsp;" . trans("Amount") . "&nbsp;".$row[1]);
}

function templates_list($name, $selected_id=null, $special_option=false)
{
	$sql = "SELECT sorder.order_no,	Sum(line.unit_price*line.quantity*(1-line.discount_percent)) AS OrderValue
		FROM ".TB_PREF."sales_orders as sorder, ".TB_PREF."sales_order_details as line
		WHERE sorder.order_no = line.order_no AND sorder.type = 1 GROUP BY line.order_no";
	return combo_input($name, $selected_id, $sql, 'order_no', 'OrderValue', array(
		'format' => '_format_template_items',
		'spec_option' => $special_option===true ? ' ' : $special_option,
		'order' => 'order_no', 'spec_id' => 0,
	));
}

function templates_list_cells($label, $name, $selected_id=null, $special_option=false)
{
	if ($label != null)
		echo "<td>$label</td>\n";
	echo "<td>";
	echo templates_list($name, $selected_id, $special_option);
	echo "</td>\n";
}

function templates_list_row($label, $name, $selected_id=null, $special_option=false)
{
	echo "<tr><td class='label'>$label</td>";
	templates_list_cells(null, $name, $selected_id, $special_option);
	echo "</tr>\n";
}

//------------------------------------------------------------------------------------

function workorders_list($name, $selected_id=null)
{
	$sql = "SELECT id, wo_ref FROM ".TB_PREF."workorders WHERE closed=0";
	return combo_input($name, $selected_id, $sql, 'id', 'wo_ref', array());
}

function workorders_list_cells($label, $name, $selected_id=null)
{
	if ($label != null)
		echo "<td>$label</td>\n";
	echo "<td>";
	echo workorders_list($name, $selected_id);
	echo "</td>\n";
}

function workorders_list_row($label, $name, $selected_id=null)
{
	echo "<tr><td class='label'>$label</td>";
	workorders_list_cells(null, $name, $selected_id);
	echo "</tr>\n";
}

//------------------------------------------------------------------------------------

function payment_terms_list($name, $selected_id=null)
{
	$sql = "SELECT terms_indicator, terms, inactive FROM ".TB_PREF."payment_terms";
	return combo_input($name, $selected_id, $sql, 'terms_indicator', 'terms', array());
}

function payment_terms_list_cells($label, $name, $selected_id=null)
{
	if ($label != null)
		echo "<td>$label</td>\n";
	echo "<td>";
	echo payment_terms_list($name, $selected_id);
	echo "</td>\n";
}

function payment_terms_list_row($label, $name, $selected_id=null)
{
	echo "<tr><td class='label'>$label</td>";
	payment_terms_list_cells(null, $name, $selected_id);
	echo "</tr>\n";
}

//------------------------------------------------------------------------------------

function credit_status_list($name, $selected_id=null)
{
	$sql ="SELECT id, reason_description, inactive FROM ".TB_PREF."credit_status";
	return combo_input($name, $selected_id, $sql, 'id', 'reason_description', array());
}

function credit_status_list_cells($label, $name, $selected_id=null)
{
	if ($label != null)
		echo "<td>$label</td>\n";
	echo "<td>";
	echo credit_status_list($name, $selected_id);
	echo "</td>\n";
}

function credit_status_list_row($label, $name, $selected_id=null)
{
	echo "<tr><td class='label'>$label</td>";
	credit_status_list_cells(null, $name, $selected_id);
	echo "</tr>\n";
}

//-----------------------------------------------------------------------------------------------

function sales_types_list($name, $selected_id=null, $submit_on_change=false, $special_option=false)
{
	$sql = "SELECT id, sales_type, inactive FROM ".TB_PREF."sales_types";

	return combo_input($name, $selected_id, $sql, 'id', 'sales_type',
	array(
		'spec_option' => $special_option===true ? trans("All Sales Types") : $special_option,
		'spec_id' => 0,
		'select_submit'=> $submit_on_change,
	) );
}

function sales_types_list_cells($label, $name, $selected_id=null, $submit_on_change=false, $special_option=false)
{
	if ($label != null)
		echo "<td>$label</td>\n";
	echo "<td>";
	echo sales_types_list($name, $selected_id, $submit_on_change, $special_option);
	echo "</td>\n";
}

function sales_types_list_row($label, $name, $selected_id=null, $submit_on_change=false, $special_option=false)
{
	echo "<tr class='sales-type-list-row'><td class='label'>$label</td>";
	sales_types_list_cells(null, $name, $selected_id, $submit_on_change, $special_option);
	echo "</tr>\n";
}

//-----------------------------------------------------------------------------------------------

function _format_date($row)
{
	return sql2date($row['reconciled']);
}

function bank_reconciliation_list($account, $name, $selected_id=null, $submit_on_change=false, $special_option=false)
{
	$sql = "SELECT reconciled, reconciled FROM ".TB_PREF."bank_trans
		WHERE bank_act=".db_escape($account)." AND reconciled IS NOT NULL
		GROUP BY reconciled";
	return combo_input($name, $selected_id, $sql, 'id', 'reconciled',
		array(
			'spec_option' => $special_option,
			'format' => '_format_date',
			'spec_id' => '',
			'select_submit'=> $submit_on_change
		) );
}


function bk_bank_reconciliation_list($account, $name, $selected_id=null, $submit_on_change=false, $special_option=false)
{
    $sql = "SELECT reconciled, reconciled FROM ".TB_PREF."gl_trans 
		WHERE account=".db_escape($account)." AND reconciled IS NOT NULL
		GROUP BY reconciled";
    return combo_input($name, $selected_id, $sql, 'id', 'reconciled',
        array(
            'spec_option' => $special_option,
            'format' => '_format_date',
            'spec_id' => '',
            'select_submit'=> $submit_on_change
        ) );
}

function bank_reconciliation_list_cells($label,$account, $name, $selected_id=null, $submit_on_change=false, $special_option=false)
{
	if ($label != null)
		echo "<td>$label</td>\n";
	echo "<td>";
	echo bank_reconciliation_list($account, $name, $selected_id, $submit_on_change, $special_option);
	echo "</td>\n";
}


function bk_bank_reconciliation_list_cells($label,$account, $name, $selected_id=null, $submit_on_change=false, $special_option=false)
{
    if ($label != null)
        echo "<td>$label</td>\n";
    echo "<td>";
    echo bk_bank_reconciliation_list($account, $name, $selected_id, $submit_on_change, $special_option);
    echo "</td>\n";
}


/*
function bank_reconciliation_list_row($label, $account, $name, $selected_id=null, $submit_on_change=false, $special_option=false)
{
	echo "<tr>\n";
	bank_reconciliation_list_cells($label, $account, $name, $selected_id, $submit_on_change, $special_option);
	echo "</tr>\n";
}
*/
//-----------------------------------------------------------------------------------------------

function workcenter_list($name, $selected_id=null, $all_option=false)
{

	$sql = "SELECT id, name, inactive FROM ".TB_PREF."workcentres";

	return combo_input($name, $selected_id, $sql, 'id', 'name',
		array(
			'spec_option' =>$all_option===true ? trans("All Suppliers") : $all_option,
			'spec_id' => ALL_TEXT,
		) );
}

function workcenter_list_cells($label, $name, $selected_id=null, $all_option=false)
{
	default_focus($name);
	if ($label != null)
		echo "<td>$label</td>\n";
	echo "<td>";
	echo workcenter_list($name, $selected_id, $all_option);
	echo "</td>\n";
}

function workcenter_list_row($label, $name, $selected_id=null, $all_option=false)
{
	echo "<tr><td class='label'>$label</td>";
	workcenter_list_cells(null, $name, $selected_id, $all_option);
	echo "</tr>\n";
}



//-----------------------------------------------------------------------------------------------

function bank_accounts_list($name, $selected_id=null, $submit_on_change=false, $spec_option=false,$multi=false)
{
	$sql = "SELECT id, bank_account_name, bank_curr_code, inactive
		FROM ".TB_PREF."bank_accounts";

	return combo_input($name, $selected_id, $sql, 'id', 'bank_account_name',
		array(
			'format' => '_format_add_curr',
			'select_submit'=> $submit_on_change,
			'spec_option' => $spec_option,
			'spec_id' => '',
			'async' => false,
			'multi'=>$multi
		) );
}

function bank_accounts_list_cells($label, $name, $selected_id=null, $submit_on_change=false,$special_option = false,$mult=false)
{
	if ($label != null)
		echo "<td>$label</td>\n";
	echo "<td>";
	echo bank_accounts_list($name, $selected_id, $submit_on_change,$special_option,$mult);
	echo "</td>\n";
}

function bank_accounts_list_row($label, $name, $selected_id=null, $submit_on_change=false,$special_option = false,$mult=false)
{
	echo "<tr><td class='label'>$label</td>";
	bank_accounts_list_cells(null, $name, $selected_id, $submit_on_change,$special_option,$mult);
	echo "</tr>\n";
}
//-----------------------------------------------------------------------------------------------

function cash_accounts_list_row($label, $name, $selected_id=null, $submit_on_change=false, $all_option=false)
{

	$sql = "SELECT id, bank_account_name, bank_curr_code, inactive
		FROM ".TB_PREF."bank_accounts
		WHERE account_type=".BT_CASH;

	if ($label != null)
		echo "<tr><td class='label'>$label</td>\n";
	echo "<td>";
	echo combo_input($name, $selected_id, $sql, 'id', 'bank_account_name',
		array(
			'spec_option' => $all_option,
			'spec_id' => ALL_TEXT,
			'format' => '_format_add_curr',
			'select_submit'=> $submit_on_change,
			'async' => true
		) );
	echo "</td></tr>\n";
}


function cash_accounts_list_cell($label, $name, $selected_id=null, $submit_on_change=false, $all_option=false)
{

    $sql = "SELECT * 
		FROM ".TB_PREF."chart_master
		WHERE account_type='1'";


    if ($label != null)
        echo "<tr><td class='label'>$label</td>\n";
    echo "<td>";
    echo combo_input($name, $selected_id, $sql, 'account_code', 'account_name',
        array(
            'spec_option' => $all_option,
            'spec_id' => ALL_TEXT,
            'format' => '_bk_format_add_curr',
            'select_submit'=> $submit_on_change,
            'async' => true
        ) );
    echo "</td></tr>\n";
}


//-----------------------------------------------------------------------------------------------

function pos_list_row($label, $name, $selected_id=null, $spec_option=false, $submit_on_change=false)
{
	$sql = "SELECT id, pos_name, inactive FROM ".TB_PREF."sales_pos";

	default_focus($name);
	echo '<tr>';
	if ($label != null)
		echo "<td class='label'>$label</td>\n";
	echo "<td>";

	echo combo_input($name, $selected_id, $sql, 'id', 'pos_name',
		array(
			'select_submit'=> $submit_on_change,
			'async' => true,
			'spec_option' =>$spec_option,
			'spec_id' => -1,
			'order'=> array('pos_name')
		) );
	echo "</td></tr>\n";

}
//-----------------------------------------------------------------------------------------------
// Payment type selector for current user.
//
function sale_payment_list($name, $category, $selected_id=null, $submit_on_change=true, $prepayments=true, $options=[])
{
	$sql = "SELECT terms_indicator, terms, inactive FROM ".TB_PREF."payment_terms";

	if ($category == PM_CASH) // only cash
			$sql .= " WHERE days_before_due=0 AND day_in_following_month=0";
	elseif ($category == PM_CREDIT) // only delayed payments
			$sql .= " WHERE days_before_due".($prepayments ? '!=': '>')."0 OR day_in_following_month!=0";
	elseif (!$prepayments)
			$sql .= " WHERE days_before_due>=0";

	return combo_input($name, $selected_id, $sql, 'terms_indicator', 'terms',
		array_merge(
			$options,
			array(
				'select_submit'=> $submit_on_change,
				'async' => true
			),
		)
	);
}

function sale_payment_list_cells($label, $name, $category, $selected_id=null, $submit_on_change=true, $prepayments=true, $options=[])
{
	if ($label != null)
		echo "<td class='label'>$label</td>\n";
	echo "<td>";

	echo sale_payment_list($name, $category, $selected_id, $submit_on_change, $prepayments, $options);

	echo "</td>\n";
}

function sale_payment_list_row($label, $name, $category, $selected_id=null, $submit_on_change=false, $prepayments=true, $options=[])
{
	echo "<tr><td class='label'>$label</td>";
	sale_payment_list_cells(null, $name, $category, $selected_id, $submit_on_change, $prepayments, $options);
	echo "</tr>\n";
}

//-----------------------------------------------------------------------------------------------

function class_list($name, $selected_id=null, $submit_on_change=false)
{
	$sql = "SELECT cid, class_name FROM ".TB_PREF."chart_class";

	return combo_input($name, $selected_id, $sql, 'cid', 'class_name',
		array(
			'select_submit'=> $submit_on_change,
			'async' => false
		) );

}

function class_list_cells($label, $name, $selected_id=null, $submit_on_change=false)
{
	if ($label != null)
		echo "<td>$label</td>\n";
	echo "<td>";
	echo class_list($name, $selected_id, $submit_on_change);
	echo "</td>\n";
}

function class_list_row($label, $name, $selected_id=null, $submit_on_change=false)
{
	echo "<tr><td class='label'>$label</td>";
	class_list_cells(null, $name, $selected_id, $submit_on_change);
	echo "</tr>\n";
}

//-----------------------------------------------------------------------------------------------
function stock_categories_list($name, $selected_id=null, $spec_opt=false, $submit_on_change=false, $fixed_asset=false, $multi = false)
{
	$where_opts = array();
	if ($fixed_asset)
		$where_opts[0] = "dflt_mb_flag='".STOCK_TYPE_FIXED_ASSET."'";
	else
		$where_opts[0] = "dflt_mb_flag!='".STOCK_TYPE_FIXED_ASSET."'";

	$sql = "SELECT category_id, `description`, inactive FROM ".TB_PREF."stock_category";
	return combo_input($name, $selected_id, $sql, 'category_id', 'description',
 		array('order'=> 'description',
			'spec_option' => $spec_opt,
			'spec_id' => -1,
 			'select_submit'=> $submit_on_change,
 			'async' => true,
			'where' => $where_opts,
            'multi' => $multi
 		));
}

function stock_categories_list_cells($label, $name, $selected_id=null, $spec_opt=false, $submit_on_change=false, $fixed_asset=false, $multi = false)
{
	if ($label != null)
		echo "<td>$label</td>\n";
	echo "<td>";
	echo stock_categories_list($name, $selected_id, $spec_opt, $submit_on_change, $fixed_asset, $multi);
	echo "</td>\n";
}

function stock_categories_list_row($label, $name, $selected_id=null, $spec_opt=false, $submit_on_change=false, $fixed_asset=false, $multi = false)
{
	echo "<tr><td class='label'>$label</td>";
	stock_categories_list_cells(null, $name, $selected_id, $spec_opt, $submit_on_change, $fixed_asset, $multi);
	echo "</tr>\n";
}

//-----------------------------------------------------------------------------------------------
function fixed_asset_classes_list($name, $selected_id=null, $spec_opt=false, $submit_on_change=false)
{
	$sql = "SELECT c.fa_class_id, CONCAT(c.fa_class_id,' - ',c.description) `desc`, CONCAT(p.fa_class_id,' - ',p.description) `class`, c.inactive FROM "
		.TB_PREF."stock_fa_class c LEFT JOIN ".TB_PREF."stock_fa_class p ON c.parent_id=p.fa_class_id";

	return combo_input($name, $selected_id, $sql, 'c.fa_class_id', 'desc',
		array('order'=>'c.fa_class_id',
			'spec_option' => $spec_opt,
			'spec_id' => '-1',
 			'select_submit'=> $submit_on_change,
 			'async' => true,
			'search_box' => true,
			'search' => array("c.fa_class_id"),
		    'search_submit' => false,
		    'spec_id' => '',
		    'size' => 3,
		    'max' => 3,
			'category' => 'class',
 		));
}

function fixed_asset_classes_list_row($label, $name, $selected_id=null, $spec_opt=false, $submit_on_change=false)
{
	echo "<tr><td class='label'>$label</td>";
	echo "<td>";
	echo fixed_asset_classes_list($name, $selected_id, $spec_opt, $submit_on_change);
	echo "</td></tr>\n";
}

//-----------------------------------------------------------------------------------------------

function gl_account_types_list($name, $selected_id=null, $all_option=false, $all=true)
{

	$sql = "SELECT id, name FROM ".TB_PREF."chart_types";

	return combo_input($name, $selected_id, $sql, 'id', 'name',
		array(
			'format' => '_format_account',
			'order' => array('class_id', 'id', 'parent'),
			'spec_option' =>$all_option,
			'spec_id' => ALL_TEXT
		) );
}

function gl_account_types_list_cells($label, $name, $selected_id=null, $all_option=false, $all=false)
{
	if ($label != null)
		echo "<td>$label</td>\n";
	echo "<td>";
	echo gl_account_types_list($name, $selected_id, $all_option, $all);
	echo "</td>\n";
}

function gl_account_types_list_row($label, $name, $selected_id=null, $all_option=false,	$all=false)
{
	echo "<tr><td class='label'>$label</td>";
	gl_account_types_list_cells(null, $name, $selected_id, $all_option,
		$all);
	echo "</tr>\n";
}

//-----------------------------------------------------------------------------------------------
function gl_all_accounts_list($name, $selected_id=null, $skip_bank_accounts=false,
	$cells=false, $all_option=false, $submit_on_change=false, $all=false, $type_id=false,
	$readonly=false, $skip_tax_accounts=false, $options=[])
{
	$sql = "SELECT chart.account_code, chart.account_name, type.name, chart.inactive, type.id"
		. " FROM 0_chart_master chart"
		. " INNER JOIN 0_chart_types `type` ON `type`.id = chart.account_type";

	if ($skip_bank_accounts)
		$sql .= " LEFT JOIN 0_bank_accounts acc"
			. " ON chart.account_code = acc.account_code";

	if ($skip_tax_accounts)
		$sql .= " LEFT JOIN 0_tax_types tax"
			." ON chart.account_code IN (tax.purchasing_gl_code, tax.sales_gl_code)";

	$sql .= " WHERE 1 = 1";

	if ($skip_bank_accounts)
		$sql .= " AND acc.account_code IS NULL";

	if ($skip_tax_accounts)
		$sql .= " AND tax.id IS NULL";

	if ($type_id ) $sql .= " AND chart.account_type=".db_escape($type_id);
	$account = ($skip_bank_accounts ? "account2" : "account");
	
	$options = array_merge(
		[
			'format' => '_format_account',
			'spec_option' => $all_option===true ?  trans("Use Item Sales Accounts") : $all_option,
			'spec_id' => '',
			'type' => 2,
			'order' => array('type.class_id','type.id','account_code'),
			'search_box' => $cells,
			'search_submit' => false,
			'size' => 12,
			'max' => 10,
			'cells' => true,
			'select_submit'=> $submit_on_change,
			'async' => false,
			'category' => 2,
			'show_inactive' => $all,
			'readonly' => $readonly
		],
		$options
	);

	return combo_input($name, $selected_id, $sql, 'chart.account_code', 'chart.account_name', $options, $account );
}

function _format_account($row)
{
	return $row[0] .  "&nbsp;&nbsp;&nbsp;&nbsp;" . $row[1];
}

function gl_all_accounts_list_cells($label, $name, $selected_id=null, 
	$skip_bank_accounts=false, $cells=false, $all_option=false, 
	$submit_on_change=false, $all=false, $type_id=false,  $readonly=false, $skip_tax_accounts=false, $options=[])
{
	if ($label != null)
		echo "<td>$label</td>\n";
	echo "<td>";
	echo gl_all_accounts_list($name, $selected_id, 
		$skip_bank_accounts, $cells, $all_option, $submit_on_change, $all,
		$type_id, $readonly, $skip_tax_accounts, $options);
	echo "</td>\n";
}

function gl_all_accounts_list_row($label, $name, $selected_id=null, 
	$skip_bank_accounts=false, $cells=false, $all_option=false, $type_id=false, $readonly=false,
	$skip_tax_accounts=false, $options=[])
{
	echo "<tr><td class='label'>$label</td>";
	gl_all_accounts_list_cells(null, $name, $selected_id, 
		$skip_bank_accounts, $cells, $all_option, false, false, $type_id,
		$readonly, $skip_tax_accounts, $options);
	echo "</tr>\n";
}

function yesno_list($name, $selected_id=null, $name_yes="", $name_no="", $submit_on_change=false)
{
	$items = array();
	$items['0'] = strlen($name_no) ? $name_no : trans("No");
	$items['1'] = strlen($name_yes) ? $name_yes : trans("Yes");

	return array_selector($name, $selected_id, $items, 
		array( 
			'select_submit'=> $submit_on_change,
			'async' => false ) ); // FIX?
}

function yesno_list_cells($label, $name, $selected_id=null, $name_yes="", $name_no="", $submit_on_change=false)
{
	if ($label != null)
		echo "<td>$label</td>\n";
	echo "<td>";
	echo yesno_list($name, $selected_id, $name_yes, $name_no, $submit_on_change);
	echo "</td>\n";
}

function yesno_list_row($label, $name, $selected_id=null, $name_yes="", $name_no="", $submit_on_change=false)
{
	echo "<tr><td class='label'>$label</td>";
	yesno_list_cells(null, $name, $selected_id, $name_yes, $name_no, $submit_on_change);
	echo "</tr>\n";
}

//------------------------------------------------------------------------------------------------

function languages_list($name, $selected_id=null, $all_option=false)
{
	global $installed_languages;

	$items = array();
	if ($all_option)
		$items[''] = $all_option;
	foreach ($installed_languages as $lang)
			$items[$lang['code']] = $lang['name'];
	return array_selector($name, $selected_id, $items);
}

function languages_list_cells($label, $name, $selected_id=null, $all_option=false)
{
	if ($label != null)
		echo "<td>$label</td>\n";
	echo "<td>";
	echo languages_list($name, $selected_id, $all_option);
	echo "</td>\n";
}

function languages_list_row($label, $name, $selected_id=null, $all_option=false)
{
	echo "<tr><td class='label'>$label</td>";
	languages_list_cells(null, $name, $selected_id, $all_option);
	echo "</tr>\n";
}

//------------------------------------------------------------------------------------------------

function bank_account_types_list($name, $selected_id=null)
{
	global $bank_account_types;

	return array_selector($name, $selected_id, $bank_account_types);
}

function bank_account_types_list_cells($label, $name, $selected_id=null)
{
	if ($label != null)
		echo "<td>$label</td>\n";
	echo "<td>";
	echo bank_account_types_list($name, $selected_id);
	echo "</td>\n";
}

function bank_account_types_list_row($label, $name, $selected_id=null)
{
	echo "<tr><td class='label'>$label</td>";
	bank_account_types_list_cells(null, $name, $selected_id);
	echo "</tr>\n";
}

//------------------------------------------------------------------------------------------------
function payment_person_types_list($name, $selected_id=null, $submit_on_change=false)
{
	global $payment_person_types;

	$items = array();
	foreach ($payment_person_types as $key=>$type)
	{
		if ($key != PT_WORKORDER)
			$items[$key] = $type;
	}		
	return array_selector($name, $selected_id, $items, 
		array( 'select_submit'=> $submit_on_change ) );
}

function payment_person_types_list_cells($label, $name, $selected_id=null, $related=null)
{
	if ($label != null)
		echo "<td>$label</td>\n";
	echo "<td>";
	echo payment_person_types_list($name, $selected_id, $related);
	echo "</td>\n";
}

function payment_person_types_list_row($label, $name, $selected_id=null, $related=null)
{
	echo "<tr><td class='label'>$label</td>";
	payment_person_types_list_cells(null, $name, $selected_id, $related);
	echo "</tr>\n";
}

//------------------------------------------------------------------------------------------------

function wo_types_list($name, $selected_id=null)
{
	global $wo_types_array;
	
	return array_selector($name, $selected_id, $wo_types_array, 
		array( 'select_submit'=> true, 'async' => true ) );
}

function wo_types_list_row($label, $name, $selected_id=null)
{
	echo "<tr><td class='label'>$label</td><td>\n";
	echo wo_types_list($name, $selected_id);
	echo "</td></tr>\n";
}

//------------------------------------------------------------------------------------------------

function dateformats_list_row($label, $name, $value=null)
{
	global $SysPrefs;

	echo "<tr><td class='label'>$label</td>\n<td>";
	echo array_selector( $name, $value, $SysPrefs->dateformats );
	echo "</td></tr>\n";
}

function dateseps_list_row($label, $name, $value=null)
{
	global $SysPrefs;

	echo "<tr><td class='label'>$label</td>\n<td>";
	echo array_selector( $name, $value, $SysPrefs->dateseps );
	echo "</td></tr>\n";
}

function thoseps_list_row($label, $name, $value=null)
{
	global $SysPrefs;

	echo "<tr><td class='label'>$label</td>\n<td>";
	echo array_selector( $name, $value, $SysPrefs->thoseps );
	echo "</td></tr>\n";
}

function decseps_list_row($label, $name, $value=null)
{
	global $SysPrefs;

	echo "<tr><td class='label'>$label</td>\n<td>";
	echo array_selector( $name, $value, $SysPrefs->decseps );
	echo "</td></tr>\n";
}

function themes_list_row($label, $name, $value=null)
{
	global $path_to_root;

	$path = $path_to_root.'/themes/';
	$themes = array();
	$themedir = opendir($path);
	while(false !== ($fname = readdir($themedir)))
	{
		if($fname!='.' && $fname!='..' && $fname!='CVS' && is_dir($path.$fname))
		{
			$themes[$fname] =  $fname;
		}
	}
	ksort($themes);

	echo "<tr><td class='label'>$label</td>\n<td>";
	echo array_selector( $name, $value, $themes );
	echo "</td></tr>\n";
}

function pagesizes_list_row($label, $name, $value=null)
{
	global $SysPrefs;

	$items = array();
	foreach ($SysPrefs->pagesizes as $pz)
		$items[$pz] = $pz;

	echo "<tr><td class='label'>$label</td>\n<td>";
	echo array_selector( $name, $value, $items );
	echo "</td></tr>\n";
}

function systypes_list($name, $value=null, $spec_opt=false, $submit_on_change=false, $exclude=array(), $options = [])
{
	global $systypes_array;

	// emove non-voidable transactions if needed
	$systypes = array_diff_key($systypes_array, array_flip($exclude));
	return array_selector($name, $value, $systypes, 
		array_merge(
			array( 
				'spec_option'=> $spec_opt,
				'spec_id' => ALL_NUMERIC,
				'select_submit'=> $submit_on_change,
				'async' => false,
			),
			$options
		)
	);
}

function systypes_list_cells($label, $name, $value=null, $submit_on_change=false, $exclude=array(), $spect_opt=false, $options = [])
{
	if ($label != null)
		echo "<td>$label</td>\n";
	echo "<td>";
	echo systypes_list($name, $value, $spect_opt, $submit_on_change, $exclude, $options);
	echo "</td>\n";
}

function systypes_list_row($label, $name, $value=null, $submit_on_change=false, $exclude=array(), $spect_opt=false, $options = [])
{
	echo "<tr><td class='label'>$label</td>";
	systypes_list_cells(null, $name, $value, $submit_on_change, $exclude, $spect_opt, $options);
	echo "</tr>\n";
}

function journal_types_list_cells($label, $name, $value=null, $submit_on_change=false)
{
	global $systypes_array;

	if ($label != null)
		echo "<td>$label</td>\n";
	echo "<td>";

	$items = $systypes_array;

	// exclude quotes, orders and dimensions
	foreach (array(ST_SALESINVOICE,ST_PURCHORDER, ST_SALESORDER, ST_DIMENSION, ST_SALESQUOTE, ST_LOCTRANSFER) as $excl)
			unset($items[$excl]);
	
	echo array_selector($name, $value, $items, 
		array( 
			'spec_option'=> trans("All"),
			'spec_id' => -1,
			'select_submit'=> $submit_on_change,
			'async' => false
			)
	);
	echo "</td>\n";
}

function cust_allocations_list_cells($label, $name, $selected=null, $submit_on_change=false,$style='')
{

	if ($label != null)
		label_cell($label);

	echo "<td $style class='transaction_type_select'>\n";
	$allocs = array( 
		ALL_TEXT=>trans("All Types"),
		'1'=> trans("Sales Invoices"),
		'2'=> trans("Unsettled transactions"),
		'3' => trans("Payments"),
		'4' => trans("Credit Notes"),
		'5' => trans("Delivery Notes")
	);
	echo array_selector($name, $selected, $allocs, array('select_submit'=> $submit_on_change));
	echo "</td>\n";
}

function reward_type_list_cells($label, $name, $selected=null, $submit_on_change=false,$style='') {
    if ($label != null)
        label_cell($label);

    echo "<td $style class='transaction_type_select'>\n";
    $allocs = array(
        ALL_TEXT=>trans("All Types"),
        '1'=> trans("Earned"),
        '2'=> trans("Redeemed")
    );
    echo array_selector($name, $selected, $allocs, array('select_submit'=> $submit_on_change));
    echo "</td>\n";
}

function supp_allocations_list_cell($name, $selected=null)
{

	echo "<td>\n";
	$allocs = array( 
		ALL_TEXT=>trans("All Types"),
		'1'=> trans("Invoices"),
		'2'=> trans("Overdue Invoices"),
		'3' => trans("Payments"),
		'4' => trans("Credit Notes"),
		'5' => trans("Overdue Credit Notes")
	);
	echo array_selector($name, $selected, $allocs);
	echo "</td>\n";
}

function supp_transactions_list_cell($name, $selected=null, $submit_on_change=false)
{

	echo "<td>\n";
	$allocs = array( 
		ALL_TEXT=>trans("All Types"),
		'6'=>trans("GRNs"),
		'1'=> trans("Invoices"),
		'2'=> trans("Unsettled transactions"),
		'3' => trans("Payments"),
		'4' => trans("Credit Notes"),
		'5' => trans("Overdue Credit Notes")
	);

	echo array_selector($name, $selected, $allocs, array('select_submit'=> $submit_on_change));
	echo "</td>\n";
}

function policy_list_cells($label, $name, $selected=null)
{
	if ($label != null)
		label_cell($label);
	echo "<td>\n";
	echo array_selector($name, $selected, 
				array( '' => trans("Automatically put balance on back order"),
			 		'CAN' => trans("Cancel any quantites not delivered")) );
	echo "</td>\n";
}

function policy_list_row($label, $name, $selected=null)
{
	echo "<tr><td class='label'>$label</td>";
	policy_list_cells(null, $name, $selected);
	echo "</tr>\n";
}

function credit_type_list_cells($label, $name, $selected=null, $submit_on_change=false)
{
	if ($label != null)
		label_cell($label);
	echo "<td>\n";
	echo array_selector($name, $selected, 
			$GLOBALS['sales_credit_types'],
			array( 'select_submit'=> $submit_on_change ) );
	echo "</td>\n";
}

function credit_type_list_row($label, $name, $selected=null, $submit_on_change=false)
{
	echo "<tr><td class='label'>$label</td>";
	credit_type_list_cells(null, $name, $selected, $submit_on_change);
	echo "</tr>\n";
}

function number_list($name, $selected, $from, $to, $no_option=false)
{
	$items = array();
	for ($i = $from; $i <= $to; $i++)
		$items[$i] = "$i";

	return array_selector($name, $selected, $items,
			array(	'spec_option' => $no_option,
					'spec_id' => ALL_NUMERIC) );
}

function number_list_cells($label, $name, $selected, $from, $to, $no_option=false)
{
	if ($label != null)
		label_cell($label);
	echo "<td>\n";
	echo number_list($name, $selected, $from, $to, $no_option);
	echo "</td>\n";
}

function number_list_row($label, $name, $selected, $from, $to, $no_option=false)
{
	echo "<tr><td class='label'>$label</td>";
	echo number_list_cells(null, $name, $selected, $from, $to, $no_option);
	echo "</tr>\n";
}

function print_profiles_list_row($label, $name, $selected_id=null, $spec_opt=false,
	$submit_on_change=true)
{
	$sql = "SELECT profile FROM ".TB_PREF."print_profiles"
		." GROUP BY profile";
	$result = db_query($sql, 'cannot get all profile names');
	$profiles = array();
	while($myrow=db_fetch($result)) {
		$profiles[$myrow['profile']] = $myrow['profile'];
	}

	echo "<tr>";
	if ($label != null)
		echo "<td class='label'>$label</td>\n";
	echo "<td>";

	echo array_selector($name, $selected_id, $profiles, 
		array( 'select_submit'=> $submit_on_change,
			'spec_option'=>$spec_opt,
			'spec_id' => ''
		 ));

	echo "</td></tr>\n";
}

function printers_list($name, $selected_id=null, $spec_opt=false, $submit_on_change=false)
{
	static $printers; // query only once for page display

	if (!$printers) {
		$sql = "SELECT id, name, description FROM ".TB_PREF."printers";	
		$result = db_query($sql, 'cannot get all printers');
		$printers = array();
		while($myrow=db_fetch($result)) {
			$printers[$myrow['id']] = $myrow['name'].'&nbsp;-&nbsp;'.$myrow['description'];
		}
	}
	return array_selector($name, $selected_id, $printers, 
		array( 'select_submit'=> $submit_on_change,
			'spec_option'=>$spec_opt,
			'spec_id' => ''
		 ));
}

//------------------------------------------------------------------------------------------------

function quick_entries_list($name, $selected_id=null, $type=null, $submit_on_change=false)
{
	$where = false;
	$sql = "SELECT id, description FROM ".TB_PREF."quick_entries";
	if ($type != null)
		$sql .= " WHERE type=$type";

	return combo_input($name, $selected_id, $sql, 'id', 'description',
		array(
			'spec_id' => '',
			'order' => 'description',
			'select_submit'=> $submit_on_change,
			'async' => false
		) );

}

function quick_entries_list_cells($label, $name, $selected_id, $type, $submit_on_change=false)
{
	if ($label != null)
		echo "<td>$label</td>\n";
	echo "<td>";	
	echo quick_entries_list($name, $selected_id, $type, $submit_on_change);
	echo "</td>";
}

function quick_entries_list_row($label, $name, $selected_id, $type, $submit_on_change=false)
{
	echo "<tr><td class='label'>$label</td>";
	quick_entries_list_cells(null, $name, $selected_id, $type, $submit_on_change);
	echo "</tr>\n";
}


function quick_actions_list_row($label, $name, $selected_id=null, $submit_on_change=false)
{
	global $quick_actions;
	
	echo "<tr><td class='label'>$label</td><td>";
	echo array_selector($name, $selected_id, $quick_actions, 
		array( 
			'select_submit'=> $submit_on_change
		) );
	echo "</td></tr>\n";
}

function quick_entry_types_list_row($label, $name, $selected_id=null, $submit_on_change=false)
{
	global $quick_entry_types;
		
	echo "<tr><td class='label'>$label</td><td>";
	echo array_selector($name, $selected_id, $quick_entry_types, 
		array( 
			'select_submit'=> $submit_on_change
			) );
	echo "</td></tr>\n";
}

function record_status_list_row($label, $name) {
	return yesno_list_row($label, $name, null, 	trans('Inactive'), trans('Active'));
}

function class_types_list_row($label, $name, $selected_id=null, $submit_on_change=false)
{
	global $class_types;
	
	echo "<tr><td class='label'>$label</td><td>";
	echo array_selector($name, $selected_id, $class_types, 
		array( 
			'select_submit'=> $submit_on_change
		) );
	echo "</td></tr>\n";
}

//------------------------------------------------------------------------------------------------

function security_roles_list(
    $name,
    $selected_id=null,
    $new_item=false,
    $submit_on_change=false,
	$show_inactive = false,
    $exclude_higher_roles = false,
    $multi = false
) {

	$sql = "SELECT id, role, inactive FROM ".TB_PREF."security_roles";

    if ($exclude_higher_roles) {
        $authUserRole = get_security_role($_SESSION['wa_current_user']->access);
        $sql .= " WHERE (level < '{$authUserRole['level']}' OR id = '{$authUserRole['id']}')";
    }

	return combo_input($name, $selected_id, $sql, 'id', 'description',
		array(
			'spec_option'=>$new_item ? trans("New role") : false,
			'spec_id' => '',
			'select_submit'=> $submit_on_change,
			'show_inactive' => $show_inactive,
            'multi' => $multi
		) );
}

function security_roles_list_cells(
    $label,
    $name,
    $selected_id=null,
    $new_item=false,
    $submit_on_change=false,
	$show_inactive = false,
    $exclude_higher_roles = false,
    $multi = false
) {
	if ($label != null)
		echo "<td>$label</td>\n";
	echo "<td>";
	echo security_roles_list($name, $selected_id, $new_item, $submit_on_change, $show_inactive, $exclude_higher_roles, $multi);
	echo "</td>\n";
}

function security_roles_list_row(
    $label,
    $name,
    $selected_id=null,
    $new_item=false,
    $submit_on_change=false,
	$show_inactive = false,
    $exclude_higher_roles = false,
    $multi = false
) {
	echo "<tr><td class='label'>$label</td>";
	security_roles_list_cells(null, $name, $selected_id, $new_item, $submit_on_change, $show_inactive, $exclude_higher_roles, $multi);
	echo "</tr>\n";
}

function tab_list_row($label, $name, $selected_id=null)
{
	global $installed_extensions;
	
	$tabs = array();
	foreach ($_SESSION['App']->applications as $app) {
		$tabs[$app->id] = access_string($app->name, true);
	}
	echo "<tr>\n";
	echo "<td class='label'>$label</td><td>\n";
	echo array_selector($name, $selected_id, $tabs);
	echo "</td></tr>\n";
}

//-----------------------------------------------------------------------------------------------

function tag_list($name, $height, $type, $multi=false, $all=false, $spec_opt = false)
{
	// Get tags
	global $path_to_root;
	include_once($path_to_root . "/admin/db/tags_db.inc");
	$results = get_tags($type, $all);

	while ($tag = db_fetch($results))
		$tags[$tag['id']] = $tag['name'];
	
	if (!isset($tags)) {
		$tags[''] = $all ? trans("No tags defined.") : trans("No active tags defined.");
		$spec_opt = false;
	}
	return array_selector($name, null, $tags,
		array(
			'multi' => $multi,
			'height' => $height,
			'spec_option'=> $spec_opt,
			'spec_id' => -1,
		) );
}

function tag_list_cells($label, $name, $height, $type, $mult=false, $all=false, $spec_opt = false)
{
	if ($label != null)
		echo "<td>$label</td>\n";
	echo "<td>\n";
	echo tag_list($name, $height, $type, $mult, $all, $spec_opt);
	echo "</td>\n";
	
}

function tag_list_row($label, $name, $height, $type, $mult=false, $all=false, $spec_opt = false)
{
	echo "<tr><td class='label'>$label</td>";
	tag_list_cells(null, $name, $height, $type, $mult, $all, $spec_opt);
	echo "</tr>\n";	
}

//---------------------------------------------------------------------------------------------
//	List of sets of active extensions 
//
function extset_list($name, $value=null, $submit_on_change=false)
{
	global $db_connections;

	$items = array();
	foreach ($db_connections as $comp)
		$items[] = sprintf(trans("Activated for '%s'"), $comp['name']);
	return array_selector( $name, $value, $items,
		array(
			'spec_option'=> trans("Available and/or installed"),
			'spec_id' => -1,
			'select_submit'=> $submit_on_change,
			'async' => true
		));
}

function crm_category_types_list($name, $selected_id=null, $filter=array(), $submit_on_change=true)
{

	$sql = "SELECT id, name, type, inactive FROM ".TB_PREF."crm_categories";

	$multi = false;
	$groups = false;
	$where = array();
	if (@$filter['class']) {
		$where[] = 'type='.db_escape($filter['class']);
	} else
		$groups = 'type';
	if (@$filter['subclass']) $where[] = 'action='.db_escape($filter['subclass']);
	if (@$filter['entity']) $where[] = 'entity_id='.db_escape($filter['entity']);
	if (@$filter['multi']) { // contact category selector for person
		$multi = true;
	}

	return combo_input($name, $selected_id, $sql, 'id', 'name',
	 	array(
	 		'multi' => $multi,
	 		'height' => $multi ? 5:1,
			'category' => $groups,
	 		'select_submit'=> $submit_on_change,
 			'async' => true,
 			'where' => $where
	 	));
}

function crm_category_types_list_row($label, $name, $selected_id=null, $filter=array(), $submit_on_change=true)
{
	echo "<tr><td class='label'>$label</td><td>";
	echo crm_category_types_list($name, $selected_id, $filter, $submit_on_change);
	echo "</td></tr>\n";
}

function payment_type_list_row($label, $name, $selected_id=null, $submit_on_change=false)
{
	global $pterm_types;
	
	echo "<tr><td class='label'>$label</td><td>";
	echo array_selector($name, $selected_id, $pterm_types, 
		array( 
			'select_submit'=> $submit_on_change
		) );
	echo "</td></tr>\n";
}

function coa_list_row($label, $name, $value=null)
{
	global $path_to_root, $installed_extensions;

	$path = $path_to_root.'/sql/';
	$coas = array();
	$sqldir = opendir($path);
	while (false !== ($fname = readdir($sqldir)))
	{
		if (is_file($path.$fname) && substr($fname,-4)=='.sql' && @($fname[2] == '_'))
		{
			$ext = array_search_value($fname, $installed_extensions, 'sql');
			if ($ext!=null) {
				$descr = $ext['name'];
			} elseif ($fname == 'en_US-new.sql') { // two standard COAs
				$descr = trans("Standard new company American COA (4 digit)");
			} elseif ($fname == 'en_US-demo.sql') {
				$descr = trans("Standard American COA (4 digit) with demo data");
			} else
				$descr = $fname;

			$coas[$fname] =  $descr;
		}
	}
	ksort($coas);

	echo "<tr><td class='label'>$label</td>\n<td>";
	echo array_selector( $name, $value, $coas );
	echo "</td></tr>\n";
}

function payment_services($name)
{
	global $payment_services;

	$services = (object)array_combine(array_keys($payment_services), array_keys($payment_services));

	return array_selector($name, null, $services, array(
			'spec_option'=> trans("No payment Link"),
			'spec_id' => '',
		));
}

function tax_algorithm_list($name, $value=null, $submit_on_change = false)
{
	global $tax_algorithms;
	
	return array_selector($name, $value, $tax_algorithms, 
		array( 
			'select_submit'=> $submit_on_change,
			'async' => true,
			)
	);
}

function tax_algorithm_list_cells($label, $name, $value=null, $submit_on_change=false)
{
	if ($label != null)
		echo "<td>$label</td>\n";
	echo "<td>";
	echo tax_algorithm_list($name, $value, $submit_on_change);
	echo "</td>\n";
}

function tax_algorithm_list_row($label, $name, $value=null, $submit_on_change=false)
{
	echo "<tr><td class='label'>$label</td>";
	tax_algorithm_list_cells(null, $name, $value, $submit_on_change);
	echo "</tr>\n";
}

function refline_list($name, $type, $value=null, $spec_option=false)
{
	$sql = "SELECT id, prefix, inactive FROM ".TB_PREF."reflines";

	$where = array();

	if (isset($type))
		$where = array('`trans_type`='.db_escape($type));

	return combo_input($name, $value, $sql, 'id', 'prefix',
		array(
			'order'=>array('prefix'),
			'spec_option' => $spec_option,
			'spec_id' => '',
			'type' => 2,
			'where' => $where,
			'select_submit' => true,
			)
		);
}

function refline_list_row($label, $name, $type, $selected_id=null, $spec_option=false)
{
	echo "<tr>";
	if ($label != null)
		echo "<td class='label'>$label</td>\n";
	echo "<td>";

	echo refline_list($name, $type, $selected_id, $spec_option);
	echo "</td></tr>\n";
}


//----------------------------------------------------------------------------------------------



function axispro_subledger_list($name, $account, $selected_id=null)
{

    if(empty($account)) $account = 0;

    $sql = "select code,CONCAT(code,' - ',name) as sub_led_name,ledger_id from 0_sub_ledgers where ledger_id = $account";

    return combo_input($name, $selected_id, $sql, 'code', 'sub_led_name',
        array(
            'type' => 1,
            'size' => 20,
            'async' => false,
            'spec_option' => 'Select',
        ) );
}




function esb_subledger_list($name, $account, $selected_id=null)
{

    if(empty($account)) $account = 0;

    $sql = "select code,CONCAT(code,' - ',name) as sub_led_name,ledger_id from 0_sub_ledgers where ledger_id = $account";

    return combo_input($name, $selected_id, $sql, 'code', 'sub_led_name',
        array(
            'type' => 1,
            'size' => 20,
            'async' => false,
            'spec_option' => 'Select',
        ) );
}



function axispro_subledger_list_pay_elemnts($name, $account, $selected_id=null)
{

    if(empty($account)) $account = 0;

    $sql = "select code,CONCAT(code,' - ',name) as sub_led_name,ledger_id from 0_sub_ledgers where ledger_id = $account";

    return combo_input($name, $selected_id, $sql, 'code', 'sub_led_name',
        array(
            'type' => 1,
            'size' => 20,
            'async' => false,
            'spec_option' => 'Select',
        ) );
}

function axispro_subledger_list_cells($label, $name, $account, $selected_id=null)
{
    if ($label != null)
        echo "<td>$label</td>\n";
    echo "<td nowrap>";
    echo axispro_subledger_list($name, $account, $selected_id);
    echo "</td>\n";
}


function axispro_esb_list_cells($label, $name, $account, $selected_id=null)
{
    if ($label != null)
        echo "<tr><td>$label</td>\n";
    echo "<td nowrap>";
    echo esb_subledger_list($name, $account, $selected_id);
    echo "</td></tr>";
}




function axispro_subledger_list_cells_pay_elements($label, $name, $account, $selected_id=null)
{
    if ($label != null)
        echo "<tr><td>$label</td>\n";
    echo "<td nowrap>";
    echo axispro_subledger_list_pay_elemnts($name, $account, $selected_id);
    echo "</td></tr>";
}




function subledger_list($name, $account, $selected_id = null, $special_option = false,$submit_on_change = false)
{
	global $Ajax, $js_lib;

	if (!($sl_type = is_subledger_account($account)))
		return '';

	$type = get_subledger_person_type($sl_type);

	if ($type == PT_CUSTOMER) {
		$placeholder = $special_option === true ? trans("All Customers") : $special_option;
		$selected_customer = isset($selected_id) 
			? $selected_id
			: (!empty($_POST[$name]) 
				? $_POST[$name] 
				: ($placeholder ? -1 : null)
			);
		
		if (!is_array($selected_customer)) {
			$selected_customer = [$selected_customer];
		}

		$customer_query = Customer::active()
			->whereIn('debtor_no', $selected_customer)
			->when($sl_type == SLT_ACCOUNTS_REC, function ($query) use ($account) {
				$query->whereHas('branches', function($query) use ($account) {
					$query->where('receivables_account', $account);
				});
			});
		
		if (
			$sl_type == SLT_ACCOUNTS_REC
			&& (empty($selected_customer) || !$customer_query->exists())
		) {
			$selected_customer = [$customer_query->value('debtor_no')];
		}
		
		$selected_customer = implode(",", array_map('db_escape', $selected_customer));
		$selector = 'select[name="' . $name .'"]';
		$select2Options = json_encode([
			'placeholder' => $placeholder ?: false,
			'allowClear' => (bool)$placeholder,
			'whereAccount' => $account
		]);

		$js = "\nsetTimeout(() => initializeCustomersSelect2('{$selector}', $select2Options));";
		$Ajax->isActive() ? $Ajax->addScript(true, $js) : ($js_lib[] = $js);

		if($sl_type == SLT_ACCOUNTS_REC){
			$sel = " ,  ".TB_PREF."cust_branch c";
			$cond = " AND d.debtor_no=c.debtor_no AND c.receivables_account=".db_escape($account)."";
		}
		$sql = "SELECT DISTINCT d.debtor_no as id, CONCAT(d.debtor_ref, ' - ', d.`name`) as name"
		." FROM"
			." ".TB_PREF."debtors_master d,"
			." ".TB_PREF."cust_branch c"
		." WHERE d.debtor_no=c.debtor_no"
			." AND NOT d.inactive"
			." AND d.debtor_no in ({$selected_customer})";

		if ($sl_type == SLT_ACCOUNTS_REC) {
			$sql .= " AND c.receivables_account=".db_escape($account);
		}
	}
	
	else if ($type == PT_SUPPLIER) {
		$sql = (
			"SELECT
				supplier_id as id,
				CONCAT(supp_ref, ' - ', supp_name) as name
			FROM ".TB_PREF."suppliers s
			WHERE NOT s.inactive"
		);
		
		if ($sl_type == SLT_ACCOUNTS_PAY) {
			$sql .= " AND s.payable_account=".db_escape($account);
		}
    }
	
	else if ($type == PT_EMPLOYEE) {
	    $sql = "SELECT id, CONCAT(emp_ref, ' - ', name) as name FROM 0_employees WHERE status = 1";
    }
	
	else if ($type == PT_USER) {
	    $sql = "SELECT id, CONCAT(user_id, ' - ', real_name) as name FROM 0_users WHERE inactive = 0";
    }
	
    else if ($type == PT_SALESMAN) {
        $sql = "SELECT salesman_code as id, salesman_name as name FROM 0_salesman WHERE 1=1";
    }

	else if ($type == PT_SUBLEDGER) {
	    $sql = "SELECT code as id, CONCAT(code, ' - ', name) as name FROM 0_sub_ledgers WHERE ledger_id = ".db_escape($account);
    }

	$mode = get_company_pref('no_customer_list');

	return combo_input(
        $name,
        $selected_id,
        $sql,
        'id',
        'name',
	    array(
		    'type' => 1,
		    'size' => 20,
		    'async' => false,
            'spec_option' => $special_option,
			'select_submit' => $submit_on_change,
			'select2' => $type != PT_CUSTOMER
	    )
    );
}

function subledger_list_cells($label, $name, $account, $selected_id = null, $special_option = false)
{
	if ($label != null)
		echo "<td>$label</td>\n";
	echo "<td nowrap>";
	echo subledger_list($name, $account, $selected_id, $special_option);
	echo "</td>\n";
}

function subledger_list_row($label, $name, $account, $selected_id=null, $all_option = false, 
	$submit_on_change=false, $show_inactive=false, $editkey = false)
{
	echo "<tr><td class='label'>$label</td><td nowrap>";
	echo subledger_list($name, $account, $selected_id);
	echo "</td>\n</tr>\n";
}

function accounts_type_list_row($label, $name, $selected_id=null)
{
	echo "<tr>";
	if ($label != null)
		echo "<td class='label'>$label</td>\n";
	echo "<td>";
	$sel = array(trans("Numeric"), trans("Alpha Numeric"), trans("ALPHA NUMERIC"));
	echo array_selector($name, $selected_id, $sel); 
	echo "</td></tr>\n";
}

function users_list_cells($label, $name, $selected_id=null, $submit_on_change=false, $spec_opt=true, $where = null, $departments = null)
{
	$_where = '';
    if(!empty($where)) {
		foreach ($where as $col => $value) { 
			$_where .= " AND {$col} = " . db_escape($value);  
		}
	}

    if (!empty($departments)) {
        if (is_array($departments)) {
            $departments = implode(",", $departments);
        }

        $_where .= " AND dflt_dimension_id IN ($departments)";
    }

    $sql = " SELECT user_id, CONCAT(real_name, ' (', user_id, ')') real_name FROM 0_users WHERE 1=1 {$_where}";

    if ($label != null)
        echo "<td>$label</td>\n";
    echo "<td>";

    echo combo_input($name, $selected_id, $sql, 'user_id', 'real_name',
        array(
            'spec_option' => $spec_opt===true ?trans("All users") : $spec_opt,
            'spec_id' => '',
            'order' => 'real_name',
            'select_submit'=> $submit_on_change,
            'async' => false
        ) );
    echo "</td>";

}

/**
 * Generate the users list cells
 * 
 * The users_list_cells list is based on the 'user_id' where as
 * the users_list_cells2 is based on the 'id' and
 * there is also option to provide default value for the first option
 */
function users_list_cells2($label, $name, $selected_id=null, $submit_on_change=false, $spec_opt=true, $where = null, $spec_id = null)
{
	$_where = '';
    if(!empty($where)) {
		foreach ($where as $col => $value) { 
			$_where .= " AND {$col} = " . db_escape($value);  
		}
	}
    $sql = " SELECT id AS user_id, CONCAT(real_name, ' (', user_id, ')') real_name FROM 0_users WHERE 1=1 {$_where}";

    if ($label != null)
        echo "<td>$label</td>\n";
    echo "<td>";

    echo combo_input($name, $selected_id, $sql, 'user_id', 'real_name',
        array(
            'spec_option' => $spec_opt === true ? trans("All users") : $spec_opt,
            'spec_id' => $spec_id === null ? ALL_TEXT : $spec_id,
            'order' => 'real_name',
            'select_submit'=> $submit_on_change,
            'async' => false
        ) );
    echo "</td>";
}

/**
 * Generate the users list row: The row alternative for users_list_cells2
 */
function users_list_rows2($label, $name, $selected_id=null, $submit_on_change=false, $spec_opt=true, $where = null, $spec_id = null) {
    echo "<tr>";
    if ($label != null) {
		echo "<td class='label'>$label</td>\n";
    }
    users_list_cells2(null, $name, $selected_id, $submit_on_change, $spec_opt, $where, $spec_id);
    echo "</tr>";
}

/**
 * Generate the employees list cells
 */
function employees_list_cells(
	$label,
	$name,
	$selected_id=null,
	$submit_on_change=false,
	$spec_opt=true,
	$where = null,
	$spec_id = null,
	$include_inactive = false,
	$skip_users = false
)
{
	global $Ajax, $js_lib;

	if ($selected_id == null) {
		$selected_id = get_post($name);
	}

	$conditions = '1 = 1';

	if (!$include_inactive) {
		$conditions .= " AND emp.`status` = " . ES_ACTIVE;
	}

	foreach (($where ?? []) as $col => $value) { 
		$conditions .= " AND emp.`{$col}` = " . db_escape($value);  
	}

	if ($skip_users) {
		$conditions .= " AND ("
			. ($selected_id ? "emp.id = ".db_escape($selected_id)." OR " : "")
			. "usr.id IS NULL"
		. ")";
	}

    $sql = (
		"SELECT
			emp.id,
			CONCAT(emp.emp_ref, ' - ', emp.`name`) formatted_name
		FROM `0_employees` as emp
		LEFT JOIN `0_users` as usr ON
			emp.id = usr.employee_id
			AND usr.`type` = ".UT_EMPLOYEE."
		WHERE {$conditions}"
	);

    if ($label != null)
        echo "<td>$label</td>\n";
    echo "<td>";

    echo combo_input($name, $selected_id, $sql, 'id', 'formatted_name',
        array(
            'spec_option' => $spec_opt === true ? trans("-- select --") : $spec_opt,
            'spec_id' => $spec_id === null ? ALL_TEXT : $spec_id,
            'order' => 'CAST(emp_ref AS UNSIGNED)',
            'select_submit'=> $submit_on_change,
            'async' => false
        ) );
    echo "</td>";
}

/**
 * Generate the employees list row
 */
function employees_list_rows($label, $name, $selected_id=null, $submit_on_change=false, $spec_opt=true, $where = null, $spec_id = null, $include_inactive = false, $skip_users = false) {
    echo "<tr>";
    if ($label != null) {
		echo "<td class='label'>$label</td>\n";
    }
    employees_list_cells(null, $name, $selected_id, $submit_on_change, $spec_opt, $where, $spec_id, $include_inactive, $skip_users);
    echo "</tr>";
}

function collations_list_row($label, $name, $selected_id=null)
{
	global $supported_collations;

	echo "<tr>";
	if ($label != null)
		echo "<td class='label'>$label</td>\n";
	echo "<td>";

	echo array_selector($name, $selected_id, $supported_collations, 
		array('select_submit'=> false) );
	echo "</td></tr>\n";
}
