<?php If(!InGroup($hackerdata['id'], 1) && !InGroup($hackerdata['id'], 2)) { AddLog($hackerdata['id'], "hacker", "abuse", "Tried to open a mod page: $page2load"); die(); } ?>
<?php
	// Get value
	$ids = array();
	if(!empty($_POST['id'])) $ids = $_POST['id'];
	
	// Get action
	$action = array("approve", "disapprove");
	if(!empty($_POST['action'])) $act = strtolower(sql($_POST['action']));
	
	if(!in_array($act, $action)) PrintMessage("Error", "Invalid action.");
	
	// Sanitize array
	$ids = array_map('intval', $ids);
	
	// Action Dependent
	if($act == 'approve') $query = "UPDATE captcha_images SET approved = 1 WHERE id IN (".implode(",", $ids).")";
	else if($act == 'disapprove') $query = "DELETE FROM captcha_images WHERE id IN (".implode(",", $ids).")";
	
	// Perform query
	$result = mysqli_query($link, $query);
	$num = mysqli_affected_rows($link);
	if($act == 'approve') $extra = '['.implode(", ", $ids).']';
	else $extra = '';
	AddLog(0, "hacker", "captcha_bot", $hackerdata['alias']." Successfully {$act}d ".$num." $extra", $now);
	PrintMessage("Success", "You have successfully {$act}d ".$num." images.");
?>