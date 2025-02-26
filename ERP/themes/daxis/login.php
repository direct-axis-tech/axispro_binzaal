<style>


    body {
        background: url('http://13.233.39.112/amer_bg/BACK.png') !important;
    }


    #loginscreen .LogoHeader {
        background: white !important;
    }

    iframe.rc-anchor-pt {
        display: none;
    }
    iframe {
        /*width: 320px;*/
        /*height: 95px;*/
        margin-left: 10px;
        /*!*zoom: 120%;*!*/
    }

    #login {
        /*zoom: 80%;*/
    }


</style>

<style>


</style>

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
if (!isset($path_to_root) || isset($_GET['path_to_root']) || isset($_POST['path_to_root']))
    die(trans("Restricted access"));
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/includes/page/header.inc");
include_once($path_to_root . "/config_db.php");
include_once($path_to_root . "/config.php");
include_once($path_to_root . "/includes/db/connect_db.inc");
set_global_connection($def_coy);
include_once("kvcodes.inc");
create_tbl_option();
//echo $UTF8_fontfile;
$js = "<script language='JavaScript' type='text/javascript'>
function defaultCompany()
{
	document.forms[0].company_login_name.options[" . $_SESSION["wa_current_user"]->company . "].selected = true;
}
</script>";

echo "<style>body { zoom: 100% !important; }</style>";


add_js_file('login.js');
// Display demo user name and password within login form if "$allow_demo_mode" is true
if ($SysPrefs->allow_demo_mode == true) {
    $demo_text = trans("Login as user: demouser and password: password");
} else {
    $demo_text = trans("");
    if (@$SysPrefs->allow_password_reset) {
        $demo_text .= " " . trans("or") . " <a href='$path_to_root/index.php?reset=1'>" . trans("request new password") . "</a>";
    }
}

if (check_faillog()) {
    $blocked_msg = '<span class="redfg">' . trans('Too many failed login attempts.<br>Please wait a while or try later.') . '</span>';

    $js .= "<script>setTimeout(function() {
	    	document.getElementsByName('SubmitUser')[0].disabled=0;
	    	document.getElementById('log_msg').innerHTML='$demo_text'}, 1000*" . $SysPrefs->login_delay . ");</script>";
    $demo_text = $blocked_msg;
}
if (!isset($def_coy))
    $def_coy = 0;
$def_theme = "default";

$login_timeout = $_SESSION["wa_current_user"]->last_act;

$title = $login_timeout ? trans('Authorization timeout') : $SysPrefs->app_title . " " . $version . " - " . trans("Login");

if (kv_get_option('powered_name') != 'false') {
    $ltitle = kv_get_option('powered_name');
    if (kv_get_option('hide_version') == 0)
        $ltitle .= " " . $version;
} else {
    $ltitle = $SysPrefs->app_title;
}

$encoding = isset($_SESSION['language']->encoding) ? $_SESSION['language']->encoding : "iso-8859-1";
$rtl = isset($_SESSION['language']->dir) ? $_SESSION['language']->dir : "ltr";
$onload = !$login_timeout ? "onload='defaultCompany()'" : "";
$onload = !$login_timeout ? "onload='defaultCompany()'" : "";
if (file_exists(dirname(__FILE__) . '/images/' . kv_get_option('favicon'))) {
    $favicon = kv_get_option('favicon') . '?' . rand(2, 5);
} else
    $favicon = 'favicon.ico?' . rand(2, 5);
echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\">\n";
echo "<html dir='$rtl' >\n";
echo "<head profile=\"http://www.w3.org/2005/10/profile\"><title>$ltitle</title>\n";
echo "<meta http-equiv='Content-type' content='text/html; charset=$encoding' >\n";
echo '<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, minimum-scale=1, user-scalable=no">';
echo "<link href='" . $path_to_root . "/themes/daxis/css/login.css' rel='stylesheet' type='text/css'> \n";
echo "<link href='$path_to_root/themes/daxis/images/$favicon' rel='icon' type='image/x-icon'> \n";
send_scripts();
if (!$login_timeout) {
    echo $js;
}

echo "<script src='https://www.google.com/recaptcha/api.js'></script>";

echo "</head>\n";

echo "<body id='loginscreen' $onload>\n";

//echo "<table class='titletext'><tr><td>$title</td></tr></table>\n";

div_start('login');

start_form(false, false, $_SESSION['timeout']['uri'], "loginform");

echo "<div class='LogoHeader'>";

$logo_img = "kv_logo.png" . '?' . rand(2, 5);


