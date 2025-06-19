<?php
  
  // a function i need
     function GetUserIP()
        {
          if (isset($_SERVER)) { if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) 
                                    { $ip = $_SERVER["HTTP_X_FORWARDED_FOR"]; } 
                                 elseif(isset($_SERVER["HTTP_CLIENT_IP"])) 
                                    { $ip = $_SERVER["HTTP_CLIENT_IP"]; } 
                                 else { $ip = $_SERVER["REMOTE_ADDR"]; }
                               }  
          else { if ( getenv( 'HTTP_X_FORWARDED_FOR' ) ) 
                      { $ip = getenv( 'HTTP_X_FORWARDED_FOR' ); } 
                 elseif ( getenv( 'HTTP_CLIENT_IP' ) ) 
                      { $ip = getenv( 'HTTP_CLIENT_IP' ); } 
                 else { $ip = getenv( 'REMOTE_ADDR' ); }
               }
          return $ip;     
        }  
        
  // convert string to number with a decryption twist ;-)
     function StringToNumber($theString) {
	          $result = "";
	          $theString = trim($theString);
              for ($i=0; $i<strlen($theString); $i++)
              {
                  $char = $theString{$i};
                  $asciivalue = ord($char)-98;
                  $result .= $asciivalue;
              }
              if ($theString == "yUoX") { $result = 0; }
              return $result;
     }
     
  // innocent until proven guilty
  
  $data=$_GET['data'];
  $hacker=$_GET['h'];
  
  list($STRjob, $STRsecondshacked, $STRtimescaught, $STRtimessaved, $STRversion, $STRkey) = split('w', $data);
  $job = intval(StringToNumber($STRjob));
  $secondshacked = intval(StringToNumber($STRsecondshacked));
  $timescaught = intval(StringToNumber($STRtimescaught));
  $timessaved = intval(StringToNumber($STRtimessaved));
  $version = intval(StringToNumber($STRversion));
  $key = intval(StringToNumber($STRkey));
  
  // lets check the values
  $error = 0;
  if ($hacker == null || $hacker == "") { $error =1; }
  if (trim($STRjob) == null || $job == 0 || $job > 11) { $error =2; }
  if (trim($STRsecondshacked) == null || $secondshacked == 0 || $secondshacked < 500) { $error=3; }
  if (trim($STRtimescaught) == null) { $error =4; }
  if (trim($STRtimessaved) == null) { $error =5; }
  if (trim($STRversion) == null) { $error =6; }
  if (trim($STRkey) == null) { $error =7; }
  
  
  // version handling
  // version before 113 cannot upload to this website, they have the old www.hackthegame.com website programmed in its exe
  $versionStr = "xyz";
  if ($version == 113) { $versionStr = "1.13"; }
  if ($version == 114) { $versionStr = "1.14"; }
  if ($version == 115) { $versionStr = "1.15"; }
  if ($version == 116) { $versionStr = "1.16"; }
  if ($version == 117) { $versionStr = "1.17"; }
  if ($version == 118) { $versionStr = "1.18"; }
  if ($version == 119) { $versionStr = "1.19"; }
  if ($versionStr == "xyz") { $error =8; }
  
  //echo $hacker."---> job:".$job." secondshacked:".$secondshacked." timescaught:".$timescaught." timessaved:".$timessaved." version:".$versionStr." error:".$error;exit;
  
  // some other values
  $ip = GetUserIp();
  $date_arr = Getdate();
  $date = $date_arr['mday']."-".$date_arr['mon']."-".$date_arr['year'];  

  if ($error != 0)
     { echo "Not all values came through correctly."; }
  
  // now check the key       
  $mykey = 1000;
  $mykey = intval($mykey / strlen($hacker));
  $mykey = intval($mykey + $job);
  $mykey = intval($mykey * $secondshacked);
  $mykey = intval($mykey + $timescaught);
  $mykey = intval($mykey * $timessaved);
  
  if ($mykey != $key && $error==0) 
     { echo "CRC check error."; $error=1; }

  // lets connect to the database
  $db = mysql_connect("localhost","chaozz_admin","chaozz666XP") or die(mysql_error());
  mysql_select_db("chaozz_htg",$db) or die(mysql_error());     

  // lets see if this hacker already submitted a score today
  $query = "SELECT * FROM highscore WHERE IP='$ip' AND SUBMITDATE='$date'";
  $sql = mysql_query($query) or die(mysql_error());

  // record found
  if (mysql_num_rows($sql) != 0 && $error==0) 
     { echo "You've already submitted a score today. You can only submit 1 score per day."; $error=1; }  
     
  // lets see if this score was already uploaded >> CHECK 1
  $query = "SELECT * FROM highscore WHERE JOB=$job AND SECONDSHACKED=$secondshacked AND TIMESCAUGHT=$timescaught AND TIMESSAVED=$timessaved AND IP='$ip'";
  $sql = mysql_query($query) or die(mysql_error());

  // record found
  if (mysql_num_rows($sql) != 0 && $error==0) 
     { echo "You've already submitted these values once."; $error=1; }

  // lets see if this score was already uploaded on another day >> CHECK 2
  $query = "SELECT * FROM highscore WHERE HACKER='$hacker' AND JOB=$job AND SECONDSHACKED=$secondshacked AND TIMESCAUGHT=$timescaught AND TIMESSAVED=$timessaved";
  $sql = mysql_query($query) or die(mysql_error());
  
  // record found
  if (mysql_num_rows($sql) != 0 && $error==0) 
     { echo "You've already submitted these values once."; $error=1; }
  
  if ($error == 0) {           
     // still here? then let's add this score
     $query = "INSERT INTO highscore (HACKER, JOB, SECONDSHACKED, TIMESCAUGHT, TIMESSAVED, SUBMITDATE, IP, VERSION) ";
     $query = $query . "VALUES ('$hacker', $job, $secondshacked, $timescaught, $timessaved, '$date', '$ip', '$versionStr')";
     $sql = mysql_query($query) or die(mysql_error());
     $scoreid = mysql_insert_id();   // id of the new score
     // send me a notification
     // if (mail("chaozz@chaozz.nl", "new score", "$hacker submitted his score. check: http://www.chaozz.nl/hackthegame/?htg=highscore&showall=1&scoreid=$scoreid&version=$versionStr", "from: highscore@hackthegame.com")) { $id = $id; } 

     echo "Your score was submitted succesfully!!<br /><a href=?htg=highscore&showall=1&scoreid=".$scoreid."&version=".$versionStr.">Click here</a> to see the hiscore list";
     echo "<br />If you want to show off your score to others, use this link: <font color=red>http://www.chaozz.nl/hackthegame/?htg=highscore&showall=1&scoreid=".$scoreid."&version=".$versionStr."</font>";
     echo "<br /><br />Please tell me what you think of this game <a href=http://www.chaozz.nl/forum/index.php?board=2.0>here</a>!!!!";
  }
?>