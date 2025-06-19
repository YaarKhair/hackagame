<?php 
	header("Last-Modified: " . gmdate("D, d M Y H:i:S") . " GMT");
	header("Cache-Control: no-store, no-cache, must-revalidate");
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache");  
	session_start();
	include_once("../modules/connectdb.php");
	include_once("../modules/settings.php");
	include_once("../modules/functions.php");

	if (!isset($hackerdata)) die("NO-HD");
	// Get code
	$code = '';
	if(!empty($_REQUEST['code'])) $code = $_REQUEST['code'];
	
	$error = "";
	// checks
    if ($code != sha1($hackerdata['last_login'].$hackerdata['started'])) $error = "Initializing, please wait";		// Wrong code
	if($hackerdata['id'] == 0) $error = "not logged in";
	if ($hackerdata['chatkick_till'] > $now) $error = "kicked";
	if ($now >= $hackerdata['offline_from'] && $now <= $hackerdata['offline_till']) $error = "offline";
	if ($hackerdata['prison_till'] > $now) $error = "prison";
	if ($hackerdata['banned_date'] > 0) $error = "banned";
	if ($hackerdata['hybernate_till'] > $now) $error = "hybernated";
	
	if ($error != "") {
		echo $error;
		@mysqli_free_result($result);
		@mysqli_close($link);
		exit;
	}
	
	// IGNORE list
	$ignore = array();
	$result = mysqli_query($link, "SELECT hacker_id2 FROM multi_list WHERE hacker_id1 = {$hackerdata['id']} AND relation = 'ignore'");
	while($row = mysqli_fetch_assoc($result)) $ignore[] = $row['hacker_id2'];
	
	$output = '';
	$result = mysqli_query($link, "UPDATE hacker SET onchatpage_date = '$now' WHERE id = {$hackerdata['id']}"); // set a valid session token, which will be read by message.php
	if (InGroup($hackerdata['id'], 1) || InGroup($hackerdata['id'], 2))$result = mysqli_query($link, "SELECT id, invisible, lastchatmsg_date, alias FROM hacker WHERE onchatpage_date > ".date($date_format, strtotime("-20 seconds"))." ORDER BY alias ASC"); 
	else $result = mysqli_query($link, "SELECT id, alias, invisible, lastchatmsg_date FROM hacker WHERE invisible = 0 AND onchatpage_date > ".date($date_format, strtotime("-20 seconds"))." ORDER BY alias ASC");
	while ($row = mysqli_fetch_assoc($result)) {
		// invisible? (staff only)
		$pre = ''; 
		$post = '';
		if ($row['id'] != $ibot_id) $post = '&nbsp;<img src="images/whisper.png" alt="Whisper" onclick="Chat.whisper('."'".$row['alias']."'".')">';
		if ($row['invisible'] == 1) { $pre = '<span style="text-decoration:line-through">'; $post = '</span>'; }
		if (in_array($row['id'],$ignore)) { $pre = '<span style="text-decoration:line-through">'; $post = '</span>'; } // ignored people are crossed out
		// away from keyboard?
		if ($row['lastchatmsg_date'] < date($date_format, strtotime("-$chat_afk minutes"))) $afk = "&nbsp;/AFK";
		else $afk = "";
		$output .= $pre.ShowHackerAlias($row['id'],0,false).$post.$afk.'<br>';
	}	

	echo $output;

	@mysqli_free_result($result);
	@mysqli_close($link);
?>