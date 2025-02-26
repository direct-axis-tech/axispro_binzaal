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
//
// Sets local POST value and adds Value to ajax posting if needed
//
/*function set_post($name, $value, $ajax_trigger=true) {
    global $Ajax;

    $_POST[$name] = $value;
    if ($ajax_trigger) $Ajax->activate($name);
}
*/
//------------------------------------------------------------------------------
//    Seek for _POST variable with $prefix.
//    If var is found returns variable name with prefix stripped,
//    and null or -1 otherwise.
//
function find_submit($prefix, $numeric=true)
{

    foreach($_POST as $postkey=>$postval )
    {
		if (strpos($postkey, $prefix) === 0)
		{
			$id = substr($postkey, strlen($prefix));
			return $numeric ? (int)$id : $id;
		}
    }
    return $numeric ? -1 : null;
}
/*
	Helper function.
	Returns true if input $name with $submit_on_change option set is subject to update.
*/
function input_changed($name)
{
	return isset($_POST['_'.$name.'_changed']);
}

//------------------------------------------------------------------------------
//
// Helper function for simple db table editor pages
//
function simple_page_mode($numeric_id = true)
{
	global $Ajax, $Mode, $selected_id;

	$default = $numeric_id ? -1 : '';
	$selected_id = get_post('selected_id', $default);
	foreach (array('ADD_ITEM', 'UPDATE_ITEM', 'RESET', 'CLONE') as $m) {
		if (isset($_POST[$m])) {
			$Ajax->activate('_page_body');
			if ($m == 'RESET'  || $m == 'CLONE') 
				$selected_id = $default;
			unset($_POST['_focus']);
			$Mode = $m; return;
		}
	}
	foreach (array('Edit', 'Delete') as $m) {
		foreach ($_POST as $p => $pvar) {
			if (strpos($p, $m) === 0) {
//				$selected_id = strtr(substr($p, strlen($m)), array('%2E'=>'.'));
				unset($_POST['_focus']); // focus on first form entry
				$selected_id = quoted_printable_decode(substr($p, strlen($m)));
				$Ajax->activate('_page_body');
				$Mode = $m;
				return;
			}
		}
	}
	$Mode = '';
}

//------------------------------------------------------------------------------
//
//	Read numeric value from user formatted input
//
function input_num($postname=null, $dflt=0)
{
	if (!isset($_POST[$postname]) || $_POST[$postname] == "")
	  	return $dflt;

    return user_numeric($_POST[$postname]);
}

//---------------------------------------------------------------------------------
//
//	Thanks to hidden fields buffering hidden() helper can be used in arbitrary places and 
//  proper html structure is still preserved. Buffered hidden fields are output on the nearest 
//  table or form closing tag (see output_hidden()).
//
$hidden_fields = array();

function hidden($name, $value=null, $echo=true)
{
  	global $Ajax, $hidden_fields;
	
	if ($value === null) 
		$value = get_post($name);
	
  	$ret = "<input type=\"hidden\" name=\"$name\" value=\"$value\">";
	$Ajax->addUpdate($name, $name, $value);
	if ($echo)
			$hidden_fields[] = $ret;
	else
		return $ret;
}
/*
	Universal submit form button.
	$atype - type of submit:
	 Normal submit:
		false - normal button; optional icon
		null  - button visible only in fallback mode; optional icon
	 Ajax submit:
		true	  - standard button; optional icon

		'default' - default form submit on Ctrl-Enter press; dflt ICON_OK icon
		'selector' - ditto with closing current popup editor window
		'cancel'  - cancel form entry on Escape press; dflt ICON_CANCEL
		'process' - displays progress bar during call; optional icon
		'nonajax' - ditto, non-ajax submit

	$atype can contain also multiply type selectors separated by space, 
	however make sense only combination of 'process' and one of defualt/selector/cancel
*/
function submit($name, $value, $echo=true, $title=false, $atype=false, $icon=false, $ui_class='')
{
	global $path_to_root;

	$aspect='';
	if ($atype === null) {
		$aspect = fallback_mode() ? " aspect='fallback'" : " style='display:none;'";

	} elseif (!is_bool($atype)) { // necessary: switch uses '=='

		$aspect = " aspect='$atype' ";
		$types = explode(' ', $atype);

		foreach ($types as $type) {
			switch($type) {
				case 'selector':
					$aspect = " aspect='selector' rel = '$value'"; 
					$value = trans("Select");
					if ($icon===false) $icon=ICON_SUBMIT; break;

				case 'default':
					if ($icon===false) $icon=ICON_SUBMIT; break;

				case 'cancel':
					if ($icon===false) $icon=ICON_ESCAPE; break;

				case 'nonajax':
				case 'download':
					$atype = false;
			}
		}
	}
	
	$is_cancel_btn = strpos($value, 'Cancel') !== false || in_array('cancel', $atype ? explode(' ', $atype) : []);
	$class_list = class_names([
		'btn',
		'btn-success' => !$is_cancel_btn,
		'bg-gray-600' => $is_cancel_btn,
		'shadow' => $is_cancel_btn,
		'text-light' => $is_cancel_btn,
		'ajaxsubmit' => !empty($atype),
		'inputsubmit' => empty($atype),
		$ui_class
	]);

	$submit_str = "<button class=\"$class_list\""
		." type=\"submit\""
		.$aspect
	    ." name=\"$name\"  id=\"$name\" value=\"$value\""
	    .($title ? " title='$title'" : '')
	    .">"
		.($icon ? "<img src='$path_to_root/themes/".user_theme()."/images/$icon' height='12' alt=''>" : '')
		."<span>&nbsp;$value</span>"
		."</button>\n";
	if ($echo)
		echo $submit_str;
	else
		return $submit_str;
}

