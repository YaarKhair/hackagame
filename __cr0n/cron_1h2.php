<?php
	// this cron runs at each half hour of the day
	include("/var/www/modules/connectdb.php");
	include("/var/www/modules/settings.php");
	include("/var/www/modules/functions.php");
	include("/var/www/modules/cronspreadvirus.php");
	include("/var/www/modules/crongw.php");
	include("/var/www/modules/cronftp.php");

	/*// Each half an hour, there's a chance that every KOTR server taken might be taken back by the NPC
	$result = mysqli_query($link, "SELECT id FROM server WHERE id IN (".implode(",", $kotr['all_servers']).") AND hacker_id NOT IN (".implode(",", $kotr['all_npcs']).")");
	if(mysqli_num_rows($result) > 0) {
		while($row = mysqli_fetch_assoc($result)) {
			if(WillItWork($kotr['takeback_chance'])) restoreKOTRserver($row['id']);
		}
	}*/
	
	// delete old swissbanks.
	//$result = mysqli_query($link, "DELETE FROM swissbank WHERE expire_date < '$now'");
	// tempfix, only delete swisslinks older than 1 week
	$delete_date = date($date_format, strtotime("-7 days"));
	$result = mysqli_query($link, "DELETE FROM swissbank WHERE expire_date < '$delete_date'");
	// uncloak servers after $cloaked_time	minutes [deprecated]
	//$result = mysqli_query($link, "UPDATE server SET cloak_color = '', cloak_till = '' WHERE cloak_till > '0' AND cloak_till < '$now'");

	// who is on the most wanted list? will we find them?
	$search_date = date($date_format, strtotime("-".$fbi_safe." hours")); // they start looking for you if you are listed for at least 2 hours.
	$result = mysqli_query($link, "SELECT id, fbi_wanteddate, fbi_additional_chance FROM hacker WHERE npc = 0 AND fbi_wanteddate > 0 AND fbi_wanteddate < ".$search_date);
	if (mysqli_num_rows($result) > 0) {
		while ($row = mysqli_fetch_assoc($result)) {
			$timewanted = SecondsDiff($row['fbi_wanteddate'], $now);
			$timewanted = round($timewanted / 3600) + 1;
			$chance = ($fbi_traceyou_chance * $row['fbi_additional_chance']) * $timewanted;
			if (WillItWork($chance)) {
				// until when you are in prison
				$free = date($date_format, strtotime("+".$fbi_prisontime." hours"));
				Prison($row['id'], "You were on the FBI Most Wanted List and got caught by the FBI.", $free);
				// AFTER you are released you are xx hours in probation and can not be relisted
				$safe_till = date($date_format, strtotime("+".$fbisafe_interval+$fbi_prisontime." hours"));
				$result2 = mysqli_query($link, "UPDATE hacker SET offline_from = '$now', offline_till = '$free', fbisafe_till = '$safe_till', fbi_additional_chance = 1, restoring_minutes = 0 WHERE id = ".$row['id']); // system offline so it's not hackable and shows offline
				// you can not be hacked a little time afterwards you are released
				$safe_till = date($date_format, strtotime("+".$pc_hacksafe_interval+($fbi_prisontime * 60)." minutes"));
				$result2 = mysqli_query($link, "UPDATE hacker SET unhackable_till = '$safe_till', WHERE id = ".$row['id']);
			}
		}
	}	
	
	// fbi hourly scan of their server
	$scan_date = date($date_format, strtotime("-".	$fbiinfection_minimal." hours")); // they can only detect your virus after x hours
	$result = mysqli_query($link, "SELECT id, date, hacker_id FROM infection WHERE success = 1 AND victim_entity = 'server' AND victim_id = $fbi_serverid AND product_id = 19 AND date < ".$scan_date);
	if (mysqli_num_rows($result) > 0) {
		while ($row = mysqli_fetch_assoc($result)) {
			$timeinfected = round (SecondsDiff($row['date'], $now) / 3600); // number of hours it's installed on their servers
			$chance = $timeinfected * $fbiscan_chance; // multiply the number of hours by $fbiscan_chance (default = 5)
			if (WillItWork($chance)) 
				CleanInfection ($row['id'], "FBI virus scan");
		}
	}	
	
	// Remove infections that have been sitting for longer than 24 hours, that are NOT spreading viruses
	$before24hours = date($date_format, strtotime("- $infection_expiry hours"));
	$infection_result = mysqli_query($link, "SELECT infection.*, product.title FROM infection LEFT JOIN product ON infection.product_id = product.id WHERE infection.date < '$before24hours' AND infection.ready = 1 AND infection.spreading = 0");	
	while($infection_row = mysqli_fetch_assoc($infection_result)) {
	
		// Remove the virus
		CleanInfection ($infection_row['id'], "Virus timed out");
	
		// If it's a failed hack, we don't need to do all that's below
		if($infection_row['success'] == 0) continue;
				
		// Set the IDs
		$hacker_id = $infection_row['hacker_id'];
		$victim_id = $infection_row['victim_id'];
		
		// If the victim is a server, get the id of the owner
		$entity = $infection_row['victim_entity'];
		$virus_name = $infection_row['title'];
		
		if($entity == "server")	$log = "";
		else $log = "system";
		
		$target = Alias4Logs ($victim_id, $entity);
		
		// Add logs to the hacker and victim
		AddLog($hacker_id, 'hacker', 'hack', "TCP/IP: Connection lost with $target [$virus_name].", $now);
		AddLog($victim_id, $entity, $log, "Virus Scanner: Local Virus Scanner detected and removed a virus [$virus_name]", $now);
	}
	
	// software on the market place can be bought by NPCs
	$result = mysqli_query($link, "SELECT id, hacker_id, product_id FROM inventory WHERE server_id = $market_serverid");
	if (mysqli_num_rows($result) > 0) {
		while ($row = mysqli_fetch_assoc($result)) {
			if (WillItWork($trade_chance)) {
				$code = mysqli_get_value("code", "product", "id", $row['product_id']);
				if ($code == "TRADEMOVIES") {
					$price = mt_rand(2000,5000);
					$items = "movies";
				}	
				else {
					$price = mt_rand(2000,5000);
					$items = "software";
				}	
				BankTransfer ($row['hacker_id'], "hacker", $price, "A hacker bought $items from you at the market place.");
				$result2 = mysqli_query($link, "DELETE FROM inventory WHERE id = ".$row['id']);
			}	
		}
	}
	
	// Cron to delete bounties if a hacker is banned or if he is hibernating
	$banned_date = date($date_format, strtotime("- $keep_bounty_after_ban days"));
	$result = mysqli_query($link, "SELECT bounty.id, bounty.victim_id, bounty.contracter_id, hacker.banned_date, hacker.hybernate_till FROM bounty LEFT JOIN hacker on bounty.victim_id = hacker.id WHERE bounty.date > '$now' AND hacker.banned_date < '$banned_date' AND hacker.banned_date != 0 OR hacker.hybernate_till > '$now'"); 
	if (mysqli_num_rows($result) > 0)
		while($row = mysqli_fetch_assoc($result)) {
			$result2 = mysqli_query($link, "DELETE FROM bounty WHERE id = {$row['id']}");
			SendIM(0, $row['contracter_id'], 'Bounty Removal', 'Your bounty on '.ShowHackerAlias($row['victim_id']).' was removed because your victim is banned or is currently hibernating', $now);
		}
	
	// gateway offline? then 5% clan money is drained
	$result = mysqli_query($link, "SELECT clan.id, clan.bankaccount FROM server INNER JOIN hacker ON server.hacker_id = hacker.id INNER JOIN clan ON hacker.clan_id = clan.id WHERE clan.bankaccount > 0 AND server.gateway = 1 AND server.offline_till > '$now'");
	if (mysqli_num_rows($result) > 0)
		while ($row = mysqli_fetch_assoc($result))
			BankTransfer($row['id'], "clan", intval($row['bankaccount'] * -0.05), "Gateway Offline Fee");
	
	AddLog (0, "hacker", "cron", "cron_1h2", $now);	
?>