<?php
	/* CRONNED HACKS */
	$result = mysqli_query($link, "SELECT * FROM infection WHERE date < '$now' AND ready = 0");
	if (mysqli_num_rows($result) > 0) {
		while ($row = mysqli_fetch_assoc($result)) {
			// data about this hack
			//$code = mysqli_get_value ("code", "product", "id", $row['product_id']);
			$title = mysqli_get_value ("title", "product", "id", $row['product_id']);
			$attacker_ip = mysqli_get_value ("ip", "hacker", "id", $row['hacker_id']); // ip of attacker
			$attacker_alias = mysqli_get_value ("alias", "hacker", "id", $row['hacker_id']); // alias of attacker
			$last_click = mysqli_get_value ("last_click", "hacker", "id", $row['hacker_id']); // last click. you don't get EP on failed hacks if the victim was inactive. EP for succeeded hacks is in dovirus.
			$victim_id = $row['victim_id'];
			$entity = $row['victim_entity'];
			$victim_ip = mysqli_get_value ("ip", "hacker", "id", $victim_id);
			//$spreading = mysqli_get_value ("spreading", "product", "id", $row['product_id']);
			
			//if (GetPerkValue($row['hacker_id'], "PERK_HIDEFAILIP") == 1) $attacker_ip = 'GH0ST.1N.TH3.W1R3S';
			
			if ($entity == "hacker")  {
				$ep_entity = "PC";
				$log = "system";
				$stats = "pchack_fail";
				$win_log = "Illegal Access Detected. System might be compromised. Logged: **REMOVED BY HACKER**";
				$fail_log = "Illegal Access Detected. System not compromised. Logged: $attacker_ip";
				
				// Damage the firewall
				$firewall_victim = mysqli_get_value_from_query("SELECT efficiency FROM system WHERE hacker_id = {$row['victim_id']} AND product_id IN (SELECT id FROM product WHERE code = 'FIREWALL')", "efficiency");
				$perk = GetPerkValue($row['victim_id'], "PERK_LESSFWDECREASE");
				$hacksystem_decrease -= $perk;
				if ($firewall_victim > $hacksystem_decrease) $fw_update_result = mysqli_query($link, "UPDATE system SET efficiency = efficiency - ".$hacksystem_decrease." WHERE product_id IN (SELECT id FROM product WHERE code = 'FIREWALL') AND hacker_id = ".$row['victim_id']);

			}
			else {
				$ep_entity = "SRV";
				$log = "";
				$stats = "serverhack_fail";
				$win_log = "Warning: High TCP/IP traffic, Logged: **REMOVED BY HACKER**";
				$fail_log = "Warning: High TCP/IP traffic, Logged: $attacker_ip";
				
				switch ($victim_id) {
					case $fbi_serverid:
						break;
					case $shop_serverid:
						break;
					default:
						$server_firewall = mysqli_get_value ("firewall", "server", "id", $victim_id);
						if ($server_firewall > $hacksystem_decrease) $fw_update_result = mysqli_query($link, "UPDATE server SET firewall = firewall - $hacksystem_decrease WHERE id = $victim_id");
	 					// in some cases people can take a server while having an infection pending on it. 
	 					// this would cause them to infect their own server. we don't want that.
	 					if ( GetServerOwner($victim_id) == $row['hacker_id']) continue; // skip this current loop
				}
			}

			$target = Alias4Logs ($victim_id, $entity, true); // don't reveal potential new IP
			
			// a succesful infection
			if ($row['success'] == 1) {
				// victim log
				AddLog ($victim_id, $entity, $log, $win_log, $now);
				// attacker log
				AddLog ($row['hacker_id'], "hacker", "hack", "$title successfully installed on $target", $now);
				// set the attacker as the last infecter on this entity
				$result2 = mysqli_query($link, "UPDATE $entity SET infecter_id = {$row['hacker_id']}, infecter_ip = '$attacker_ip' WHERE id = {$row['victim_id']}"); // $row['victim_id'] is either a hacker_id or a server_id
			}
			else {
				// victim log
				AddLog ($victim_id, $entity, $log, $fail_log, $now);
				// attacker log
				AddLog ($row['hacker_id'], "hacker", "hack", "$title NOT installed on $target", $now);

				// only give EP/HP when not inactive
				$inactive = date($date_format, strtotime("-".$no_epdays." days"));
				// only give EP/HP when the restore timer is below 3x restore time
				$restore_timer = mysqli_get_value ("restoring_minutes", "hacker", "id", $victim_id);
				if ($restore_timer < $restore_time * 3) $toomany_hacks = false;
				else $toomany_hacks = true; // this player has already been hacked 3 times and has not been online since

				if($last_click > $inactive && !$toomany_hacks) {
					$chance = $row['chance']; // see how big the chance was
					$ep_fail = round(GainEP($chance) / 10);
					$skill_fail = round(GainSkill($chance) / 10);
					AddEP ($row['hacker_id'] , $ep_fail, $skill_fail, $now, $ep_entity);
					RegisterResult ($row['hacker_id'], $stats, $now);
				}
			}
			// set this hack as handled (ready = 1)
			$result2 = mysqli_query($link, "UPDATE infection SET ready = 1 WHERE id = {$row['id']}");
		}	
	}	
?>