function submit_center($name, $value, $echo=true, $title=false, $async=false, $icon=false, $ui_class='')
{
	if ($echo) echo "<center>";
	submit($name, $value, $echo, $title, $async, $icon, $ui_class);
	if ($echo) echo "</center>";
}

function submit_center_first($name, $value, $title=false, $async=false, $icon=false, $ui_class='')
{
	echo "<center>";
	submit($name, $value, true, $title, $async, $icon, $ui_class);
	echo "&nbsp;";
}

function submit_center_last($name, $value, $title=false, $async=false, $icon=false, $ui_class='')
{
	echo "&nbsp;";
	submit($name, $value, true, $title, $async, $icon, $ui_class);
	echo "</center>";
}
/*
	For following controls:
	'both' - use both Ctrl-Enter and Escape hotkeys 
	'upgrade' - use Ctrl-Enter with progress ajax indicator and Escape hotkeys. Nonajax request for OK option is performed.
	'cancel' - apply to 'RESET' button
*/
function submit_add_or_update($add=true, $title=false, $async=false, $clone=false)
{
	$cancel = $async;

	if ($async === 'both') {
		$async = 'default'; $cancel = 'cancel';
	}
	elseif ($async === 'upgrade') {
		$async = 'default nonajax process'; $cancel = 'cancel';
	}
	elseif ($async === 'default')
		$cancel = true;
	elseif ($async === 'cancel')
		$async = true;
	
	if ($add)
		submit('ADD_ITEM', trans("Add new"), true, $title, $async);
	else {
		submit('UPDATE_ITEM', trans("Update"), true, trans('Submit changes'), $async);
		if ($clone) submit('CLONE', trans("Clone"), true,
			trans('Edit new record with current data'), $async);
		submit('RESET', trans("Cancel"), true, trans('Cancel edition'), $cancel);
	}
}

function submit_add_or_update_center($add=true, $title=false, $async=false, $clone=false)
{
	echo "<center>";
	submit_add_or_update($add, $title, $async, $clone);
	echo "</center>";
}

function submit_add_or_update_row($add=true, $right=true, $extra="", $title=false, $async=false, $clone = false)
{
	echo "<tr>";
	if ($right)
		echo "<td>&nbsp;</td>\n";
	echo "<td $extra>";
	submit_add_or_update($add, $title, $async, $clone);
	echo "</td></tr>\n";
}

function submit_cells($name, $value, $extra="", $title=false, $async=false, $ui_class="")
{
	echo "<td $extra>";
	submit($name, $value, true, $title, $async, false, $ui_class);
	echo "</td>\n";
}

function collapse_control($label, $target_id, $is_collapsed)
{
	if ($is_collapsed) {
		$area_expanded = 'false';
		$class = 'collapsed';
	} else {
		$area_expanded = "true";
		$class = '';
	}

	echo (
		"<button 
			class=\"btn btn-primary {$class}\" 
			type=\"button\" 
			data-bs-toggle=\"collapse\" 
			data-bs-target=\"#{$target_id}\"
			aria-expanded=\"{$area_expanded}\"
			aria-controls=\"{$target_id}\">
			{$label}
		</button>"
	);
}

function start_collapsible_div($id, $is_collapsed)
{
	$class = $is_collapsed ? '' : 'show';
	echo "<div class=\"collapse {$class}\" id=\"{$id}\">";
}

function end_collapsible_div()
{
	echo "</div>";
}

function submit_row($name, $value, $right=true, $extra="", $title=false, $async=false)
{
	echo "<tr>";
	if ($right)
		echo "<td>&nbsp;</td>\n";
	submit_cells($name, $value, $extra, $title, $async);
	echo "</tr>\n";
}

function submit_return($name, $value, $title=false)
{
	if (@$_REQUEST['popup']) {
		submit($name, $value, true, $title, 'selector');
	}
}

function submit_js_confirm($name, $msg, $set = true) {
	global $Ajax;
	$js = "_validate.$name=".($set ? "function(){ return confirm('"
				. strtr($msg, array("\n"=>'\\n')) . "');};"
				: 'null;');
	if (in_ajax()) {
		$Ajax->addScript(true, $js);
	} else
		add_js_source($js);
}
//-----------------------------------------------------------------------------------

function set_icon($icon, $title=false)
{
	global $path_to_root;
	if (basename($icon) === $icon) // standard icons does not contain path separator
		$icon = "$path_to_root/themes/".user_theme()."/images/$icon";
	return "<img src='$icon' style='vertical-align:middle;width:12px;height:12px;border:0;'".($title ? " title='$title'" : "")." >\n";	
}

