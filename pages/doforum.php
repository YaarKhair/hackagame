<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php
	include('modules/permissions.php');
	
	// Chatkick
	if ($hackerdata['chatkick_till'] > $now) return "You were kicked from chat and forums and are allowed to return in ".Seconds2Time(SecondsDiff($now, $hackerdata['chatkick_till']));

	// List of actions
	$actions = array("add_reply", "add_thread", "delete_thread", "delete_reply", "edit_thread");
	
	// Current action
	$action = '';
	if(!empty($_REQUEST['action'])) $action = sql($_REQUEST['action']);
	
	// Check if it exists
	if(!In_Array($action, $actions)) return "Invalid action.";
	
	if($action == "add_reply") {
		// Get thread ID
		$thread_id = 0;
		if(!empty($_REQUEST['thread_id'])) $thread_id = intval($_REQUEST['thread_id']);
		
		// Board ID
		$board_id = mysqli_get_value("board_id", "thread", "id", $thread_id);
		
		// See if you're alllowed to add a reply
		$clan_id = mysqli_get_value("clan_id", "board", "id", $board_id);
		if($clan_id > 0 && $clan_id != $hackerdata['clan_id']) return "You are not allowed to add a reply to this thread.";
		
		// Get the text
		$reply_text = '';
		if(!empty($_REQUEST['reply_text'])) $reply_text = sql($_REQUEST['reply_text']);
		
		// See if the thread is locked
		$locked = mysqli_get_value("locked", "thread", "id", $thread_id);
		if($locked == 1) return "This thread is locked and you cannot add replies to it.";
		
		// Insert the reply
		$result = mysqli_query($link, "INSERT INTO thread_reply (thread_id, hacker_id, text, creation_date) VALUES ($thread_id, {$hackerdata['id']}, '$reply_text', '$now')");
		
		PrintMessage("success", "Your reply was added to the thread.");
		$_REQUEST['thread_id'] = $thread_id;
		include('pages/forum.php');
	}
	
	if($action == "add_thread") {
		$board_id = 0;
		if(!empty($_POST['board_id']) && $_POST['board_id'] > 0) $board_id = intval($_POST['board_id']);
		
		// Get clan ID
		$clan_id = mysqli_get_value("clan_id", "board", "id", $board_id);
		
		// Check if the board exists
		$count = mysqli_get_value_from_query("SELECT COUNT(id) as count FROM board WHERE id = $board_id", "count");
		if($count == 0) return "Invalid board.";
		
		// Get thread title
		$title = '';
		if(!empty($_POST['thread_title'])) $title = sql($_POST['thread_title']);
		if(strlen($title) < 3) return "The title of the thread needs to be 3 characters at least.";
		
		// Get the thread body
		$body = '';
		if(!empty($_POST['thread_body'])) $body = sql($_POST['thread_body']);
		if(strlen($body) < 25) return "The body of the thread needs to be 25 characters at least.";
		
		// Council only
		$council_only = 0;
		if(!empty($_POST['council_only'])) $council_only = checkbox($_POST['council_only']);
		
		// Locked
		$locked = 0;
		if(!empty($_POST['locked'])) $locked = checkbox($_POST['locked']);
		
		// Pinned
		$pinned = 0;
		if(!empty($_POST['pinned'])) $pinned = checkbox($_POST['pinned']);
		
		// Check
		$can_edit = false;
		if($is_staff) $can_edit = true;
		if($clan_id > 0 && $clan_id == $hackerdata['clan_id'] && $hackerdata['clan_council'] == 1) $can_edit = true; // clan forums? council only!
		if($clan_id == 0) $can_edit = true; // public forums
		if(!$can_edit) return "You cannot add a thread in this board.";


		// Insert thread
		$result = mysqli_query($link, "INSERT INTO thread (board_id, hacker_id, title, message, council_only, locked, creation_date, pinned) VALUES ($board_id, {$hackerdata['id']}, '$title', '$body', $council_only, $locked, '$now', $pinned)");
		
		// Show success and redirect
		PrintMessage("success", "Thread successfully added.");
		$_REQUEST['board_id'] = $board_id;
		include('pages/forum.php');
	}
	
	if($action == "delete_thread") {
		$thread_id = 0;
		if(!empty($_POST['thread_id'])) $thread_id = intval($_POST['thread_id']);
		
		// Check if thread exists
		$count = mysqli_get_value_from_query("SELECT count(id) FROM thread WHERE id = $thread_id", "count(id)");
		if($count == 0) return "Invalid thread ID.";
		
		// Check if you are allowed to delete this thread
		$board_id = mysqli_get_value("board_id", "thread", "id", $thread_id);
		$clan_id = mysqli_get_value("clan_id", "board", "id", $board_id);
		$hacker_id = mysqli_get_value("hacker_id", "thread", "id", $thread_id);
		
		// Check
		$can_edit = false;
		if($is_staff) $can_edit = true;
		if($clan_id > 0 && $clan_id == $hackerdata['clan_id'] && $hackerdata['clan_council'] == 1) $can_edit = true; // clan forums. only if you are council
		if($clan_id == 0 && $hackerdata['id'] == $hackerdata) $can_edit = true; // public forums. only if you are the thread owner
		if(!$can_edit) return "You cannot delete this thread.";
		
		// Delete thread
		$result = mysqli_query($link, "DELETE FROM thread WHERE id = $thread_id");
		$result = mysqli_query($link, "DELETE FROM thread_reply WHERE thread_id = $thread_id");
		
		PrintMessage("success", "You have successfully deleted this thread.");
		unset($_REQUEST);
		$_REQUEST = array();
		$_REQUEST['board_id'] = $board_id;
		include('pages/forum.php');
	}
	
	if($action == "delete_reply") {
		$reply_id = 0;
		if(!empty($_GET['reply_id']) && $_GET['reply_id'] > 0) $reply_id = intval($_GET['reply_id']);
		
		// See if reply exists
		$count = mysqli_get_value_from_query("SELECT count(id) FROM thread_reply WHERE id = ".$reply_id, "count(id)");
		if($count == 0) return "This reply does not exist.";
		
		// Get reply hacker_id
		$hacker_id = mysqli_get_value_from_query("SELECT hacker_id FROM thread_reply WHERE id = ".$reply_id, "hacker_id");
		
		// Get clan_id
		$clan_id = mysqli_get_value("clan_id", "board", "id", mysqli_get_value("board_id", "thread", "id", mysqli_get_value("thread_id", "thread_reply", "id", $reply_id)));
		
		// Kill reply if staff
		$can_delete = false;
		if($is_staff) $can_delete = true;
		if($hacker_id == $hackerdata['id']) $can_delete = true;
		if($clan_id > 0 && $clan_id == $hackerdata['clan_id'] && $hackerdata['clan_council'] == 1) $can_delete = true;
		if($can_delete == false) return "You cannot delete this reply.";
		
		// Delete
		$result = mysqli_query($link, "DELETE FROM thread_reply WHERE id = $reply_id");
		PrintMessage("success", "Reply deleted.");
	}
	
	if($action == "edit_thread") {
		$thread_id = 0;
		if(!empty($_POST['thread_id'])) $thread_id = intval($_POST['thread_id']);
		
		// Get thread title
		$title = '';
		if(!empty($_POST['thread_title'])) $title = sql($_POST['thread_title']);
		if(strlen($title) < 3) return "The title of the thread needs to be 3 characters at least.";
		
		// Get the thread body
		$body = '';
		if(!empty($_POST['thread_body'])) $body = sql($_POST['thread_body']);
		if(strlen($body) < 25) return "The body of the thread needs to be 25 characters at least.";
		
		// Council only
		$council_only = 0;
		if(!empty($_POST['council_only'])) $council_only = checkbox($_POST['council_only']);
		
		// Locked
		$locked = 0;
		if(!empty($_POST['locked'])) $locked = checkbox($_POST['locked']);
		
		// Pinned
		$pinned = 0;
		if(!empty($_POST['pinned'])) $pinned = checkbox($_POST['pinned']);

		// Check if thread exists
		$count = mysqli_get_value_from_query("SELECT count(id) FROM thread WHERE id = $thread_id", "count(id)");
		if($count == 0) return "Invalid thread ID.";
		
		// Check if you are allowed to edit this thread
		$board_id = mysqli_get_value("board_id", "thread", "id", $thread_id);
		$clan_id = mysqli_get_value("clan_id", "board", "id", $board_id);
		$hacker_id = mysqli_get_value("hacker_id", "thread", "id", $thread_id);
		
		$can_edit = false;
		if($is_staff) $can_edit = true;
		if($clan_id > 0 && $clan_id == $hackerdata['clan_id'] && $hackerdata['clan_council'] == 1) $can_edit = true; // clan forums. only if you are council
		if($clan_id == 0 && $hackerdata['id'] == $hackerdata) $can_edit = true; // public forums. only if you are the thread owner
		if(!$can_edit) return "You cannot modify this thread.";
		
		// Update thread
		$result = mysqli_query($link, "UPDATE thread SET title = '$title', message = '$body', council_only = $council_only, locked = $locked, pinned = $pinned WHERE id = $thread_id");
		
		PrintMessage("success", "Thread successfully updated.");
		$_REQUEST['thread_id'] = $thread_id;
		include("pages/forum.php");
	}