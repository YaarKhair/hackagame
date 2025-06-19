<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php
	include("modules/permissions.php");

	// flood protection
	if ($hackerdata['nextim_date'] > $now) 
		return "Flood Protection!<br>You need to wait another ".SecondsDiff($now, $hackerdata['nextim_date'])." seconds before sending another IM.";

	$title = "";
	if (!empty($_POST['title'])) $title = sql($_POST['title']);

	$clan_id = 0;
	if (!empty($_POST['clanim'])) {
		if ($hackerdata['clan_council'] == 0) return "Access denied.";
		$clan_id = $hackerdata['clan_id']; 
		if (!IsOnlineServer(GetGateway($clan_id))) return "Your gateway is currently offline.";
	}

	$username = "";
	if (!empty($_POST['username'])) {
		$username = sql($_POST['username']);
		$username = explode(";",$username);
		$username = array_map('trim',$username);
		$username = array_unique($username);
	}
	
	$onlineonly = 0;
	if (!empty($_POST['onlineonly'])) $onlineonly = CheckBox(sql($_POST['onlineonly']));
	if ($onlineonly == 1) $online = " AND last_click >= '".date($date_format, strtotime("-15 minutes"))."'";
	else $online = "";
	
	$copy2self = " AND id != {$hackerdata['id']}";
	if(!empty($_POST['copy2self'])) $copy2self = CheckBox($_POST['copy2self']);
	if($copy2self == 1) $copy2self = '';
	
	$message = "";
	if (!empty($_POST['message'])) {
		$message = $_POST['message'];
		$message = preg_replace('#\r?\n#', '[br]', $message);
		$message = sql($message, false);
		$message = str_replace("[br]", "<br>", $message);
		$message = FilterTags ($message, $hackerdata['id']);
	}
	else return "Message empty. Nothing to send.";
	
	if (!$is_staff) $message .= "<br><br>[small]Message sent from IP {$hackerdata['ip']} using HF Lookout[/small]";

	$error = "";
	$counter = 0;
	if ($clan_id == 0) {
		foreach($username as $alias) {
			$result = mysqli_query($link, "SELECT id FROM hacker WHERE alias = '$alias'");
			if(mysqli_num_rows($result) == 0)
				$error .= "$alias, ";
			else {
				$row = mysqli_fetch_assoc($result);
				$sendlist[$counter] = $row['id'];
				$counter ++;
			}	
		}
		if ($counter == 0) return "Message not sent. No hackers matched your criterea.";
		if ($counter > $max_recipients && !InGroup($hackerdata['id'], 1)) return "You can not send a message to more than $max_recipients hackers at a time.";
	}	
	else {
		// mail a clan
		$query = "";
		$clan_group = intval($_POST['clan_group']);
		if ($clan_group == 1) $query = "SELECT id FROM hacker WHERE clan_id = ".$clan_id.$online.$copy2self; // all
		if ($clan_group == 2) $query = "SELECT id FROM hacker WHERE clan_council = 1 AND clan_id = ".$clan_id.$online.$copy2self; // council
		if ($clan_group == 3) $query = "SELECT id FROM hacker WHERE clan_council = 0 AND clan_id = ".$clan_id.$online.$copy2self; // non-council
		
		$result = mysqli_query($link, $query);
		if (mysqli_num_rows($result) == 0) return "Message not sent. No hackers matched your criterea.";
		while ($row = mysqli_fetch_assoc ($result)) {
			$sendlist[$counter] = $row['id'];
			$counter ++;
		}	
	}
	
	// lets send a message to all reciepients
	foreach ($sendlist as $id) 
		SendIM ($hackerdata['id'], $id, $title, $message, $now, $clan_id);

	PrintMessage ("Success", "Message sent succesfully.", "40%"); // screen
	
	if ($error != "") PrintMessage ("Error", "Your message could not be delivered to $error", "40%"); // screen
	
	// flood prevention (donators == 0 is to enable the protection for those getting free premium at signup. they are not donators, but ARE premium)
	if ((!IsPremium($hackerdata['id']) || $hackerdata['donator'] == 0) && !$is_staff) {
		$nextim_date = date($date_format, strtotime("+ ".$sendim_interval." seconds"));
		$result = mysqli_query($link, "UPDATE hacker SET nextim_date = '".$nextim_date."' WHERE id = ".$hackerdata['id']);
	}	
	// show inbox
	$folder = "inbox";
	$redirect = "mailbox";
	include ("./pages/$redirect.php");
?>