<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php
	if ($_SESSION['dofbidatabase'] != 1) return "Session error";
	$_SESSION['dofbidatabase'] = 0;
	
	$option = intval($_POST['option']); 
	if(!in_array($option, range(1,3))) return "Invalid input.";
	
	// Self checks
	if ($now <= $hackerdata['nextserverhack_date']) return "Your system is not yet ready for another server hack.";	// too soon
	if (!IsOnlineServer(GetGateway($hackerdata['clan_id']))) return "Your clan gateway is offline!";	// is your gateway online
	if (NumServers($hackerdata['id']) == 0) return "You need to own at least one server to initiate the attack from";	// do you own a server
	if ($hackerdata['network_id'] != 2) return "You are not connected to the internet.";  // are you connected to the internet? 

	// calculate EP and Skill
	$chance = BattleSysPvF($hackerdata['id']);
	$ep_win = GainEP($chance);
	$skill_win = GainSkill($chance);
	$ep_fail = round($ep_win / 10);
	$skill_fail = round($skill_win / 10);
	
	// admins are fucking cheaters
	if (InGroup($hackerdata['id'], 1)) $chance = 100; // admins like to cheat.. :D
	
	// If it's addition or removal, get the victim's data
	if($option == 1 || $option == 2) {
			$alias = sql($_POST['alias']);	
			if ($option == 1) {
				$ip = sql($_POST['ip']);	
				$ip_sql = " AND ip = '$ip'";
				$ip_wrong = " with IP $ip";
			}
			$result = mysqli_query($link, "SELECT id, hybernate_till, clan_id, prison_till, fbi_wanteddate, network_id, jailed_bail, jailed_till, fbisafe_till, npc FROM hacker WHERE alias = '$alias'".$ip_sql);
			if (mysqli_num_rows($result) == 0) 
				return "Unknown hacker $alias".$ip_wrong;
			$row = mysqli_fetch_assoc($result);
			$victim_id = $row['id'];
			$npc =  $row['npc'];
	}
	
	// Perk
	$perk = GetPerkValue($hackerdata['id'], "PERK_INCREASEFBI");
	$chance += $perk;
		
	// Success
	if(WillItWork($chance)) {
    	
		// Add or Remove checks
		if($option == 1 || $option == 2) {
			
			// Check on the victim
			//if ($option == 1 && !InsideScope($hackerdata['id'], $victim_id)) return "This hacker is outside of your scope. Try someone a bit more close to your own level.";	// Level cap when adding
    	    if (InGroup($row['id'], 1) || InGroup($row['id'], 2)) return "This is a member of the game administration and can not be hacked.";	// is this an admin?
			if ($option == 1 && $row['clan_id'] == 0) return "The person you are adding is not in a clan and can not be added."; // is the victim in a clan?
			if ($option == 1 && $row['clan_id'] == $hackerdata['clan_id']) return "You can not add your clan mates."; // is he a clan mate?
			if ($row['hybernate_till'] > $now) return "This hacker is in hibernation and can not be hacked."; // is he in hibernation?
			if ($row['prison_till'] > $now && $option == 1) return "This hacker is already in prison.";	// prison
			if (mysqli_get_value('banned_date', 'hacker', "id", $victim_id) > 0) return "This hacker is banned and can not be hacked.";	// banned?
			if ($row['jailed_till'] > $now && $row['jailed_bail'] == 0) return "This user is banned by the game administration and can not be hacked."; // jailed by an admin?
			if ($now <= $row['fbisafe_till'] && $option == 1) return "The hacker you're trying to list was just recently released and is still in probation.<br>Please wait ".Seconds2Time(SecondsDiff($now, $row['fbisafe_till'])); // still protected?
			if ($row['npc'] > 0 && $row['npc'] != $hackerdata['id']) return "This is not your NPC!"; // If it's an NPC then it needs to be yours
			if ($option == 1 && $row['fbi_wanteddate'] > 0) return "This hacker is already on the FBI Most Wanted list."; // on the list already?
			if ($option == 2 && $row['fbi_wanteddate'] == 0) return "This hacker is not on the FBI Most Wanted list."; // already removed?
		}
		
		// Addition
		if($option == 1) {
			$result = mysqli_query($link, "UPDATE hacker SET fbi_wanteddate = '".$now."', fbi_additional_chance = 1 WHERE id = ".$victim_id);
    		$message = "Thank you officer. The hacker $alias has been added to our Most Wanted list. Your session has been automatically disconnected due to standard security protocol.";
			$message_entity = "Success";
    		$log = $hackerdata['alias']." successfully added $alias.";
		}
		
		// Removal
		if($option == 2) {
		    $fbisafe_till = date($date_format, strtotime("+".$fbisafe_interval." hours"));
			$result = mysqli_query($link, "UPDATE hacker SET fbi_wanteddate = '0', fbisafe_till = '$fbisafe_till', fbi_additional_chance = 1 WHERE id = ".$victim_id);
    		$message = "Thank you officer. The hacker ".$alias." has been removed from our Most Wanted list.";
			$message_entity = "Success";
			$log = $hackerdata['alias']." successfully removed $alias.";
			if ($hackerdata['id'] != $victim_id) SendIM (0, $victim_id, "FBI", "<img src=\"images/fbi_small.png\" align=\"right\" />Dear Sir, Madam,<br><br>You were removed from our database by [[@{$hackerdata['alias']}]]", $now);
		}
		
		// View logs
		if($option == 3) {
            $result = mysqli_query($link, "SELECT date, details FROM log WHERE hacker_id = 0 AND event = 'fbi' AND date <= '$now' AND deleted = 0 ORDER BY date DESC, id DESC LIMIT 10");
            if(mysqli_num_rows($result) > 0) {
                $title = "FBI Logs";
                echo '<strong>'.$title.'</strong><br>';
				echo '<p id="chatwindow">';
                while($row = mysqli_fetch_assoc($result)) {
                     echo Number2Date($row['date']).' | '.$row['details']."<br>";
                }
				echo '</p>';
            } else return "There are no logs.";
            $log = $hackerdata['alias']." successfully viewed the logs.";
		}
		
		// Give out EP and register successful hack
		AddEP($hackerdata['id'], $ep_win, $skill_win, $now, "FBI");
    RegisterResult ($hackerdata['id'], "fbihack_win", $now);
		
	} else {
		// Failed the hack and listed yourself
		if($option == 1) $log = $hackerdata['alias']." failed adding $alias.";
		if($option == 2) $log = $hackerdata['alias']." failed removing $alias.";
		if($option == 3) $log = $hackerdata['alias']." failed viewing the logs.";

		// Are you on the list already
		if($hackerdata['fbi_wanteddate'] == 0) {
    		$result = mysqli_query($link, "UPDATE hacker SET fbi_wanteddate = '".$now."', fbi_additional_chance = 1 WHERE id = ".$hackerdata['id']);
    		$message = "The FBI has detected illegal access from your PC. You were backtraced and are now added to the Most Wanted list yourself!";
				$message_entity = "Error";
		} 
		else {
			// You're already on the list and failed another hack
			$result = mysqli_query($link, "UPDATE hacker SET fbi_additional_chance = fbi_additional_chance + 1 WHERE id = ".$hackerdata['id']);
			$message = "The FBI has detected illegal access from your PC. You were backtraced and they are now working even harder to catch you.";
			$message_entity = "Error";
		}
		
		// If you were trying to remove someone and failed, they should know about it.
		if ($option == 2 && $victim_id != $hackerdata['id']) SendIM(0, $row['id'], "FBI Update", "Hacker [[@{$hackerdata['alias']}]] tried taking you off the FBI Most Wanted list, but failed. That hacker is now also added to the list.", $now);
		
		// Give out EP and Register failed result
		AddEP ($hackerdata['id'], $ep_fail, $skill_fail, $now, "FBI");
		RegisterResult ($hackerdata['id'], "fbihack_fail", $now);
	}
	
	// change FBI server details and set a new hack interval
	$next_hack = date($date_format, strtotime("+".$fbiserver_hack_interval." minutes"));
	$result = mysqli_query($link, "UPDATE hacker SET nextserverhack_date = '".$next_hack."', fbi_serverip = '".randomip()."', fbi_serverpass = '".createrandomPassword()."' WHERE id = ".$hackerdata['id']); 
	$_SESSION['nextserverhack_date'] = $next_hack;
	
	// Print Message and addlog if removal or addition
	PrintMessage ($message_entity, $message, "40%");
	AddLog(0,"hacker", "fbi", $log, $now);
?>