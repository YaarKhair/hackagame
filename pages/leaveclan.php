<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php
	$code = "";
	
	if (!empty($_GET['code'])) $code = sql($_GET['code']);
	
	if ($hackerdata['clan_id'] == 0)
		return "You are not in a clan.";
	
	if ($code != sha1($hackerdata['started'].$hackerdata['last_login'])) 
		return "You did not click a valid LeaveClan link! If you were tricked in clicking a leaveclan link, report it to the game admins!!";

	if ($hackerdata['clan_id'] > 0 && IsFounder($hackerdata['id'])) $killclan_id = $hackerdata['clan_id'];
	else $killclan_id = 0;
	
	RemoveFromClan ($hackerdata['id'], $hackerdata['clan_id'], "You left a clan");
	PrintMessage ("Info", "You left the clan.<br>You dropped all your servers, if any", "40%");
	AddLog ($hackerdata['id'], "hacker", "clan", "Left his clan", $now);
	if ($killclan_id > 0) KillClan($killclan_id, "Founder left the clan");
?>