<?php
    if($_SESSION['dojail'] != 1) return "Session error.";
	
	// Bust and bail are done by a third person 
	$id = 0;
	if (!empty($_POST['id'])) 
		$id = intval($_POST['id']);
	
	$action = "";
	if(!empty($_POST['action'])) 
	$action = strtolower(sql($_POST['action']));
	
	$actions = array("bail","bust","bribe");
	if(!in_array($action,$actions)) return "Invalid action.";
	
	if($action == "bribe") {
		// Bribe is done by the person himself so no need to query anything
		// Setting a few stuff
		$bribe = $hackerdata['jailed_bail'] * 1.5;
		$chance = 50;
		
		// Doing a few checks
		if($hackerdata['nextbribe_date'] > $now) return "You already tried to bribe this police offer once. You cannot attempt it again.";
		if($hackerdata['bankaccount'] < $bribe) return "You do not have enough money to bribe the police officer.";
		if($hackerdata['jailed_till'] < $now) return "You are not in jail!";
		if($hackerdata['jailed_bail'] == 0) return "You were jailed by GA!";

		BankTransfer ($hackerdata['id'], "hacker", $bribe * -1, "Bribe for the police officer");
		
		// Everything checks out, let's see if your bribe will work
		if(WillItWork($chance)) {
			$result = mysqli_query($link,"UPDATE hacker SET jailed_till = '$now' WHERE id = {$hackerdata['id']}");
			$message = "The police officer accepted the bribe and set you free!";
		} else {
			$twice_bail = $hackerdata['jailed_bail'] * 2;
			$result = mysqli_query($link,"UPDATE hacker SET nextbribe_date = '{$hackerdata['jailed_till']}', jailed_bail = $twice_bail WHERE id = {$hackerdata['id']}");
			$message = "The police officer reported your attempt to bribe a police officer and they doubled your bail as a result. The officer however failed to report you already paid him.";
		}
	}
	
	if($action == "bail") {
		// bail is done by a third person so you need to query the stuff
		// check jail person details
		$query = "SELECT id, jailed_bail, alias, real_ip FROM hacker WHERE jailed_from <= '".$now."' AND jailed_till >= '".$now."' AND id = ".$id;
		$result = mysqli_query($link, $query);
		if (mysqli_num_rows($result) == 0) 
			Return "This person is not in jail.";
			
		$row = mysqli_fetch_assoc($result);
		
		// are you trying to bail yourself?
		if ($id == $hackerdata['id']) 
			Return "You can not bail yourself out.";
		
		// check if you are in jail yourself
		if (IsJailed($hackerdata['id']))
			Return "You can not bail someone out if you are in jail yourself.";
		
		// first let's check if the hacker can afford this
		if ($hackerdata['bankaccount'] < $row['jailed_bail'])
			Return "You can't afford to bail this person out.";

		// if no bail is set this person was jailed by administration
		if ($row['jailed_bail'] == 0) 
			Return "This person is jailed by the game administration and can not be bailed out.";
			
		// Are you on the same IP? Log a duplicate abuse
		if($hackerdata['real_ip'] == $row['real_ip']) {
			$score = 2;
			AddLog($hackerdata['id'], 'hacker', 'staff', "Duplicate: Hacker {$hackerdata['alias']} tried to bail {$row['alias']} [$score]", $now);
			AddLog($row['id'], 'hacker', 'staff', "Duplicate: Hacker {$row['alias']} was bailed by {$hackerdata['alias']} [$score]", $now);
			$result = mysqli_query($link, "UPDATE hacker SET duplicate_score = duplicate_score + $score WHERE id = ".$row['id']);
			$result = mysqli_query($link, "UPDATE hacker SET duplicate_score = duplicate_score + $score WHERE id = ".$hackerdata['id']);	
		}

		// pay the bail
		BankTransfer ($hackerdata['id'], "hacker", $row['jailed_bail'] * -1, "Jail Bail for ".$row['alias']);
		RegisterResult ($hackerdata['id'], "bails", $now);
		
		// set the hacker free
		$result = mysqli_query($link, "UPDATE hacker SET jailed_till = '$now', nextbribe_date = '$now' WHERE id = $id");
		SendIM(0, $id, "Bailed out", "You were set free because ".ShowHackerAlias($hackerdata['id'], 0, false)." has bailed you out.", $now);
		
		$message = "You bailed ".$row['alias']." out of jail.";
	}
	
	if($action == "bust") {
		// check jail person details
		$query = "SELECT id, alias, jailed_bail, real_ip FROM hacker WHERE jailed_from <= '".$now."' AND jailed_till >= '".$now."' AND id = ".$id;
		$result = mysqli_query($link, $query);
		if (mysqli_num_rows($result) == 0) 
			Return "This person is not in jail.";
			
		$row = mysqli_fetch_assoc($result);
		
		// are you trying to bust yourself?
		if ($id == $hackerdata['id']) 
			Return "You can not bust yourself out.";
		
		// check if you are in jail yourself
		if (IsJailed($hackerdata['id']))
			Return "You can not bust someone out if you are in jail yourself.";
		
		// if no bail is set this person was jailed by administration
		if ($row['jailed_bail'] == 0) 
			Return "This person is jailed by the game administration and can not be bailed out.";
			
		// Are you on the same IP? Log a duplicate abuse
		if($hackerdata['real_ip'] == $row['real_ip']) {
			$score = 2;
			AddLog($hackerdata['id'], 'hacker', 'staff', "Duplicate: Hacker {$hackerdata['alias']} tried to bust {$row['alias']} [$score]", $now);
			AddLog($row['id'], 'hacker', 'staff', "Duplicate: Hacker {$row['alias']} was busted by {$hackerdata['alias']} [$score]", $now);
			$result = mysqli_query($link, "UPDATE hacker SET duplicate_score = duplicate_score + $score WHERE id = ".$row['id']);
			$result = mysqli_query($link, "UPDATE hacker SET duplicate_score = duplicate_score + $score WHERE id = ".$hackerdata['id']);	
		}
		
		// Perk
		$perk = GetPerkValue($hackerdata['id'], "PERK_INCREASEBUST");
		$bust_chance += $perk;
		
		if (WillItWork($bust_chance)) {
			// set the hacker free
			$result = mysqli_query($link, "UPDATE hacker SET jailed_till = '$now', nextbribe_date = '$now' WHERE id = $id");
			SendIM(0, $id, "Busted out", "You were set free because ".ShowHackerAlias($hackerdata['id'], 0, false)." has busted you out.", $now);
			AddEP($hackerdata['id'], $bust_ep,0, $now, "BST");
			RegisterResult ($hackerdata['id'], "bust_win", $now);
			$message = "You broke in to the police computer system and busted out {$row['alias']}. Good job!";
		}
		else { 
			SendIM(0, $id, "Busted", ShowHackerAlias($hackerdata['id'], 0, false)." tried to bust you out, but got caught while trying.", $now);
			AddEP($hackerdata['id'], $bust_ep/2,0, $now, "BST");
			RegisterResult ($hackerdata['id'], "bust_fail", $now);
			Jail($hackerdata['id'], $bust_jailbail, $bust_jailtime, "You got caught breaking into the police computer system and were sent to jail.");
			$message = "You tried busting {$row['alias']} out of jail by hacking the police computer system. You failed and got caught!";
		}			
	}
	
	PrintMessage("Info",$message);
	include('pages/jail.php');
?>