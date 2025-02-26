<!DOCTYPE html>
<html lang="en">
<head>
<title>Easy Responsive Tabs to Accordion Demos</title>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<link rel="stylesheet" href="./themes/grayblue/all_css/easy-responsive-tabs.css">
<link href="http://www.jqueryscript.net/css/jquerysctipttop.css" rel="stylesheet" type="text/css">
<style>
.demo{margin:150px auto;width:980px;}
.demo h1{margin:0 0 25px;}
.demo h3{margin:10px 0;}
pre{background-color:#FFF;}
@media only screen and (max-width:780px){
.demo{margin:5%;width:90%;}
.how-use{display:none;float:left;width:300px;}
}
#tabInfo{display:none;}
</style>
</head>
<body>

<div class="demo">
<div>
<div id="horizontalTab">
<ul class="resp-tabs-list">
<li>Responsive Tab-1</li>
<li>Responsive Tab-2</li>
<li>Responsive Tab-3</li>
</ul>
<div class="resp-tabs-container">
<div>
<p><h3>ada</h3>, consectetur adipiscing elit. Vestibulum nibh urna, euismod ut ornare non, volutpat vel tortor. Integer laoreet placerat suscipit. Sed sodales scelerisque commodo. Nam porta cursus lectus. Proin nunc erat, gravida a facilisis quis, ornare id lectus. Proin consectetur nibh quis urna gravida mollis.</p>
</div>
<div>
<p>This tab has <h4>dadas</h4><h4>dadas</h4><h4>dadas</h4><h4>dadas</h4><h4>dadas</h4><h4>dadas</h4><h4>dadas</h4><h4>dadas</h4><div>this is whaaaaaaaattttttttttt</div></div>icon in consectetur adipiscing eliconse consectetur adipiscing elit. Vestibulum nibh urna, ctetur adipiscing elit. Vestibulum nibh urna, t.consectetur adipiscing elit. Vestibulum nibh urna,  Vestibulum nibh urna,it.</p>
</div>
<div>
<p>Suspendisse blandit velit Integer laoreet placerat suscipit. Sed sodales scelerisque commodo. Nam porta cursus lectus. Proin nunc erat, gravida a facilisis quis, ornare id lectus. Proin consectetur nibh quis Integer laoreet placerat suscipit. Sed sodales scelerisque commodo. Nam porta cursus lectus. Proin nunc erat, gravida a facilisis quis, ornare id lectus. Proin consectetur nibh quis urna gravid urna gravid eget erat suscipit in malesuada odio venenatis.</p>
</div>
</div>
</div>
<br />
<div id="tabInfo">
Selected tab: <span class="tabName"></span>
</div>
<br />

<div style="height: 30px; clear: both"></div>
</div>
</body>
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
<script src="./themes/grayblue/all_js/easy-responsive-tabs.js"></script>
<script>
$(document).ready(function () {
$('#horizontalTab').easyResponsiveTabs({
type: 'default', //Types: default, vertical, accordion           
width: 'auto', //auto or any width like 600px
fit: true,   // 100% fit in a container
closed: 'accordion', // Start closed if in accordion view
activate: function(event) { // Callback function if tab is switched
var $tab = $(this);
var $info = $('#tabInfo');
var $name = $('span', $info);
$name.text($tab.text());
$info.show();
}
});
$('#verticalTab').easyResponsiveTabs({
type: 'vertical',
width: 'auto',
fit: true
});
});
</script>
<script type="text/javascript">

  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-36251023-1']);
  _gaq.push(['_setDomainName', 'jqueryscript.net']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();

</script>
</html>
