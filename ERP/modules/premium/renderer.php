<?php

echo "<link href='$path_to_root/themes/".user_theme()."/all_css/select.css' rel='stylesheet' type='text/css'> \n";

   echo '<meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">';

 echo "<link href='$path_to_root/themes/".user_theme()."/all_css/style.css' rel='stylesheet' type='text/css'> \n";
 
   echo "<link href='$path_to_root/themes/".user_theme()."/all_css/bootstrap.min.css' rel='stylesheet' type='text/css'> \n";

 echo "<link href='https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css' rel='stylesheet' type='text/css'> \n";
 echo "<link href='https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css' rel='stylesheet' type='text/css'> \n";

   echo "<link href='$path_to_root/themes/".user_theme()."/all_css/jquery-jvectormap-1.2.2.css' rel='stylesheet' type='text/css'> \n";
  echo "<link href='$path_to_root/themes/".user_theme()."/all_css/AdminLTE.min.css' rel='stylesheet' type='text/css'> \n";
   echo "<link href='$path_to_root/themes/".user_theme()."/all_css/_all-skins.min.css' rel='stylesheet' type='text/css'> \n";


  // echo "<link href='$path_to_root/themes/grayblue/as.css' rel='stylesheet' type='text/css'> \n";
   
  //  echo "<link href='$path_to_root/themes/grayblue/bootstrap/css/bootstrap.min.css' rel='stylesheet' type='text/css'>";
//	echo "<link href='$path_to_root/themes/grayblue/as.css' type='text/js'>";
  //  echo "<link href='$path_to_root/themes/grayblue/dist/css/AdminLTE.min.css' type='text/js'>";
    
 //   echo "<link href='https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css' type='text/js'>";
  //  echo "<link href='https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css' type='text/js'>";
  
//    echo "<link href='$path_to_root/themes/grayblue/dist/css/skins/_all-skins.min.css' type='text/js'>";


