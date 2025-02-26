<?php
class tabs
{
    public function Alltabs()
    {  
      echo"<section>";
      echo"<div class='col-xs-12'>";  
      echo "<div class=''>";
  
        echo'   <style>

#tabs {
overflow: hidden;
width: 100%;
margin: 0;
padding: 0;
list-style: none;
}
#tabs li {
float: left;
margin: 0 -15px 0 0;
}
#tabs a {
float: left;
position: relative;
padding: 0 40px;
height: 0;
line-height: 30px;
text-transform: uppercase;
text-decoration: none;
color: #fff;
border-right: 30px solid transparent;
border-bottom: 30px solid #3D3D3D;
border-bottom-color: #777\9;
opacity: .3;
filter: alpha(opacity=30);
}
#tabs a:hover,  #tabs a:focus {
border-bottom-color: #2ac7e1;
opacity: 1;
filter: alpha(opacity=100);
}
#tabs a:focus {
outline: 0;
}
#tabs #current {
z-index: 3;
border-bottom-color: #3d3d3d;
opacity: 1;
filter: alpha(opacity=100);
}
/* ----------- */
#content {
background: #fff;
border-top: 2px solid #3d3d3d;
padding: 2em;/*height: 220px;*/
}
#content h2,  #content h3,  #content p {
margin: 0 0 15px 0;
}

</style>';
echo'<div class="col-md-12 col-xs-12 col-sm-12">';
echo'<ul id="tabs" >
<li><a href="#" name="#tab1">Customer</a></li>
<li><a href="#" name="#tab2">Sales</a></li>
<li><a href="#" name="#tab3">Suppliers</a></li>
<li><a href="#" name="#tab4">Items</a></li>
<li><a href="#" name="#tab5">General</a></li>

</ul>
<div id="content" >


<div id="tab1" style="backgroud-color:green">';


include_once("$path_to_root/themes/".user_theme(). "/Dashboard_Widgets/ChartsDonuts.php");                    
                            $_bank = new AllDonutCharts();
                            
                            $_bank->customers();


echo'</div>

<div id="tab2">';                         
                            
                            $_bank->sales();
echo'</div>


<div id="tab3">';
                            $_bank->Suppliers();
echo'</div>

<div id="tab4">';
                           $_bank->Items();
    
echo'</div>

<div id="tab5">';
                           $_bank->General();
echo'</div>


</div>

</div>
<script>
 function resetTabs(){
        $("#content > div").hide(); //Hide all content
        $("#tabs a").attr("id",""); //Reset ids      
    }

    var myUrl = window.location.href; //get URL
    var myUrlTab = myUrl.substring(myUrl.indexOf("#")); // For localhost/tabs.html#tab2, myUrlTab = #tab2     
    var myUrlTabName = myUrlTab.substring(0,4); // For the above example, myUrlTabName = #tab

    (function(){
        $("#content > div").hide(); // Initially hide all content
        $("#tabs li:first a").attr("id","current"); // Activate first tab
        $("#content > div:first").fadeIn(); // Show first tab content
        
        $("#tabs a").on("click",function(e) {
            e.preventDefault();
            if ($(this).attr("id") == "current"){ //detection for current tab
             return       
            }
            else{             
            resetTabs();
            $(this).attr("id","current");';// Activate this
 echo"   $($(this).attr('name')).fadeIn(); // Show content for current tab
            }";
 echo'      });

 for (i = 1; i <= $("#tabs li").length; i++) {
          if (myUrlTab == myUrlTabName + i) {
              resetTabs();
        $("a[name="+myUrlTab+"]").attr("id","current"); // Activate url tab
              $(myUrlTab).fadeIn(); // Show url tab content        
          }
        }
    })()
  </script> ';
      echo "</div>";
      echo "</div>";
      echo"</section>";
    }  
   
}

?>
