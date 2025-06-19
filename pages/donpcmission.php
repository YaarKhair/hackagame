<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php
	if ($_SESSION['npc_mission'] != 1) return "Session error";
	$_SESSION['npc_mission'] = 0;
		
	// see if we may do a job or if we are too soon
	if ($now <= $hackerdata['nextnpc_date']) return "Please be patient. Your system is not yet ready for another contract.";
	// still in progress?
	$result = mysqli_query($link, "SELECT id FROM npc_mission WHERE hacker_id = {$hackerdata['id']}");
	if (mysqli_num_rows($result) > 0) return "Please wait until your current contract is finished entirely (reward, email, etc)";

	if ($hackerdata['fbi_wanteddate'] > 0) return "You are wanted by the FBI. We don't need that kind of attention. Go away!";
	
	$mission_ok = false;
	
	// server mission (2) or hacker (1) mission?
	if (NumServers($hackerdata['id']) == 0 || $hackerdata['network_id'] == 1) $mission_type = 1;
	else $mission_type = mt_rand(1,2); // type 1 = PC hack // type 2 = SERVER hack

	// which tools can you use, level based?
	$hacker_level = EP2Level($hackerdata['ep']);
	$result = mysqli_query ($link, "SELECT id FROM product WHERE code = 'PCHACK' AND level <= $hacker_level");
	if (mysqli_num_rows($result) == 1) $noobtool = 1; // only one tool fit for their level? give them a noobtool mission!
	else $noobtool = 0; // give them a mission with a tool they are allowed to use

	$num_attempts = 0;
	$max_attempts = 15;

	while ($mission_ok == false && $num_attempts < $max_attempts) 
	{
		if ($noobtool == 1) 
		{ 
			$mission_type = 1; 
			$goal_id = 99; 
		} 
		else
		{
			// pick a ramdom goal for the random chosen mission_type
			if ($mission_type == 1) $goal_id = mt_rand(1,5);
			else $goal_id = mt_rand(10,12);
		}
		

		$tool = 0;
		$need_server = 0;
		$num_attempts ++;
		
		if ($goal_id == 1) { $tool = 16; $extra_costs = 8000; } // kick someone offline
		if ($goal_id == 2) { $tool = 17; $extra_costs = 8000; } // steal someones money
		if ($goal_id == 3) { $tool = 36; $extra_costs = 8000; } // trash someones hdd
		if ($goal_id == 4) { $tool = 19; $extra_costs = 100000; $need_server = 1; } // remove from FBI
		if ($goal_id == 5) { $tool = 76; $extra_costs = 8000; } // inbox crusher
		
		if ($goal_id == 99) { $tool = 74; $extra_costs = 1000; } // n00btool
		
		if ($goal_id == 10) { $tool = 19; $extra_costs = 0; $need_server = 1; } // steal a server
		if ($goal_id == 11) { $tool = 79; $extra_costs = -200000; $need_server = 1; } // infect a server (negative extra costs is to stop flooding economy)
		if ($goal_id == 12) { $tool = 100; $extra_costs = 50000; $need_server = 1; } // steal the software off a server
		
		$tool_level = mysqli_get_value("level", "product", "id", $tool);
		$tool_price = mysqli_get_value("price", "product", "id", $tool);
		
		// last minute checks
		if ($hacker_level >= $tool_level && ($tool_price < $hackerdata['bankaccount'] || HasOnHDD($hackerdata['id'], $tool)) ) $mission_ok = true; // level ok and enough money for tool or tool on your hdd?
		if ($need_server && NumServers($hackerdata['id']) == 0) $mission_ok = false;
		if ($goal_id == 4 && $hacker_level < $fbi_contract_level) $mission_ok = false; // you only get FBI contracts if your level is high enough, due to the risk involved
	}
	
	if (!$mission_ok) return "We were not able to find a suitable contract for you, please make sure you have enough money for tools.";

	// create an NPC
	$alias = randomNPCname();
	$bank = round(mt_rand(0, (EP2Level(GetHackerEP($hackerdata['id'])) * 250))+ 15000); // give the npc some money
	$started = $hackerdata['started'];
	$last_click = date($date_format, strtotime("-1 days"));
	$ep = $hackerdata['ep']; // same rank as you :)
	$strength = ((EP2Level($ep) / $maxlevel) * 100) - 10; // for the server firewall and health strength is your total level progress in % - 10.
	$skill = $hackerdata['skill'];
	$ip = randomip();
	$active = 0; // so they do not show up in any stats
	$network_id = $hackerdata['network_id'];
	$ethic_id = 2; // 1 = white, 2 = grey, 3 = black

	// EASIER CONTRACTS FOR NOOBS	
	if ($goal_id == 99) 
	{
		$ep = intval($ep * 0.75); // 75% for noobnet
		$skill = intval($skill * 0.75); // 75% for noobnet
		$strength = intval($strength * 0.75); // 75% for noobnet
	}

	$expiredate = date($date_format, strtotime("+".$mission_expire." minutes"));
	$reward = round(($tool_price * 3) + $extra_costs + mt_rand(40, 500));

	$hacker_id = $hackerdata['id'];
	$fbi_wanteddate = 0;

	if ($goal_id == 1) $missiontext = "An anonymous contact wants you to kick a hacker offline. All they have is this ip: $ip";
	if ($goal_id == 2) $missiontext = "An anonymous contact wants you to steal back their money, stolen by a hacker. The last ip they have of him is $ip";
	if ($goal_id == 3) $missiontext = "An anonymous contact wants you to trash the HDD of a hacker known as $alias , which contains classified hidden data. His last known IP address is $ip";
	if ($goal_id == 4) 
	{
		$missiontext = "An anonymous contact wants you to get a friend of theirs taken off the FBI Most Wanted list. The hacker is called $alias, with IP $ip, and was caught doing a hacking job for this company. They need his name cleared or they will be compromised as well.";
		$fbi_wanteddate = $now;
	}
	if ($goal_id == 5) $missiontext = "An anonymous contact wants you to destroy the inbox of a hacker known as $alias (ip: $ip). It contains data that should not be out in the open.";
	if ($goal_id == 99) $missiontext = "Hello n00b, welcome to your training.<br>We want you to initialize a PC hack on a hacker named $alias, with IP $ip and we want you to use the n00btool.<br><strong>FOLLOW ALL FOUR STEPS!</strong><br><br>Step 1. Go to <a href=\"?h=shop&shop=software\">1338 Software</a> and buy the n00bTool.<br>Step 2. Go to <a href=\"?h=hackapc\">Hack a PC</a> and fill in the IP $ip and select the n00bTool, then initiate the hack.<br>Step 3. Keep checking your <a href=\"?h=infections\">Infections list</a>, and if the hack was succesful, EXECUTE THE VIRUS!!";

	if ($mission_type == 2) 
	{
		$server_ip = randomip(); // for the IM which contains the IP you need to attack
		$password = createrandomPassword();
		if ($goal_id == 10) $missiontext = "An anonymous contact wants you to steal back a server that was taken by a hacker.<br>The IP address of their stolen server is $server_ip , but the hacker changed the password.<br>You need to hack it and take it and HOLD IT until the end of the contract. They will take the server from you after the contract is done.";
		if ($goal_id == 11) $missiontext = "An anonymous contact wants you to infect a server that is owned by a rival company with a health draining virus.<br>The IP address of the target server is $server_ip .<br>You need to infect it before the end of the contract.";
		if ($goal_id == 12) $missiontext = "An anonymous contact wants you to steal the software that is currently running on a server that is owned by a rival company.<br>The IP address of the target server is $server_ip .<br>You need to steal it's software before the end of the contract.";
	}
	else $server_ip = ""; // for the log

	$missiontext .= "<br><br>The offer will expire at ".Number2Date($expiredate).". They will contact you after it expires to evaluate the results.";
	if ($reward > 0) $missiontext.= "<br>They are prepared to pay you $currency".number_format($reward)." if you succeed.";
	
	$result = mysqli_query($link, "SELECT id FROM hacker WHERE alias = '' AND ip = '' ORDER BY RAND() LIMIT 1"); // retired NPC's can be reused
	
	if (mysqli_num_rows($result) > 0) 
	{
		$row = mysqli_fetch_assoc($result); // replace the first unused retired NPC
		$npc_id = $row['id'];
		$query = "UPDATE hacker SET restoring_minutes = 0, activationcode = '', active = $active, alias = '$alias', started = '$started', last_click = '$last_click', bankaccount = $bank, ep = $ep, skill = $skill, ethic_id = $ethic_id, real_ip = '$ip', ip = '$ip', npc = $hacker_id, offline_from = '0', offline_till = '0', unhackable_till = '0', network_id = $network_id, infecter_id = 0, fbi_wanteddate = '$fbi_wanteddate' WHERE id = $npc_id";
		$result2 = mysqli_query($link, $query);
		$result2 = mysqli_query($link, "DELETE FROM system WHERE hacker_id = $npc_id"); // delete old system
		$result2 = mysqli_query($link, "DELETE FROM inventory WHERE hacker_id = $npc_id"); // delete old files
		$result2 = mysqli_query($link, "DELETE FROM log WHERE hacker_id = $npc_id"); // delete old logs
		$result2 = mysqli_query($link, "DELETE FROM im WHERE reciever_id = $npc_id"); // delete old ims
		// delete any server he ever owned but somehow got stuck.
		$result2 = mysqli_query($link, "SELECT id, hacker_id FROM server WHERE hacker_id = $npc_id AND id > 2500");
			if (mysqli_num_rows($result2) > 0)
				while ($row2 = mysqli_fetch_assoc($result2))
					DropServer($row2['id'], $row2['hacker_id']); // drop it like it's hoooooot..
		
	}
	else 
	{
		$npc_id = mysqli_next_id("hacker");
		$query = "INSERT INTO hacker (activationcode, active, alias, started, last_click, bankaccount, ep, skill, ethic_id, real_ip, ip, npc, network_id, infecter_id, fbi_wanteddate, offline_from, offline_till, unhackable_till) VALUES ('', $active, '$alias', '$started', '$last_click', $bank, $ep, $skill, $ethic_id, '$ip', '$ip', $hacker_id, $network_id, 0, '$fbi_wanteddate', '0', '0', '0')";
		$result2 = mysqli_query($link, $query);
	}
	
	// **************DEBUG**************
	// check if the NPC exists
	$result = mysqli_query($link, "SELECT alias, ip FROM hacker WHERE npc = $hacker_id");
	if (mysqli_num_rows($result) == 0) 
		return "An error occured. Please request a new contract";
	// **************END OF DEBUG**************
	
	// give the npc a system that equals yours, except the virusscanner for MST results.
	$result = mysqli_query($link, "SELECT system.hacker_id, system.product_id, system.efficiency, product.code AS code FROM system LEFT JOIN product ON system.product_id = product.id WHERE product.code <> 'VIRUSSCANNER' AND hacker_id = $hacker_id");
	if (mysqli_num_rows($result) > 0) 
	{
		while ($row = mysqli_fetch_assoc($result)) 
		{
			if($row['code'] == 'HDD') $efficiency = 166;
			else $efficiency = $row['efficiency'];
			$result2 = mysqli_query($link, "INSERT INTO system (hacker_id, product_id, efficiency) values ($npc_id, ".$row['product_id'].", ".($efficiency / 100) * $strength.")"); // % efficiency of the original system
		}	
	}
	// give the npc some files so we can steal them and trade them
	$num_files = mt_rand(3, 12);
	for ($i = 0; $i < $num_files; $i++) 
	{
		$type = mt_rand(1,5);
		$product_id = 0;
		if ($type == 1) $product_id = mysqli_get_value("id", "product", "code", "TRADEMOVIES", false);
		if ($type == 2) $product_id = mysqli_get_value("id", "product", "code", "TRADESOFTWARE", false);
		if ($type == 3) 
		{
			$result2 = mysqli_query($link, "SELECT id FROM product WHERE code = 'PCHACK' ORDER BY RAND() LIMIT 0,1");
			$row2 = mysqli_fetch_assoc($result2);
			$product_id = $row['id'];
		}	
		if ($product_id > 0) $result2 = mysqli_query($link, "INSERT INTO inventory (hacker_id, product_id) VALUES ($npc_id, $product_id)");
	}