?>
<?php
	class renderer
	{
		function wa_header()
		{
			page(trans($help_context = " "), false, true);
		}

		function wa_footer()
		{
			end_page(false, true);
		}

		function menu_header($title, $no_menu, $is_index)
		{

			global $path_to_root, $help_base_url,$img,$img2,$img3, $db_connections;
			$local_path_to_root = $path_to_root;
			global $leftmenu_save, $app_title, $version;
            
  
  
  	$sql = "SELECT value FROM ".TB_PREF."sys_prefs WHERE `name`='coy_logo' ";
	$result = db_query($sql, "could not get sales type");
	$row = db_fetch_row($result);
    
    $User_logo ;
    //var_dump($row[0]);
    
    

  
      
			// Build screen header
			$leftmenu_save = "";
			$sel_app = $_SESSION['sel_app'];
			echo " <div class='wrapper'>\n";
echo'
<!-- Modal -->
  <div class="modal fade" id="myModal" role="dialog">';
   
include_once("$path_to_root/themes/".user_theme()."/Datamodal.php");                    
                            $_data= new dashboardmodal();
                            
                            $_data->DataModal();
 echo'</div>';

    echo'
<!-- Modal -->
  <div class="modal fade" id="myPayModel" role="dialog">';
   
include_once("$path_to_root/themes/".user_theme()."/Datamodal.php");                    
                            $_data= new dashboardmodal();
                            
                            $_data->myPayModel();
 echo'</div>';
 
//Reorder level
 echo'
<!-- Modal -->
  <div class="modal fade" id="myReOModal" role="dialog">';
   
include_once("$path_to_root/themes/".user_theme()."/Datamodal.php");                    
                            $_data= new dashboardmodal();
                            
                            $_data->myREOModel();
 echo'</div>';
		//	echo "<div id='topsection'> \n";
		//	echo "  <div class='innertube'> \n";
      
      
      // images for notification icons
        $img = "$path_to_root/themes/".user_theme()."/images/receipt-plus-icon.png";
        $img2 = "$path_to_root/themes/".user_theme()."/images/payment-icon.png";        
        $img3 = "$path_to_root/themes/".user_theme()."/images/Inventory-maintenance-icon.png";     
          
          
            echo"<header class='main-header navbar-static-top'>";
    
          //  <!-- Logo -->
           echo"<a style='background-color:#fff;cursor:Default' href='#' class='logo'>";
             // <!-- mini logo for sidebar mini 50x50 pixels -->
              echo"<span class='logo-mini'><img src='$path_to_root/themes/".user_theme()."/images/SM-logo.png' style='margin-top:12px;' ></span>";
//              <!-- logo for regular state and mobile devices -->
              echo"<img src='$path_to_root/themes/".user_theme()."/images/hisaab_logo_new.png' class='' height='40px;' width='210px;'>";
           echo"</a>";
           // <!-- Header Navbar: style can be found in header.less -->
            echo"<nav class='navbar navbar-static-top' role='navigation'>";
             // <!-- Sidebar toggle button-->
              echo"<a href='#' class='sidebar-toggle' data-toggle='offcanvas' role='button'>";
                echo"<span class='sr-only'>Toggle navigation</span>";
              echo"</a>";
              
              
            //  <!-- Navbar Right Menu -->
			
              echo"<div class='navbar-custom-menu'>";
                echo"<ul class='nav navbar-nav'>";
                     echo"<li></li>";
               //<!-- Messages: style can be found in dropdown.less-->
            include_once("$path_to_root/themes/".user_theme()."/notification_ui.php");                    
                            $_dash= new notification();
                            
                            $_dash->All_notification();
			


//              <!-- User Account: style can be found in dropdown.less -->
                
                
                
                
                
                
                  echo"<li class='dropdown user user-menu' >";
            echo"<a href='#' class='dropdown-toggle' data-toggle='dropdown'>";
    
   
   
    if ($row[0] == "")
    {
       $User_logo = "$path_to_root/themes/".user_theme()."/images/No_Image_Available.png";	
        echo " <img src='".$User_logo."' class='user-image' alt='User Image'>";
     /*   $img ="$path_to_root/themes/grayblue/images/$row[0]";
     return $img;*/
    }else
    {
        $User_logo = company_path() . "/images/" .$row[0];
        echo " <img src='".$User_logo."' class='user-image' alt='User Image'>";
   
    }   
                 
                 echo " <span class='hidden-xs'> " . $db_connections[$_SESSION["wa_current_user"]->company]["name"] ."</span>";
               echo " </a>";
              echo"<ul class='dropdown-menu'>";
              echo"<!-- User image -->";
                 echo " <li class='user-header'>";
                 //$logo = company_path() . "/images/" . $this->company['coy_logo'];
                  echo "<img src='".$User_logo."' class='img-circle' alt='User Image'>";
                   echo " <p>";
                    echo   $db_connections[$_SESSION["wa_current_user"]->company]["name"] ;
                    $begin = begin_fiscalyear();
                     $end=end_fiscalyear();
                    $begin1 = date2sql($begin);
                    $end1= date2sql($end);
                     echo "  <small>Current Fiscal Year</small>";                 
                     echo "  <small>".$begin1." / ".$end1."</small>";

                    echo " </p>";
                 echo "  </li>";
              echo"<!-- Menu Body -->";
                echo"<!-- Menu Footer-->";
                 echo "  <li class='user-footer'>";
                 echo "    <div class='pull-left'>";
                 echo"<a class='btn btn-default btn-flat' href='$local_path_to_root/admin/display_prefs.php?'><i class='fa fa-cogs'></i> <span>".trans("Configuration")."</span></a>";
                  echo "   </div>";
                   
                  echo "   <div class='pull-right'>";
                 echo"<a class='btn btn-default btn-flat' href='$local_path_to_root/access/logout.php?'><i class='fa fa-sign-out'></i> <span>".trans("Logout")."</span></a>";
                  echo "   </div>";
                  echo " </li>";
               echo"</ul>";
               echo "  </li>";
              echo "  <li>";
              echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
              echo "  </li>";
              echo "  <!-- Control Sidebar Toggle Button -->";
               // echo" <li>
            //    <a href='#' data-toggle='control-sidebar'><i class='fa fa-gears'></i></a>
          //    </li>";
            //  echo "  <li>";
              //echo "  <a href='#' style='height:50px;' data-toggle='control-sidebar'><i style='margin-top:3px;' class='fa fa-gears'></i></a>";
            //   echo "        </li>";
            //    echo"</ul>";
              echo"</div>";    
          
          
          echo"  </nav>";
          echo"</header>";
          	//echo "    <h1 style='background-color:red;width:220px'>" . $app_title . " " . $version . "</h1>\n";
			//echo "<h1 id='his' style='margin-left:50px;font-size:23px;'>Hisaab <span id='hisaabpk'>ERP : Hisaab.pk <i style='font-size:12px;'>Simple Cloud Accounting</i></span><h1>\n";

		//	echo "  </div>\n";

		//	echo "  <div id='topinfo'>" . $db_connections[$_SESSION["wa_current_user"]->company]["name"] ."</div>\n";

		/*	echo "  <div id='iconlink'>";
	   		// Logout on main window only
	   	/*	if (!$no_menu) {
	   	//	echo "<a id='btnlog' href='$local_path_to_root/access/logout.php?'>Log out</a>";	
        //echo "    <a class='' href='$local_path_to_root/access/logout.php?'><img src='$local_path_to_root/themes/grayblue/images/logoukost2.png' style='margin-top:-5px' width='120px' title='".trans("Logout")."' /></a>";
     		}
  			// Popup help
     		if ($help_base_url != null) {
			  echo "<a target = '_blank' onclick=" .'"'."javascript:openWindow(this.href,this.target); return false;".'" '. "href='". help_url()."'><img src='$local_path_to_root/themes/grayblue/images/help-browser.png' title='".trans("Help")."' /></a>\n";
	   		}
     		echo "  </div>\n"; // iconlink
     		echo "  </div>\n";
*/
        // <!-- Left side column. contains the logo and sidebar -->
             echo"<aside class='main-sidebar'>";
              // <!-- sidebar: style can be found in sidebar.less -->
              
                echo"<section class='sidebar'>";
                echo"<div class='user-panel'>";
                echo"<div class='pull-left image'>";
                echo"<img src='".$User_logo."' class='img-circle' alt='User Image'>";
            echo"</div>";
            echo"<div class='pull-left info'>";
             echo" <p> " . $db_connections[$_SESSION["wa_current_user"]->company]["name"] ."</p>";
             echo" <a href='#'><i class='fa fa-circle text-success'></i> Welcome</a>";
           echo" </div>";
          echo"</div>";
          echo" <ul class='sidebar-menu'>";
          echo" <li class='header'>MAIN NAVIGATION</li>";
                             
     		if (!$no_menu)
     		{ 
     		 
                       $applications = $_SESSION['App']->applications;
                        
                       // $leftmenu_save .= " <li >";
                       foreach($applications as $app)
                        {
                        
                            
                        //var_dump($app);
                            $acc = access_string($app->name);
                          // var_dump($acc);
                          
                          if($app->name == "DashBoard")
                            {
                                $gly = "fa fa-dashboard";
                           
                            
                            
                            $leftmenu_save .= "      <li class='treeview'>";
                            $leftmenu_save .= "
                            <a style='' class='"
                                	.($sel_app == $app->id ? '' : '')
    								."'href='$local_path_to_root/index.php?application=".$app->id.
                                SID ."'$acc[1]><i class='$gly'></i><span>" .$acc[0] . "</span>
                              
                            </a>\n";
                             }
                          
                          else if($app->name == "Customers")
                            {
                                $gly = "glyphicon glyphicon-user";
                           
                            
                            
                            $leftmenu_save .= "      <li class='treeview'>";
                            $leftmenu_save .= "
                            <a style='' class='"
                                	.($sel_app == $app->id ? '' : '')
    								."'href='$local_path_to_root/index.php?application=".$app->id.
                                SID ."'$acc[1]><i class='$gly'></i><span>" .$acc[0] . "</span>
                              
                            </a>\n";
                             }
                            else  if($app->name == "&Suppliers")
                            {
                                $gly = "fa fa-truck";
                           
                            
                            
                            $leftmenu_save .= "      <li class='treeview'>";
                            $leftmenu_save .= "
                            <a style='' class='"
                                	.($sel_app == $app->id ? '' : '')
    								."'href='$local_path_to_root/index.php?application=".$app->id.
                                SID ."'$acc[1]><i class='$gly'></i><span>" .$acc[0] . "</span>
                              
                            </a>\n";
                             }
                             else  if($app->name == "Inventory")
                            {
                                $gly = "fa fa-th";
                           
                            
                            
                            $leftmenu_save .= "      <li class='treeview'>";
                            $leftmenu_save .= "
                            <a style='' class='"
                                	.($sel_app == $app->id ? '' : '')
    								."'href='$local_path_to_root/index.php?application=".$app->id.
                                SID ."'$acc[1]><i class='$gly'></i><span>" .$acc[0] . "</span>
                              
                            </a>\n";
                             }
                             else if($app->name == "&General Ledger")
                            {
                                $gly = "glyphicon glyphicon-duplicate";
                           
                            
                            
                            $leftmenu_save .= "      <li class='treeview'>";
                            $leftmenu_save .= "
                            <a style='' class='"
                                	.($sel_app == $app->id ? '' : '')
    								."'href='$local_path_to_root/index.php?application=".$app->id.
                                SID ."'$acc[1]><i class='$gly'></i><span>" .$acc[0] . "</span>
                              
                            </a>\n";
                             } else if($app->name == "S&ettings")
                            {
                                $gly = "fa fa-wrench";
                           
                            
                            
                            $leftmenu_save .= "      <li class='treeview'>";
                            $leftmenu_save .= "
                            <a style='' class='"
                                	.($sel_app == $app->id ? '' : '')
    								."'href='$local_path_to_root/index.php?application=".$app->id.
                                SID ."'$acc[1]><i class='$gly'></i><span>" .$acc[0] . "</span>
                              
                            </a>\n";
                             }
                            else{
                             $gly = "fa fa-share";
                            $leftmenu_save .= "      <li class='treeview'>";
                            $leftmenu_save .= "
                            <a style='' class='"
                                	.($sel_app == $app->id ? '' : '')
    								."'href='$local_path_to_root/index.php?application=".$app->id.
                                SID ."'$acc[1]><i class='$gly'></i><span>" .$acc[0] . "</span>
                              
                            </a>\n";
                            }
                             
                            if ($sel_app == $app->id)
                            {
                                $curr_app_name = $acc[0];
                                $curr_app_link = $app->id;
                            }
                            $leftmenu_save .= "      </li>";
                        }
                     
                       // $leftmenu_save .= "    </li>";
                        

                        $leftmenu_save .= " <li class='header'>" . $_SESSION["wa_current_user"]->name . "</li>";
                        $leftmenu_save .= " <li ><a href='$local_path_to_root/admin/display_prefs.php?'><i class='fa fa-cogs'></i> <span>".trans("Configuration")."</span></a></li>";
                        $leftmenu_save .= " <li ><a href='$local_path_to_root/admin/change_current_user_password.php?selected_id=".$_SESSION["wa_current_user"]->username."'><i class='fa fa-key'></i> <span>".trans("Change password")."</span></a></li>";
                        $leftmenu_save .= " <li ><a href='$local_path_to_root/access/logout.php?'><i class='fa fa-sign-out'></i> <span>".trans("Logout")."</span></a></li>\n";
                       
			if (!$no_menu)
				echo $leftmenu_save;
                     
                }
                echo"</ul>";
                echo"</section>";
                //<!-- /.sidebar -->
              echo"</aside>";


		echo "	<div class='content-wrapper' style='background-color:white' >";
        echo"<section>";
			if ($title && !$no_menu)
             {
		
                    //<!-- Content Header (Page header) -->
                echo"<section class='content-header' style='background-color:;padding-bottom:4px;'>";
                  echo"<h1>";
                   	echo " <a style='' href='$local_path_to_root/index.php?application=".$curr_app_link. SID ."'>" . $curr_app_name . "</a>";
                           if ($no_menu)
        					echo "<br>";
        				   elseif ($title && !$is_index)
        					echo "<small><a id='mname' href='#'>" . $title . "</a></small>";
                            	$indicator = "$path_to_root/themes/".user_theme(). "/images/ajax-loader.gif";
              // 	echo " <span style=''><img id='ajaxmark' src='$indicator' style='visibility:hidden;'></span>";
               // echo"<small>Version 2.0</small>";
                echo"  </h1>";
                echo"  <ol class='breadcrumb'>";
              // echo"   <li><a href='#'><i class='fa fa-dashboard'></i> Home</a></li>";
               	echo"&nbsp;". (user_hints() ? "<span id=''></span>" : "");
                 echo" </ol>";
              echo"  </section>";
                   
              
            
			//	echo "  <div id='contentcolumn'>\n";
			//	echo "    <div class='innertube'>\n";
			//	echo (user_hints() ? "<span id='hints' style='float:right;'>sfssff</span>" : "");
			//	echo "      <p class='breadcrumb' style='padding:14px;'>\n";
			//	echo "        <a class='shortcut' id='mname' style='' href='$local_path_to_root/index.php?application=".$curr_app_link. SID ."'>" . $curr_app_name . "&nbsp</a>\n";
			//	if ($no_menu)
			//		echo "<br>";
			//	elseif ($title && !$is_index)
			//		echo "        <a id='mname' href='#'>" . $title . "</a>\n";
			//	$indicator = "$path_to_root/themes/".user_theme(). "/images/ajax-loader.gif";
			//	echo " <span style=''><img id='ajaxmark' src='$indicator' align='center' style='visibility:hidden;'></span>";
			//	echo " </p>\n";
            
     		}
         
         echo"<section>";
           
  
        
    echo"</section>";
		}

		function display_applications(&$waapp)
		{
		  $path_to_root = ".";
		  $sel_app = $_SESSION['sel_app'];
          $applications = $_SESSION['App']->applications;
		  foreach($applications as $app)
                        {
		  if ($sel_app == $app->id)
                            {
                                //$curr_app_name = ;
                                //echo "this is :".$app->id;
                                //echo"<br /><br /><br />";
                                if($app->id == 'dashboard')
                                {
                                
                include_once("$path_to_root/themes/".user_theme()."/Dashboard.php");                    
                            $_dash= new dashboard();
                            
                            $_dash->renderDash();
                                   
           
                                }else{
                                                                      
                                    echo "<section class='content' style='padding:5px;margin-top:3px;'>";
           $selected_app = $waapp->get_selected_application();
           foreach ($selected_app->modules as $module)
			{
			  echo"<div class='box box-success '  style='padding-bottom:10px;'>";
                   echo"<div class='box-header with-border '>";
                        echo"<h3 class='box-title '>" . str_replace(' ','&nbsp;',$module->name) . "</h3>";
                          echo"<div class='box-tools pull-right'>";
                            echo"<button class='btn btn-box-tool' data-widget='collapse'><i class='fa fa-minus'></i></button>";
                            //echo"<button class='btn btn-box-tool' data-widget='remove'><i class='fa fa-times'></i></button>";
                          echo"</div>";
                    echo"</div>";//<!-- /.box-header -->
                   
                  echo"<div class='box-body no-padding'>";
	           	   echo"<div class='row'>";
                  
                        echo"<div class='col-md-12 col-sm-11' >";
                        echo"<div class='pad col-md-12 col-sm-12 col-lg-12' >";
                            //<!-- text will go here  -->
                            
                          foreach ($module->lappfunctions as $appfunction)
            				{
            					$this->renderButtonsForAppFunctions($appfunction);
            				}
            
            				foreach ($module->rappfunctions as $appfunction)
            				{
            					$this->renderButtonsForAppFunctions($appfunction);
            				}
                        echo"</div>";
                        echo"</div>";//<!-- /.col -->

                   echo"</div>";//<!-- /.row -->     
             echo"</div>";
             echo"</div>";
           }  
           
                             
          echo"</section>";
          
                                }
                            }
                            
		
	
		   }
		  
          
         
         echo " </div>";//<!-- /.content-wrapper -->
         
         
		/*	$selected_app = $waapp->get_selected_application();

			foreach ($selected_app->modules as $module)
			{
				echo "      <div class='shiftcontainer'>\n";

					echo "        <div class='shadowcontainer'>\n";
								echo "          <div class='' style='background-color:;'>\n";
			echo "<div style='background-color:#357ca5 !important;text-align:center;padding:8px;'>";
				echo "            <b style='font-size:15px;color:white;'>" . str_replace(' ','&nbsp;',$module->name) . "</b><br />\n";
			echo "</div>";	

				echo "            <div class='buttonwrapper'>\n";

				foreach ($module->lappfunctions as $appfunction)
				{
					$this->renderButtonsForAppFunctions($appfunction);
				}

				foreach ($module->rappfunctions as $appfunction)
				{
					$this->renderButtonsForAppFunctions($appfunction);
				}

				echo "            </div>\n";
				echo "          </div>\n";
				echo "        </div>\n";
				echo "      </div>\n";
				echo "      <br />\n";
			
		}*/
        }

		function renderButtonsForAppFunctions($appfunction)
		{
			if ($_SESSION["wa_current_user"]->can_access_page($appfunction->access))
			{
				if ($appfunction->label != "")
				{
					$lnk = access_string($appfunction->label);

					echo "<a class='btn  btn-primary col-lg-3 col-sm-3 col-xs-12'   style='margin-top:3px;margin-right:2px;' href='$appfunction->link'$lnk[1]>" .$lnk[0] . "</a>\n";
				}
			}
			else	
				echo "<a class='btn  btn-primary col-lg-3 col-sm-3 col-xs-12' style='margin-top:3px;margin-right:2px;' disabled=''  href='#' title='".trans("Inactive")."' alt='".trans("Inactive")."'><span style='color:#cccccc;'>".access_string($appfunction->label, true)."</span></a>\n";
		}
        
        
		function menu_footer($no_menu, $is_index)
		{
			global $leftmenu_save, $db_connections;

			if (!$no_menu)
			{
			
         echo" <footer class='main-footer'>";
         
          // echo"<center>";
        
          echo"<div class='pull-left '>";
           	echo "  <div><span style='color:#3c8dbc'></span> <strong> " . $db_connections[$_SESSION["wa_current_user"]->company]["name"] ."</strong></div>\n";
          echo"</div>";
        
          echo"<div class='pull-right hidden-xs'>";
            echo"<b><span style='color:#3c8dbc'>Date : </span> ".Today() . "&nbsp;" ."<span style='color:#3c8dbc'>Time : </span> ". CurrentTime()."</b>";
          echo"</div>";
          
        echo"<strong style='margin-left:20%;'>" . $_SESSION["wa_current_user"]->name . "</strong>";
        
        //echo"</center>";
        
         echo"</footer>";
          
      echo"<div class='control-sidebar-bg'></div>";
		  echo"</div>";//<!-- ./wrapper -->
    
        
        
			/*	echo "    </div>\n";
				echo "  </div>\n";
					echo "string";*/
      

			}
            
            

      //  echo"</div>"; // <!-- ./main wrapper div's e -->

/*
			echo "</div>\n";*/

/*
				if (isset($_SESSION['wa_current_user']))
					echo "<td class=bottomBarCell>" . Today() . " | " . CurrentTime() . "</td>\n";
				echo "<td align='center' class='footer'><a target='_blank' href='$power_url'><font color='#ffffff'>$app_title $version - " . trans("Theme:") . " " . user_theme() . "</font></a></td>\n";
				echo "<td align='center' class='footer'><a target='_blank' href='$power_url'><font color='#ffff00'>$power_by</font></a></td>\n";
*/
		}

	}

