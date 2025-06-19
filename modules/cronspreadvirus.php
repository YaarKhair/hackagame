<?php	
	// fill recordset with all infected servers
	$result = mysqli_query($link, "SELECT infection.*, server.efficiency, server.id as server_id, server.hacker_id as server_hackerid FROM infection LEFT JOIN server ON infection.victim_id = server.id WHERE infection.victim_entity = 'server' AND server.npc = 0 AND infection.spreading = 1 AND infection.success = 1 AND infection.ready = 1");
	if ($result && mysqli_num_rows($result) > 0) 
	{
		while ($row = mysqli_fetch_assoc($result)) 
		{
			// descrease server health if itś infect with health killer
			if ($row['product_id'] == 79) 
			{
				// if health of server is too low, kill it
				if ($row['efficiency'] <= $serverinfected_decrease) 
				{
					DropServer($row['server_id'], $row['server_hackerid'], "Health drained to 0% due to virus");
					// reward the infecter
					RegisterResult ($row['hacker_id'], "serverskilled", $now);
					AddEP ($row['hacker_id'], $virus_dropped_ep, 0, $now, "SRV");
				}	
				// cripple it if it's not
				else $result2 = mysqli_query($link, "UPDATE server SET efficiency = efficiency - $serverinfected_decrease WHERE id = ".$row['server_id']);
			}
			
			// If the owner of the infection is in your clan, kill the infection and set the chance of spreading to 0
			$attack_clanid =  mysqli_get_value ("clan_id", "hacker", "id", $row['hacker_id']);
			$victim_clanid =  mysqli_get_value ("clan_id", "hacker", "id", $row['server_hackerid']);
			
			if($attack_clanid == $victim_clanid) 
			{
				CleanSystem($row['id'], "Virus Scanner", "server", -1);
				continue;
			}
			
			// Increase spread chance
			$perk = GetPerkValue($row['hacker_id'], "PERK_INCREASESPREADCHANCE");
			$chance = $virusspread_chance + $perk;
			
      		// which servers to check (servers that already are infected by you with a spreading virus will be skipped)
      		$infect_result = mysqli_query($link, "SELECT server.id, server.ip FROM server WHERE server.gateway = 0 AND server.hacker_id = {$row['server_hackerid']} AND server.id NOT IN (SELECT victim_id FROM infection WHERE spreading = 1 AND hacker_id = {$row['hacker_id']} AND victim_entity = 'server') ORDER BY RAND() LIMIT 4");
            
			// record found?
			if ($infect_result && mysqli_num_rows($infect_result) > 0)
			{
				while($row_infect = mysqli_fetch_assoc($infect_result)) 
				{
					if (WillItWork($chance)) 
					{
						// lets see if the server has a honeypot installed
						$result2 = mysqli_query($link, "SELECT id FROM infection WHERE victim_id = {$row_infect['id']} AND victim_entity = 'server' AND product_id = 119");
						if ($result2 && mysqli_num_rows($result2) > 0) SendIM(0, $row['server_hackerid'], "Server Virus Alert", "Your honey pot running on server ".GetServerName($row_infect['id'])." has been infected with a spreading virus! Please scan your other servers ASAP to stop the spreading.", $now);
						$result2 = mysqli_query($link, "INSERT INTO infection (hacker_id, victim_id, victim_entity, victim_ip, product_id, date, chance, success, ready, spreading) VALUES ({$row['hacker_id']}, {$row_infect['id']}, 'server', '{$row_infect['ip']}', {$row['product_id']}, '$now', $chance, 1, 1, 1)");
						AddLog ($row_infect['id'], "server", "", "Warning: Unusual file activity", $now);
					}
				}
			}
		}	
	}
?>