<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php
	if ($_SESSION['dobailout'] != 1) return "Session error";
	$_SESSION['dobailout'] = 0;
		
	$id = 0;
	if (!empty($_POST['id'])) 
		$id = intval($_POST['id']);

	// check jail person details
	$query = "SELECT id, jailed_bail, alias, real_ip FROM hacker WHERE jailed_from <= '".$now."' AND jailed_till >= '".$now."' AND id = ".$id;
	$result = mysqli_query($link, $query);
	if (mysqli_num_rows($result) == 0) 
		Return "This person is not in jail.";
		
	$row = mysqli_fetch_assoc($result);
	
	// are you trying to bail yourself?
	if ($id == $hackerdata['id']) 
		Return "You can not bail yourself out.";
	// are you trying to bail yourself?

	if ($row['real_ip'] == $hackerdata['real_ip']) // && !IsWhiteListed($row['real_ip'])) 
		Return "This function is unavailable for people sharing an IP.";
	
	// check if you are in jail yourself
	if (IsJailed($hackerdata['id']))
		Return "You can not bail someone out if you are in jail yourself.";
	
	// first let's check if the hacker can afford this
	if ($hackerdata['bankaccount'] < $row['jailed_bail'])
		Return "You can't afford to bail this person out.";

	// if no bail is set this person was jailed by administration
	if ($row['jailed_bail'] == 0) 
		Return "This person is jailed by the game administration and can not be bailed out.";

	// pay the bail
	BankTransfer ($hackerdata['id'], "hacker", $row['jailed_bail'] * -1, "Jail Bail for ".$row['alias']);
	RegisterResult ($hackerdata['id'], "bails", $now);
	
	// set the hacker free
	$result = mysqli_query($link, "UPDATE hacker SET jailed_till = '$now' WHERE id = $id");
	SendIM(0, $id, "Bailed out", "You were set free because ".ShowHackerAlias($hackerdata['id'], 0, false)." has bailed you out.", $now);
		
	PrintMessage ("Success", "You bailed ".$row['alias']." out of jail.", "40%");
    
    include ("pages/jail.php");
?>