function button($name, $value, $title=false, $icon=false,  $aspect='')
{
	// php silently changes dots,spaces,'[' and characters 128-159
	// to underscore in POST names, to maintain compatibility with register_globals
	$rel = '';
	if ($aspect == 'selector') {
		$rel = " rel='$value'";
		$value = trans("Select");
	}
	if (user_graphic_links() && $icon)
	{
		if ($value == trans("Delete")) // Helper during implementation
			$icon = ICON_DELETE;
		return "<button type='submit' class='editbutton' name='"
			.html_specials_encode(strtr($name, array('.'=>'=2E', '='=>'=3D',// ' '=>'=20','['=>'=5B'
			)))
			."' value='1'" . ($title ? " title='$title'":" title='$value'")
			. ($aspect ? " aspect='$aspect'" : '')
			. $rel
			." >".set_icon($icon)."</button>\n";
	}
	else
		return "<input type='submit' class='editbutton' name='"
			.htmlentities(strtr($name, array('.'=>'=2E', '='=>'=3D',// ' '=>'=20','['=>'=5B'
			)))
			."' value='$value'"
			.($title ? " title='$title'":'')
			. ($aspect ? " aspect='$aspect'" : '')
			. $rel
			." >\n";
}

function button_cell($name, $value, $title=false, $icon=false, $aspect='')
{
	echo "<td align='center'>";
	echo button($name, $value, $title, $icon, $aspect);
	echo "</td>";
}

function delete_button_cell($name, $value, $title=false)
{
	button_cell($name, $value, $title, ICON_DELETE);
}

function edit_button_cell($name, $value, $title=false)
{
	button_cell($name, $value, $title, ICON_EDIT);
}

function select_button_cell($name, $value, $title=false)
{
	button_cell($name, $value, $title, ICON_ADD, 'selector');
}
//-----------------------------------------------------------------------------------

function check_value($name)
{
	if (is_array($name)) {
		$ret = array();
		foreach($name as $key)
			$ret[$key] = check_value($key);
		return $ret;
	} else
		return (empty($_POST[$name]) ? 0 : 1);
}

function checkbox($label, $name, $value=null, $submit_on_change=false, $title=false, $class='')
{
  	global $Ajax;

	$str = '';	

	if ($label)
		$str .= $label . "  ";
	if ($submit_on_change !== false) {
		if ($submit_on_change === true)
			$submit_on_change = 
				"JsHttpRequest.request(\"_{$name}_update\", this.form);";
	}
	if ($value === null)
		$value = get_post($name,0);

	$class = class_names($class);

	$str .= "<input"
	    .($value == 1 ? ' checked':'')
	    ." type='checkbox' name='$name' value='1'"
	    .($submit_on_change ? " onclick='$submit_on_change'" : '')
	    .($title ? " title='$title'" : '')
	    .($class ? " class='$class'" : '')
	    ." >\n";

	$Ajax->addUpdate($name, $name, $value);
	return $str;
}

function check($label, $name, $value=null, $submit_on_change=false, $title=false, $class='')
{
	echo checkbox($label, $name, $value, $submit_on_change, $title, $class);
}

function check_cells($label, $name, $value=null, $submit_on_change=false, $title=false,
	$params='', $inputClass='')
{
	if ($label != null)
		echo "<td>$label</td>\n";
	echo "<td $params>";
	echo check(null, $name, $value, $submit_on_change, $title, $inputClass);
	echo "</td>";
}

function check_row($label, $name, $value=null, $submit_on_change=false, $title=false, $inputClass='')
{
	echo "<tr><td class='label'>$label</td>";
	echo check_cells(NULL, $name, $value, $submit_on_change, $title, '', $inputClass);
	echo "</tr>\n";
}

//-----------------------------------------------------------------------------------
function radio($label, $name, $value, $selected=null, $submit_on_change=false)
{
	if (!isset($selected))
		$selected = get_post($name) === (string)$value;

	if ($submit_on_change === true)
		$submit_on_change = 
			"JsHttpRequest.request(\"_{$name}_update\", this.form);";

	return "<input type='radio' name=$name value='$value' ".($selected ? "checked":'')
	    .($submit_on_change ? " onclick='$submit_on_change'" : '')
		.">".($label ? $label : '');
}

//-----------------------------------------------------------------------------------
function labelheader_cell($label, $params="")
{
	echo "<td class='tableheader' $params>$label</td>\n";
}

function label_cell($label, $params="", $id=null)
{
    global $Ajax;

	if(isset($id))
	{
	    $params .= " id='$id'";
	    $Ajax->addUpdate($id, $id, $label);
	}
	echo "<td $params>$label</td>\n";

	return $label;
}

function email_cell($label, $params="", $id=null)
{
	label_cell("<a href='mailto:$label'>$label</a>", $params, $id);
}

function amount_decimal_cell($label, $params="", $id=null)
{
	$dec = 0;
	label_cell(price_decimal_format($label, $dec), "nowrap align=right ".$params, $id);
}

