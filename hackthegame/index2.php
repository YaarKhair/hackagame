<?php
  	  // welke page tonen?
  	  $page="main";
  	  if (isset($_GET['htg']))
		if (ctype_alpha($_GET['htg'])) $page=htmlentities(stripslashes($_GET['htg']));
  	  
function nl2br2($string) {
$string = str_replace(array("\r\n", "\r", "\n"), "", $string);
return $string;
}
  	  
?>
<html>
  <head>
<title>HackTheGame - the free Hacking simulation game</title>
 <style type="text/css">
 #topbar {
position: absolute;
top: 0;
left: 0;
width: 100%;
height: 25px;
line-height: 25px;
vertical-align: middle;
background: lightyellow;
color:red;
}
#topbar a {
display: block;
padding-left: 5px;
height: 25px;
line-height: 25px;
vertical-align: middle;
color: red;
font-family: arial;
font-weight: bold;
text-align: center;
}
</style>
  </head> 
 <body bgcolor="#000000" background="gfx/background.gif" alink="#c8c8c8" link="#c8c8c8" vlink="#c8c8c8">
<div id="topbar"><a href="http://www.hackerforever.com">Play the online version of HackTheGame, called HackerForever. A free Multiplayer Online Game. Click here!</a></div>
<div align="center">
<br><br>
  <table width="920"><tr><td valign="top">
	  <table width="100%" height="100%" border="1" cellpadding="4" cellspacing="8" bordercolor="#357F09">
	    <tr>
	     <td bgcolor="#000000" align="center">
	      <img src="gfx/htg_small.jpg">
	     </td>
	     <td bgcolor="#000000" align="center" background="gfx/td_bg.jpg">
	      <font color="#c8c8c8" size="14" face="verdana,arial"><b>HackTheGame</b></font><br>
	      <font color="lime" size="2" face="verdana,arial">The free hacking simulation game <strong>+editor!</strong></font>
	     </td>
	    <tr>
	     <td bgcolor="#357F09" colspan="2" align="center">
	       <font color="#c8c8c8" size="2" face="courier">root[<?php echo $page; ?>] #
	        <a href="?htg=main">Main</a> | 
	        <a href="?htg=about">About</a> | 
	        <a href="?htg=downloads">Downloads</a> | 
	        <a href="?htg=missionpacks">MissionPacks</a> | 
	        <a href="?htg=screenshots">Screenshots</a> | 
<!--	        <a href="?htg=highscore">Highscore</a> | //-->
	        <a href="?htg=links">Links</a>
	      </td>
	     </tr>    
	    <tr>
	     <td bgcolor="#000000" colspan="2">
	      <font color="#c8c8c8" size="2" face="courier">
	        <br>
	        <?php 
	          $info_file = "inc/".$page.".inc";
	          include ($info_file);
	        ?>
	      </font>
	      <br clear="all"><br><div align="center">
	      <!-- BANNER HERE //-->
	      </div>
	     </td>
	    </tr>
	   </table> 
   </td><td valign="top">
   <!-- start block here-->
   <!-- side bar-->
   <!-- end block here-->
  </td></tr></table> 
   <font color="#357F09" size="2" face="verdana, arial">
   &copy; 2005-2013 chaozz@work | Designed by <a href=http://www.chaozz.nl>chaozz.nl</a><img src="gfx/cursor.gif" alt="cursor"><br />
   <!-- footer ad here-->
</div> 
	<!-- Start of StatCounter Code -->
	<script type="text/javascript" language="javascript">
	var sc_project=1369236; 
	var sc_invisible=1; 
	var sc_partition=11; 
	var sc_security="5e0208bb"; 
	</script>
	
	<script type="text/javascript" language="javascript" src="http://www.statcounter.com/counter/counter.js"></script><noscript><a href="http://www.statcounter.com/" target="_blank"><img  src="http://c12.statcounter.com/counter.php?sc_project=1369236&amp;java=0&amp;security=5e0208bb&amp;invisible=1" alt="web hit counter" border="0"></a> </noscript>
	<!-- End of StatCounter Code -->   
 </body>
</html>  
