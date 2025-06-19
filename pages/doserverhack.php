<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php
	if ($_SESSION['doserverhack'] != 1) return "Session error";
	$_SESSION['doserverhack'] = 0;
	
	// read the tool
	if (!empty($_POST['product_id'])) $product_id = intval($_POST['product_id']);
	else return "Error. Did you select a tool?";
	
	// Overlock
	if (!empty($_POST['overclock'])) $overclock = CheckBox($_POST['overclock']);
	else $overclock = 0;


	// tool specific checks
	if (mysqli_get_value("code", "product", "id", $product_id) != "SERVERHACK") return "Wrong tool for the job.";
	if (!HasOnHDD($hackerdata['id'], $product_id)) return "You do not have this tool on your HDD.";
	else if (!AllowedUseProduct($hackerdata['id'], $product_id)) return "Your level does not permit you to use this tool.";
	
	// too soon!
	if ($now <= $hackerdata['nextserverhack_date']) return "Your system is not yet ready for another server hack.";
	
	// still on n00bnet?
	if ($hackerdata['network_id'] != 2) return "You are not connected to the internet.";
	
	// is your own gateway online? no gateway, no server attacks
	if (!IsOnlineServer(GetGateway($hackerdata['clan_id']))) return "Your clans' gateway is offline!";
	
	// do you own a server? (not a gateway!)
	if (NumServers($hackerdata['id']) == 0 || (NumServers($hackerdata['id']) == 1 && IsFounder($hackerdata['id']))) return "You need to own at least one server (not a gateway) to initiate the attack from.";
	
    if (!CorrectFormHash("srv1", $_POST[$_SESSION['srv1_field']])) return "Wrong hash!";
/* CALCULATE CHANCE */

	$extra_chance = 0;

	// Overlock?
	if($overclock == true) {
		if($hackerdata['next_overclock'] > $now) return "You are only allowed to overclock once every 24 hours.";
		$extra_chance += $overclock_increase_chance;
	}

	// Perk that adds chance
	$perk = GetPerkValue($hackerdata['id'], "PERK_SERVERHACK");
	$extra_chance += $perk;

	$server_ip = "";
	if (!empty($_POST['server_ip'])) $server_ip = sql($_POST['server_ip']);
	$shop_serverip = mysqli_get_value("ip", "server", "id", $shop_serverid); // current ip of shop server

	switch ($server_ip) {
	
		case $hackerdata['fbi_serverip']:
			// WE ARE ATTACKING THE FBI SERVER
			if ($product_id != 19) return "You can only use a Brute Force Password Cracker on this server.";
			$server_id = $fbi_serverid;
			$hacker_clan = 0; // not in your clan
			$chance =  BattleSysPvF ($hackerdata['id'], $extra_chance);
			break;
		
		case $shop_serverip:
			// WE ARE ATTACKING THE SHOP SERVER
			if ($product_id != 98) return "You can only use a Server.log Stealer on this server.";
			$server_id = $shop_serverid;
			$hacker_clan = 0; // not in your clan
			$chance =  BattleSysPvF ($hackerdata['id'], $extra_chance);
			break;
		
		default:
			$server_id = mysqli_get_value("id", "server", "ip", $server_ip, false);
			// misc server hack checks
			$return_value = include_once("./pages/_inc_serverhackchecks.php");
			if ($return_value != 1) return $return_value;
			//if(in_array($server_id, $kotr['all_servers'])) if($product_id != 19) return 'You can only use a BFPC on KOTR';
			$chance = BattleSysPvS ($hackerdata['id'], $server_id, $extra_chance);
	}
	
	// Increase timers if overclock (this is done here, not above, because server hack checks need to be done first)
	if($overclock == true) {
		$server_hack_interval += $overclock_increase_time_server;
		$after1day = date($date_format, strtotime("+ $overclock_next_overclock_hours hours"));
		$overclock_update = mysqli_query($link, "UPDATE hacker SET next_overclock = '$after1day' WHERE id = {$hackerdata['id']}");
		
		// Now also increase his pc timer
		$pc_timer = $hackerdata['nextpchack_date'];
		if($pc_timer == 0 || $now > $pc_timer) $pc_timer = $now;
		$pc_timer = date($date_format, strtotime("$pc_timer + $overclock_increase_time_pc minutes"));
		$pc_timer_result = mysqli_query($link, "UPDATE hacker SET nextpchack_date = '$pc_timer' WHERE id = {$hackerdata['id']}");
	}

	$target = Alias4Logs ($server_id, "server");
	if (InGroup($hackerdata['id'], 1)) $chance = 100; // admins cheat

/* END OF CHANCE CODE */

    
	// bruteforce or infect
	DeleteFromInventory($hackerdata['id'], $product_id);	
	
	// calculate how long it will take
	$duration = $server_hacktime - round(GetEfficiency ($hackerdata['id'], "INTERNET") / $internet_divider); // min 0, max 80
	$hack_active = date($date_format, strtotime("+".$duration." minutes")); // when the hack result is active
		
	// how long must a hacker wait for next attack after this one?
	$next_date = date($date_format, strtotime("+".$server_hack_interval." minutes"));
	$result = mysqli_query($link, "UPDATE hacker SET nextserverhack_date = '".$next_date."' WHERE id = ".$hackerdata['id']);
	$_SESSION['nextserverhack_date'] = $next_date;

	// hack
	$product_title = mysqli_get_value("title", "product", "id", $product_id);
	AddLog ($hackerdata['id'], "hacker", "hack", "$product_title initiated on $target", $now);
	
	if ($product_id == 19)
		PrintMessage ("Success", "Connecting to $server_ip ... [OK]<br>Initiating rainbow tables [OK]<br>Brute Force attack initiated on $target [OK]");
	else
		PrintMessage ("Success", "Connecting to $server_ip ... [OK]<br>Initiating Remote Virus Installer [OK]<br>Remote Virus Installer initiated on $target [OK]");

	if (WillItWork($chance)) {
		$success = 1;
		// lets see if the server has a honeypot installed
		$owner_id = mysqli_get_value("hacker_id", "server", "id", $server_id);
		$result2 = mysqli_query($link, "SELECT id FROM infection WHERE victim_id = $server_id AND victim_entity = 'server' AND product_id = 119");
		if ($result2 && mysqli_num_rows($result2) > 0) SendIM(0, $owner_id, "Server Virus Alert", "Your honey pot running on server ".GetServerName($server_id)." has been infected with a spreading virus! Please scan your other servers ASAP to stop the spreading.", $now);
  }	
	else {
		$success = 0;			
		// jailed when failed?
		if (GetJailed($hackerdata['id'], $hack_jailchance))
			Jail($hackerdata['id'], $hack_jailbail, $hack_jailtime, "You got caught trying to hack your way into a server and were sent to jail.");
	}	

	// insert the hack into the crontable
	$result2 = mysqli_query($link, "INSERT INTO infection (hacker_id, victim_id, victim_entity, victim_ip, product_id, date, chance, success, ready) VALUES ({$hackerdata['id']}, $server_id, 'server', '$server_ip', $product_id, '$hack_active', $chance, $success, 0)");
	include ("./pages/infections.php");
?>