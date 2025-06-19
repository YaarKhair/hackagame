<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php
	$code =0;
	if (!empty($_GET['code'])) $code = sql($_GET['code']);
	if ($code != sha1($hackerdata['started'].$hackerdata['last_login'])) return "You might have been forged into clicking this link. Please report this to the game staff";
	
	$id =0;
	if (!empty($_GET['id'])) $id = intval($_GET['id']);
	
	$action = "";
	if (!empty($_GET['action'])) $action = sql($_GET['action']);

	if ($id == 0 || $action == "")
		return "Missing values.";
	
	// in a clan?
	if ($hackerdata['clan_id'] == 0)
		return "You are not in a clan.";
	
	// are you allowed to do this?
	if ($hackerdata['clan_council'] == 0)
		return "You are not part of the clan council.";
	
	// in your clan?
	$result = mysqli_query($link, "SELECT * FROM hacker WHERE id = ".$id);
	$row = mysqli_fetch_assoc($result);
	
	if ($row['clan_id'] != $hackerdata['clan_id'])
		return "This hacker is not in your clan.";

	// kicking, demoting, etc the leader? no way!
	if (IsFounder($id))
		return "This hacker is the clan leader and can not be demoted, promoted, kicked or made leader.";
		
	// promoting someone to founder while your not the founder
	if ($action == "founder") {
		if (!IsFounder($hackerdata['id'])) {
			return "Only the current founder can appoint a new founder.";
		}
		if (NumServers($id) >= MaxServers($id)) return "The new founder needs to drop at least 1 server to be able to take over the clan gateway.";
		if (IsHybernated($id)) return "The hacker you selected is currently in hibernation and can not be appointed as the new founder.";
	}	

	// name of the clan
	$clan = mysqli_get_value ("alias", "clan", "id", $hackerdata['clan_id']);
	$alias = mysqli_get_value ("alias", "hacker", "id", $id);
	
	if ($action == "promote") {
		$result = mysqli_query($link, "UPDATE hacker SET clan_council = 1 WHERE id = ".$id);
		SendIM(0, $id, "Promoted!", "[[@".$hackerdata['alias']."]] has promoted you to the council of ".$clan, $now);
		PrintMessage ("Success", "Member promoted to clan council. IM sent to hacker.", "40%");
	}
	if ($action == "demote") {
		$result = mysqli_query($link, "UPDATE hacker SET clan_council = 0 WHERE id = $id");
		SendIM(0, $id, "Demoted!", "[[@".$hackerdata['alias']."]] has demoted you to regular member of ".$clan, $now);
		PrintMessage ("Success", "Member demoted to regular clan member. IM sent to hacker.", "40%");
	}
	if ($action == "kick") {
		RemoveFromClan ($id, $hackerdata['clan_id']);
		SendIM(0, $id, "Kicked!", "[[@".$hackerdata['alias']."]] has kicked you from ".$clan, $now);
		PrintMessage ("Success", "Member kicked from your clan. IM sent to hacker.", "40%");
	}
	if ($action == "founder") {
		$result = mysqli_query($link, "UPDATE clan SET founder_id = $id WHERE id = ".$hackerdata['clan_id']);
		$result = mysqli_query($link, "UPDATE hacker SET clan_founder = 1 WHERE id = ".$id);
		$result = mysqli_query($link, "UPDATE hacker SET clan_founder = 0 WHERE id = ".$hackerdata['id']);
		$result = mysqli_query($link, "UPDATE server SET hacker_id = $id, previous_ownerid = ".$hackerdata['id']." WHERE hacker_id = ".$hackerdata['id']." AND gateway = 1");
		SendIM(0, $id, "Clan leader!", $hackerdata['alias']." has appointed you as the new leader of ".$clan, $now);
		$donate_date = mysqli_get_value ('donater_from', 'hacker' , 'id', $id);
		$clan_avatar_link = "/uploads/clans/{$hackerdata['clan_id']}.jpg";
		$clan_avatar_size = filesize($clan_avatar_link);
		if($donate_date < date($date_format, strtotime("-".$premium_time." months")) && $clan_avatarsize >= $premium_clan_avatarsize) {
			unlink($clan_avatar_link);
			PrintMessage ("Success", "Member was appointed as the new leader. IM sent to hacker. Clan avatar was reset because the new founder is not a premium hacker.", "40%");
		}
		PrintMessage ("Success", "Member was appointed as the new leader. IM sent to hacker.", "40%");
	}

	AddLog ($hackerdata['clan_id'], "clan", "member_action", "Hacker ".$hackerdata['alias']." did a *".$action."* on ".$alias, $now);
	include ("./pages/members.php");
?>