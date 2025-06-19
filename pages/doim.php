<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php
	if (!empty($_POST['message_id'])) {
		$message_id = $_POST['message_id'];
		$num = count($message_id);
	}
	else $num = 0;
	if (!empty($_POST['action'])) $action = sql($_POST['action']);

	$redirect = "";
	$pinned = 0;
	$pinned_msg = " pinned message(s) skipped.";
	$post = "";

	if ($num == 0) return "No messages selected.";
	
	if ($action == "delete") {
		for($i=0; $i < $num; $i++) {
			// see if you are the reciever
			$result = mysqli_query($link, "SELECT sender_id, reciever_id, pinned FROM im WHERE id = ".intval($message_id[$i]));
			if (mysqli_num_rows($result) > 0) {
				$row = mysqli_fetch_assoc ($result);
				if ($row['pinned'] == 1) $pinned ++;
				if ($row['sender_id'] == $hackerdata['id'] && $row['reciever_id'] == $hackerdata['id']) {
					$result = mysqli_query($link, "DELETE FROM im WHERE pinned = 0 AND id = ".intval($message_id[$i])); // if you are the sender and receiver, we don't need to keep a copy
					$folder = "inbox";
					$redirect = "mailbox";
				}
				elseif ($row['reciever_id'] == $hackerdata['id']) {
					$result = mysqli_query($link, "UPDATE im SET reciever_del = 1, pinned = 0, unread = 0 WHERE pinned = 0 AND id = ".intval($message_id[$i])); // don't delete pinned messages
					$folder = "inbox";
					$redirect = "mailbox";
				}	
				elseif($row['sender_id'] == $hackerdata['id']) {
					$result = mysqli_query($link, "UPDATE im SET sender_del = 1 WHERE id = ".intval($message_id[$i]));
					$folder = "outbox";
					$redirect = "mailbox";
				}	
			}	
		}	
		if ($pinned > 0) $post = $pinned.$pinned_msg;
		if ($redirect != "") PrintMessage ("Success", $num-$pinned." message(s) deleted. $post", "40%");
	}
	
	if ($action == "unread") {
		for($i=0; $i < $num; $i++) {
			// see if you are the reciever
			$result = mysqli_query($link, "SELECT id FROM im WHERE reciever_id = ".$hackerdata['id']." AND id = ".intval($message_id[$i]));
			if (mysqli_num_rows($result) == 1) {
				$result = mysqli_query($link, "UPDATE im SET unread = 1 WHERE id = ".intval($message_id[$i]));
				$folder = "inbox";
				$redirect = "mailbox";
			}	
		}	
		if ($redirect != "") PrintMessage ("Success", "$num message(s) marked as unread", "40%");
	}
	if ($action == "read") {
		for($i=0; $i < $num; $i++) {
			// see if you are the reciever
			$result = mysqli_query($link, "SELECT id FROM im WHERE reciever_id = ".$hackerdata['id']." AND id = ".intval($message_id[$i]));
			if (mysqli_num_rows($result) == 1) {
				$result = mysqli_query($link, "UPDATE im SET unread = 0 WHERE id = ".intval($message_id[$i]));
				$folder = "inbox";
				$redirect = "mailbox";
			}	
		}	
		if ($redirect != "") PrintMessage ("Success", "$num message(s) marked as read", "40%");
	}
	if ($action == "pin") {
		// check if you are within the pin limits
		$result = mysqli_query($link, "SELECT id FROM im WHERE reciever_id = {$hackerdata['id']} AND reciever_del = 0 AND pinned = 1");
		if (mysqli_num_rows($result) + $num > $max_pinned) Return "You can only pin $max_pinned messages.";
		
		for($i=0; $i < $num; $i++) {
			// see if you are the reciever
			$result = mysqli_query($link, "SELECT id FROM im WHERE reciever_id = ".$hackerdata['id']." AND id = ".intval($message_id[$i]));
			if (mysqli_num_rows($result) == 1) {
				$result = mysqli_query($link, "UPDATE im SET pinned = 1 WHERE id = ".intval($message_id[$i]));
				$folder = "inbox";
				$redirect = "mailbox";
			}	
		}	
		if ($redirect != "") PrintMessage ("Success", "$num message(s) pinned", "40%");
	}
	if ($action == "unpin") {
		for($i=0; $i < $num; $i++) {
			// see if you are the reciever
			$result = mysqli_query($link, "SELECT id FROM im WHERE reciever_id = ".$hackerdata['id']." AND id = ".intval($message_id[$i]));
			if (mysqli_num_rows($result) == 1) {
				$result = mysqli_query($link, "UPDATE im SET pinned = 0 WHERE id = ".intval($message_id[$i]));
				$folder = "inbox";
				$redirect = "mailbox";
			}	
		}	
		if ($redirect != "") PrintMessage ("Success", "$num message(s) unpinned", "40%");
	}
	include ("./pages/$redirect.php");
?>