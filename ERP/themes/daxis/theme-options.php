<?php 
/*--------------------------------------------------\
| Kvcodes    	|               | default.css       |
|---------------------------------------------------|
| For use with:                                    	|
| FrontAccounting 									|
| http://www.directaxistech.com/  	            			|
| by KV varadha                            			|
|---------------------------------------------------|
| Note:                                         	|
| Changes can be made to this CSS that will be  	|
| reflected throughout FrontAccounting.             |
|                                                   |
\--------------------------------------------------*/
$page_security = 'SA_OPEN';
$path_to_root = "../..";

include_once($path_to_root . "/includes/lang/language.inc");

include_once($path_to_root."/includes/ui.inc");
include_once($path_to_root."/includes/session.inc");
include_once($path_to_root."/themes/daxis/kvcodes.inc");
include_once($path_to_root . "/admin/db/company_db.inc");




if(!function_exists('kv_update_user_theme')){
	function kv_update_user_theme($id, $theme){
		$sql = "UPDATE ".TB_PREF."users SET theme=". db_escape($theme)." WHERE id=".db_escape($id);
		return db_query($sql, "could not update user display prefs for $id");
	}	
}

page(trans($help_context = "Theme Options"));

if(isset($_GET['updated'])){
	display_notification("Your Custom Settings Updated Successfully!");
}
if(isset($_POST['submit_options'])){
	$dir =  $path_to_root."/themes/daxis/images";
	if($_FILES['logo']["size"] >0){
		$tmpname = $_FILES['logo']['tmp_name'];		
		$ext = end((explode(".", $_FILES['logo']['name'])));
		$filesize = $_FILES['logo']['size'];
		$filetype = $_FILES['logo']['type'];
		if (file_exists($dir."/".kv_get_option('logo')))
			unlink($dir."/".kv_get_option('logo'));
			
		move_uploaded_file($tmpname, $dir."/kv_logo.".$ext);
		kv_update_option('logo', 'kv_logo.'.$ext);
	}
	
	if($_FILES['favicon']["size"] >0){
		$tmpname = $_FILES['favicon']['tmp_name'];		
		$extn = end((explode(".", $_FILES['favicon']['name'])));
		$filesize = $_FILES['favicon']['size'];
		$filetype = $_FILES['favicon']['type'];
		if (file_exists($dir."/".kv_get_option('favicon')))
			unlink($dir."/".kv_get_option('favicon'));
			
		move_uploaded_file($tmpname, $dir."/kv_favicon.".$extn);
		kv_update_option('favicon', 'kv_favicon.'.$extn);
	}

	if(!isset($_POST['hide_version'])){
		$_POST['hide_version'] = 0;
	}
	if(!isset($_POST['hide_help_link'])){
		$_POST['hide_help_link'] = 0;
	}
	if(!isset($_POST['hide_dashboard'])){
		$_POST['hide_dashboard'] = 0;
	}
		
	kv_update_option('hide_version', $_POST['hide_version']);
	kv_update_option('hide_help_link', $_POST['hide_help_link']);
	kv_update_option('hide_dashboard', $_POST['hide_dashboard']);
	
	if(strlen(trim($_POST['powered_name'])) > 0 ){
		kv_update_option('powered_name', $_POST['powered_name']);
	}

	if(strlen(trim($_POST['powered_url'])) > 0 ){
		kv_update_option('powered_url', $_POST['powered_url']);
	}
	if(strlen(trim($_POST['theme'])) > 0 ){
		kv_update_option('theme', $_POST['theme']);
	}
	if(strlen(trim($_POST['color_scheme'])) > 0 ){
		kv_update_option('color_scheme', $_POST['color_scheme']);
	}
	$_POST['theme'] = clean_file_name($_POST['theme']);
	$chg_theme = $_POST['theme'];
	if ($chg_theme){
		kv_update_user_theme($_SESSION["wa_current_user"]->user, $_POST['theme']);
		$_SESSION["wa_current_user"]->prefs->theme = $_POST['theme'];	
	}
	unset($_FILES);
	unset($_POST);
	if ($chg_theme)
		meta_forward($_SERVER['PHP_SELF'].'?updated=yes');		
}

if(kv_get_option('hide_version') == 0 || kv_get_option('hide_version') == 1 ){
	$_POST['hide_version'] = kv_get_option('hide_version'); 
}else{
	$_POST['hide_version']= 0;
}
if(kv_get_option('hide_dashboard') == 0 || kv_get_option('hide_dashboard') == 1 ){
	$_POST['hide_dashboard'] = kv_get_option('hide_dashboard'); 
}else{
	$_POST['hide_dashboard']= 0;
}

if(kv_get_option('hide_help_link') == 0 || kv_get_option('hide_help_link') == 1 ){
	$_POST['hide_help_link'] = kv_get_option('hide_help_link'); 
}else{
	$_POST['hide_help_link']= 0;
}

if(kv_get_option('powered_name') != 'false'){
	$_POST['powered_name'] = kv_get_option('powered_name'); 
}else{
	$_POST['powered_name']= 'FrontAccounting';
}

if(kv_get_option('powered_url') != 'false'){
	$_POST['powered_url'] = kv_get_option('powered_url'); 
}else{
	$_POST['powered_url']= 'frontaccounting.com';
}

if(kv_get_option('color_scheme') != 'false'){
	$_POST['color_scheme'] = kv_get_option('color_scheme'); 
}else{
	$_POST['color_scheme']= 'default';
}

	start_form(true);
		start_table(TABLESTYLE, "width='60%'");
			table_section_title(trans("General Options"));


			/** Only for developer */

//				kv_image_row(trans("Upload Logo") . ":", 'logo', 'logo');
//				kv_image_row(trans("Favicon Icon") . ":", 'favicon', 'favicon');
//				check_row(trans("Hide Version Details").':', 'hide_version', null);
//				check_row(trans("Hide Top Help Link").':', 'hide_help_link', null);
//				text_row(trans("Powered Name*:"), 'powered_name', null, 28, 80);
//				text_row(trans("Powered By*:"), 'powered_url', null, 28, 80);
				check_row(trans("Hide Dashboard").':', 'hide_dashboard', null);
//				themes_list_row(trans("Theme:"), "theme", user_theme());


				Saai_color_schemes(trans("Color Schemes:"), "color_scheme", null);


			//table_section_title(trans("General Options"));
				//text_row(trans("Powered Name*:"), 'powered_name', null, 28, 80);
				//text_row(trans("Powered By*:"), 'powered_url', null, 28, 80);
			//$tzlist = DateTimeZone::listIdentifiers(DateTimeZone::ALL);
			//print_r($tzlist); 

			//$tzlis =  generate_timezone_list();

			//$options = array('select_submit'=> false,'disabled' => null);
			//echo '<tr><td> Time Zone </td><td>'. array_selector('time_zone', null, $tzlis, $options).'</td> </tr>';

			//print_r($tzlis);

		end_table();
		br();
		submit_center('submit_options', trans("Update Options"), trans('Theme data'));

	end_form();

br();
br();
end_page(); ?>