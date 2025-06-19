<?php
	// this cron runs each 6 hours
    include("/var/www/modules/connectdb.php");
    include("/var/www/modules/settings.php");
    include("/var/www/modules/functions.php");
    //include("/var/www/modules/cronspreadvirus.php");
    include("/var/www/modules/cronftp.php");
    
	// change the shop IP
	$result = mysqli_query ($link, "UPDATE server SET ip = '".randomip()."' WHERE id = $shop_serverid");
 	CleanSystem ($shop_serverid, "Connection lost", "server", -1);

	/*// Do we end it or not? 
	$tier1_owner = mysqli_get_value("hacker_id", "server", "id", $kotr['tier1_servers'][0]); 
	// Do we end it or not? 
	$tier1_owner = mysqli_get_value("hacker_id", "server", "id", $kotr['tier1_servers'][0]); 
	if($tier1_owner != $kotr['tier1_npc'] && $tier1_owner > 0) {
	
		// Find the current winning clan
		$points = array();
		$clan_members = array();
		$debug = '';
		$last_serverid = $kotr['initial_serverid'] + ($kotr['internet_col'] * $kotr['internet_rows']);
		$query = "SELECT hacker_id, id, executer_id FROM server WHERE id IN (".implode(",", $kotr['all_servers']).") AND hacker_id != {$kotr['tier1_npc']} AND hacker_id != {$kotr['tier2_npc']} AND hacker_id != {$kotr['tier3_npc']} AND hacker_id != 0";
		$result = mysqli_query($link, $query);
		while($row = mysqli_fetch_assoc($result)) {
			$clan_id = mysqli_get_value("clan_id", "hacker", "id" , $row['hacker_id']);
			$executer_clanid = mysqli_get_value("clan_id", "hacker", "id", $row['executer_id']);
			
			// Set the points
			$points4server = 0;
			if(in_array($row['id'], $kotr['tier3_servers'])) $points4server = $kotr['points']['Tier3'];
			if(in_array($row['id'], $kotr['tier2_servers'])) $points4server = $kotr['points']['Tier2'];
			if(in_array($row['id'], $kotr['tier1_servers'])) $points4server = $kotr['points']['Tier1'];
			if(isset($points[$clan_id])) $points[$clan_id] += $points4server;
			else $points[$clan_id] = $points4server;

			// Add the member to his clan members array if and only if the owner of the server is the same person who last executed against it
			$give_reward = false;
			if($row['hacker_id'] == $row['executer_id']) $give_reward = true;
			if($row['hacker_id'] != $row['executer_id'] && $executer_clanid != $clan_id) $give_reward = true;
			if($give_reward) $clan_members[$clan_id][] = $row['hacker_id'];
						
		}
		
	
		// Check for the winner and check if there's a tie
		$highest_points = max($points);
		$winning_clan = array_search($highest_points, $points);	
		$count_values = array_count_values($points);
		if($count_values[$highest_points] == 1 && $highest_points >= $kotr['min_win_points']) {	// Not a tie and higher than the minimum points
			
			// Clean the array from duplicates
			$clan_members = array_unique($clan_members[$winning_clan]);
			
			// Insert winner ID
			AddLog($winning_clan, "clan", "kotr", mysqli_get_value("alias", "clan", "id", $winning_clan)." won King Of The Ring.", $now);
			
			foreach($clan_members as $member) {
				// Give EP and Cash
				AddEP($member, $kotr['reward']['EP'], 0, $now, 'KTR');
				BankTransfer($member, 'hacker', $kotr['reward']['Cash'], 'Congratulations on winning King Of The Ring!', $now);
				RegisterResult($member, 'kotr_win', $now);
				AddLog($member, 'hacker', 'kotr_debug', 'Won KOTR. Cash and EP should be given.', $now);
			}
			restoreKOTR();
		}
	}*/
	AddLog (0, "hacker", "cron", "cron_6h", $now);
?>