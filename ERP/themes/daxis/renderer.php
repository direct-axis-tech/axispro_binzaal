<?php
/*-------------------------------------------------------+
| Saai Theme for FrontAccounting
| http://www.directaxistech.com/
+--------------------------------------------------------+
| Author: Kvvaradha  
| Email: admin@directaxistech.com
+--------------------------------------------------------+*/
include_once("kvcodes.inc");
create_tbl_option();
function addhttp($url) {
		    if (!preg_match("~^(?:f|ht)tps?://~i", $url)) {
		        $url = "http://" . $url;
		    }
		    return $url;
		}
	class renderer{
		function get_icon($category){
			global  $path_to_root, $SysPrefs;

			if ($SysPrefs->show_menu_category_icons)
				$img = $category == '' ? 'right.gif' : $category.'.png';
			else	
				$img = 'right.gif';
			return "<img src='$path_to_root/themes/".user_theme()."/images/$img' style='vertical-align:middle;' border='0'>&nbsp;&nbsp;";
		}

		function wa_header(){
			if(isset($_GET['application']) && ($_GET['application'] == 'orders' || $_GET['application'] == 'orders#header'))
				page(trans($help_context = "Sales"), false, true);
			elseif(isset($_GET['application']) && ($_GET['application'] == 'AP'|| $_GET['application'] == 'AP#header'))
				page(trans($help_context = "Purchases"), false, true);
			elseif(isset($_GET['application']) && ($_GET['application'] == 'stock'|| $_GET['application'] == 'stock#header'))
				page(trans($help_context = "Items & Services"), false, true);
			elseif(isset($_GET['application']) && ($_GET['application'] == 'manuf'|| $_GET['application'] == 'manuf#header'))
				page(trans($help_context = "Manufacturing"), false, true);
			elseif(isset($_GET['application']) && ($_GET['application'] == 'proj'|| $_GET['application'] == 'proj#header'))
				page(trans($help_context = "Dimensions"), false, true);
			elseif(isset($_GET['application']) && ($_GET['application'] == 'assets'|| $_GET['application'] == 'assets#header'))
				page(trans($help_context = "Fixed Assets"), false, true);
			elseif(isset($_GET['application']) && ($_GET['application'] == 'GL'|| $_GET['application'] == 'GL#header'))
				page(trans($help_context = "GL & Banking"), false, true);
			elseif(isset($_GET['application']) && ($_GET['application'] == 'extendedhrm'|| $_GET['application'] == 'extendedhrm#header'))
				page(trans($help_context = "HRM and Payroll"), false, true);
			elseif(isset($_GET['application']) && ($_GET['application'] == 'system'|| $_GET['application'] == 'system#header'))
				page(trans($help_context = "Setup Menu"), false, true);
			elseif(!isset($_GET['application']) || ($_GET['application'] == 'dashboard'|| $_GET['application'] == 'dashboard#header'))
				page(trans($help_context = "Dashboard"), false, true);



            elseif(!isset($_GET['application']) || ($_GET['application'] == 'app_report'|| $_GET['application'] == 'app_report#header'))
                page(trans($help_context = "APP REPORT"), true, true);


			else
				page(trans($help_context = "Main Menu"), false, true);
		}

		function wa_footer(){
			end_page(false, true);
		}

		function menu_header($title, $no_menu, $is_index, $newThemeMode = false){
			global $path_to_root, $SysPrefs, $db_connections, $icon_root, $version ;			
			
            $icons = [
                'orders'    => 'monetization_on',
                'AP'        => 'add_shopping_cart',
                'stock'     => 'storage',
                'manuf'     => 'location_city',
                'assets'    => 'receipt',
                'proj'      => 'dialpad',
                'GL'        => 'account_balance_wallet',
                'system'    => 'settings'
            ];
            $icon_root = 'av_timer';
            $indicator = "$path_to_root/themes/".user_theme(). "/images/ajax-loader.gif";
            $applications = $_SESSION['App']->applications;
            $local_path_to_root = $path_to_root;
            $sel_app = $_SESSION['sel_app'];
            $logo_img = file_exists(dirname(__FILE__) . '/images/' . kv_get_option('logo'))
                ? kv_get_option('logo') . '?' . rand(2, 5)
                : 'Saaisaran.png?' . rand(2, 5);
            ?>

            <?= view('system.amc-notifications')->render() ?>

            <?php if (!$newThemeMode): ?>
                <div class="wrapper">
                    <div class="main-panel" id="main-panel">
                        <div class="content header-positioned header-tablet-and-mobile-positioned menubar-positioned">
							<?php if (!$no_menu): ?>
                            <div class="wrapper-custom">
                                <?= view('layout.header.main')->render() ?>
                                <?= view('layout.header._menubar')->render() ?>
                            </div>
							<?php endif; ?>
                            <center>
                                <table class="tablestyle_noborder">
                                    <tr>
                                        <td>
                                            <img id="ajaxmark" src="<?= $indicator ?>" align="center" style="visibility:hidden;" alt="ajaxmark">
                                            <div class="ajax-blocker"></div>
                                        </td>
                                    </tr>
                                </table>
                            </center>
                            <div class="content inner-box-content">
            <?php else: ?>
                <div class="kt-grid kt-grid--hor kt-grid--root header-fixed header-tablet-and-mobile-fixed menubar-fixed">
                    <div class="kt-grid__item kt-grid__item--fluid kt-grid kt-page kt-grid--hor kt-wrapper wrapper-custom" id="kt_wrapper">
						<?php if (!$no_menu): ?>
                        <div id="kt_header" class="kt-header kt-grid__item  kt-header--fixed" data-ktheader-minimize="on">
                            <?= view('layout.header.main')->render() ?>
                            <?= view('layout.header._menubar')->render() ?>
                        </div>
						<?php endif; ?>
                        <table class="w-100 tablestyle_noborder">
                            <tr>
                                <td class="text-center p-0">
                                    <img id="ajaxmark" src="<?= $indicator ?>" style="visibility:hidden" alt="ajaxmark">
                                    <div class="ajax-blocker"></div>
                                </td>
                            </tr>
                        </table>
            <?php endif;
		}

		function menu_footer($no_menu, $is_index){
			global $version, $path_to_root, $Pagehelp, $Ajax, $SysPrefs;

			include_once($path_to_root . "/includes/date_functions.inc");

			if(kv_get_option('powered_name') != 'false'){
				$app_title = kv_get_option('powered_name');
			}else 
				$app_title = 'Vanigam';

			if(kv_get_option('powered_url') != 'false'){
				$powered_url = addhttp(kv_get_option('powered_url'));
			}else 
				$powered_url = 'http://frontaccounting.com';			
			
			echo '</div>';

			if ($no_menu == false){

				if(isset($_GET['application']) && $_GET['application'] == 'stock')
					echo '</div>';
				echo '<footer class="footer">
            	<div class="container-fluid">
                <nav class="pull-left">
                    <ul> <li> <a target="_blank" href="'.$powered_url.'" tabindex="-1">'.$app_title.' ';
					if(kv_get_option('hide_version')== 0 )
					{
//					    echo $version;
					}
				echo '</a>- <a href="http://www.directaxistech.com" >  www.directaxistech.com </a>' .show_users_online().' </li>';
				if (isset($_SESSION['wa_current_user'])) {
					$phelp = implode('; ', $Pagehelp);
					$Ajax->addUpdate(true, 'hotkeyshelp', $phelp);
					echo "<li> ".$phelp."</li>";
				}
				echo '</ul>
                </nav>
                <p class="copyright pull-right">
                    Copyrights &copy; '.date('Y').' <a href="'.$powered_url.'" target="_blank"></a> 
                </p>
            </div>
        </footer>';
    } echo '</div>
</div>'; ?>


<script>
var toggleMenu = function(){
            var m = document.getElementById('sidebar'),
                c = m.className;
              m.className = c.match( ' active' ) ? c.replace( ' active', '' ) : c + ' active';

              var m = document.getElementById('main-panel'),
                c = m.className;
              m.className = c.match( ' active' ) ? c.replace( ' active', '' ) : c + ' active';
        }




</script>
</div>
</div><?php 
		}

		function display_applications(&$waapp)	{
			global $path_to_root;

			$selected_app = $waapp->get_selected_application();
			if (!$_SESSION["wa_current_user"]->check_application_access($selected_app))
				return;

			if (method_exists($selected_app, 'render_index'))	{
				$selected_app->render_index();
				return;
			}

			if( !isset($_GET['application']) || $_GET['application'] == 'dashboard'){	
				require("dashboard.php");
			}
			else if ($_GET['application'] == 'app_report') {

                require("app_report.php");

            }else{

				echo '<div class="MenuPage"> ';
				foreach ($selected_app->modules as $module)	{
	        		if (!$_SESSION["wa_current_user"]->check_module_access($module))
	        			continue;
					// image
					echo '<div class="MenuPart"><div class="subHeaders"> '.$module->name.'</div>';
					echo '<ul class="left">';

					foreach ($module->lappfunctions as $appfunction){
						$img = $this->get_icon($appfunction->category);
						if ($appfunction->label == "")
							echo "&nbsp;<br>";
						elseif ($_SESSION["wa_current_user"]->can_access_page($appfunction->access)) {
							echo '<li>'.$img.menu_link($appfunction->link, $appfunction->label)."</li>";
						}
						//elseif (!$_SESSION["wa_current_user"]->hide_inaccessible_menu_items())	{
							//echo '<li>'.$img.'<span class="inactive">'.access_string($appfunction->label, true)."</span></li>";
						//}
					}
					echo "</ul>";
					if (sizeof($module->rappfunctions) > 0)	{
						echo "<ul class='right'>";
						foreach ($module->rappfunctions as $appfunction){
							$img = $this->get_icon($appfunction->category);
							if ($appfunction->label == "")
								echo "&nbsp;<br>";
							elseif ($_SESSION["wa_current_user"]->can_access_page($appfunction->access)) {
								echo '<li>'.$img.menu_link($appfunction->link, $appfunction->label)."</li>";
							}
							//elseif (!$_SESSION["wa_current_user"]->hide_inaccessible_menu_items())	{
								//echo '<li>'.$img.'<span class="inactive">'.access_string($appfunction->label, true)."</span></li>";
							//}
						}
						echo "</ul>";
					}
					echo "<div style='clear: both;'></div>";
				}
				echo "</div></div> </div> </div>";
			}			
  		}
	}