function amount_cell($label, $bold=false, $params="", $id=null)
{
	if ($bold)
		label_cell("<b>".price_format($label)."</b>", "nowrap align=right ".$params, $id);
	else
		label_cell(price_format($label), "nowrap align=right ".$params, $id);
}

//JAM  Allow entered unit prices to be fractional
function unit_amount_cell($label, $bold=false, $params="", $id=null)
{
	if ($bold)
		label_cell("<b>".unit_price_format($label)."</b>", "nowrap align=right ".$params, $id);
	else
		label_cell(unit_price_format($label), "nowrap align=right ".$params, $id);
}


function percent_cell($label, $bold=false, $id=null)
{
	if ($bold)
		label_cell("<b>".percent_format($label)."</b>", "nowrap align=right", $id);
	else
		label_cell(percent_format($label), "nowrap align=right", $id);
}
// 2008-06-15. Changed
function qty_cell($label, $bold=false, $dec=null, $id=null)
{
	if (!isset($dec))
		$dec = get_qty_dec();
	if ($bold)
		label_cell("<b>".number_format2($label, $dec)."</b>", "nowrap align=right", $id);
	else
		label_cell(number_format2($label, $dec), "nowrap align=right", $id);
}

function label_cells($label, $value, $params="", $params2="", $id=null)
{
	if ($label != null)
		echo "<td $params>$label</td>\n";
	label_cell($value, $params2, $id);
}

function label_row($label, $value, $params="", $params2="", $leftfill=0, $id=null)
{
	echo "<tr class='customer-credit-row'>";
	if ($params == "")
	{
		echo "<td class='label'>$label</td>";
		$label = null;
	}	
	label_cells($label, $value, $params, $params2, $id);
	if ($leftfill!=0)
	  	echo "<td colspan=$leftfill></td>";
	echo "</tr>\n";
}

function text_input($name, $value=null, $size='', $max='', $title='', $params='', $readonly = false)
{
	if ($value === null)
		$value = get_post($name);

	$readonly = $readonly ? ' readonly' : '';
	$title = $title ? " title='$title'" : '';

	return "<input $params type=\"text\" name=\"$name\" size=\"$size\" maxlength=\"$max\" value=\"$value\""
		. $title . $readonly
		.">";
}

//-----------------------------------------------------------------------------------

function text_cells($label, $name, $value=null, $size="", $max="", $title=false, 
	$labparams="", $post_label="", $inparams="", $readonly = false)
{
  	global $Ajax;

	default_focus($name);
	if ($label != null)
		label_cell($label, $labparams);
	echo "<td>";

	echo text_input($name, $value, $size, $max, $title, $inparams, $readonly);

	if ($post_label != "")
		echo " " . $post_label;

	echo "</td>\n";
	$Ajax->addUpdate($name, $name, $value);
}

function text_cells_ex($label, $name, $size, $max=null, $init=null, $title=null,
	$labparams=null, $post_label=null, $submit_on_change=false, $readonly=false)
{
  	global $Ajax;

	default_focus($name);
	if (!isset($_POST[$name]) || $_POST[$name] == "")
	{
		if ($init)
			$_POST[$name] = $init;
		else
			$_POST[$name] = "";
	}
	if ($label != null)
		label_cell($label, $labparams);

	if (!isset($max))
		$max = $size;

	echo "<td>";
	$class = $submit_on_change ? 'class="searchbox"' : '';
	echo "<input $class type=\"text\" name=\"$name\" size=\"$size\" maxlength=\"$max\" value=\"{$_POST[$name]}\""
	 .($title ? " title='$title'": '')
	 .($readonly ? ' readonly' : '')." >";

	if ($post_label)
		echo " " . $post_label;

	echo "</td>\n";
	$Ajax->addUpdate($name, $name, $_POST[$name]);
}

function text_row($label, $name, $value, $size, $max, $title=null, $params="", $post_label="",$label_extra_class = "", $readonly = false)
{
	echo "<tr><td class='label $label_extra_class'>$label</td>";
	text_cells(null, $name, $value, $size, $max, $title, $params, $post_label, "", $readonly);

	echo "</tr>\n";
}

//-----------------------------------------------------------------------------------

function text_row_ex($label, $name, $size, $max=null, $title=null, $value=null, $params=null, $post_label=null, $submit_on_change = false, $readonly = false)
{
	echo "<tr><td class='label'>$label</td>";
	text_cells_ex(null, $name, $size, $max, $value, $title, $params, $post_label, $submit_on_change, $readonly);

	echo "</tr>\n";
}

//-----------------------------------------------------------------------------------
function email_row($label, $name, $value, $size, $max, $title=null, $params="", $post_label="")
{
	if (get_post($name)) 
		$label = "<a href='Mailto:".$_POST[$name]."'>$label</a>";
	text_row($label, $name, $value, $size, $max, $title, $params, $post_label);
}

