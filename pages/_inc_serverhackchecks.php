<?php
	// WE ARE ATTACKING ANOTHER PLAYER'S SERVER
	$result = mysqli_query($link, "SELECT server.*, hacker.ep, hacker.hybernate_till, hacker.alias, hacker.clan_id, hacker.banned_date, hacker.jailed_bail, hacker.jailed_till, hacker.real_ip, hacker.npc FROM `server` LEFT JOIN `hacker` ON server.hacker_id = hacker.id WHERE server.hacker_id <> 0 AND server.ip = '".$server_ip."'");
	if (mysqli_num_rows($result) == 0) return "Connection timeout on IP $server_ip.";
	
	// fill row with serverinfo
	$row = mysqli_fetch_assoc($result); 
	$server_id = $row['id'];
	$server_ip = $row['ip'];
	$server_password = $row['password'];
	$server_firewall = $row['firewall'];
	$server_name = Alias4Logs ($row['id'], "server");
	$server_passdate = $row['pass_date'];

	$hacker_id = $row['hacker_id'];
	$hacker_ip = $row['real_ip'];
	$hacker_clan = $row['clan_id'];
	$npc = $row['npc'];

	// scope
	if(!isset($checkscope)) $checkscope = true;
	if(!isset($checkrange)) $checkrange = true;
	if(!isset($checknpcowner)) $checknpcowner = true;
	
	// Is this for KOTR Tournament?
	/*if(in_array($server_id, $kotr['all_servers'])) {
	
		// Did your clan win KOTR last, well you can't play.
		if($hackerdata['clan_id'] == mysqli_get_value_from_query("SELECT clan_id FROM log WHERE event = 'kotr' ORDER BY date DESC LIMIT 1", "clan_id")) return "Your clan won the KOTR last and you cannot play this round.";
 		 		
 		// Disable scope and range check
		$checkscope = false;
		$checkrange = false;
		$checknpcowner = false;
		
		// Find out in which tier is the server
		$tier = 0;
		if(in_array($server_id, $kotr['tier3_servers'])) $tier = 3;
		if(in_array($server_id, $kotr['tier2_servers'])) $tier = 2;
		if(in_array($server_id, $kotr['tier1_servers'])) $tier = 1;
		
		// Find out how many servers he has per tier
		$tier_arr = GetServersPerTier($hackerdata['clan_id']);
				
		// If it's in tier 2 and you don't have a server in tier 3, error
		$canhack = false;
		if($tier == 3) $canhack = true;
		if($tier == 2 && $tier_arr['tier3_servers'] >= $kotr['min_tier_servers']) $canhack = true;
		if($tier == 1 && $tier_arr['tier2_servers'] >= $kotr['min_tier_servers'] && $tier_arr['tier3_servers'] >= $kotr['min_tier_servers']) $canhack = true;
		if(!$canhack) return 'You are not allowed to hack this server yet!';
	}*/
	
	// Disable scope for NPCs
	if($npc > 0) $checkscope = false;
	
	// if the server you are attacking is touching a gateway, or it is a gateway, then disable the scope check
	if ($checkrange) 
		if (InRange ($server_id, "gateway", 1) || $row['gateway'] == 1 || $npc > 0) $checkscope = false; // servers touching a gw are not protected by scope
	
	// scope
	if ($checkscope)
		if (!InsideScope($hackerdata['id'], $hacker_id)) return "This hacker is outside of your scope; try someone closer to your own level.";
	
	// VICTIM CHECKS
	if ($row['npc'] == 0) {
		// in a group that makes you unhackable? like admin, mod or dev?
		if (IsUnhackable($row['hacker_id'])) return "This member can not be hacked due to a group membership (admin, mod, dev, etc).";	
		// is the server hackable yet?
		if ($now <= $row['unhackable_till']) return "This server was recently under attack of hackers and is currently being inspected by the FBI Cybercrime Division.<br><br>They are releasing the server in ".Seconds2Time(SecondsDiff($now, $row['unhackable_till']));
		// is the hacker owning it in hybernation?
		if ($row['hybernate_till'] > $now) return "The hacker who owns this server is in hibernation.";
		// attacking a noob?
		if (EP2Level($row['ep']) < $noob_level && EP2Level($hackerdata['ep']) >= $noob_level) return "Pick someone your own size. Attacking n00bs is for the weak.";
		// is the hacker banned?
		if ($row['banned_date'] > 0) return "The owner of this server is banned. This server will be dropped automatically.";
		// admin jailed? account is frozen
		if ($row['jailed_till'] > $now && $row['jailed_bail'] == 0) return "This user is jailed by the game administration and can not be hacked.";
		// is it a gateway? this can not be hacked by 1 person
		if ($row['gateway'] == 1) {
			if (!isset($goal_id)) {
				// normal server hack
				if ($product_id > 0) return "This is a gateway and can not be hacked by 1 person.";
				else return "You can not reset a gateway password.";
			}	
			else {
				// ddos attack
				if ($goal_id != 1) return "You can not do a bruteforce attack on a gateway.";
			}
		}
		else {
			if (isset($goal_id)) {
				// a gateway attack on a normal server? NO SIR!
				if ($goal_id == 1) return "This is not a gateway!";
			}	
		}	
		// you can only attack a clanmates server if you want to reset the password and take it.
		if ($hacker_clan == $hackerdata['clan_id'] && ($product_id > 0 || isset($goal_id))) return "You can not attack servers of your own clan.";
		// are you on a shared real ip? then you can not hack this person
		if ($hackerdata['real_ip'] == $hacker_ip && $product_id > 0) return "You are sharing an internet connection with the person you are attacking. This is not allowed.";
	}
	else {
		// is this the server of an NPC? if so, is it your NPC?
		if($checknpcowner) 
			if ($row['npc'] != $hackerdata['id']) return "This NPC can not be hacked by you.";
	}	
?>