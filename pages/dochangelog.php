<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php if(!InGroup($hackerdata['id'], 1) && !InGroup($hackerdata['id'], 2)) { AddLog($hackerdata['id'], "hacker", "abuse", "Player tried to access $page2load", $now); die(); } ?>
<?php
	$action = '';
	if(!empty($_POST['action'])) $action = sql($_POST['action']);
	
	$log_id = array();
	if(!empty($_POST['log_id'])) $log_id = $_POST['log_id'];	// it's an array
	
	$private = 0;
	if(!empty($_POST['private'])) $private = checkbox($_POST['private']);
	
	$type = '';
	if(!empty($_POST['type'])) $type = intval($_POST['type']);
	
	$details = '';
	if(!empty($_POST['details'])) $details = sql($_POST['details']);

	$ticket_id = 0;
	if(!empty($_POST['ticket_id'])) $ticket_id = intval($_POST['ticket_id']);
	
	if($action == 'delete') {
		// Sanitize input array
		$log_id = array_map('intval', $log_id);
		
		// Delete from db
		$result = mysqli_query($link, "DELETE FROM changelog WHERE id IN (".implode(",", $log_id).")");
		
		PrintMessage("Success", "You have successfully deleted ".mysqli_affected_rows($link)." log(s)");
	}
	
	if($action == 'add') {
		// Check if types exist
		if(!isset($changelog_types[$type])) return "This type does not exist";
		
		// Insert into database
		$result = mysqli_query($link, "INSERT INTO changelog (type, private, details, date, hacker_id, ticket_id) VALUES ($type, $private, '$details', '$now', {$hackerdata['id']}, $ticket_id)") or die(mysqli_error($link));
		$result = mysqli_query($link, "UPDATE misc SET lastchangelogentry_date = '$now'");
		PrintMessage("Success", "Entry successfully added");		
	}
	
	include 'pages/changelog.php';
?>