function email_row_ex($label, $name, $size, $max=null, $title=null, $value=null, $params=null, $post_label=null)
{
	if (get_post($name)) 
		$label = "<a href='Mailto:".$_POST[$name]."'>$label</a>";
	text_row_ex($label, $name, $size, $max, $title, $value, $params, $post_label);
}

function link_row($label, $name, $value, $size, $max, $title=null, $params="", $post_label="")
{
	$val = get_post($name);
	if ($val) {
		if (strpos($val,'http://')===false)
			$val = 'http://'.$val;
		$label = "<a href='$val' target='_blank'>$label</a>";
	}
	text_row($label, $name, $value, $size, $max, $title, $params, $post_label);
}

function link_row_ex($label, $name, $size, $max=null, $title=null, $value=null, $params=null, $post_label=null)
{
	$val = get_post($name);
	if ($val) {
		if (strpos($val,'http://')===false)
			$val = 'http://'.$val;
		$label = "<a href='$val' target='_blank'>$label</a>";
	}
	text_row_ex($label, $name, $size, $max, $title, $value, $params, $post_label);
}

//-----------------------------------------------------------------------------------
//
//	Since FA 2.2  $init parameter is superseded by $check. 
//  When $check!=null current date is displayed in red when set to other 
//	than current date.
//	
function date_cells($label, $name, $title = null, $check=null, $inc_days=0, 
	$inc_months=0, $inc_years=0, $params=null, $submit_on_change=false)
{
	global $path_to_root, $Ajax;

//    display_error(print_r($_POST[$name] ,true)); die;

	if (!isset($_POST[$name]) || $_POST[$name] == "")
	{
		if ($inc_years == 1001)
			$_POST[$name] = null;
		else
		{
			$dd = Today();
			if ($inc_days != 0)
				$dd = add_days($dd, $inc_days);
			if ($inc_months != 0)
				$dd = add_months($dd, $inc_months);
			if ($inc_years != 0)
				$dd = add_years($dd, $inc_years);
			$_POST[$name] = $dd;
		}
	}
	if (user_use_date_picker())
	{
		$calc_image = (file_exists("$path_to_root/themes/".user_theme()."/images/cal.gif")) ? 
			"$path_to_root/themes/".user_theme()."/images/cal.gif" : "$path_to_root/themes/default/images/cal.gif";
		$post_label = "<a tabindex='-1' alt='$name' class='clsClander' href=\"javascript:date_picker(document.getElementsByName('$name')[0]);\">"
		. "	<img src='$calc_image' style='vertical-align:middle;padding-bottom:4px;width:16px;height:16px;border:0;' alt='".trans('Click Here to Pick up the date')."'></a>\n";
	}	
	else
		$post_label = "";

	if ($label != null)
		label_cell($label, $params);

	echo "<td>";
	
	$class = $submit_on_change ? 'date active' : 'date';

	$aspect = $check ? 'aspect="cdate"' : '';
	if ($check && (get_post($name) != Today()))
		$aspect .= ' style="color:#FF0000"';

	default_focus($name);
	$size = (user_date_format()>3)?11:10; 
	echo "<input type=\"text\" name=\"$name\" class=\"$class\" $aspect size=\"$size\" maxlength=\"12\" value=\"" 
	 . $_POST[$name]. "\""
	 .($title ? " title='$title'": '')." > $post_label";
	echo "</td>\n";



	$Ajax->addUpdate($name, $name, $_POST[$name]);
}

function date_row($label, $name, $title=null, $check=null, $inc_days=0, $inc_months=0, 
	$inc_years=0, $params=null, $submit_on_change=false)
{
	echo "<tr><td class='label'>$label</td>";
	date_cells(null, $name, $title, $check, $inc_days, $inc_months, 
		$inc_years, $params, $submit_on_change);
	echo "</tr>\n";
}

//-----------------------------------------------------------------------------------
function date_time_cells($label, $name, $title = null, $params=null)
{
	global $Ajax, $js_lib;

    $init = '';

    if (!isset($_POST[$name])) {
        $_POST[$name] = $init;
    }

	if ($label != null)
		label_cell($label, $params);

	echo "<td>";

    $fmt = getDateFormatForMomentJs() . " hh:mm A";
    $js = "\n$('input[name=\"$name\"]').daterangepicker({timePicker: true, singleDatePicker: true, showDropdowns: true, locale: {format: '$fmt', cancelLabel: 'Clear'}, autoUpdateInput: false});"
        . "\n$('input[name=\"$name\"]').on('apply.daterangepicker', applyDateTimeOnDateRangePicker)"
        . "\n$('input[name=\"$name\"]').on('cancel.daterangepicker', clearDateRangePickerValue)";

    $js_lib[] = "\nfunction applyDateTimeOnDateRangePicker(e, p) { $(this).val(p.startDate.format('$fmt')); };";
    $js_lib[] = "\nfunction clearDateRangePickerValue() { $(this).val(''); };";
    $Ajax->isActive() ? $Ajax->addScript(true, $js) : ($js_lib[] = "$js");

	default_focus($name);
	echo "<input type=\"text\" name=\"$name\" size=\"25\" value=\"{$_POST[$name]}\"".($title ? " title='$title'": '')." >";
	echo "</td>\n";

	$Ajax->addUpdate($name, $name, $_POST[$name]);
}