/*
	// special steam key challange
	$steam[0] = "Here is part 1 of a Steam key for the game HackNet:<br>2GPZY-****-****<br><br>If you have all 3 parts, contact GA for the missing characters. The first to do this, wins the game.";
	$steam[1] = "Here is part 2 of a Steam key for the game HackNet:<br>****-PK6TG-****<br><br>If you have all 3 parts, contact GA for the missing characters. The first to do this, wins the game.";
	$steam[2] = "Here is part 3 of a Steam key for the game HackNet:<br>****-****-HAB**<br><br>If you have all 3 parts, contact GA for the missing characters. The first to do this, wins the game.";
	$rnd_key = mt_rand(0, 2);
	$result = mysqli_query ($link, "INSERT INTO file (title, text) VALUES ('steam', '$steam[$rnd_key]')");
	$last_id = mysqli_insert_id($link);
	$result = mysqli_query ($link, "INSERT INTO inventory (hacker_id, file_id, datechanged) VALUES ($npc_id, $last_id, '$now')");
*/
	// send the npc some emails, for inbox crushing missions
	if ($goal_id == 5) 
	{
		$num_mails = mt_rand(2, 4);
			for ($i = 0; $i < $num_mails; $i ++)
				$result2 = mysqli_query ($link, "INSERT INTO im (sender_id, reciever_id, date, unread, title, message) VALUES (0, $npc_id, '$now', 1, 'secret', 'This is our world now... the world of the electron and the switch, the beauty of the baud. We make use of a service already existing without paying for what could be dirt-cheap if it wasn\'t run by profiteering gluttons, and you call us criminals. We explore... and you call us criminals. We seek after knowledge... and you call us criminals. We exist without skin color, without nationality, without religious bias... and you call us criminals. You build atomic bombs, you wage wars, you murder, cheat, and lie to us and try to make us believe it\'s for our own good, yet we\'re the criminals.<br>Yes, I am a criminal. My crime is that of curiosity. My crime is that of judging people by what they say and think, not what they look like. My crime is that of outsmarting you, something that you will never forgive me for.<br>I am a hacker, and this is my manifesto. You may stop this individual, but you can\'t stop us all... after all, we\'re all alike.')");
	}
	
	// if this is a server hacking mission, then let's give the npc a server
	if ($mission_type == 2) 
	{
		// serversoftware
		$software = Array ("Spam Software", "Phishing Software", "Porn Software", "File Sharing Server Software");
		shuffle ($software);
		$server_software_id = $PRODUCT[$software[0]];
		
		// create new server
		$server_id = CreateServer($npc_id, $server_ip);
		$result2 = mysqli_query($link, "UPDATE server SET product_id = $server_software_id, hacker_id = $npc_id, npc = $hacker_id, firewall = $strength, password = '$password', efficiency = $strength, infecter_id = 0 WHERE id = $server_id");
		
		// disclose the hacker ip from the server.log
		AddLog ($server_id, "server", "", "Booting server from $ip", $now);
		AddLog ($server_id, "server", "", "{$software[0]} executed from $ip", $now);
	}
	else $server_id = 0; // for the log
	
	// give the npc an avatar
	@unlink("./uploads/hacker/".$npc_id.".jpg");
	Copy ("./images/npc.jpg", "./uploads/hacker/".$npc_id.".jpg");
	// make the actual mission goal
	$result = mysqli_query($link, "INSERT INTO npc_mission (hacker_id, npc_id, goal1_id, object1_id, reward, date) VALUES ($hacker_id, $npc_id, $goal_id, 0, $reward, $expiredate)");
	// message from system
	SendIM (0, $hacker_id, "Contract", $missiontext, date($date_format, strtotime("+1 minutes")));
	// Log the contract
	AddLog($hacker_id, "hacker" ,"contract" , "Type: $mission_type, Goal: $goal_id, Tool: $tool, NPC ID: $npc_id , NPC Name: $alias, Server ID: $server_id, Server IP: $server_ip", date($date_format, strtotime("+ 1 minutes")));
	// set the date in hacker profile
	$result = mysqli_query($link, "UPDATE hacker SET nextnpc_date = '$expiredate' WHERE id = $hacker_id");
	$_SESSION['nextnpc_date'] = $expiredate;
	// print result
	PrintMessage ("Success", "Thank you for providing your services to us. We will contact you in a few minutes via email..", "40%");
?>