?>
<?php
 echo "<script src='$path_to_root/themes/".user_theme()."/all_js/jQuery-2.1.4.min.js'></script>";			
  
    echo "<script src='$path_to_root/themes/".user_theme()."/all_js/bootstrap.min.js'></script>";			
    echo "<script src='$path_to_root/themes/".user_theme()."/all_js/fastclick.min.js'></script>";			
    echo "<script src='$path_to_root/themes/".user_theme()."/all_js/app.min.js'></script>";			

    echo "<script src='$path_to_root/themes/".user_theme()."/all_js/jquery.sparkline.min.js'></script>";			
    echo "<script src='$path_to_root/themes/".user_theme()."/all_js/jquery-jvectormap-1.2.2.min.js'></script>";
    
    echo "<script src='$path_to_root/themes/".user_theme()."/all_js/jquery-jvectormap-world-mill-en.js'></script>";
    			
    echo "<script src='$path_to_root/themes/".user_theme()."/all_js/jquery.slimscroll.min.js'></script>";
    //echo"<script type='text/javascript' src='https://ajax.googleapis.com/ajax/libs/jquery/1.9.0/jquery.min.js'></script>";
		
    echo "<script src='$path_to_root/themes/".user_theme()."/all_js/Chart.min.js'></script>";
   echo "<script src='$path_to_root/themes/".user_theme()."/all_js/dashboard2.js'></script>";
   echo "<script src='$path_to_root/themes/".user_theme()."/all_css/newdash.js'></script>";
   
echo "<script src='$path_to_root/themes/".user_theme()."/all_js/select.js'></script>";
    			
    echo "<script src='$path_to_root/themes/".user_theme()."/all_js/demo.js'></script>";


?>