//-----------------------------------------------------------------------------------

function date_time_row($label, $name, $title=null, $params=null)
{
	echo "<tr><td class='label'>$label</td>";
	date_time_cells(null, $name, $title, $params);
	echo "</tr>\n";
}

//-----------------------------------------------------------------------------------
function password_row($label, $name, $value, $params='')
{
	echo "<tr><td class='label'>$label</td>";
	label_cell("<input type='password' name='$name' size=20 maxlength=20 value='$value' $params>");
	echo "</tr>\n";
}	

//-----------------------------------------------------------------------------------
function file_cells($label, $name, $id="")
{
	if ($id != "")
		$id = "id='$id'";
	label_cells($label, "<input type='file' name='$name' $id >");
}		
function file_row($label, $name, $id = "")
{
	echo "<tr><td class='label'>$label</td>";
	file_cells(null, $name, $id);
	echo "</tr>\n";
}	

/*-----------------------------------------------------------------------------------

 Reference number input.

 Optional  $context array contains transaction data used in number parsing:
 	'data' - data used for month/year codes
	'location' - location code
 	'customer' - debtor_no
 	'supplier' - supplier id
 	'branch' - branch_code
*/
function ref_cells($label, $name, $title=null, $init=null, $params=null, $submit_on_change=false, $type=null, $context=null, $readonly=false)
{
	global $Ajax, $Refs;

	if (isset($type)) {
		if (empty($_POST[$name.'_list'])) // restore refline id
			$_POST[$name.'_list'] = $Refs->reflines->find_refline_id(empty($_POST[$name]) ? $init : $_POST[$name], $type);

		if (empty($_POST[$name])) // initialization
		{
			if (isset($init))
			{
				$_POST[$name] = $init;
			} else {
				$_POST[$name] = $Refs->get_next($type, $_POST[$name.'_list'], $context);
			}
			$Ajax->addUpdate(true, $name, $_POST[$name]);
		}

		if (check_ui_refresh($name)) { // call context changed
			$_POST[$name] = $Refs->normalize($_POST[$name], $type, $context, $_POST[$name.'_list']);
			$Ajax->addUpdate(true, $name, $_POST[$name]);
		}

		if ($Refs->reflines->count($type)>1) {
			if (list_updated($name.'_list')) {
				$_POST[$name] = $Refs->get_next($type, $_POST[$name.'_list'], $context);
				$Ajax->addUpdate(true, $name, $_POST[$name]);
                $Ajax->addUpdate(true, $name."_backup", $_POST[$name]);
			}
			$list = refline_list($name.'_list', $type);
		} else {
			$list = '';
		}

		if (isset($label))
			label_cell($label, $params);

        hidden($name."_backup", $_POST[$name]);

		label_cell($list."<input name='".$name."' "
			.(check_edit_access($name) ? '' : 'disabled ')
			.($readonly ? 'readonly ' : '')
			."value='".@$_POST[$name]."' size=16 maxlength=35>");
	}
	else // just wildcard ref field (e.g. for global inquires)
	{
		text_cells_ex($label, $name, 16, 35, $init, $title, $params, null, $submit_on_change, $readonly);
	}
}

//-----------------------------------------------------------------------------------

function ref_row($label, $name, $title=null, $init=null, $submit_on_change=false, $type=null, $context = null, $readonly=false)
{
	echo "<tr><td class='label'>$label</td>";
	ref_cells(null, $name, $title, $init, null, $submit_on_change, $type, $context, $readonly);
	echo "</tr>\n";
}

//-----------------------------------------------------------------------------------

function percent_row($label, $name, $init=null)
{

	if (!isset($_POST[$name]) || $_POST[$name]=="")
	{
		$_POST[$name] = $init == null ? '' : $init;
	}

	small_amount_row($label, $name, $_POST[$name], null, "%", user_percent_dec());
}

function amount_cells_ex($label, $name, $size, $max=null, $init=null, $params=null, $post_label=null, $dec=null, $readonly=false, $submit_on_change=false)
{
	global $Ajax;

	if (!isset($dec))
	  	$dec = user_price_dec();
	if (!isset($_POST[$name]) || $_POST[$name] == "")
	{
		if ($init !== null)
			$_POST[$name] = $init;
		else
			$_POST[$name] = '';
	}
	if ($label != null)
	{
		if ($params == null)
			$params = "class='label'";
		label_cell($label, $params);
	}
	if (!isset($max))
		$max = $size;

	$classes = class_names(['amount', 'searchbox' => $submit_on_change]);

	if ($label != null)
		echo "<td>";
	else
		echo "<td align='right'>";

	echo "<input class='{$classes}' type=\"text\" name=\"$name\" size=\"$size\" maxlength=\"$max\" dec=\"$dec\" value=\"" . $_POST[$name]. '"'. ($readonly ? ' readonly' : '') .">";

	if ($post_label) {
		echo "<span id='_{$name}_label'> $post_label</span>";
		$Ajax->addUpdate($name, '_'.$name.'_label', $post_label);
	}
	echo "</td>\n";
	$Ajax->addUpdate($name, $name, $_POST[$name]);
	$Ajax->addAssign($name, $name, 'dec', $dec);
}


