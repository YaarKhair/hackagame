<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php
	$username = "";
	if (!empty($_POST['username'])) $username = sql($_POST['username']);
	
	$reason = "";
	if (!empty($_POST['reason'])) $reason = sql($_POST['reason']); 
	
	// can we find this user?
	$result = mysqli_query($link, "SELECT id, clan_id FROM hacker WHERE network_id = 2 AND npc = 0 AND alias = '$username'");
	if (mysqli_num_rows($result) == 0)
		return "There is no hacker connected to the internet named $username";

	else {
		$row = mysqli_fetch_assoc ($result);
		if ($row['clan_id'] == $hackerdata['clan_id']) return "This hacker is already a member of your clan";
	}	
	
	// gateway size limit
	$clansize = GetClanSize($hackerdata['clan_id']);
	// number of members
	$result2 = mysqli_query($link, "SELECT * FROM hacker WHERE clan_id = ".$hackerdata['clan_id']);
	if (mysqli_num_rows($result2) >= $clansize) 
		return "Your clan already has the maximum number of members, which is ".$clansize;
	
	// name of the clan
	$result2 = mysqli_query($link, "SELECT id, alias FROM clan WHERE id = ".$hackerdata['clan_id']);
	$row2 = mysqli_fetch_assoc($result2);
	$clan_id = $row2['id'];
	$clan_alias = $row2['alias'];
	
	
	// if all is good
	$id = mysqli_next_id("invite");
	$result = mysqli_query($link, "INSERT INTO invite (clan_id, hacker_id, inviter_id, date) VALUES (".$hackerdata['clan_id'].", ".$row['id'].", ".$hackerdata['id'].", '".$now."')");
	
	$message = "You were invited to join the clan <a href=\"index.php?h=claninfo&id=$clan_id\">$clan_alias</a><br>They attached this message: $reason<br><br>";
	$message .= "You can either <a href=\"index.php?h=invite&action=accept&id=$id\" onClick=\"return confirm('Are you sure? If you are a clan founder your old clan will die!')\">accept</a> or <a href=\"index.php?h=invite&action=decline&id=$id\">decline</a>.";
	
	SendIM ($hackerdata['id'], $row['id'], "Invite from $clan_alias", $message, $now);
	AddLog ($hackerdata['clan_id'], "clan", "im", "IM sent to $username", $now);
	
	PrintMessage ("Success", "Your invite was sent to $username", "40%"); // screen
?>
