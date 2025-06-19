<?php
	if ($_SESSION['dobailout'] != 1) return "Session error";
	$_SESSION['dobailout'] = 0;
	
	$id = 0;
	if (!empty($_POST['id'])) 
		$id = intval($_POST['id']);

	// check jail person details
	$query = "SELECT id, alias, jailed_bail, real_ip FROM hacker WHERE jailed_from <= '".$now."' AND jailed_till >= '".$now."' AND id = ".$id;
	$result = mysqli_query($link, $query);
	if (mysqli_num_rows($result) == 0) 
		Return "This person is not in jail.";
		
	$row = mysqli_fetch_assoc($result);
	
	// are you trying to bail yourself?
	if ($id == $hackerdata['id']) 
		Return "You can not bust yourself out.";
	
	// are you trying to bail someone who is active on the same IP as you?
	if ($row['real_ip'] == $hackerdata['real_ip']) // && !IsWhiteListed($row['real_ip'])) 
		Return "This function is unavailable for people sharing an IP.";

	// check if you are in jail yourself
	if (IsJailed($hackerdata['id']))
		Return "You can not bust someone out if you are in jail yourself.";
	
	// if no bail is set this person was jailed by administration
	if ($row['jailed_bail'] == 0) 
		Return "This person is jailed by the game administration and can not be bailed out.";
	
	if (WillItWork($bust_chance)) {
		// set the hacker free
		$result = mysqli_query($link, "UPDATE hacker SET jailed_till = '$now' WHERE id = $id");
		SendIM(0, $id, "Busted out", "You were set free because ".ShowHackerAlias($hackerdata['id'], 0, false)." has busted you out.", $now);
		PrintMessage ("Success", "You broke in to the police computer system and busted out {$row['alias']}. Good job!");
		AddEP($hackerdata['id'], $bust_ep,0, $now, "BST");
		RegisterResult ($hackerdata['id'], "bust_win", $now);
        include ("pages/jail.php");
	}
	else { 
		PrintMessage("Error", "You tried busting {$row['alias']} out of jail by hacking the police computer system. You failed and got caught!");
		SendIM(0, $id, "Busted", ShowHackerAlias($hackerdata['id'], 0, false)." tried to bust you out, but got caught while trying.", $now);
		AddEP($hackerdata['id'], $bust_ep/2,0, $now, "BST");
		RegisterResult ($hackerdata['id'], "bust_fail", $now);
		Jail($hackerdata['id'], $bust_jailbail, $bust_jailtime, "You got caught breaking into the police computer system and were sent to jail.");
	}			
?>