if ($_SESSION['captcha_error']) {
    echo "<div style='text-align: center;font-size: 20px;margin: 7px;background: #ff7272;color: white; border-radius: 7px; padding: 7px;'>"
        . trans('Captcha validation failed! Try again') . "</div>";
    kill_login();
} else if ($_SESSION['login_failed']) {
    echo "<div style='text-align: center;font-size: 20px;margin: 7px;background: #ff7272;color: white; border-radius: 7px; padding: 7px;'>"
        . trans('Incorrect Username/Password') . "</div>";
    kill_login();
}


if (!$login_timeout) { // FA logo


//    if (file_exists(dirname(__FILE__) . '/images/' . kv_get_option('logo'))) {
//        $logo_img = kv_get_option('logo') . '?' . rand(2, 5);
//    } else
//        $logo_img = 'Saaisaran.png?' . rand(2, 5);


    echo "<a target='_blank' href='" . kv_get_option('powered_url') . "'><img src='$path_to_root/themes/daxis/images/$logo_img' alt='" . kv_get_option('powered_name') . "' height='100' onload='fixPNG(this)' border='0' style='/* max-width: 250px; */'/></a>";

} else {

    echo "<a target='_blank' href='" . kv_get_option('powered_url') . "'><img src='$path_to_root/themes/daxis/images/$logo_img' alt='" . kv_get_option('powered_name') . "' height='100' onload='fixPNG(this)' border='0' style='/* max-width: 250px; */'/></a>";

}
echo "</div>\n";

echo "<input type='hidden' id=ui_mode name='ui_mode' value='" . $_SESSION["wa_current_user"]->ui_mode . "' >\n";
div_start('Login_Div');
echo '<div class="ContentLogin"> Login </div>';
start_table(false, "class='login'");
/*if (!$login_timeout){
    if(kv_get_option('hide_version') == 0 )
        table_section_title(trans("Version")." $version   Build ".$SysPrefs->build_version ." - ".trans("Login"));
    else
        table_section_title(trans("Login"));
}*/
$value = $login_timeout ? $_SESSION['wa_current_user']->loginname : ($SysPrefs->allow_demo_mode ? "demouser" : "");

text_cells(null, "user_name_entry_field", $value, 20, 30, false, "", "", " placeholder='Username' ");

$password = $SysPrefs->allow_demo_mode ? "password" : "";

//password_row('password', $password);
echo '<tr> <td><input type="password" name="password" size="20" maxlength="20" value="" placeholder="Password"> </td> </tr>';

if ($login_timeout) {
    hidden('company_login_name', $_SESSION["wa_current_user"]->company);
} else {
    if (isset($_SESSION['wa_current_user']->company))
        $coy = $_SESSION['wa_current_user']->company;
    else
        $coy = $def_coy;
    if (!@$text_company_selection) {
        echo "<tr><td><select name='company_login_name'>\n";
        for ($i = 0; $i < count($db_connections); $i++)
            echo "<option value=$i " . ($i == $coy ? 'selected' : '') . ">" . $db_connections[$i]["name"] . "</option>";
        echo "</select>\n";
        echo "</td></tr>";
    } else {
//			$coy = $def_coy;
        text_cells(trans("Company"), "company_login_nickname", "", 20, 50);
    }
    start_row();
    //label_cell($demo_text, "colspan=2 align='center' id='log_msg'");
    end_row();
};


//echo '<tr><td colspan="2"></td></tr>';

end_table(1);

//echo '<div data-theme="light" class="g-recaptcha" data-sitekey="6LdDGmIUAAAAALH4_WdTKt5H4bT80FPUwJiFAL2h"></div>';

br();

echo "<center><input type='submit' class='buttonui btn-block btn-large' value='" . trans("Login") . "' name='SubmitUser'"
    . ($login_timeout ? '' : " onclick='set_fullmode();'") . (isset($blocked_msg) ? " disabled" : '') . " ></center>\n";

foreach ($_SESSION['timeout']['post'] as $p => $val) {
    // add all request variables to be resend together with login data
    if (!in_array($p, array('ui_mode', 'user_name_entry_field', 'password', 'SubmitUser', 'company_login_name')))
        if (!is_array($val))
            echo "<input type='hidden' name='$p' value='$val'>";
        else
            foreach ($val as $i => $v)
                echo "<input type='hidden' name='{$p}[$i]' value='$v'>";
}


end_form(1);
//$Ajax->addScript(true, "document.forms[0].password.focus();");

echo "<script language='JavaScript' type='text/javascript'>
    //<![CDATA[
            <!--
            document.forms[0].user_name_entry_field.select();
            document.forms[0].user_name_entry_field.focus();
            //-->
    //]]>
    </script>";
//echo '<div align="center" ><a href="http://www.directaxistech.com/module/Saaisaran-frontaccouting-theme/" target="_blank"> Kvcodes </a> </div> ' ;
div_end();


div_end();


echo "</body></html>\n";




//exit;
?>



