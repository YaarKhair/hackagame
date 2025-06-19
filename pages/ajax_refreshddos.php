<?php
	/*** The unfinished work of Sins ****/
	
	// modules
	include_once("modules/connectdb.php");
	include_once("modules/settings.php");
	include_once("modules/functions.php");
	session_start();
	
	// Get the goal id
	$pages = array(1 => "clanhackgw", 2 => "clanhackserver", 3 => "clanhackpc");
	$goal_id = 0;
	if(!empty($_GET['goal_id'])) $goal_id = intval($_GET['goal_id']);
	
	// Check if you're actually on that page, if not then fuck you
	if($hackerdata['current_page'] != $pages[$goal_id]) die('IM GONNA KILL A FUCKING KITTEN IF YOU DO THIS AGAIN');
	
	// Find the hackers
	$output = '';
	$result = mysqli_query($link, "SELECT id FROM hacker WHERE current_page = '{$pages[$goal_id]}'");
	while($row = mysqli_fetch_assoc($result)) {
		$output .= '<li>'.ShowHackerAlias($row['id']).'<br>';
		
	}
	
?>