<?php
		$query = "SELECT offline_till, banned_date, hybernate_till, ep, id, alias, ip, real_ip, jailed_bail, jailed_till, unhackable_till, npc, network_id, clan_id, prison_till FROM hacker WHERE ip = '$hacker_ip'";
		$result = mysqli_query($link, $query);
		if (mysqli_num_rows($result) == 0) return "Time out on $hacker_ip";
		
		// read victim hacker data!!
		$row = mysqli_fetch_assoc($result); 
		$hacker_id = $row['id'];
		$hacker_ip = $row['ip'];
		$hacker_firewall = GetEfficiency($hacker_id, "FIREWALL");
		$hacker_clan = $row['clan_id'];
		$hacker_alias = $row['alias'];
		
    	// hybernation?
		if ($row['hybernate_till'] > $now) return "This hacker is in hibernation and can not be hacked.";
		// banned?
		if ($row['banned_date'] > 0) return "This hacker is banned and can not be hacked.";
		// is the victim online?
		if ($row['offline_till'] > $now) return "The system you are trying to attack is offline and can not be hacked.";
		// scope
		if ($hackerdata['network_id'] > 1 && !InsideScope($hackerdata['id'], $hacker_id) && $row['npc'] == 0) return "This hacker is outside of your scope; try someone closer to your own level.";
		// too soon (goal_id is for DDOS)
		if (!isset($goal_id)) if ($hackerdata['nextpchack_date'] > $now) return "Your system is not yet ready for another pc hack.";
		// admin jailed? account is frozen
		if ($row['jailed_till'] > $now && $row['jailed_bail'] == 0) return "This user is jailed by the game administration and can not be hacked.";
		// in a group that makes you unhackable? like admin, mod or dev?
		if (IsUnhackable($row['id'])) return "This member can not be hacked due to a group membership (admin, mod, dev, etc).";	
		// is it you?
		if ($row['id'] == $hackerdata['id']) return "You can not hack yourself.";
		// a virusscan in progress
		//if ($hackerdata['scan_till'] > $now) return "Your system is currently being scanned for virusses. Please wait until the scan is completed.";
		// tool checks (ddos does it's own checks, and for some attacks requires multiple products)
		if (!isset($goal_id))  {
			if (!HasOnHDD($hackerdata['id'], $product_id)) return "You do not have this tool on your HDD.";
			// does your level permit you to use this tool?
			if (!AllowedUseProduct($hackerdata['id'], $product_id)) return "Your level does not permit you to use this tool.";
			// is your victim leveled high enough for this tool?
			if (!AllowedUseProduct($hacker_id, $product_id)) return "Your victims level is too low for you to use this tool.";
			// n00btool? only on n00bnet
			if ($product_id == 74 && $row['network_id'] != 1) return "This tool can only be used on victims that are connected to n00bNET";
			// is this a PC hack tool?
			if (mysqli_get_value("code", "product", "id", $product_id) != "PCHACK") return "Wrong tool for the job.";
		}
		// in prison, or just got out??
		if ($row['unhackable_till'] > $now) return "The system you're trying to attack is currently unavailable. The system is currently under investigation by the FBI.<br><br>They are releasing the pc in ".Seconds2Time(SecondsDiff($now, $row['unhackable_till']));
		// a noob? only a noob may attack a noob
		if (EP2Level($row['ep']) < $noob_level && EP2Level($hackerdata['ep']) >= $noob_level && $row['npc'] == 0) return "Pick someone your own size. Attacking n00bs is for the weak.";
		// are you connected to the same network as your victim? (we make an exception for NPCs. if you get a noobnet contract but leave noobnet during your contract, this will prevent an error)
		if ($hackerdata['network_id'] != $row['network_id'] && $row['npc'] != $hackerdata['id']) return "You are not connected to the same network as your victim.";
		// clan mate?
		if ($hackerdata['clan_id'] > 0 && $row['clan_id'] == $hackerdata['clan_id']) return "You can not hack your clan members";
		// you're on internet and attacking an NPC? is it for you?
		if ($hackerdata['network_id'] == 2 && $row['npc'] != $hackerdata['id'] && $row['npc'] > 0) return "This NPC can not be hacked by you.";
		// are you on n00bnet? then you can only attack the NPCs, but it does not matter which one.
		if ($hackerdata['network_id'] == 1 && $row['npc'] == 0) return "When connected to n00bnet you can only attack NPCs. Consult the n00bManual on the public forums.";
		// are you on a shared real ip? then you can not hack this person
		if ($hackerdata['real_ip'] == $row['real_ip']) return "You are sharing an internet connection with the person you are attacking. Hacking eachother is not permitted.";
?>