//-----------------------------------------------------------------------------------

function amount_cells($label, $name, $init=null, $params=null, $post_label=null, $dec=null, $readonly=false, $submit_on_change=false)
{
	amount_cells_ex($label, $name, 15, 15, $init, $params, $post_label, $dec, $readonly, $submit_on_change);
}

//JAM  Allow entered unit prices to be fractional
function unit_amount_cells($label, $name, $init=null, $params=null, $post_label=null, $dec=null)
{
	if (!isset($dec))
	  	$dec = user_price_dec()+2;

	amount_cells_ex($label, $name, 15, 15, $init, $params, $post_label, $dec+2);
}

function amount_row($label, $name, $init=null, $params=null, $post_label=null, $dec=null, $readonly=false, $submit_on_change=false)
{
	echo "<tr>";
	amount_cells($label, $name, $init, $params, $post_label, $dec, $readonly, $submit_on_change);
	echo "</tr>\n";
}

function small_amount_row($label, $name, $init=null, $params=null, $post_label=null, $dec=null, $readonly = false)
{
	echo "<tr>";
	small_amount_cells($label, $name, $init, $params, $post_label, $dec, $readonly);
	echo "</tr>\n";
}

//-----------------------------------------------------------------------------------

function qty_cells($label, $name, $init=null, $params=null, $post_label=null, $dec=null, $readonly=false, $submit_on_change=false)
{
	if (!isset($dec))
	  	$dec = user_qty_dec();

//	amount_cells_ex($label, $name, 10, 15, $init, $params, $post_label, $dec);
	amount_cells_ex($label, $name, 5, 15, $init, $params, $post_label, $dec, $readonly, $submit_on_change);
}

function qty_row($label, $name, $init=null, $params=null, $post_label=null, $dec=null)
{
	if (!isset($dec))
	  	$dec = user_qty_dec();

	echo "<tr>";
	amount_cells($label, $name, $init, $params, $post_label, $dec);
	echo "</tr>\n";
}

function small_qty_row($label, $name, $init=null, $params=null, $post_label=null, $dec=null)
{
	if (!isset($dec))
	  	$dec = user_qty_dec();

	echo "<tr>";
	small_amount_cells($label, $name, $init, $params, $post_label, $dec);
	echo "</tr>\n";
}

//-----------------------------------------------------------------------------------

function small_amount_cells($label, $name, $init=null, $params=null, $post_label=null, $dec=null, $readonly = false)
{
	amount_cells_ex($label, $name, 7, 12, $init, $params, $post_label, $dec, $readonly);
}

//-----------------------------------------------------------------------------------

function small_qty_cells($label, $name, $init=null, $params=null, $post_label=null, $dec=null)
{
  	if (!isset($dec))
	  	$dec = user_qty_dec();
	amount_cells_ex($label, $name, 7, 12, $init, $params, $post_label, $dec);
}

//-----------------------------------------------------------------------------------

function textarea_cells($label, $name, $value, $cols, $rows, $max=255, $title = null, $params="")
{
  	global $Ajax;

	default_focus($name);
	if ($label != null)
		echo "<td $params>$label</td>\n";
	if ($value == null)
		$value = (!isset($_POST[$name]) ? "" : $_POST[$name]);
	echo "<td><textarea name='$name' cols='$cols' rows='$rows'"
	.($title ? " title='$title'" : '')
	.($max ? " maxlength=$max" : '')
	.">$value</textarea></td>\n";
	$Ajax->addUpdate($name, $name, $value);
}

function textarea_row($label, $name, $value, $cols, $rows, $max=255, $title=null, $params="")
{
	echo "<tr><td class='label'>$label</td>";
	textarea_cells(null, $name, $value, $cols, $rows, $max, $title, $params);
	echo "</tr>\n";
}

//-----------------------------------------------------------------------------------
//
//	When show_inactive page option is set 
//  displays value of inactive field as checkbox cell.
//  Also updates database record after status change.
//
function inactive_control_cell($id, $value, $table, $key)
{
	global	$Ajax;

	$name = "Inactive". $id;
	$value = $value ? 1:0;

	if (check_value('show_inactive')) {
		if (isset($_POST['LInact'][$id]) && (get_post('_Inactive'.$id.'_update') || 
			get_post('Update')) && (check_value('Inactive'.$id) != $value)) {
			update_record_status($id, !$value, $table, $key);

			$value = (int)!$value;
			$Ajax->activate($name);
		}
		echo '<td align="center">'. checkbox(null, $name, $value, true, '')
 			. hidden("LInact[$id]", $value, false) . '</td>';	
	}
}
//
//	Displays controls for optional display of inactive records
//
function inactive_control_row($th) {
	echo  "<tr><td colspan=".(count($th)).">"
		."<div style='float:left;'>"
		. checkbox(null, 'show_inactive', null, true). trans("Show also Inactive")
		."</div><div style='float:right;'>"
		. submit('Update', trans('Update'), false, '', null)
		."</div></td></tr>";
}
//
//	Inserts additional column header when display of inactive records is on.
//
function inactive_control_column(&$th) {
	global $Ajax;
	
	if (check_value('show_inactive')) 
		array_insert($th, count($th)-2 , trans("Inactive"));
	if (get_post('_show_inactive_update')) {
		$Ajax->activate('_page_body');
	}
}

