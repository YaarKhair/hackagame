<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php
    if ($_SESSION['doclanhack'] != 1) return "Session error";
    $_SESSION['doclanhack'] = 0;

    if (!empty($_POST['goal_id'])) $goal_id = intval($_POST['goal_id']); // goal of the attack
    else return "Invalid attack";
    
    if ($goal_id < 1 || $goal_id > 3) return "Invalid goal";
    // 1 == hack a gateway
    // 2 == hack a server
    // 3 == hack a hacker       // what gateway?
    $hacker_alias = '';
    $product_id = 0;
    $extra_info = '';
    
    // is your own gateway online? no gateway, no ddos attacks
    if (!IsOnlineServer(GetGateway($hackerdata['clan_id']))) return "Your gateway is offline!";
    
    // clan details
    $result = mysqli_query($link, "SELECT nextgatewayhack_date, nextserverhack_date, nextpchack_date FROM clan WHERE id = ".$hackerdata['clan_id']);
    $clanrow = mysqli_fetch_assoc ($result);

    if ($goal_id == 1) {
		$clan_id = intval($_POST['clan_id']); // which clan are we targetting?
		$ip = GetGatewayIP($clan_id); // find their gateway
		$server_ip = $ip; // in this doclanhack script we use $ip, but _inc_serverhackchecks.php uses $server_ip
		$entity = "server";
		
        $return_value = include_once("./pages/_inc_serverhackchecks.php");
		$target = Alias4Logs ($server_id, "server");
        if ($now < $clanrow['nextgatewayhack_date']) return "Your clan is not ready for another gateway hack. Time left: ".Seconds2Time(SecondsDiff($now, $row['nextgatewayhack_date']));
		
        // if a defacing message was sent, we are defacing the clan info page of the victim clan
        if (!empty($_POST['extra_info'])) {
			$extra_info = $_POST['extra_info'];
			$extra_info = preg_replace('#\r?\n#', '[br]', $extra_info);
			$extra_info = sql($extra_info, false);
			$extra_info = str_replace("[br]", "<br>", $extra_info);
			$extra_info = FilterTags ($extra_info, $hackerdata['id']);
			$defacing = true;
        }
        else $defacing = false;
        $page = "clanhackgw";
    }   
            
    if ($goal_id == 2) {
        if (!empty($_POST['server_ip'])) $ip = sql($_POST['server_ip']);
		$server_ip = $ip; // in this doclanhack script we use $ip, but _inc_serverhackchecks.php uses $server_ip
		$entity = "server";
		
        $return_value = include_once("./pages/_inc_serverhackchecks.php");
		$target = Alias4Logs ($server_id, "server");
        if ($now < $clanrow['nextserverhack_date']) return "Your clan is not ready for another server hack. Time left: ".Seconds2Time(SecondsDiff($now, $row['nextserverhack_date']));
        $page = "clanhackserver";
    }   

    if ($goal_id == 3) {
        if (!empty($_POST['hacker_ip'])) $ip = sql($_POST['hacker_ip']);
        if (!empty($_POST['hacker_alias'])) $hacker_alias = sql($_POST['hacker_alias']);
		
        $return_value = include_once("./pages/_inc_pchackchecks.php");
		$target = Alias4Logs ($hacker_id, "hacker");
        // is your clan allowed to a clan hack yet?
        if ($now < $clanrow['nextpchack_date']) return "Your clan is not ready for another pc hack. Time left: ".Seconds2Time(SecondsDiff($now, $row['nextpchack_date']));
        $page = "clanhackpc";
		$entity = "hacker";
    }
    // a server or pc check created an error
    if ($return_value != 1) return $return_value;
    
    // if the npc's can not be ddos-ed, unless their npc id is 1, which is a KOTR and can be ddos-ed
    if ($row['npc'] != 0 && $row['npc'] != 1) return "NPCs can not be hacked by means of a clan hack."; // both serverhackchecks and pchackchecks will return this field, Note: NPC 1 is enabled for DDoSes on KOTR

    // is the team complete?
    $online = date($date_format, strtotime("-$offline_limit minutes"));
    $team = mysqli_query($link, "SELECT id, alias, ip, real_ip FROM hacker WHERE clan_id = ".$hackerdata['clan_id']." AND (current_page = '$page' OR current_page = 'doclanhack') AND last_click >= '$online' ORDER BY clan_council DESC, ep DESC");

    $size = mysqli_num_rows($team);
    
    if ($size > $clanhack_maxsize) return "Too many members in the group. $clanhack_maxsize is the maximum size.";
    if ($size < $clanhack_minsize) return "Not enough members in the group. At least $clanhack_minsize are required.";

    $counter = 0; // 
    $duration = 0; // how long will the hack take?
    $chance = 0; // will it work?
    $player_chance = 0;
    $player_list = '';
    $debug_prev_alias;

    // check the members of the team
    while ($row = mysqli_fetch_assoc($team)) {
        $counter++;
        // are you really the leader? else abort
        if ($counter == 1)
        	if ($row['alias'] != $hackerdata['alias'] && !IsFounder($hackerdata['id'])) return "You are not the DDOS leader, you should not be here!";

        if ($goal_id == 1) {
        	if (!HasOnHDD($row['id'], 102)) $defacing = false; // you can not deface the victim clan if one member is missing the tool
            if (!HasOnHDD($row['id'], 69)) return "{$row['alias']} does not have the required tools.";
            if (!AllowedUseProduct($row['id'], 69)) return "{$row['alias']} is not permitted to use a tool.";
        }   
        if ($goal_id == 2) {
            if (!HasOnHDD($row['id'], 19)) return "{$row['alias']} does not have the required tools.";
            if (!AllowedUseProduct($row['id'], 19)) return "{$row['alias']} is not permitted to use a tool.";
        }   
        if ($goal_id == 3) {
            if (!HasOnHDD($row['id'], 16)) return "{$row['alias']} does not have the required tools.";
            if (!AllowedUseProduct($row['id'], 16)) return "{$row['alias']} is not permitted to use a tool.";
        }   
        //if (NumServers($row['id']) == 0) return "One of the team members does not own a server.";
        $duration += round(GetEfficiency ($row['id'], "INTERNET") / $internet_divider); // every team members' internet efficiency

        // player 1 gets NOTHING EXTRA, player 2 will get 1 time extra_chance added, player 3 gets 2* extra_chance, etc
        $extra_chance = ($counter-1) * $clanhack_extra_chance;
        
        // every team members' change of success
        if ($goal_id == 1 || $goal_id == 2) $player_chance = BattleSysPvS ($row['id'], $server_id, $extra_chance); 
        else $player_chance = BattleSysPvP ($row['id'], $hacker_id, $extra_chance); 
        
        
        $chance += $player_chance;
        
        if ($counter > 1) {
            if (!IsWhiteListed($row['real_ip'])) 
                if ($row['real_ip'] == $prev_ip) return "Two or more hackers in this DDOS attack are sharing an internet connection. This is not allowed.<br>Debug: $debug_prev_alias ($prev_ip) and {$row['alias']} ({$row['real_ip']})";
        }
        $prev_ip = $row['real_ip'];
				$debug_prev_alias = $row['alias'];
        $player_list .= "Syncing attack with {$row['alias']} [OK]<br>";
        
    }
    $duration = round($duration / $size); // the average of all connections
    $chance = round($chance / $size); // average of alle battle outcomes.
    
    // damage the system firewall a bit
    if ($goal_id == 1 || $goal_id == 2) { if ($server_firewall > $hacksystem_decrease) $result = mysqli_query($link, "UPDATE server SET firewall = firewall - ".$hacksystem_decrease." WHERE id = $server_id"); }
    else if ($hacker_firewall > $hacksystem_decrease) $result = mysqli_query($link, "UPDATE system SET efficiency = efficiency - ".$hacksystem_decrease." WHERE product_id IN (SELECT id FROM product WHERE code = 'FIREWALL') AND hacker_id = $hacker_id");

    // calculate how long it will take
    if ($goal_id == 1) $duration = $gateway_hacktime - $duration; // duration = min 0, max 40
    if ($goal_id == 2) $duration = $server_hacktime - $duration; // duration = min 0, max 40
    if ($goal_id == 3) $duration = $pc_hacktime - $duration; // duration = min 0, max 40
    
    $hackactive_date = date($date_format, strtotime("+".$duration." minutes")); // when the hack result is active
    
    // how long must the clan wait for next attack after this one?
    if ($goal_id == 1) { $field = 'nextgatewayhack_date'; $next_clanhack = date($date_format, strtotime("+".$clan_gwhack_interval + $duration." minutes")); } // for the clan
    if ($goal_id == 2) { $field = 'nextserverhack_date'; $next_clanhack = date($date_format, strtotime("+".$clan_serverhack_interval + $duration." minutes")); } // for the clan
    if ($goal_id == 3) { $field = 'nextpchack_date'; $next_clanhack = date($date_format, strtotime("+".$clan_pchack_interval + $duration." minutes")); } // for the clan
    $result = mysqli_query($link, "UPDATE clan SET $field = '$next_clanhack' WHERE id = ".$hackerdata['clan_id']);

    // will it work?
    if (InGroup($hackerdata['id'], 1)) $chance = 100; // admins like to cheat.. :D

    if (WillItWork($chance)) {
        // WIN!
        $ep = GainEP($chance -25); // 25 bonus
        $skill = round($ep / 5);
        
        $success = 1;
        
        $hackresult = true;
        $epresult = 1;
    }
    else {
        // FAIL!
        $ep = $hackjobfailed_ep;
        $skill = 1;
        $hackresult = false;
        $epresult = 0;
        
        $success = 0;
    }
    // insert the hack into the crontable
    if ($goal_id == 1) {
        $product_id = 69;
        $victim_id = $server_id;
    }   
    if ($goal_id == 2) {
        $product_id = 19;
        $victim_id = $server_id;
    }
    if ($goal_id == 3) {
        $product_id = 16;
        $extra = $chance;
        $victim_id = $hacker_id;
    }
    $result2 = mysqli_query($link, "INSERT INTO infection (hacker_id, victim_id, victim_entity, victim_ip, product_id, date, chance, extra_info, success) VALUES (".$hackerdata['id'].", $victim_id, '$entity', '$ip', $product_id, '$hackactive_date', $chance, '$extra_info', $success)");
    
    // handle the logs
    mysqli_data_seek($team, 0); // set cursor on first hacker of team record

    if ($goal_id == 1 || $goal_id == 2) {
        // log server ddos attack
        AddLog ($server_id, "server", "", "Warning: High TCP/IP traffic, Possible DDOS", $hackactive_date);
        if ($hackresult)
        	while ($row = mysqli_fetch_assoc($team)) 
            	AddLog ($server_id, "server", "", "Warning: High TCP/IP traffic, Logged: **REMOVED BY HACKER**", $hackactive_date);
        else 
        	while ($row = mysqli_fetch_assoc($team))
            	AddLog ($server_id, "server", "", "Warning: High TCP/IP traffic, Logged: ". Alias4Logs ($row['id'], "hacker"), $hackactive_date);
		
		// if $goal_id == 1, then clean server infected with the gateway finder, infected by the attacking clan, owned by the target clan.
		$result = mysqli_query($link, "SELECT server.id FROM infection LEFT JOIN server ON infection.victim_id = server.id LEFT JOIN hacker as hacker_infecter ON infection.hacker_id = hacker_infecter.id LEFT JOIN hacker as hacker_owner ON server.hacker_id = hacker_owner.id  WHERE infection.victim_entity = 'server' AND infection.product_id = {$PRODUCT['Gateway Finder']} AND hacker_infecter.clan_id = {$hackerdata['clan_id']} AND hacker_owner.clan_id = $clan_id");
		if (mysqli_num_rows ($result) > 0)
			while ($row = mysqli_fetch_assoc($result))
				CleanSystem ($row['id'], "Connection lost", "server", -1); // clean this server
    }
    if ($goal_id == 3) {
        // log pc ddos attack
        AddLog ($server_id, "server", "", "Warning: High TCP/IP traffic, Possible DDOS", $hackactive_date);
        if ($hackresult)
	        while ($row = mysqli_fetch_assoc($team)) 
    	        AddLog ($server_id, "server", "", "Illegal Access detected. Username: **REMOVED BY HACKER**", $hackactive_date);
        else 
            while ($row = mysqli_fetch_assoc($team)) 
                AddLog ($server_id, "server", "", "Illegal Access attempts. System not compromised. Logged: ". Alias4Logs ($row['id'], "hacker"), $hackactive_date);
    }

	PrintMessage ("Success", "$player_list<br>All team members are connecting to target ... [OK]<br>Initiating combined rainbow tables [OK]<br>Ddos attack initiated on $target [OK]");
    
    // update all team members
    mysqli_data_seek($team, 0);

    while ($row = mysqli_fetch_assoc($team)) {
        if ($goal_id == 1) {
            if ($defacing) DeleteFromInventory($row['id'], 102); // if defacement tool was used, delete it
            DeleteFromInventory($row['id'], 69); // delete the gw ddos tool
            if ($hackresult) RegisterResult ($row['id'], "gwhack_win", $hackactive_date);
            else RegisterResult ($row['id'], "gwhack_fail", $hackactive_date);
        }   
        if ($goal_id == 2) {
            DeleteFromInventory($row['id'], 19);
            if ($hackresult) RegisterResult ($row['id'], "serverhack_win", $hackactive_date);
            else RegisterResult ($row['id'], "serverhack_fail", $hackactive_date);
        }   
        if ($goal_id == 3) {
            DeleteFromInventory($row['id'], 16);
            if ($hackresult) RegisterResult ($row['id'], "pchack_win", $hackactive_date);
            else RegisterResult ($row['id'], "pchack_fail", $hackactive_date);
        }
		
        AddEP ($row['id'], $ep, $skill, $hackactive_date, "DDOS", $hackresult);
        
        // alert the ddos members of the result
        if ($row['id'] != $hackerdata['id']) {
	        if (!$hackresult) SendIM(0, $row['id'], "DDOS Hack Result (FAIL)", "The DDOS attack on $target failed.", $hackactive_date);
	        else SendIM(0, $row['id'], "DDOS Hack Result (WIN)", "The DDOS attack on $target was succesful. Check with the DDOS leader for details.", $hackactive_date);
    	} 
    }
?>