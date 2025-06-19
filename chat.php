<?php
	//	Error reporting incase you need to debug
	session_start();
    include_once("modules/connectdb.php");
    include_once("modules/settings.php");
    include_once("modules/functions.php");

    function GetTimestamp($line){
        $regex_ts = '/\[(.*?)\]/';
        $matches = array();
        preg_match($regex_ts, $line, $matches);
		return $matches[1];
	}	
	function GetCommand ($command) {
		Global $msg;
		if (stripos($msg, $command." ") === 0 || stripos($msg, $command."|") === 0 || $command == $msg) return true;
		else return false;
	}

    // VARIABLES
    $show_lines = 40;
	$aliaspre = '<strong>&lt;';
	$aliaspost = '&gt;</strong>';
	$timecolor = '6E6E6E';
	$timepre = '[';
	$timepost = ']';
	$sendto_id = 0; // default id = 0, means for everyone. /whisper will set this to a value
	$ibot_sendto_id = 0; // default id = 0, means for everyone. /whisper will set this to a value
	$ibot_msgcolor = 'FF0000';
	$ibot_msg = ''; // the bot is silent ;)
	$ibot_alias = "iBot";
	$ibot_aliaspre = '&lt;';
	$ibot_aliaspost = '&gt;';
	$ibot_aliascolor = '9F9F9F';
    $notallowed = array('[color]', '[/color]', '[big]', '[/big]', '[pre]', '[/pre]', '[small]', '[/small]', '[quote]', '[/quote]', '[url]', '[/url]', '[img]', '[/img]');
    $receiver_id = 0; // only used for /whisper
    $online = date($date_format, strtotime("-15 minutes"));
	
	// Get the code
	$code = '';
	if(!empty($_REQUEST['code'])) $code = $_REQUEST['code'];
	
	if (!isset($hackerdata)) Die ("Your session has expired. Please login to the game again.");
	$hacker_id = $hackerdata['id'];
	
    // Do the checks 
    //if ($hackerdata['onchatpage_date'] < date($date_format, strtotime("-5 minutes"))) return "Initializing, please wait";		// Not in chat for the last 5 minutes?
    //if ($hackerdata['last_click'] < $online) return "A Timeout occurred, please refresh";	// Timed out
    if ($code != sha1($hackerdata['last_login'].$hackerdata['started'])) return "Initializing, please wait...";		// Wrong code
    if ($hackerdata['chatkick_till'] > $now) return "kicked";		// Chatkicked?
    if ($now >= $hackerdata['offline_from'] && $now <= $hackerdata['offline_till']) return "offline";		// Are you offline?
    if ($hackerdata['prison_till'] > $now) return "prison";	// Are you in prison?
    if ($hackerdata['banned_date'] > 0) return "banned";		// Are you banned?
    if ($hackerdata['hybernate_till'] > $now) return "hibernation";	// Are you in hibernation?

    // Set some variables so you can alter them later in the commands for commands such as /me that require a color change
    $msg_color = $hackerdata['chat_color'];
    $alias_color = mysqli_get_value("color", "clan", "id", $hackerdata['clan_id']);

    // You can call this page in two ways, 1. send a message, 2. request the chat content
    
    // remove any chat mention so the sound stops beeping and the title returns to normal
   	$result2 = mysqli_query($link, "UPDATE hacker SET chat_mention = 0 WHERE id = {$hackerdata['id']}"); // remove the chat mention, so we don't get spammed!!
 
    // Message was sent, lets check it out
	if (isset($_REQUEST['msg'])) {
		$msg = sql(htmlspecialchars($_REQUEST['msg'])); // sending a message is important ;)
        
        // User groups
		include ("modules/permissions.php");
		
        // first lets remove any spam in the message
        if (!$is_staff) {
    		// MESSAGE LENGTH filter
    		if (strlen($msg) > $max_msg_length) $msg = substr($msg, 0, $max_msg_length);
    		// SPAM FILTER
    		$msg = preg_replace('/\b(https?|ftp|file):\/\/[-A-Z0-9+&@#\/%?=~_|$!:,.;]*[A-Z0-9+&@#\/%=~_|$]/i', '**SPAM**', $msg);
    		$msg = preg_replace('/\b(www|fpt).[-A-Z0-9+&@#\/%?=~_|$!:,.;]*[A-Z0-9+&@#\/%=~_|$]/i', '**SPAM**', $msg);
    	}
    	    	
        // chat commands
    	include('commands.php');
    	
    	
    	// remove unwanted tags like [big] [/big] [pre] [/pre] [small] [/small]
    	foreach($notallowed as $tag)
    		$msg = str_ireplace($tag,"", $msg);
    			
    	$msg = trim($msg);
        $time = date('H:i');
        
        // add the new line (We check $msg = "" here because certain chat commands reset our message to "" and output text via ibot.)
        if ($msg != "" ) $result = mysqli_query ($link, "INSERT INTO chat (sender_id, sender_alias, sender_color, receiver_id, message, message_color, date, deleted) VALUES ({$hackerdata['id']}, '{$hackerdata['alias']}', '$alias_color', $receiver_id, '$msg', '$msg_color', '$now', 0)");
        
    	// did ibot respond?
        if ($ibot_msg != '')
            $result = mysqli_query ($link, "INSERT INTO chat (sender_id, sender_alias, sender_color, receiver_id, message, message_color, date, deleted) VALUES ($ibot_id, '$ibot_alias', '$ibot_aliascolor', $ibot_sendto_id, '$ibot_msg', '$ibot_msgcolor', '$now', 0)");
            
		// If the message has a mention, update the database field
	    preg_match_all('/@\w+/', $msg, $matches); // is there a mention ?
	
	    if($matches[0]) {
	        foreach($matches[0] as $alias2mention) {
	            $alias2mention = str_replace('@', '', $alias2mention); // strip the @
	            $online = date($date_format, strtotime("-15 minutes"));
	    	    $result_mention = mysqli_query($link, "SELECT id FROM hacker WHERE alias = '$alias2mention' AND last_click >= '$online'"); // check if it's a valid alias, and if the hacker is active
	    	    if(mysqli_num_rows($result_mention) > 0) {
	    	        // Update that hacker's field
	    	        $row_mention = mysqli_fetch_assoc($result_mention);
	    	        $result_mention = mysqli_query($link, "UPDATE hacker SET chat_mention = 1 WHERE id = {$row_mention['id']}");
	    	    }
	        }
	    }
	}
	
	// prepare the output
    $chat2client = Array(); // what we sent to the actual player
    $result = mysqli_query($link, "SELECT sender_id, sender_alias, sender_color, message, message_color, date FROM chat WHERE (receiver_id = 0 OR receiver_id = $hacker_id) AND deleted <> 1 AND sender_id NOT IN (SELECT hacker_id2 FROM multi_list WHERE hacker_id1 = {$hackerdata['id']} AND relation = 'ignore') ORDER BY id DESC LIMIT $show_lines"); // we use $hacker_id and not $hackerdata['id'] becuase whisper can alter this value
        
    while ($row = mysqli_fetch_assoc($result)) {
        $time = date('H:i',strtotime($row['date']));
        $message = trim(ReplaceBBC(htmlspecialchars_decode((" ".$row['message'])), false)); // add the first space for smilies at the first spot.
        $message_color = $row['message_color'];
        $sender_color = $row['sender_color'];
                        
        if (substr(strtolower($message), 0, 4) == "/me ") {
        	$message = substr($message, 4, strlen($message) -4);
        	$sender_alias = "* ".$row['sender_alias']; // the me command
        	$sender_color = "41A317";
        	$message_color = "41A317";
        }
        else $sender_alias = $aliaspre.$row['sender_alias'].$aliaspost;
        
            
        $chat2client[] = '<span style="color:#'.$timecolor.'">'.$timepre.$time.$timepost.'</span>&nbsp;<span style="color:#'.$sender_color.'">'.$sender_alias.'</span>&nbsp;<span style="color:#'.$message_color.'">'.$message.'</span>';
    }
    $chat2client = array_reverse($chat2client);
    foreach($chat2client as $line)
        echo $line.'<br>';
	mysqli_close($link);
?>