function customer_credit_row($customer, $credit, $parms='')
{
	global $path_to_root;
	
	label_row( trans("Current Credit:"),
		"<a target='_blank' " . ($credit<0 ? 'class="redfg"' : '')
		."href='$path_to_root/sales/inquiry/customer_inquiry.php?customer_id=".$customer."'"
		." onclick=\"javascript:openWindow(this.href,this.target); return false;\" >"
		. price_format($credit)
		."</a>", $parms);
}

function supplier_credit_row($supplier, $credit, $parms='')
{
	global $path_to_root;
	
	label_row( trans("Current Credit:"),
		"<a target='_blank' " . ($credit<0 ? 'class="redfg"' : '')
		."href='$path_to_root/purchasing/inquiry/supplier_inquiry.php?supplier_id=".$supplier."'"
		." onclick=\"javascript:openWindow(this.href,this.target); return false;\" >"
		. price_format($credit)
		."</a>", $parms);
}

function bank_balance_row($bank_acc, $parms='', $date='')
{
	global $path_to_root;

	$to = add_days($date ?: Today(), 1);
	$bal = get_balance_before_for_bank_account($bank_acc, $to);
	label_row( trans("Bank Balance:"),
		"<a target='_blank' " . ($bal<0 ? 'class="redfg"' : '')
		."href='$path_to_root/gl/inquiry/bank_inquiry.php?bank_account=".$bank_acc."'"
		." onclick=\"javascript:openWindow(this.href,this.target); return false;\" >&nbsp;"
		. price_format($bal)
		."</a>", $parms, "", 0, '__bank_balance_row');
}

function ahref($label, $href, $target="", $onclick="") {
  echo "<a href='$href' target='$target' onclick='$onclick'>$label</a>";
}

function ahref_cell($label, $href, $target="", $onclick="") {
  echo "<td align='center'>&nbsp;&nbsp;";
  ahref($label, $href, $target, $onclick);
  echo "&nbsp;&nbsp;</td>";
}

function editable_select_cells($label = "", $name, $value = null, $options = null, $input_size = 25, $label_attribs = null, $readonly = false,
    $submit_on_select = false)
{
    global $Ajax, $js_lib;

	if ($options == null || !is_array($options)){
		$options = [];
	}

    if (is_null($value)) {
        $value = get_post($name);
    }

	if(!empty($label)){
		echo "<td {$label_attribs}>{$label}</td>";
	}

	$disabled = $readonly ? ' disabled' : '';
    $comp_list = "<select"
        . " name=\"_{$name}_comp_list\""
        . " onchange=\"_handleChangeEditableSelect('{$name}')\"{$disabled}>";
	$comp_list .= '<option value=""></option>';
	foreach ($options as $key => $text) {
		$comp_list .= "<option value=\"{$key}\" " . ($key == get_post("_{$name}_comp_list") ? "selected" : "") . ">{$text}</option>";
	}
	$comp_list .= "</select>";

	echo "<td>";
	echo "<div class=\"select-editable\">";

    $Ajax->addUpdate(true, "_{$name}_comp_list_wrapper", $comp_list);
    echo "<span id=\"_{$name}_comp_list_wrapper\">{$comp_list}</span>";

	$readonly = $readonly ? ' readonly' : '';
	echo "<input type=\"text\" name=\"{$name}\" value=\"{$value}\" size=\"{$input_size}\"{$readonly}>&nbsp;";

    if ($submit_on_select != false) {
        echo sprintf(SELECT_BUTTON, $disabled, user_theme(), (fallback_mode() ? '' : 'display:none;'), "__{$name}_comp_list_update");
    }

	echo "</div>";

	echo "</td>";
    $Ajax->addUpdate($name, $name, $value);
    $js_lib[] = "\n function _handleChangeEditableSelect(n) {\n"
        .   "   let _n = `_\${n}_comp_list`;\n"
        .   "   let el = document.getElementsByName(_n)[0];\n"
        .   "   document.getElementsByName(n)[0].value = el.options[el.selectedIndex].text || '';\n"
        .   "   let _el_up = document.getElementsByName(`_\${_n}_update`)[0];\n"
        .   "   if (_el_up) JsHttpRequest.request(_el_up);\n"
        .   "};\n";
}

function editable_select_row($label, $name, $value = "", $options = null, $input_size = 25, $label_attribs = null, $readonly = false,
    $submit_on_select = false)
{
	if ($options == null || !is_array($options)){
		$options = [];
	}

	echo "<tr>";
	echo "<td class=\"label\">{$label}</td>";
	editable_select_cells(null, $name, $value, $options, $input_size, $label_attribs, $readonly, $submit_on_select);
	echo "</tr>";
}