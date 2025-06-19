<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php
	// Get the infection ID and action
	$infection_id = 0;
	if(!empty($_GET['infection_id'])) $infection_id = intval($_GET['infection_id']);
	
	$action = '';
	if(!empty($_GET['action'])) $action = sql($_GET['action']);
	
	// Set an array of valid actions
	$valid_actions = array("execute", "uninstall", "removefailed", "removeallfailed", "uninstallallinfections");
	
	// Is your action valid?
	if(!in_array($action, $valid_actions)) return 'Invalid action.';
	
	// Prepare the query for uninstall, remove failed, remove all failed and uninstall all infections
	$base_query = "DELETE FROM infection WHERE ready = 1 AND hacker_id = {$hackerdata['id']} ";
	$extra_queries = array("uninstall" => "AND success = 1 AND id = $infection_id",
	"uninstallallinfections" => "AND success = 1",
	"removefailed" => "AND success = 0 AND id = $infection_id",
	"removeallfailed" => "AND success = 0");
	
	// Execute the query
	if(isset($extra_queries[$action])) {
		$query = $base_query.$extra_queries[$action];
		$result = mysqli_query($link, $query);
		PrintMessage('Success', 'Action completed successfully.');
	}
	
	// Handling the virus if it was an execution
	if($action == 'execute') {
	
		// 1. Fill a recordset with the information about the infection
		$infection_result = mysqli_query($link, "SELECT infection.*, hacker.ip FROM infection LEFT JOIN hacker ON infection.hacker_id = hacker.id WHERE infection.hacker_id = {$hackerdata['id']} AND infection.success = 1 AND infection.ready = 1 AND infection.id = $infection_id");
        if (!$infection_result || mysqli_num_rows($infection_result) == 0) return "Invalid infection. Aborting execution.";
        $infection_row = mysqli_fetch_assoc($infection_result);
        
        // 2. Calculate EP and chance	--	 moved.
		
		// 3. Set some base variables
		//$code 			= 	mysqli_get_value ("code", "product", "id", $infection_row['product_id']);	// is it a server hack or a pc hack?	Extra: code in the database determines whether it's a tool for PC hacks or Server hacks
		$entity 		= 	$infection_row['victim_entity'];
		$title			=	mysqli_get_value("title", "product", "id", $infection_row['product_id']);
		$efficiency		=	mysqli_get_value("efficiency", "product", "id", $infection_row['product_id']);
		$victim_id		=	$infection_row['victim_id'];
		
		// 4. Handle it if it was a PC HACK
		/**
		**	Small explanation before digging in: 
		**  if it was a PC HACK, victim_id would be an ID of a hacker
		**/
		if($entity == 'hacker') {
		
			// 4a. Fill a record set with information about the victim
			$victim_result = mysqli_query($link, "SELECT last_click, alias, offline_till, hybernate_till, banned_date, unhackable_till, clan_id, ip, npc FROM hacker WHERE id = {$infection_row['victim_id']}");
			if(mysqli_num_rows($victim_result) == 0) return 'Invalid ID.';
			$victim_row = mysqli_fetch_assoc($victim_result);

			// 4b. Set some base variables
			//$entity 		= 	'hacker';
			$log 			= 	'system';
			$key			=	'pc_points';	// used in war, refer to step 8
			$ep_desc 		= 	'PC';
			$win 			= 	'pchack_win';
			$offline_time 	= 	0;
			$log_id			= 	$victim_id;	// the log it gets stored in
			$hacker_id		=	$victim_id; // the one that gets restore time
			$alias			=	$victim_row['alias'];
			$ip				=	$victim_row['ip'];
			$npc			=	$victim_row['npc'];
			$victim_clanid	=	$victim_row['clan_id'];
			$last_click 	=	$victim_row['last_click'];
			$SpecialTarget			=	false;
			$in_prison		=	false;
			$addlog_attacker=	''; // for attacker
			$target			 = 	Alias4Logs ($victim_id, $entity, true);

			// 4c. Do some background checks on the victim
			if ($victim_row['offline_till'] > $now) return "This hacker is not online.";
			if ($victim_row['hybernate_till'] > $now) return "This hacker is currently hibernating.";
			if ($victim_row['banned_date'] > 0) return "This hacker is banned.";
			if ($victim_row['unhackable_till'] > $now) return "The system you're trying to attack is currently unavailable. The system is currently under investigation by the FBI.<br><br>They are releasing the $entity in ".Seconds2Time(SecondsDiff($now, $victim_row['unhackable_till']));
						
			// 4d. Check if the victim has a bounty on him
			$send_to = array();
			$bounty_result = mysqli_query ($link, "SELECT * FROM bounty WHERE contracter_id != {$hackerdata['id']} AND victim_id = {$infection_row['victim_id']} AND tool_id = {$infection_row['product_id']}");
			if (mysqli_num_rows($bounty_result) > 0) {
					while ($bounty_row = mysqli_fetch_assoc($bounty_result)) {
				
						// Build an array of people to send messages to and give the reward
						$send_to[] = $bounty_row['contracter_id'];
						BankTransfer ($hackerdata['id'], "hacker", $bounty_row['reward'], "Bounty Board reward");
					
						// Send the IMs
						SendIM (0, $hackerdata['id'], "Bounty Board Service", "Dear Sir, Madam,<br>You received a reward of $currency".number_format($bounty_row['reward']). " for your hack on $alias", $now);
						SendIM (0, $bounty_row['contracter_id'], "Bounty Board Service", "Dear Sir, Madam,<br>Your bounty of $currency".number_format($bounty_row['reward']). " on $alias was claimed.", $now);
					
						// Update stats and delete bounty
						$stat_result = mysqli_query ($link, "UPDATE hacker SET bountiestotal = bountiestotal + {$bounty_row['reward']} WHERE id = {$hackerdata['id']}");
						RegisterResult($hackerdata['id'], 'bountiescollected', $now);
						$delete_bounty_result = mysqli_query ($link, "DELETE FROM bounty WHERE id = {$bounty_row['id']}"); // delete the bounty
	
					}
			}
			
			// 4e. Call the file responsibile for PC viruses
			include('pages/_inc_doviruspc.php');
		}
		
		// 5. Handle it if it was a server hack
		/**
		**	Small explanation before digging in: 
		**  if it was a SERVER HACK, victim_id would be an ID of a server
		**/
		else {
			
			// 5a. Setting some variables
			//$entity 		= 	'server';
			$log			= 	''; // server has 1 log, no need to specify
			$key			=	'server_points';	// for war, refer to step 8
			$log_id			=	$infection_row['victim_id'];
			$hacker_id		=	mysqli_get_value("hacker_id", "server", "id", $victim_id); // owner of the server. the one that gets restore time, hp deduction, etc

			//$alias 			= 	GetServerName ($infection_row['victim_id']);
			$ep_desc 		= 	"SRV";
	        $win 			= 	"serverhack_win";
	        $SpecialTarget			=	false;
	        $in_prison		=	false;
			$addlog_attacker=  ''; // for attacker
			$server_firewall= 	mysqli_get_value("firewall", "server", "id", $infection_row['victim_id']);
			$target 		= 	Alias4Logs ($victim_id, $entity, true);
			
			switch ($victim_id) {
				case $fbi_serverid:
					$log 		= 	""; // no log
					$npc 		= 	1; // so you do not get a hackpoint
					$last_click = 	$now; // irrelevant, since NPC >0
					$victim_clanid = 0;
					$SpecialTarget 		=	true;	// needed in giving out the EP
					$ip 		=	$hackerdata['fbi_serverip'];
					break;
				case $shop_serverid:
					$log 		= 	""; // no log
					$npc 		= 	1; // so you do not get a hackpoint
					$last_click = 	$now; // irrelevant, since NPC >0
					$victim_clanid = 0;
					$SpecialTarget 		=	true;	// needed in giving out the EP 
					$ip			=	mysqli_get_value ("ip", "server", "id", $shop_serverid);
					break;
				
				default:
					// 5b. Fill a recordset with information of the owner of the server
					$query = "SELECT hacker.last_click, hacker.hybernate_till, hacker.banned_date, hacker.clan_id, hacker.npc, server.unhackable_till, server.ip FROM server LEFT JOIN hacker on server.hacker_id = hacker.id WHERE server.id = {$infection_row['victim_id']}";
					$victim_result = mysqli_query($link, $query);
					if (mysqli_num_rows($victim_result) == 0) return "Invalid id. Aborting execution.";
					$victim_row = mysqli_fetch_assoc ($victim_result);

					$ip				=	$victim_row['ip'];		
					// 5c. Base variables for not FBI servers
					$last_click		= 	$victim_row['last_click'];
					$npc			=	$victim_row['npc'];
					$victim_clanid	=	$victim_row['clan_id'];
				
					// 5d. Do some background checks
					if ($victim_row['hybernate_till'] > $now) return "The owner of this server is currently hibernating.";
					if ($victim_row['banned_date'] > 0) return "The owner of this server is banned.";
					if ($victim_row['unhackable_till'] > $now) return "The system you're trying to attack is currently unavailable. The system is currently under investigation by the FBI.<br><br>They are releasing the $entity in ".Seconds2Time(SecondsDiff($now, $victim_row['unhackable_till']));

					// Is he imprisoned?
					if(mysqli_get_value("prison_till", "hacker", "id", $hacker_id) > $now) $in_prison = true;
			}
			
			// 5d. Call the file that is responsible for server viruses
			include('pages/_inc_dovirusserver.php');
		}
			
		
		// 6. Find the protection time after the virus was executed
		if ($entity == "hacker") {
	
			// How long is the victim safe from another attack?
			$level = GetHackerLevel($infection_row['victim_id']);
			$safe_level = ($level - ($level % 10)) / 10; //  0-9 = 0, 10-19 = 1, etc
			$safe_minutes = ($pc_hacksafe_interval - ($safe_level * $safe_multiplier)) + $offline_time; //offline time is for connection destroyer attacks, so that the safe time starts after you come back online
			if ($safe_minutes < 0) $safe_minutes = 0;
			
			// Update his record
			$safe_till = date($date_format, strtotime("+".$safe_minutes." minutes"));
			if ($npc == 0) $protection_result = mysqli_query($link, "UPDATE hacker SET unhackable_till = '$safe_till' WHERE id = ".$infection_row['victim_id']);
			
		} 
		else {
			// If it's a gateway destroyer, he is safe for more than normal time
			if ($infection_row['product_id'] == $PRODUCT['Gateway Destroyer']) $safe_till = date($date_format, strtotime("+".$gw_hacksafe_interval." minutes"));
			else $safe_till = date($date_format, strtotime("+".$server_hacksafe_interval." minutes"));
		}
		
		// 6a. Run the protection query
		if ($infection_row['victim_id'] > 0 && $npc == 0) $protection_result = mysqli_query($link, "UPDATE $entity SET unhackable_till = '$safe_till' WHERE id = ".$infection_row['victim_id']); // if $infection_row['victim_id'] == 0 then it's the FBI server
		
		// if you execute a hack on a real player, you are not allowed to change your ip for xx hours
		if ($npc == 0)
		{
			// first lets see his current ip refresh date
			$refresh_result = mysqli_query($link, "SELECT nextiprefresh_date FROM hacker WHERE id = {$hackerdata['id']}");
			$refresh_row = mysqli_fetch_assoc ($refresh_result);
			$current_nextiprefresh_date = $refresh_row['nextiprefresh_date'];
			
			$new_nextiprefresh_date = date($date_format, strtotime("+".$iprefresh_cooldown_afterexecution." hours"));
			
			if ($new_nextiprefresh_date > $current_nextiprefresh_date) $refresh_result =  mysqli_query($link, "UPDATE hacker SET nextiprefresh_date = '$new_nextiprefresh_date' WHERE id = {$hackerdata['id']}");
		}
		
		// 2. Calculate the chance, ep, and skill
		$success_chance = $infection_row['chance'];	// this is the success chance for the infection
		$ep_win = GainEP($success_chance);
		$skill_win = round($ep_win / 5);

		// NPC? Cut down the EP to promote PvP
		if($npc == 1) $ep_win = round($ep_win * $npc_ep_cut);	// This should apply for NPCs, including FBI and shop
		
		// 7. Handle Rewards (Achievements, EP, Skill, Etc)
		
		// only give EP/HP when not inactive
		$inactive = date($date_format, strtotime("-".$no_epdays." days"));
		// only give EP/HP when the restore timer is below 3x restore time
		$restore_timer = mysqli_get_value ("restoring_minutes", "hacker", "id", $hacker_id);
		if ($restore_timer < $restore_time * 3) $toomany_hacks = false;
		else $toomany_hacks = true; // this player has already been hacked 3 times and has not been online since
		
		if($last_click > $inactive && !$toomany_hacks) {

		if ($npc == 0 && !$SpecialTarget) {
      	// If you get hacked you lose a HackPoint. If you hack someone get gain a hackpoint
				AddHackpoint ($hacker_id, -1, -1, "You got hacked");
				AddHackpoint ($hackerdata['id'], 1, 1, "You hacked a $entity");
			}
				
			// Update restoration time
			if(!$in_prison && !$SpecialTarget) {
				$query = "UPDATE hacker SET restoring_minutes = restoring_minutes + $restore_time WHERE id = $hacker_id";
				$restore_update = mysqli_query($link, $query);
			}
			
			// Register the result and add
			AddEP ($hackerdata['id'], $ep_win, $skill_win, $now, $ep_desc);
			RegisterResult ($hackerdata['id'], $win, $now);
		}    
		
		// 8. Handle war stuffz
		$war_result = mysqli_query($link, "SELECT id, attacker_clanid, attacker_points, victim_clanid, victim_points FROM war WHERE (attacker_clanid = {$hackerdata['clan_id']} OR victim_clanid = {$hackerdata['clan_id']}) AND (attacker_clanid = $victim_clanid OR victim_clanid = $victim_clanid) AND active = 1");
		if(mysqli_num_rows($war_result) > 0) {	// There is a war between the clans of the attacker and the victim
			
			// Who do we deduct points from?
			$war_row = mysqli_fetch_assoc($war_result);
			
			if($war_row['attacker_clanid'] == $hackerdata['clan_id']) $deduct_from = 'victim';
			else $deduct_from = 'attacker';
			
			// Points to deduct
			$points = $war_row[$deduct_from.'_points'] - $war[$key];
			if($points < 0) $points = 0;
			
			// Update the points
			$warpoint_result = mysqli_query($link, "UPDATE war SET ".$deduct_from."_points = $points WHERE id = {$war_row['id']}");
			
		}
		
		// 9. Handle logs, set the executer of the virus IP 
		$executer_ip = $infection_row['ip'];
		if (GetPerkValue($hackerdata['id'], "PERK_HIDEFAILIP") == 1) $executer_ip = 'GH0ST.1N.TH3.W1R3S';
		$last_execution_result = mysqli_query($link, "UPDATE $entity SET executer_id = {$hackerdata['id']}, executer_ip = '$executer_ip' WHERE id = $victim_id");
		
		// Add a log entry for the victim log
		AddLog ($log_id, $entity, $log, "Suspicious system activity detected", $now);

		// Add a log entry for the attacker
		$virus_name = mysqli_get_value ("title", "product", "id", $infection_row['product_id']);
		AddLog ($hackerdata['id'], "hacker", "hack", "$virus_name executed on $target. $addlog_attacker", $now);
				
		// delete any pending IP refreshes (no cowardly hiding after your hack)
		$ip_refresh_info = "";
		if ($hackerdata['iprefresh_date'] > 0 && $npc > 0)
		{
			$ip_result = mysqli_query ($link, "UPDATE hacker SET iprefresh_date = 0 WHERE id = {$hackerdata['id']}");
			$ip_refresh_info = "<br><br>Your pending IP refresh was cancelled by your ISP because of suspcious traffic coming from your IP.";
		}
		// 11. Delete the record and redirect him to the infections page
		$spreading = mysqli_get_value("spreading", "product", "id", $infection_row['product_id']);
		if ($spreading == 0) $record_delete_result = mysqli_query($link, "DELETE FROM infection WHERE id = ".$infection_row['id']); // delete this infections
		else $record_delete_result = mysqli_query($link, "UPDATE infection SET spreading = 1 WHERE id = ".$infection_row['id']); // set it to spreading
		
        PrintMessage ("Success", "$virus_name successfully executed on $target.$ip_refresh_info");
	}
	
	// Return to infections
	include('pages/infections.php');
?>