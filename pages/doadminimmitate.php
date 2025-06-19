<?php
	if(!InGroup($hackerdata['id'], 1) && !isset($_SESSION['immitator_id'])) return "Page not found.";
	
	$hacker_id = 0;
	if(!empty($_REQUEST['hacker_id'])) $hacker_id = intval($_REQUEST['hacker_id']);
	
	$action = '';
	if(!empty($_REQUEST['action'])) $action = sql($_REQUEST['action']);
	
	if($action == 'immitate') {
		$result = mysqli_query($link, "SELECT id FROM hacker WHERE id = $hacker_id");
		if(mysqli_num_rows($result) == 0) return "Hacker not found.";
		else {
			// Start immitating him
			$_SESSION['hacker_id'] = $hacker_id;
			$_SESSION['immitator_id'] = $hackerdata['id'];
			
			// Disable session hijacking
			$_SESSION['hijacking'] = false;
			
			PrintMessage("Success", "Immitation session has begun");
		}
	}
	
	if($action == 'endimmitate') {
		$_SESSION['hacker_id'] = $_SESSION['immitator_id'];
		$_SESSION['hijacking'] = true;
		unset($_SESSION['immitator_id']);
		
		PrintMessage("Success", "Immitation successfully ended");
	}
?>