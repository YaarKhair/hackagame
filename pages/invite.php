<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php
	if ($hackerdata['clan_id'] > 0) if (!IsOnlineServer(GetGateway($hackerdata['clan_id']))) return "You can only accept or decline if your gateway is online.";
	$id = 0;
	if (!empty($_GET['id'])) $id = intval($_GET['id']);
	
	$action = sql($_GET['action']);
	
	if ($action == "delete") {
		// delete the invite
		$result = mysqli_query($link, "DELETE FROM invite WHERE id = ".$id." AND clan_id = ".$hackerdata['clan_id']);
		PrintMessage ("Success", "Invite revoked", "40%");
	}
	if ($action == "accept") {
		// are you allowed to hop clans already?
		$minutes = intval(SecondsDiff($now, $hackerdata['nextinvite_date']) / 60);
		if ($now < $hackerdata['nextinvite_date']) return "You need to wait $minutes minutes before you can join a new clan.";

		// is there an invite for you?
		$result = mysqli_query($link, "SELECT * FROM invite WHERE id = ".$id." AND hacker_id = ".$hackerdata['id']);
		if (mysqli_num_rows($result) == 0) return "You are not invited to join this clan, or this invite was revoked.";

		$row = mysqli_fetch_assoc($result);
			
		if ($hackerdata['clan_id'] > 0 && IsFounder($hackerdata['id'])) $killclan_id = $hackerdata['clan_id'];
		else $killclan_id = 0;
			
		// if you're joining, the clan must not be over their member limit
		$result2 = mysqli_query($link, "SELECT id FROM hacker WHERE clan_id = ".$row['clan_id']);
		$nummem = mysqli_num_rows($result2);
		if ($nummem >= GetClanSize($row['clan_id'])) 
			return "This clan has reached its maximum number of members. You can not join it.";
				
		// update the hacker record
		$nextinvite_date = date($date_format, strtotime("+".$invite_interval." minutes"));
		$result = mysqli_query($link, "UPDATE hacker SET nextinvite_date = '$nextinvite_date', previous_clanid = ".$hackerdata['clan_id'].", clan_id = ".$row['clan_id'].", clan_council = 0 WHERE id = ".$hackerdata['id']);
		
		// if you had a gateway, reset it to a normal server
		$result = mysqli_query($link, "UPDATE server SET gateway = 0, product_id = 0 WHERE gateway = 1 AND hacker_id = ".$hackerdata['id']);

		// if you were the founder, kill your clan
		if ($killclan_id > 0) KillClan($killclan_id, "Founder left. Clan died.");
				
		// notify the inviter
		SendIM(0, $row['inviter_id'], "Your Invite", $hackerdata['alias']." has accepted the invite to join your clan.", $now);

		// delete the invite
		$result = mysqli_query($link, "DELETE FROM invite WHERE id = ".$id);
		PrintMessage ("Success", "You accepted the invite and are now member of this clan.", "40%");
	}
	if ($action == "decline") {
		// is there an invite for you?
		$result = mysqli_query($link, "SELECT * FROM invite WHERE id = ".$id." AND hacker_id = ".$hackerdata['id']);
		if (mysqli_num_rows($result) == 0) return "You are not invited to join this clan, or this invite was revoked.";
		
		$row = mysqli_fetch_assoc($result);
		
		// notify the inviter
		SendIM(0, $row['inviter_id'], "Your Invite", $hackerdata['alias']." has declined the invite to join your clan.", $now);

		// delete the invite
		$result = mysqli_query($link, "DELETE FROM invite WHERE id = ".$id." AND hacker_id = ".$hackerdata['id']);
		PrintMessage ("Success", "You declined the invite to join this clan.", "40%");
	}
?>