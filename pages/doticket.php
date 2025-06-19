<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php
	$action = "";
	if (!empty($_POST['action'])) $action = sql($_POST['action']);
	elseif (!empty($_GET['action'])) $action = sql($_GET['action']);

	// MODS ONLY!
	$modonly = Array ("instaclose", "change_type", "change_status", "change_title", "delete_reply", "reset_vote");
	if (in_array($action, $modonly) && (!InGroup($hackerdata['id'], 1) && !InGroup($hackerdata['id'], 2) && !InGroup($hackerdata['id'], 6))) return "Staff only.";

	// instaclose works with an array of tickets, so we handle this before the normal ticket stuff. STAFF ONLY!
	if ($action == "instaclose") {
		$ticket_id = $_POST['ticket_id']; // array of tickets
		$num = count($ticket_id);
		if ($num == 0) return "No tickets selected.";
		$message = "Ticket InstaClosed";
		$status_id = 17; // closed
		
		for($i=0; $i < $num; $i++) {
			$result2 = mysqli_query($link, "UPDATE ticket SET status_id = $status_id WHERE id = ".intval($ticket_id[$i]));
			$result2 = mysqli_query($link, "INSERT INTO ticket (respons_id, hacker_id, date, message) VALUES (".intval($ticket_id[$i]).", ".$hackerdata['id'].", '$now', '$message')");
		}	
		echo '<script type="text/javascript">location.href = "'.$gameurl.'/?h=modtickets&status_id=1"</script>';
		return "redirecting...";
	}
	
	$ticket_id = 0;
	if (!empty($_POST['ticket_id'])) $ticket_id = intval($_POST['ticket_id']);
	elseif (!empty($_GET['ticket_id'])) $ticket_id = intval($_GET['ticket_id']);
	
	$im = 1; // normal member replies generate an IM
	$email = 0;
	// only mods can disable IM or enable EMAIL
	if (InGroup($hackerdata['id'], 1) || InGroup($hackerdata['id'], 2)) {
		$im = 0;
		if (!empty($_POST['im'])) $im = CheckBox($_POST['im']);
		$email = 0;
		if (!empty($_POST['email'])) $email = CheckBox($_POST['email']);
	}

	// poll answer
	$poll_answer =0;
	if (!empty($_REQUEST['poll_answer'])) $poll_answer = intval($_REQUEST['poll_answer']);
	
	if ($action == "vote") {
		if ($poll_answer != 0) {
			if ($poll_answer < 1 || $poll_answer > 2) return "Invalid poll value"; // like =1 dislike =2			
		}	
		else return "Invalid vote";
	}	

	// read ticket info
	if ($action != "post_new"){
		$result = mysqli_query($link, "SELECT ticket.*, ticket_type.type_desc, ticket_type.type, ticket_status.status, ticket_status.status_desc FROM ticket LEFT join ticket_type ON ticket.type_id = ticket_type.id LEFT JOIN ticket_status ON ticket.status_id = ticket_status.id WHERE ticket.id = $ticket_id");
		if (mysqli_num_rows($result) == 0) 
			return "Ticket not found";
		else
			$row = mysqli_fetch_assoc($result);
		$banned = IsBanned($row['hacker_id']);
		$emailaddress = mysqli_get_value ("email", "hacker", "id", $row['hacker_id']);
		$subject = "Your Ticket";
		$mail_body = 'Your ticket titled '.$row['title'].' has changed.. [<a href="?h=doreadticket&ticket_id='.$ticket_id.'">View Ticket</a>]';
	}		
	
	if ($action == "post_reply" || $action == "vote") {
		// you are not staff, so are you allowed to reply?
		if (!InGroup($hackerdata['id'], 1) && !InGroup($hackerdata['id'], 2)) {
			if ($row['status'] == 3) // closed
				return "You can not reply to this ticket because this ticket is closed";
			
			if ($row['status'] == 1 && $row['hacker_id'] != $hackerdata['id']) // new
				return "You can not reply to this ticket, because the staff has to review the ticket first.";
		}
	}
	
	if ($action == "vote") {
		$result = mysqli_query($link, "DELETE FROM poll_vote WHERE ticket_id = $ticket_id AND hacker_id = ".$hackerdata['id']); // remove old vote
		$result = mysqli_query($link, "INSERT INTO poll_vote (ticket_id, hacker_id, a_id) VALUES ($ticket_id, {$hackerdata['id']}, '$poll_answer')");
	}
	
	if ($action == "reset_vote") {
		$result = mysqli_query($link, "DELETE FROM poll_vote WHERE ticket_id = $ticket_id");
		$message = "[b]Votes were reset on this ticket.[/b]";
		$result2 = mysqli_query($link, "INSERT INTO ticket (respons_id, hacker_id, date, message) VALUES ($ticket_id, ".$hackerdata['id'].", '$now', '$message')");		
	}
	
	if ($action == "change_type" || $action == "post_new") {
		$type_id = 0;
		if (!empty($_POST['type_id'])) $type_id = intval($_POST['type_id']);
		$result = mysqli_query($link, "SELECT id, type_desc FROM ticket_type WHERE id = $type_id AND active = 1");
		if (mysqli_num_rows($result) == 0) return "Invalid type";
		else $type_desc = mysqli_get_value("type_desc", "ticket_type", "id", $type_id);
	}
	if ($action == "change_status") {
		$status_id = 0;
		if (!empty($_POST['status_id'])) $status_id = intval($_POST['status_id']);
		elseif (!empty($_GET['status_id'])) $status_id = intval($_GET['status_id']); // for closing via link
		if (!mysqli_get_value("status_desc", "ticket_status", "id", $status_id)) return "Invalid status";
		else $status_desc = mysqli_get_value("status_desc", "ticket_status", "id", $status_id);
	}
	if ($action == "post_reply" || $action == "post_new") {
		if (!empty($_POST['message'])) {
			$message = $_POST['message'];
			$message = preg_replace('#\r?\n#', '[br]', $message);
			$message = sql($message);
			$message = str_replace("[br]", "<br>", $message);
			$message = FilterTags($message, $hackerdata['id']);
		}	
		else return "Invalid message";
	}
	if ($action == "post_new" || $action == "change_title") {
		if (!empty($_POST['title'])) {
			$title = sql($_POST['title']);
			if (strlen($title) > 30) $title = substr($title, 0, 30);
		}
		else return "Invalid title";
	}
	
	if ($action == "delete_reply") {
		$reply_id = 0;
		if (!empty($_POST['reply_id'])) $reply_id = intval($_POST['reply_id']);
		elseif (!empty($_GET['reply_id'])) $reply_id = intval($_GET['reply_id']);
		$result2 = mysqli_query($link, "DELETE FROM ticket WHERE id = $reply_id");
		
		// now find the new last reply
		$result2 = mysqli_query($link, "SELECT hacker_id, date FROM ticket WHERE respons_id = $ticket_id ORDER BY date DESC LIMIT 1");
		if (mysqli_num_rows($result) > 0 ) { 
			$row2 = mysqli_fetch_assoc ($result2);
			$result3 = mysqli_query($link, "UPDATE ticket SET reply_hacker_id = ".$row2['hacker_id'].", reply_date = '".$row2['date']."' WHERE id = $ticket_id"); // update it!
		}	
	}
	if ($action == "change_status") {
		$message = "Status changed to [b]".$status_desc."[/b]";
		$result2 = mysqli_query($link, "UPDATE ticket SET status_id = $status_id WHERE id = $ticket_id");
		$result2 = mysqli_query($link, "INSERT INTO ticket (respons_id, hacker_id, date, message) VALUES ($ticket_id, ".$hackerdata['id'].", '$now', '$message')");
	}
	if ($action == "change_type") {
		$message = "Type changed to [b]".$type_desc."[/b]";
		$result2 = mysqli_query($link, "UPDATE ticket SET type_id = $type_id WHERE id = $ticket_id");
		$result2 = mysqli_query($link, "INSERT INTO ticket (respons_id, hacker_id, date, message) VALUES ($ticket_id, ".$hackerdata['id'].", '$now', '$message')");
		// delete any votes, since the type has changed, old votes no longer count
		$result2 = mysqli_query($link, "DELETE FROM poll_vote WHERE ticket_id = $ticket_id");
	}
	if ($action == "change_title") {
		$message = "Title changed to [b]".$title."[/b]";
		$result2 = mysqli_query($link, "UPDATE ticket SET title = '$title' WHERE id = $ticket_id");
		$result2 = mysqli_query($link, "INSERT INTO ticket (respons_id, hacker_id, date, message) VALUES ($ticket_id, ".$hackerdata['id'].", '$now', '$message')");
	}
	if ($action == "post_reply") {
		$result2 = mysqli_query($link, "INSERT INTO ticket (respons_id, hacker_id, date, message) VALUES ($ticket_id, ".$hackerdata['id'].", '$now', '$message')");
	}
	if ($action == "post_new") {
		$status_id = 1; // a new ticket is always new
		$ticket_id = mysqli_next_id("ticket");
		$result2 = mysqli_query($link, "INSERT INTO ticket (hacker_id, status_id, type_id, date, title, message) VALUES (".$hackerdata['id'].", $status_id, $type_id, '$now', '$title', '$message')");
		$result2 = mysqli_query($link, "UPDATE hacker SET support_tickets = support_tickets -1 WHERE id = ".$hackerdata['id']);
	}
	// inform the ticket owner
	if ($action != "post_new" && $action != "delete_reply" && $action != "vote") {	
		if ($row['hacker_id'] != $hackerdata['id']) {
			if ($email == 1 || $banned) $mailresult = SendMail($emailaddress, $subject, $mail_body); //external mail
			if ($im == 1) SendIM (0, $row['hacker_id'], $subject, $mail_body, $now); //ingame mail
		}	
	}	

	if ($action == "post_reply" || $action == "change_type" || $action == "change_title" || $action == "change_status")
		$result2 = mysqli_query($link, "UPDATE ticket SET reply_hacker_id = ".$hackerdata['id'].", reply_date = '$now' WHERE id = $ticket_id");
	echo '<script type="text/javascript">location.href = "?h=doreadticket&ticket_id='.$ticket_id.'"</script>';
?>