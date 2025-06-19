<?php
  echo "Highscore is no longer available."; exit;

  $showall=$_GET['showall'];
  if ($showall == null || $showall !=1) { $showall = 0; }

  $version=$_GET['version'];
  if ($version == null) { $version = "1.19"; }

  $scoreid=$_GET['scoreid'];
  if ($scoreid == null) { $scoreid = 0; }
   
  $limit = 10; // default is top 10
  if ($showall == 1) { $limit = 9999; }  // show 9999 records

  echo "<div align=center>";
  echo "<strong>NOTE: uploading of highscore is disabled in 1.20</strong><br />";

  if ($showall == 0) { 
                      echo "<strong><font color=lime>Below are the top 10 hackers</font></strong><br />";
                      echo "Game version: <strong><font color=lime>".$version."</strong></font><br />";
                      echo "Can't find your score? Try <a href=?htg=highscore&showall=1&version=".$version.">the complete list</a><br />";
  }
  else { 
        echo "<strong><font color=lime>Below are all hackers</font></strong><br />";
        echo "Game version: <strong><font color=lime>".$version."</strong></font><br />";
        echo "Is the list too long? Try <a href=?htg=highscore&version=".$version.">the Top 10</a><br />"; 
  }
  if ($scoreid != 0) {   // if scoreid was send, notify user of highlight
	  echo "<font color=yellow>Your score is highlighted in yellow in the list below</font><br />";
  }
  
  // the game version  
  echo "<br />";
  echo "All game version highscores: ";
  echo "<a href=?htg=highscore&version=1.19>1.19</a> ";
  echo "<a href=?htg=highscore&version=1.18>1.18</a> ";
  echo "<a href=?htg=highscore&version=1.17>1.17</a> ";
  echo "<a href=?htg=highscore&version=1.16>1.16</a> ";
  echo "<a href=?htg=highscore&version=1.15>1.15</a> ";
  echo "<a href=?htg=highscore&version=1.14>1.14</a> ";
  
?>
 
  <table width="95%" border="1" cellpadding="3" cellspacing="0" bordercolor="#357F09">
    <tr>
     <td bgcolor="#357F09"><font color="#0A2E0E" size="2" face="fixedsys"><strong>Rank</td>
     <td bgcolor="#357F09"><font color="#0A2E0E" size="2" face="fixedsys"><strong>Hacker</td>
     <td bgcolor="#357F09"><font color="#0A2E0E" size="2" face="fixedsys"><strong>Jobs completed</td>
     <td bgcolor="#357F09"><font color="#0A2E0E" size="2" face="fixedsys"><strong>Times caught</td>
     <td bgcolor="#357F09"><font color="#0A2E0E" size="2" face="fixedsys"><strong>Times saved</td>
     <td bgcolor="#357F09"><font color="#0A2E0E" size="2" face="fixedsys"><strong>Seconds hacked</td>
     <td bgcolor="#357F09"><font color="#0A2E0E" size="2" face="fixedsys"><strong>Submit date</td>
     <td bgcolor="#357F09"><font color="#0A2E0E" size="2" face="fixedsys"><strong>Game version</td>
    </tr>
<?php     
  // lets connect to the database
  $db = mysql_connect("localhost","chaozz_admin","chaozz666XP") or die(mysql_error());
  mysql_select_db("chaozz_htg",$db) or die(mysql_error());     

  // lets see if this score was already uploaded >> CHECK 1
  $query = "SELECT * FROM highscore WHERE VERSION = '".$version ."' ORDER BY JOB DESC, TIMESCAUGHT ASC, TIMESSAVED ASC, SECONDSHACKED ASC LIMIT 0,".$limit;
  $sql = mysql_query($query) or die(mysql_error());
  
  // no record found
  if (mysql_num_rows($sql) == 0) 
     { echo "<tr><td><font color=\"#c8c8c8\" size=\"2\" face=\"fixedsys\">none</td>"; 
       echo "<td><font color=\"#c8c8c8\" size=\"2\" face=\"fixedsys\">none</td>"; 
       echo "<td><font color=\"#c8c8c8\" size=\"2\" face=\"fixedsys\">none</td>"; 
       echo "<td><font color=\"#c8c8c8\" size=\"2\" face=\"fixedsys\">none</td>"; 
       echo "<td><font color=\"#c8c8c8\" size=\"2\" face=\"fixedsys\">none</td>"; 
       echo "<td><font color=\"#c8c8c8\" size=\"2\" face=\"fixedsys\">none</td>"; 
       echo "<td><font color=\"#c8c8c8\" size=\"2\" face=\"fixedsys\">none</td>"; 
       echo "<td><font color=\"#c8c8c8\" size=\"2\" face=\"fixedsys\">none</td></tr>"; 
   } 
  else { 
       // show top 25 
       $rank = 0; 
       $counter = 0;
       while ($row = mysql_fetch_row($sql)) { 
             // game version and job completion level
             $totalmissions = 11;
             $percent = round(($row[2] / $totalmissions) * 100 ,2);
             
	         // ranking
	         $rank = $rank +1; 
             if ($rank == 1)  { $color = "red"; } 
             else { $color = "#0A2E0E"; } 
             
             // row color
             if ($counter == 0) { 
	             $rowcolor = "#F1FDDB";
	             $counter = 2; 
	         }
             else { 
	             $rowcolor = "#DFF9B3"; 
	             $counter = 0;
	         }
             if ($scoreid == $row[0]) {
	             $rowcolor = "yellow";   // highlight the score with id 'scoreid'
             }
             echo "<tr bgcolor=\"".$rowcolor."\"><td valign=\"top\" nowrap><font color=\"".$color."\" size=\"2\" face=\"fixedsys\">".$rank."</td>"; 
             echo "<td valign=\"top\" nowrap><font color=\"".$color."\" size=\"2\" face=\"fixedsys\">".$row[1]."</td>"; 
             echo "<td valign=\"top\" nowrap><font color=\"".$color."\" size=\"2\" face=\"fixedsys\">".$row[2]." (".$percent."%)</td>"; 
             echo "<td valign=\"top\" nowrap><font color=\"".$color."\" size=\"2\" face=\"fixedsys\">".$row[4]."</td>"; 
             echo "<td valign=\"top\" nowrap><font color=\"".$color."\" size=\"2\" face=\"fixedsys\">".$row[5]."</td>"; 
             echo "<td valign=\"top\" nowrap><font color=\"".$color."\" size=\"2\" face=\"fixedsys\">".$row[3]."</td>"; 
             echo "<td valign=\"top\" nowrap><font color=\"".$color."\" size=\"2\" face=\"fixedsys\">".$row[6]."</td>"; 
             echo "<td valign=\"top\" nowrap><font color=\"".$color."\" size=\"2\" face=\"fixedsys\">".$row[8]."</td></tr>"; 
       } 
  } 
?> 
 </table> 
 </div> 