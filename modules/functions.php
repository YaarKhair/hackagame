<?php
	function refreshServerIP($server_id) {
		global $link;
		global $now;
		$ip = randomip();
		$result = mysqli_query($link, "UPDATE server SET ip = '$ip' WHERE id = $server_id");
		AddLog($server_id, "server", "", "IP refreshed to $ip", $now);
	}
	function onlineServer($server_id) {
		global $link;
		global $now;
		$result = mysqli_query($link, "UPDATE server SET offline_till = '$now' WHERE id = $server_id");
	}
	function offlineServer($server_id) {
		global $link;
		global $now;
		$result = mysqli_query($link, "UPDATE server SET offline_from = '$now', offline_till = '99999999999999' WHERE id = $server_id");
	}
	function IsStaff ($hacker_id)
	{
		global $staff_clanid;
		if (mysqli_get_value("clan_id", "hacker", "id", $hacker_id) == $staff_clanid) return true;
		else return false;
	}
	function Alias4Logs ($id, $entity, $anonymous = false)
	{
		global $link, $fbi_serverid, $fbi_servername, $shop_serverid, $shop_servername, $hackerdata_id;
		if ($entity == "server")
		{
			if ($id == $shop_serverid) {
				$ip = "(***.***.***.***)"; //we don't give the  ip, as it constantly changes and will show the updated ip in infectionlist
				$alias = $shop_servername;
			}
			
			else if ($id == $fbi_serverid) {
				$ip = "(***.***.***.***)"; //we don't give the  ip, as it constantly changes and will show the updated ip in infectionlist
				$alias = $fbi_servername;
			}
			
			else {
				$ip = "(".mysqli_get_value ("ip", "server", "id", $id).")";
				$alias = "server";
			}	
		}
		else
		{
			if (!$anonymous) $ip = "(".mysqli_get_value ("ip", "hacker", "id", $id).")";
			else $ip = "(***.***.***.***)";
			$alias = "hacker";
		}
		
		return "$alias $ip";
		
	}
	function is2xEP() {
		$day = date('Ymd');
		$EP = mysqli_get_value_from_query("SELECT id FROM doubleep_date WHERE date = '$day'", 'id');
		if($EP > 0) return true;
		else return false;
	}
	function PrintIcon($icon, $size) {
		return '<img class="icon '.$size.'" title="'.$icon.'" src="theme/icons/'.$icon.'.png">';
	}
	function GetProgress($hacker_id, $code, $show_color = false) {
		if(HasInstalled($hacker_id, $code)) {
			$eff = round(GetEfficiency($hacker_id, $code) / GetInitialEfficiency($hacker_id, $code) * 100);
			if($eff < 33) $color = 'red';
			if($eff > 33 && $eff < 66) $color = 'orange';
			if($eff > 66) $color = 'green';
			return '<span class="'.$color.'">'.$eff.'%</span>';
		}
		else return 0;
	}
	
	function mysqli_get_value_from_query($query, $value) {
		global $link;
		$result = mysqli_query($link, $query);
		if(mysqli_num_rows($result) > 0) {
			$row = mysqli_fetch_assoc($result);
			return $row[$value];
		} return false;
	}
	function RemovePerk($perk_id, $reason = '') {
		global $link;
		global $now;
		$result = mysqli_query($link, "SELECT perks.hacker_id, product.title FROM perks LEFT JOIN product ON perks.product_id = product.id WHERE perks.id = $perk_id");
		if(mysqli_num_rows($result) > 0) {
			$row = mysqli_fetch_assoc($result);
			$result2 = mysqli_query($link, "DELETE FROM perks WHERE id = $perk_id");
		}
		AddLog($row['hacker_id'], "hacker", "perk", "Perk {$row['title']} removed. $reason", $now);
	}
	function GetPerkValue($hacker_id, $perk_code) {
		if (EquippedPerk($hacker_id, $perk_code)) $perk_val = mysqli_get_value("efficiency", "product", "code", $perk_code, false);
		else $perk_val = 0;
		return $perk_val;
	}
	function NumPerks($hacker_id) {
		global $link;
		$result = mysqli_query($link, "SELECT count(id) as perks FROM perks WHERE hacker_id = $hacker_id");
		$row = mysqli_fetch_assoc($result);
		return $row['perks'];
	}
	
	function EquippedPerk($hacker_id, $perk_code) {
		global $link;
		$product_id = mysqli_get_value("id", "product", "code", $perk_code, false);
		$result = mysqli_query($link, "SELECT id FROM perks WHERE product_id = $product_id AND hacker_id = $hacker_id");
		if($result && mysqli_num_rows($result) == 1) return true;
		else return false;
	}
	
	function AllowedNumPerks($hacker_id) {
		global $link;
		$level = EP2Level(GetHackerEP($hacker_id));
		$num = floor($level / 20);
		return $num;
	}
	
	function DeleteAvatar($id, $entity) {
		global $allowed_avatar_extensions;
		// Find the folder
		$target = "./uploads/$entity/";
		
		// Make an array with the possible files
		$targets = array();
		foreach($allowed_avatar_extensions as $extension) 
			if(file_exists($target.$id.".".$extension)) @unlink($target.$id.".".$extension);
	}
	
	function ResetEmail($id, $email) {
		global $link;
		$old_email = mysqli_get_value("email", "hacker", "id", $id);
		$result = mysqli_query($link, "UPDATE hacker SET email = '$email' WHERE id = $id");		
	}
	
	function isvaliduserpassword($password) {
		global $min_password_length;
		if (
				(strtoupper($password) == $password) ||
				(strtolower($password) == $password) ||
				(ctype_alnum($password)) ||
				(strlen($password) < $min_password_length)
			)
			return false;
		else
			return true;
	}
	
	function generatePassword($length) {
		$char = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890!@#%*()_+';
		$char = str_split($char);
		shuffle($char);
		$pass = '';
		for($i = 0; $i < $length; $i++) $pass .= $char[$i];
		return $pass;
	}

	function isTOR($ip) {
		$tor_list = file('modules/tor_ip.txt');
		if(in_array($ip, $tor_list)) return true;
		else return false;
	}
	function restoreKOTR() {
		global $kotr;
		global $link;
		global $now;
		global $PRODUCT;
		
		// we reset all servers in the KOTR internet to the NPC's
		for ($i = 1; $i < 4; $i++) {
			$result = mysqli_query($link, "UPDATE server SET hacker_id = {$kotr['tier'.$i.'_npc']} WHERE id IN (".implode(",", $kotr['tier'.$i.'_servers']).")");	
			$result = mysqli_query($link, "UPDATE hacker SET restoring_minutes = 0 WHERE id = {$kotr['tier'.$i.'_npc']}"); // reset the "restoring_minutes" field so new hacks on the tier guards give EP again
		}
		
		// kill all infections on the servers
		$result = mysqli_query($link, "DELETE FROM infection WHERE victim_id IN (".implode(",", $kotr['all_servers']).") AND product_id = {$PRODUCT['Brute Force Password Cracker']}");
		
		// give all servers a full firewall, a random password
		foreach($kotr['all_servers'] as $server) {
			$result = mysqli_query($link, "UPDATE server SET password = '".createrandompassword()."', firewall = 100 WHERE id = $server");	
		}
	}
	
	function restoreKOTRserver($server_id) {
		global $kotr, $link;
		$tier = 0;
		if(in_array($server_id, $kotr['tier3_servers'])) $tier = 3;
		if(in_array($server_id, $kotr['tier2_servers'])) $tier = 2;
		if(in_array($server_id, $kotr['tier1_servers'])) $tier = 1;
		$npc_id = $kotr['tier'.$tier.'_npc'];
		$result = mysqli_query($link, "UPDATE server SET password = '".createrandompassword()."', firewall = 100, hacker_id = $npc_id WHERE id = $server_id");		
	}
		
	function InWar($clan_id) {
		global $link;
 		$result = mysqli_query($link, "SELECT id FROM war WHERE (attacker_clanid = $clan_id OR victim_clanid = $clan_id) AND active = 1");
		if(mysqli_num_rows($result) > 0) return true;
		else return false;
	}
	
	function GetServersPerTier($clan_id) {
		global $link;
		global $kotr;
		$tiers = array();
		for($i = 1; $i <= 3; $i++) {
			$key = 'tier'.$i.'_servers';
			$result = mysqli_query($link, "SELECT server.id FROM server LEFT JOIN hacker ON server.hacker_id = hacker.id WHERE hacker.clan_id = $clan_id AND server.id IN (".implode(",", $kotr[$key]).")");
			$num = mysqli_num_rows($result);
			$tiers[$key] = $num;
		}
		return $tiers;
	}
	
	function InWarPvP($hacker_id1, $hacker_id2) {
		global $link;	
		$clan1 = mysqli_get_value('clan_id', 'hacker', 'id', $hacker_id1);
		$clan2 = mysqli_get_value('clan_id', 'hacker', 'id', $hacker_id2);	
		$result = mysqli_query($link, "SELECT id FROM war WHERE (attacker_clanid = $clan1 OR victim_clanid = $clan1) AND (attacker_clanid = $clan2 OR victim_clanid = $clan2) AND active = 1");
		if(mysqli_num_rows($result) > 0) return true; 
		else return false;	
	}
	
	function InWarCvC($clan1, $clan2) {
		$result = mysqli_query($link, "SELECT id FROM war WHERE (attacker_clanid = $clan1 OR victim_clanid = $clan1) AND (attacker_clanid = $clan2 OR victim_clanid = $clan2) AND active = 1");
		if(mysqli_num_rows($result) > 0) return true; 
		else return false;	
	}
	
	function AddHackpoint ($hacker_id, $add_hp, $add_credit, $reason) {
		Global $link;
		Global $now;
		
		$result = mysqli_query ($link, "SELECT hackpoints, hackpoints_credit FROM hacker WHERE id = $hacker_id");
		$row = mysqli_fetch_assoc($result);
		
		// note that $new_hackpoints can be -1 as well
		$hackpoints = $row['hackpoints'] + $add_hp;
		$hackpoints_credit = $row['hackpoints_credit'] + $add_credit;
		
		if ($hackpoints < 0) $hackpoints = 0;
		if ($hackpoints_credit < 0) $hackpoints_credit = 0;
		
		$result = mysqli_query ($link, "UPDATE hacker SET hackpoints = $hackpoints, hackpoints_credit = $hackpoints_credit WHERE id = $hacker_id");
		AddLog ($hacker_id, "hacker", "hp", "$add_credit HP ($reason)", $now);
	}
	
    function returnCheckServers($server_id, $radius = 1) {
        global $internet_cols, $internet_rows;
        $check = array();
		// [ ][*][@][*][ ]
        if ($server_id % $internet_cols != 1) $check[] = $server_id -1; // is not the first server on the row
        if ($server_id % $internet_cols != 0) $check[] = $server_id +1; // is not the last server on the row
		// [ ]
		// [#]
		// [@]
		// [#]
		// [ ]
        if ($server_id > $internet_rows) $check[] = $server_id -$internet_rows;
        if ($server_id <= ($internet_cols * $internet_rows) - $internet_rows) $check[] = $server_id +$internet_rows;
        
		if ($radius == 2)	{
			// [*][ ][@][ ][*]
			if ($server_id % $internet_cols != 1 && $server_id % $internet_cols != 2) $check[] = $server_id -2; // is not 1 or 2. don't do > 0 because 0 is the LAST server on the row
			if ($server_id % $internet_cols != 0 && $server_id % $internet_cols != $internet_cols -1) $check[] = $server_id +2; // is not 0 or 49 (last two on row)
			
			// [#]
			// [ ]
			// [@]
			// [ ]
			// [#]
			if ($server_id > ($internet_rows * 2)) $check[] = $server_id -($internet_rows *2);
			if ($server_id <= ($internet_cols * $internet_rows) - ($internet_rows * 2)) $check[] = $server_id +($internet_rows *2);
			
			// [#][ ][#]
			// [ ][@][ ]
			// [#][ ][#]
			if ($server_id % $internet_cols != 1 && $server_id > $internet_rows) $check[] = $server_id - $internet_rows -1;
			if ($server_id % $internet_cols != 0 && $server_id > $internet_rows) $check[] = $server_id - $internet_rows +1;
			if ($server_id % $internet_cols != 1 && $server_id <= ($internet_cols * $internet_rows) - $internet_rows) $check[] = $server_id + $internet_rows -1;
			if ($server_id % $internet_cols != 0 && $server_id <= ($internet_cols * $internet_rows) - $internet_rows) $check[] = $server_id + $internet_rows +1;
		}
        
        return $check;
    }
		
	function InRange ($server_id, $checkfor, $radius) {
		Global $link;
		Global $internet_rows;
		Global $internet_cols;
		Global $now;
		
		if ($checkfor == "gateway") { $field = "gateway"; $value = 0; }
		else { $field = "hybernate_till"; $value = $now; }
		
		$check = returnCheckServers ($server_id, $radius);
		$counter = 0;
		
		foreach ($check as $check_id) {
			$result = mysqli_query($link, "SELECT $field FROM server LEFT JOIN hacker ON server.hacker_id = hacker.id WHERE hacker_id > 0 AND server.id = $check_id");
			$row = mysqli_fetch_assoc($result);
            if ($row[$field] > $value ) $counter ++;
		}
        if ($counter > 0) return true;
    	else return false;
		
	}	
	function OnList ($list_owner, $ignored, $list) {
		Global $link;
		$result = mysqli_query($link, "SELECT id FROM multi_list WHERE hacker_id1 = $list_owner AND hacker_id2 = $ignored AND relation = '$list'");
		if (mysqli_num_rows($result) > 0) return true;
		else return false;
	}	
		
	function PlayerTutorial($hacker_id, $level) {
		Global $link;
		Global $now;
		Global $ibot_id;
		
		// skip tutorial if players set that option
		$show_tutorial = mysqli_get_value("show_tutorial", "hacker", "id", $hacker_id);
		if ($show_tutorial == 0) return;
		
		$result = mysqli_query($link, "SELECT title, message FROM tutorial WHERE level = $level");
		if (mysqli_num_rows($result) > 0) {
			$row = mysqli_fetch_assoc($result);
			SendIM ($ibot_id, $hacker_id, $row['title'], $row['message'], $now);
		}
	}
	function IsPremium ($id) {
		Global $link;
		Global $now;
		$result = mysqli_query($link, "SELECT donator_till FROM hacker WHERE donator_till > '$now' AND id = $id");
		if (mysqli_num_rows($result) > 0) return true;
		else return false;
	}
	function IPLocation($ip) {
		$tags = get_meta_tags("http://www.geobytes.com/IpLocator.htm?GetLocation&template=php3.txt&IpAddress=$ip");
		if (!isset ($tags['country'])) return "Invalid IP";
		else return $tags['country'].', '.$tags['city'];
	}
	function Ban ($id, $banned_reason, $banned_by = 0) {
		Global $link;
		Global $now;
		
		// ban this hacker
		$result2 = mysqli_query($link, "UPDATE hacker SET hybernate_from = 0, hybernate_till = 0, banned_date = '$now', banned_reason = '$banned_reason', banned_by = $banned_by, support_tickets = 1 WHERE id = $id"); // 1 support ticket for unban request    
	}	
	function Unban ($id) {
		Global $link;
		Global $now;
		$result = mysqli_query($link, "UPDATE hacker SET support_tickets = 999, banned_date = 0, real_ip = '".randomip()."' WHERE id = $id"); // unban the player
	}
	function AddFormHash ($form) {
		$hash = sha1(mt_rand(9,99999));
		$field = createrandomPassword();
		$_SESSION[$form.'_hash'] = $hash;
		$_SESSION[$form.'_field'] = $field;
		echo '<input type="hidden" name="'.$field.'" value="'.$hash.'">';
    }
    function CorrectFormHash ($form, $hash) {
        if ($_SESSION[$form.'_hash'] == $hash) return true;
        else return false;
    }
    Function ID2Cat($id) {
		Global $link;
        $result = mysqli_query($link, "SELECT name FROM wikicat WHERE id = $id");
        $row = mysqli_fetch_assoc($result);
        return $row['name'];
    }
    function arrayCopy( array $array ) {
        $result = array();
        foreach( $array as $key => $val ) {
            if( is_array( $val ) ) {
                $result[$key] = arrayCopy( $val );
            } elseif ( is_object( $val ) ) {
                $result[$key] = clone $val;
            } else {
                $result[$key] = $val;
            }
        }
        return $result;
    }
	Function InsideScope($attacker_id, $victim_id) {
		Global $scopefree_level;
		Global $maxlevel;
		
        if (IsFileHolder($victim_id)) return true; // if you're holding the CTF, you can be hacked by EVERYONE!!
        
		$victim_level = EP2Level(GetHackerEP($victim_id));
		if ($victim_level < $scopefree_level) $victim_scope = intval ($victim_level / 5);
		else $victim_scope = $maxlevel; // unlimited scope, or scope free
		
		$attacker_level = EP2Level(GetHackerEP($attacker_id));
		if ($attacker_level < $scopefree_level) $attacker_scope = intval ($attacker_level / 5);
		else $attacker_scope = $maxlevel;  // inlimited scope, or scope free
		
		if ($victim_level > $attacker_level && $victim_level - $attacker_level > $attacker_scope) return false;
		if ($victim_level < $attacker_level && $attacker_level - $victim_level > $victim_scope) return false;
		
		return true; // still here? then i guess it's ok to attack him/her
	}
	Function CleanInfection($infection_id, $reason)
	{
		Global $link;
		Global $now;
		// clear a particular infection id
		$result = mysqli_query ($link, "SELECT infection.*, product.title FROM infection LEFT JOIN product ON infection.product_id = product.id WHERE infection.id = $infection_id");
		if (!$result || mysqli_num_rows($result) == 0) return;
		
		// notify the infection owner
		$row = mysqli_fetch_assoc($result);
		$alias = Alias4Logs($row['victim_id'], $row['victim_entity']);
		$title = $row['title'];
		
		if ($row['ready'] ==  1) AddLog ($row['hacker_id'], "hacker", "hack", "Connection lost to the $title installed on $alias ($reason)", $now);
		else AddLog ($row['hacker_id'], "hacker", "hack", "Connection lost to the $title while trying to install it on $alias ($reason)", $now);
		
		$result = mysqli_query($link, "UPDATE infection SET success = 0, ready = 1, spreading = 0, date = '$now' WHERE id = $infection_id"); // make it fail
	}
	Function CleanSystem($id, $reason, $entity, $ready = 1, $spreading = -1) 
	{
		// clean a pc or server
        Global $link;
        Global $now;
		
		// prepare the query ($ready = -1 ALL / $ready = 0 PENDING / $ready = 1 READY)
		if ($ready == -1) $addsql1 = "";
		else $addsql1 = "AND infection.ready = $ready";
		
		// prepare the sql ($spreading -1 ALL /  $spreading = 0 NON SPREADING VIRUS / $spreading = 1 SPREADING VIRUS
		if ($spreading == -1) $addsql2 = "";
		else $addsql2 = "AND infection.spreading = $spreading";
		$result = mysqli_query($link, "SELECT infection.*, product.title FROM infection LEFT JOIN product ON infection.product_id = product.id WHERE infection.victim_entity = '$entity' AND infection.victim_id = $id AND infection.success = 1 $addsql1 $addsql2 ORDER BY infection.hacker_id ASC");
        
		if ($entity == "hacker") {
			$log = "system";
			$alias = Alias4Logs($id, "hacker");
		}   
		else {
			$log = "";
			$alias = Alias4Logs($id, "server");
		}
		
		$counter = 0;
		$viruslist = '<br><br>Detected:<br>';
		
        // loop through (pending) infections on victim
        if (mysqli_num_rows($result) > 0) 
		{
            while ($row = mysqli_fetch_assoc($result)) 
			{
                // email the virus owner about the connection loss
                $title = $row['title'];
				$viruslist .= $title."<br>";
				// notify the virus owner of the removal
                if ($row['ready'] ==  1) AddLog ($row['hacker_id'], "hacker", "hack", "Connection lost to the $title installed on $alias ($reason)", $now);
                else AddLog ($row['hacker_id'], "hacker", "hack", "Connection lost to the $title while trying to install it on $alias ($reason)", $now);
                $counter++;
            }   
            $result2 = mysqli_query($link, "UPDATE infection SET success = 0, ready = 1, spreading = 0, date = '$now' WHERE victim_entity = '$entity' AND victim_id = $id"); // make them all fail
        }
		if ($counter == 0) $viruslist = '';
		AddLog ($id, $entity, $log, "Virus Scanner executed and found and removed $counter virus(es).$viruslist", $now);
    }
	Function DownloadTime($hacker_id, $size) {
		Global $internet_multiplier;
		if ($hacker_id > 0) $efficiency = GetEfficiency ($hacker_id, "INTERNET"); 
		$mb_per_min = ($efficiency * $internet_multiplier);
		$minutes = intval($size / $mb_per_min);
		$perk = GetPerkValue($hacker_id, "PERK_DECREASEDOWNLOAD");
		$minutes = Max($minutes - $perk, 0);
		return $minutes;
	}
	Function DownloadSpeed($efficiency) {
		// used in the ISP shop
		Global $internet_multiplier;
		$speed = $efficiency * $internet_multiplier;
		return $speed;
	}	
	Function DownloadMax($efficiency) {
		// used in the ISP shop
		Global $simultanious_dl_divider;
		$simultanious = round($efficiency / $simultanious_dl_divider);
		return $simultanious;
	}	
	Function DownloadQueueMax($hacker_id) {
		Global $simultanious_dl_divider;
		$network_id = mysqli_get_value ("network_id", "hacker", "id", $hacker_id);
		if ($network_id == 1) return 1;
		else {
			$efficiency = GetEfficiency ($hacker_id, "INTERNET"); 
			return DownloadMax($efficiency);
		}	
	}	
	Function DownloadQueueCurrent($hacker_id) 
    {
		Global $link;
		$result = mysqli_query($link, "SELECT id FROM filetransfer WHERE ready_date <> 0 AND ((source_id = $hacker_id AND source_entity = 'hacker') OR (destination_id = $hacker_id AND destination_entity = 'hacker'))");
		return mysqli_num_rows($result);
	}	
	Function Gamble ($hacker_id, $bet) {
		Global $link;
		if ($bet > 0)
			$result = mysqli_query($link, "UPDATE hacker SET gamble_won = gamble_won + $bet WHERE id = $hacker_id");
		else	{
			$bet = $bet *-1; // make it postive
			$result = mysqli_query($link, "UPDATE hacker SET gamble_lost = gamble_lost + $bet WHERE id = $hacker_id");
		}	
	}
	Function IsFileHolder($hacker_id) 
    {
		Global $link;
		GLOBAL $ctf_fileid;
		$result = mysqli_query($link, "SELECT id FROM inventory WHERE hacker_id = $hacker_id AND product_id = $ctf_fileid");
		if (mysqli_num_rows($result) == 0) Return false;
		else return true;
	}
	Function ShowFileHolder() {
		Global $link;
		GLOBAL $ctf_fileid;
		GLOBAL $ctf_serverid;
		$result = mysqli_query($link, "SELECT hacker_id, server_id FROM inventory WHERE product_id = $ctf_fileid");
		if (mysqli_num_rows($result) == 0) Return "Resetting";
		else {
			$row = mysqli_fetch_assoc($result);
			if ($row['server_id'] == $ctf_serverid) Return "CTF FTP";
			else return Alias4Logs($row['hacker_id'], "hacker");
		}	
	}
	Function KillClan ($clan_id, $reason='') {
		Global $link;
        Global $now;
		// kick any member
		$result = mysqli_query($link, "SELECT id FROM hacker WHERE clan_id = $clan_id");
		if (mysqli_num_rows($result) > 0)
			while ($row = mysqli_fetch_assoc($result)) RemoveFromClan($row['id'], $clan_id, $reason);
			
		// set clan to inactive and kill bank account
		$result = mysqli_query($link, "UPDATE clan SET active = 0, bankaccount = 0, founder_id = 0, shorttag = '' WHERE id = $clan_id");
		$result = mysqli_query($link, "DELETE FROM invite WHERE clan_id = $clan_id");
		$result = mysqli_query($link, "DELETE FROM topic WHERE clan_id = $clan_id");
		AddLog ($clan_id, "clan", "clan", "Clan Killed: $reason", $now);
		
		// Set their active wars to 0 points
		$result = mysqli_query($link, "SELECT id, attacker_clanid, victim_clanid, victim_points, attacker_points FROM war WHERE (attacker_clanid = $clan_id OR victim_clanid = $clan_id) AND active = 1");
		if(mysqli_num_rows($result) > 0) {
			$row = mysqli_fetch_assoc($result);
			
			// Are you the attacker or the victim
			if($row['attacker_clanid'] == $clan_id) 
				$entity = 'attacker';
			else $entity = 'victim';
			
			// Update the points to 0
			$result = mysqli_query($link, "UPDATE war SET ".$entity."_points = 0 WHERE id = {$row['id']}");
			
			// Kill their avatar
			DeleteAvatar($clan_id, "clan");
		}
	}
	Function FilterTags ($message, $hacker_id) {
		if (InGroup($hacker_id, 1) || InGroup($hacker_id, 2)) return $message;
		$message = str_replace("[url]", "", $message);
		$message = str_replace("[/url]", "", $message);
		$message = str_replace("[img]", "", $message);
		$message = str_replace("[/img]", "", $message);
		$message = preg_replace('/\b(https?|ftp|file):\/\/[-A-Z0-9+&@#\/%?=~_|$!:,.;]*[A-Z0-9+&@#\/%=~_|$]/i', '[color=#FF0000]**SPAM**[/color]', $message);
		$message = preg_replace('/\b(www|fpt).[-A-Z0-9+&@#\/%?=~_|$!:,.;]*[A-Z0-9+&@#\/%=~_|$]/i', '[color=#FF0000]**SPAM**[/color]', $message);
		return $message; 
	}	
	Function GainEP ($chance) {
		Global $default_ep; // 100
		Global $ep_multiplier;
		Global $default_pvp_chance;
		
		$result = $default_ep - $chance; // 100-chance. if the chance was 100 or higher then you get no EP reward
		if ($result < 1) $result = 0;
		else $result *= $ep_multiplier;
		return $result;
	}
	Function GainSkill ($chance) {
		$skill = Round(GainEP($chance) / 5);
		return $skill;
	}
	Function ShowIcons($width = "20%") {
		Global $maxlevel;
		echo '<div class="accordion">
				<input id="badges" type="checkbox" class="accordion-toggle">
				<label for="badges">Badges</label>
				<div class="accordion-box">';
		echo '
				<div class="row"><div class="col w10"><img class="badge" src="images/admin.png" title="administrator"></div><div class="col w90 left">Administrator</div></div>
    			<div class="row"><div class="col w10"><img class="badge" src="images/mod.png" title="moderator"></div><div class="col w90 left">Moderator</div></div>
    			<div class="row"><div class="col w10"><img class="badge" src="images/chatmod.png" title="supporter"></div><div class="col w90 left">Supporter</div></div>
				<div class="row"><div class="col w10"><img class="badge" src="images/founder.png" title="founder"></div><div class="col w90 left">Clan Founder</div></div>
				<div class="row"><div class="col w10"><img class="badge" src="images/council.png" title="council"></div><div class="col w90 left">Clan Council</div></div>
				<div class="row"><div class="col w10"><img class="badge" src="images/level120.png" title="level '.$maxlevel.'"></div><div class="col w90 left">Level '.$maxlevel.'</div></div>
				<div class="row"><div class="col w10"><img class="badge" src="images/premium.png" title="premium hacker"></div><div class="col w90 left">Premium Hacker</div></div>
				<div class="row"><div class="col w10"><img class="badge" src="images/donater.png" title="donator"></div><div class="col w90 left">Donator</div></div>
				<div class="row"><div class="col w10"><img class="badge" src="images/n00b.png" title="n00bNET"></div><div class="col w90 left">n00bNET</div></div>
				<div class="row"><div class="col w10"><img class="badge" src="images/jailed.png" title="jailed"></div><div class="col w90 left">Jailed</div></div>
				<div class="row"><div class="col w10"><img class="badge" src="images/imprisoned.png" title="imprisoned"></div><div class="col w90 left">Imprisoned</div></div>
			';
		echo '</div></div>';
	}		
	Function ServerSecurityLevel ($server_id, $clan_id = 0) {
		// This function is now deprecated, cluster security was removed as of 22/11/2016
		Global $link;
		Global $internet_cols;
		Global $internet_rows;
		
		if ($clan_id == 0) return 50; // npc server
		
		// if the server is owned by an npc, the cluster security is not in effect
		$result = mysqli_query($link, "SELECT id FROM server WHERE npc > 0 AND id = $server_id");
		if (mysqli_num_rows($result) == 1)
			return 100;
			
		// count how many servers a server is connected to. clustering adds to security
		$serverlink = 0;
	
		// left (2,3,4,5...49,0)
		if ($server_id % $internet_cols != 1) {
			$result = mysqli_query($link, "SELECT server.id FROM server LEFT JOIN hacker ON server.hacker_id = hacker.id WHERE hacker.clan_id = $clan_id AND server.id = ".intval($server_id - 1));
			if (mysqli_num_rows($result) > 0) $serverlink ++;
		}	
		// right (1,2,3,4,5...48,49)
		if ($server_id % $internet_cols != 0) {
			$result = mysqli_query($link, "SELECT server.id FROM server LEFT JOIN hacker ON server.hacker_id = hacker.id WHERE hacker.clan_id = $clan_id AND server.id = ".intval($server_id + 1));
			if (mysqli_num_rows($result) > 0) $serverlink ++;
		}	
		// up
		if ($server_id > $internet_cols) { // the 2nd row or more, so check above
			$result = mysqli_query($link, "SELECT server.id FROM server LEFT JOIN hacker ON server.hacker_id = hacker.id WHERE hacker.clan_id = $clan_id AND server.id = ".intval($server_id - $internet_cols));
			if (mysqli_num_rows($result) > 0) $serverlink ++;
		}	
		// down
		if ($server_id <= ($internet_cols * $internet_rows) - $internet_cols) { // the 2nd last row, so check below
			$result = mysqli_query($link, "SELECT server.id FROM server LEFT JOIN hacker ON server.hacker_id = hacker.id WHERE hacker.clan_id = $clan_id AND server.id = ".intval($server_id + $internet_cols));
			if (mysqli_num_rows($result) > 0) $serverlink ++;
		}
		// return security percentage
		if ($serverlink == 0) return 25;
		if ($serverlink == 1) return 50;
		if ($serverlink == 2) return 75;
		if ($serverlink > 2) return 100;
	}
	Function CheckNewAchievement($hacker_id, $achievement, $score = 0) {
		Global $link;
		Global $now;
		Global $currency;

        $pos = strripos($achievement, "fail");
		if ($pos !== false) return false; // no achievements for fails
		
		// score will be > 0 when checking the level achievement
		if ($achievement != "level") $score = mysqli_get_value ($achievement, "hacker", "id", $hacker_id); // if score == 0, we are checking a different achievement
		if ($score < 1) return false; // if that score is 0, lets get out of here
		
		// did you reach a new tier?
		$result = mysqli_query($link, "SELECT * FROM achievement WHERE hacker_field = '$achievement'");
		if (mysqli_num_rows($result) > 0) {
			$row = mysqli_fetch_assoc($result);
			if ($score % $row['increment'] == 0) {
				// new tier!
				$tier = floor($score / $row['increment']);
				
				// capped!
				if ($row['tier_cap'] > 0)
					if ($tier > $row['tier_cap']) { SendIM (0, $hacker_id, "New Achievement Tier reached!", "You reached tier #$tier for the achievement {$row['name']}.", $now); return; }
					
				$ep = $row['ep_reward'] * $tier;
				$skill = $row['skill_reward'] * $tier;
				$cash = $row['cash_reward'] * $tier;
				
				// Send him an IM, add his EP, skill and cash.
				SendIM (0, $hacker_id, "New Achievement Tier reached!", "You reached tier #$tier for the achievement {$row['name']}. You are rewarded with $ep EP, $skill Skill and $currency".number_format($cash), $now);	
				AddEP ($hacker_id, $ep, $skill, $now, 'ACH');
				BankTransfer($hacker_id, 'hacker', $cash, 'New tier reward', $now);
			}
		}
	}
	Function GetWikiPending() {
		Global $link;
		$result = mysqli_query($link, "SELECT id FROM wiki WHERE pending = 1");
		return mysqli_num_rows($result);
	}
	Function GetGroups($hacker_id) {
		Global $link;
		$groups = "";
		$result = mysqli_query($link, "SELECT name FROM hacker_permgroup INNER JOIN permgroup ON hacker_permgroup.permgroup_id = permgroup.id WHERE permgroup.hidden = 0 AND hacker_id = $hacker_id");
		if (!$result || mysqli_num_rows($result) == 0) $groups = "N/A";
		else
		{
			while ($row = mysqli_fetch_assoc($result))
				$groups .= $row['name']. ", ";
			$groups = rtrim($groups, ", ");
		}
		return $groups;
	}
	Function InStaff ($hacker_id) {
		if (!InGroup($hacker_id, 1) && !InGroup($hacker_id, 2)) return false;
		else return true;
	}
	Function InGroup ($hacker_id, $group_id) {
		Global $link;
		$result = mysqli_query($link, "SELECT id FROM hacker_permgroup WHERE hacker_id = $hacker_id AND permgroup_id = $group_id");
		if ($result && mysqli_num_rows($result) > 0) return true;
		else return false;
	}
	Function IsUnhackable ($hacker_id) {
		Global $link;
		$result = mysqli_query($link, "SELECT SUM( unhackable ) AS IsUnhackable FROM hacker_permgroup LEFT JOIN permgroup ON hacker_permgroup.permgroup_id = permgroup.id WHERE hacker_id = $hacker_id");
		if (!$result || mysqli_num_rows($result) == 0) return false;
		$row = mysqli_fetch_assoc ($result);
		if ($row['IsUnhackable'] > 0) return true; // one of the groups your in is Unhackable
		else return false;
	}
	Function RemoveFromClan ($hacker_id, $clan_id, $reason = '') {
		Global $link;
		// make the hacker clanless
		$result = mysqli_query($link, "UPDATE hacker SET clan_id = 0, clan_council = 0, previous_clanid = $clan_id WHERE id = $hacker_id");
		
		// disinfect any server he has infected
		$result = mysqli_query($link, "SELECT id FROM infection WHERE victim_entity = 'server' AND hacker_id = $hacker_id");
		if (mysqli_num_rows($result) > 0)
			while ($row = mysqli_fetch_assoc($result))
				CleanInfection ($row['id'], "Connection lost");
		
		// drop his servers
		$result = mysqli_query($link, "SELECT id FROM server WHERE hacker_id = $hacker_id");
		if (mysqli_num_rows($result) > 0) {
			while ($row = mysqli_fetch_assoc($result)) {
				DropServer ($row['id'], $hacker_id, $reason);
			}
		}	
	}
	Function GetWorldRankEP ($hacker_id) {
		Global $link;
		Global $staff_clanid;
		
		$worldrank = 0;
		$found = false;
		$result = mysqli_query($link, "SELECT id FROM hacker WHERE active = 1 AND npc = 0 AND banned_date = 0 AND alias <> '' AND network_id = 2 AND clan_id <> $staff_clanid ORDER BY ep DESC, skill DESC");
		while ($row = mysqli_fetch_assoc($result)) {
			$worldrank++;
			if ($row['id'] == $hacker_id)
			{
				$found = true;
				break;
			}
		}
		if ($found) return $worldrank;
		else return "N/A";
	}
	Function GetWorldRankHP ($hacker_id) {
		Global $link;
		Global $staff_clanid;

		$worldrank = 0;
		$found = false;
		$result = mysqli_query($link, "SELECT id FROM hacker WHERE active = 1 AND npc = 0 AND banned_date = 0 AND alias <> '' AND network_id = 2 AND clan_id <> $staff_clanid ORDER BY hackpoints DESC");
		while ($row = mysqli_fetch_assoc($result)) {
			$worldrank++;
			if ($row['id'] == $hacker_id) 
			{
				$found = true;
				break;
			}
		}
		if ($found) return $worldrank;
		else return "N/A";
	}
	Function WillItWork ($chance) {
		$random = mt_rand(0,100);
		if ($random <= $chance) return true;
		else return false;
	}
	Function CreateServer ($hacker_id, $ip = '') {
		Global $link;
		$password = createrandomPassword();
		if(strlen($ip) == 0) $ip = randomip();
		$result = mysqli_query($link, "INSERT INTO server (hacker_id, ip, password) VALUES ($hacker_id, '$ip', '$password')");
		return mysqli_insert_id($link);
	}
	Function DropServer ($server_id, $hacker_id, $reason = '') {
		Global $link;
		global $now;
		// drop the server
		if ($reason != '') $reason = "(Reason: $reason)";
		AddLog($server_id, "server", "", "Server cancelled $reason", $now);
		$result = mysqli_query($link, "UPDATE server SET offline_from = '0', offline_till = '0', infecter_id = 0, infecter_ip = '', executer_id = 0, executer_ip = '', hacker_id = 0, product_id = 0, product_efficiency = 0, gateway = 0, password = '', previous_ownerid = $hacker_id, drop_date = '$now', firewall = 0, ftp_motd = '' WHERE id = $server_id");
		// delete files from dropped servers
		$result = mysqli_query($link, "DELETE FROM file WHERE inventory_id IN (SELECT id FROM inventory WHERE server_id = $server_id)");
		$result = mysqli_query($link, "DELETE FROM inventory WHERE server_id = $server_id");
		CleanSystem($server_id, "Server Cancelled", "server", -1); // Clean ALL
	}
	Function AllowedUseProduct ($hacker_id, $product_id) {
		Global $link;
		// are you leveled enough to use this product?
		$query = "SELECT level FROM product WHERE id = ".$product_id;
		$result = mysqli_query($link, $query);
		$row = mysqli_fetch_assoc($result);
		if ($row['level'] > EP2Level(GetHackerEP($hacker_id))) return false;
		else return true;
	}	
	Function IsOnlineServer ($server_id) {
		Global $link;
		global $date_format;
		global $now;
		if ($server_id == 0) return false;
		$query = "SELECT id FROM server WHERE offline_from <= '".$now."' AND offline_till >= '".$now."' AND id = ".$server_id;
		$result = mysqli_query($link, $query);
		if (mysqli_num_rows($result) == 0) 
			return true;
		else 
			return false;
	}	
	Function GetClanSize ($clan_id) {
		$gateway_id = GetGateway ($clan_id);
		$size = mysqli_get_value("product_efficiency", "server", "id", $gateway_id);
		return $size;
	}	
	Function GetGateway ($clan_id) {
		Global $link;
		$result = mysqli_query($link, "select server.id from clan left join hacker on clan.founder_id = hacker.id left join server on hacker.id = server.hacker_id where server.gateway = 1 and clan.id = $clan_id");
		if (mysqli_num_rows($result) == 0) return 0;
		else {
			$row = mysqli_fetch_assoc ($result);
			return $row['id'];
		}	
	}
	Function GetGatewayIP ($clan_id) {
		Global $link;
		$result = mysqli_query($link, "select server.ip from clan left join hacker on clan.founder_id = hacker.id left join server on hacker.id = server.hacker_id where server.gateway = 1 and clan.id = $clan_id");
		if (mysqli_num_rows($result) == 0) return "N/A";
		else {
			$row = mysqli_fetch_assoc ($result);
			return $row['ip'];
		}	
	}
	Function PostCount ($hacker_id) {
		Global $link;
		$result = mysqli_query($link, "SELECT id FROM topic WHERE hacker_id = $hacker_id");
		return mysqli_num_rows($result);
	}	
	Function SendMail ($email, $subject, $message) {
		// this uses the built in php sendmail. only works if mail is correctly configured on the server.
		if (!isvalidemail($email)) return false;
		global $mail_from, $mail_name;
		$headers   = array();
		$headers[] = "MIME-Version: 1.0";
		$headers[] = "Content-type:text/html;charset=UTF-8";
		$headers[] = "From: $mail_name <$mail_from>";
		//$headers[] = "Bcc: JJ Chong <bcc@domain2.com>";
		$headers[] = "Reply-To: $mail_name <$mail_from>";
		$headers[] = "Subject: {$subject}";
		$headers[] = "X-Mailer: PHP/".phpversion();

		$result = Mail ($email, $subject, $message, implode("\r\n", $headers));
		return $result;
	}
	Function SendMail_SMTP ($address, $subject, $body) {
		// this uses php mailer module
		if (!isvalidemail($address)) return false;
		global $mail_from, $mail_name;

		require '/var/www/PHPMailer/PHPMailerAutoload.php';

		//Create a new PHPMailer instance
		$mail = new PHPMailer;
		//Tell PHPMailer to use SMTP
		$mail->isSMTP();
		//Enable SMTP debugging
		// 0 = off (for production use)
		// 1 = client messages
		// 2 = client and server messages
		$mail->SMTPDebug = 0;
		//Ask for HTML-friendly debug output
		$mail->Debugoutput = 'html';
		//Set the hostname of the mail server
		$mail->Host = "your smtp host";
		//Set the SMTP port number - likely to be 25, 465 or 587
		$mail->Port = 587;
		//Whether to use SMTP authentication
		$mail->SMTPAuth = true;
		//Username to use for SMTP authentication
		$mail->Username = "smtp username";
		//Password to use for SMTP authentication
		$mail->Password = "smtp password";
		$mail->AddReplyTo($mail_from, $mail_name);
		$mail->SetFrom($mail_from, $mail_name);
		$mail->AddAddress($address, "Player");
		$mail->Subject    = $subject;
		$mail->AltBody    = "To view the message, please use an HTML compatible email viewer!"; // optional, comment out and test
		$mail->MsgHTML($body);
		//$mail->AddAttachment("images/phpmailer.gif");      // attachment
		if(!$mail->Send()) return false;
		else return true;
		//  echo "Mailer Error: " . $mail->ErrorInfo; 
	}
    
	Function GetEthicPoints ($hacker_id, $type) {
		global $ethicpoints;
		$ethic_id = mysqli_get_value ("ethic_id", "hacker", "id", $hacker_id);
		$points = 0;
		if ($type == "defense") {
			if ($ethic_id == 3) $points = $ethicpoints; // whitehat better defense
			if ($ethic_id == 2) $points = $ethicpoints * -1; // blackhat lesser defense
		}
		else {
			if ($ethic_id == 3) $points = $ethicpoints * -1; // whitehat lesser offense
			if ($ethic_id == 2) $points = $ethicpoints; // blackhat better offense
		}
		return $points;
	}
	Function BattleSysPvP ($attacker_id, $victim_id, $extra_chance = 0, $debug = false) {
		/* BATTLE SYSTEM: Player vs Player */
		global $level_percentage;
		global $skill_percentage;
		global $system_percentage;
		global $default_pvp_chance;
		global $point_multiplier;
		global $maxlevel;
		global $maxskill;
		global $maxsystem;
		global $maxserver;
		global $now;
		//global $scope_minchance;
		global $default_ep; //100
		
		// attacker
		$attacker_ethicpoints = GetEthicPoints($attacker_id, "offense");
		$attacker_level = (EP2Level(GetHackerEP($attacker_id)) / $maxlevel) * $level_percentage;
		$attacker_skill = (GetSkill($attacker_id) / $maxskill) * $skill_percentage;
		$attacker_system = ((GetHardwarePoints($attacker_id) + GetSoftwarePoints($attacker_id)) / $maxsystem) * $system_percentage;
		

		$attacker_points = $attacker_level + $attacker_skill + $attacker_system + $attacker_ethicpoints + $default_pvp_chance;
		
		// victim
		$victim_ethicpoints = GetEthicPoints($victim_id, "defense");
		$victim_level = (EP2Level(GetHackerEP($victim_id)) / $maxlevel) * $level_percentage;
		$victim_skill = (GetSkill($victim_id) / $maxskill) * $skill_percentage;
		$victim_system = ((GetHardwarePoints($victim_id) + GetSoftwarePoints($victim_id)) / $maxsystem) * $system_percentage;
		
		$victim_points = $victim_level + $victim_skill + $victim_system + $victim_ethicpoints;
		
		$points = $attacker_points - $victim_points;
		
		if($debug) {
    		Echo 'Attacker Ethic Points: '.$attacker_ethicpoints.'<br>';
    		Echo 'Attacker Level: '.$attacker_level.'<br>';
    		Echo 'Attacker skill: '.$attacker_skill.'<br>';
    		Echo 'Attacker system: '.$attacker_system.'<br><br>';
    		Echo 'Victim Ethic Points: '.$victim_ethicpoints.'<br>';
    		Echo 'Victim Level: '.$victim_level.'<br>';
    		Echo 'Vicitm skill: '.$victim_skill.'<br>';
    		Echo 'Victim system: '.$victim_system.'<br><br>';
    		echo "Points awarded: $attacker_points - $victim_points = $points";
				if ($points < $default_pvp_chance) echo "Points are below $default_pvp_chance, so points are set to $default_pvp_chance"; // always a tiny chance of winning (low hacking high)
				if ($points > ($default_ep - $default_pvp_chance)) echo "Points are above $default_ep, so points are set to ".$default_ep-$default_pvp_chance;
		}
		
		if ($points < $default_pvp_chance) $points = $default_pvp_chance; // always a tiny chance of winning (low hacking high)
		if ($points > ($default_ep - $default_pvp_chance)) $points = $default_ep - $default_pvp_chance; // always a tiny chance of losing (high hacking low)
		
		$points+= $extra_chance; // this goes last. if you have 20% chance becuase of minimum chance but got overclock on, it still adds 10%
		return round($points);
	}

	Function BattleSysPvS ($attacker_id, $server_id, $extra_chance = 0) {
		/* BATTLE SYSTEM: Player vs Server */
		global $level_percentage;
		global $skill_percentage;
		global $system_percentage;
		global $default_pvs_chance;
		global $point_multiplier;
		global $maxlevel;
		global $maxskill;
		global $maxsystem;
		global $maxserver;
		//global $scope_minchance;
		global $default_ep; //100
			
		// attacker
		$attacker_ethicpoints = GetEthicPoints($attacker_id, "offense");
		$attacker_level = (EP2Level(GetHackerEP($attacker_id)) /$maxlevel) * $level_percentage;
		$attacker_skill = (GetSkill($attacker_id) / $maxskill) * $skill_percentage;
		$attacker_system = ((GetHardwarePoints($attacker_id) + GetSoftwarePoints($attacker_id)) / $maxsystem) * $system_percentage;
		
		$attacker_points = $attacker_level + $attacker_skill + $attacker_system + $attacker_ethicpoints + $default_pvs_chance;
		
		// victim
		$victim_id = mysqli_get_value ("hacker_id", "server", "id", $server_id);
		
		$victim_ethicpoints = GetEthicPoints($victim_id, "defense");
		$victim_level = (EP2Level(GetHackerEP($victim_id)) / $maxlevel) * $level_percentage;
		$victim_skill = (GetSkill($victim_id) / $maxskill) * $skill_percentage;
		$victim_system = (GetServerPoints($server_id) / $maxserver) * $system_percentage;
		
		$victim_points = $victim_level + $victim_skill + $victim_system + $victim_ethicpoints;
		
		$points = $attacker_points - $victim_points;
		
		if ($points < $default_pvs_chance) $points = $default_pvs_chance;
		if ($points > ($default_ep - $default_pvs_chance)) $points = $default_ep - $default_pvs_chance;
		
		$points+= $extra_chance; // this goes last. if you have 20% chance becuase of minimum chance but got overclock on, it still adds 10%
		return round($points);
	}

	Function BattleSysPvF ($attacker_id, $extra_chance = 0) {
		/* BATTLE SYSTEM: Player vs FBI */
		return round(EP2Level(GetHackerEP($attacker_id)) / 2.3); // lvl30 = 20% chance | lvl60 = 40% chance	
	}
	Function BattleSysNvP ($victim_id) {
		global $h4h_findip_chance;
		/* BATTLE SYSTEM: NPC (Hire a Hacker vs Player */
		return $h4h_findip_chance;
	}
 	Function GetServerPoints ($server_id) {
		Global $link;
		$result = mysqli_query($link, "select server.efficiency, server.firewall FROM server WHERE server.id = ".$server_id);
		if (mysqli_num_rows($result) == 0) return 0;
		else {
			$row = mysqli_fetch_assoc ($result);
			return intval($row['efficiency'] + $row['firewall']);
		}	
	}
	
 	Function GetHardwarePoints ($hacker_id) {
		Global $link;
		$result = mysqli_query($link, "select sum(system.efficiency) as syspoints FROM system LEFT JOIN product ON system.product_id = product.id WHERE (product.code = 'CPU' OR product.code = 'MAINBOARD' OR product.code = 'RAM') AND system.hacker_id = ".$hacker_id);
		if (mysqli_num_rows($result) == 0) return 0;
		else {
			$row = mysqli_fetch_assoc ($result);
			return $row['syspoints'];
		}	
	}
	Function GetSoftwarePoints ($hacker_id) {
		Global $link;
		$result = mysqli_query($link, "select sum(system.efficiency) as syspoints FROM system LEFT JOIN product ON system.product_id = product.id WHERE (product.code = 'OS' OR product.code = 'FIREWALL' OR product.code = 'SECURITY') AND system.hacker_id = ".$hacker_id);
		if (mysqli_num_rows($result) == 0) return 0;
		else {
			$row = mysqli_fetch_assoc ($result);
			return $row['syspoints'];
		}	
	}
	Function GetInitialEfficiency ($hacker_id, $code) {
		Global $link;
		// efficiency of products in store
		$result = mysqli_query($link, "SELECT product.efficiency FROM system INNER JOIN product ON system.product_id = product.id WHERE system.hacker_id = ".$hacker_id." AND product.code = '".$code."'");
		if (mysqli_num_rows($result) == 0) return 0;
		else {
			$row = mysqli_fetch_assoc($result);
			return $row['efficiency'];
		}	
	}
	
	Function GetEfficiency ($hacker_id, $code) {
		Global $link;
		GLOBAL $n00bnet_speed;
		// efficiency of hackers gear
		$result = mysqli_query($link, "SELECT system.efficiency FROM system INNER JOIN product ON system.product_id = product.id WHERE system.hacker_id = ".$hacker_id." AND product.code = '".$code."'");
		if (mysqli_num_rows($result) == 0) {
			if ($code == "INTERNET") return $n00bnet_speed; // noobnet
			else return 0;
		}	
		else {
			$row = mysqli_fetch_assoc($result);
			return $row['efficiency'];
		}	
	}
	Function HasInstalled ($hacker_id, $code, $return_id = false) {
		Global $link;
		/* returns the title if a certain product is installed, if not it returns false */
		$result = mysqli_query($link, "SELECT product.title, product.id FROM system INNER JOIN product ON system.product_id = product.id WHERE system.hacker_id = $hacker_id AND product.code = '$code'");
		if (mysqli_num_rows($result) == 0) return false;
		else {
			$row = mysqli_fetch_assoc($result);
			if (!$return_id) return $row['title'];
			else return $row['id'];
		}	
	}
	Function HasProductInstalled ($hacker_id, $product_id) {
		Global $link;
		/* returns true if a certain product is installed, if not it returns false */
		$result = mysqli_query($link, "SELECT product.id FROM system INNER JOIN product ON system.product_id = product.id WHERE system.hacker_id = $hacker_id AND product.id = $product_id");
		if (mysqli_num_rows($result) == 0) return false;
		else return true;
	}
	Function HasOnHDD ($hacker_id, $product, $return_num = false) {
		Global $link;
		if (is_numeric($product)) {
			$product_id = $product;
			$result = mysqli_query($link, "SELECT inventory.id FROM inventory LEFT JOIN filetransfer on inventory.id = filetransfer.id WHERE ISNULL(filetransfer.id) AND inventory.server_id = 0 AND inventory.hacker_id = $hacker_id AND inventory.product_id = $product_id");
		}
		else {
			$product_code = $product;
			$result = mysqli_query($link, "SELECT inventory.id FROM inventory LEFT JOIN product ON inventory.product_id = product.id LEFT JOIN filetransfer on inventory.id = filetransfer.id WHERE ISNULL(filetransfer.id) AND inventory.server_id = 0 AND inventory.hacker_id = $hacker_id AND product.code = '$product_code'");
		}
        if (!$return_num) {
    		if (mysqli_num_rows($result) == 0) return false;
    		else return true;
	    }    
        else {
            return intval(mysqli_num_rows($result)); // return the number of records.
        }
	}
	Function ShowClanAlias ($clan_id,  $color = false) {
		Global $link;
		$result = mysqli_query($link, "SELECT id, alias, color FROM clan WHERE id = $clan_id");
		if(mysqli_num_rows($result) == 0) return "N/A";
		$row = mysqli_fetch_assoc($result);
		if (!$color) $color = "";
		else $color = "color:#".$row['color'];
		$pre = "<span style=\"$color\">";
		$post = '</span>';
		$alias = '<a href="?h=claninfo&id='.$row['id'].'">'.$pre.$row['alias'].$post.'</a>';
		
		return $alias;
	}
	Function ShowHackerAlias ($hacker_id, $include_clan = 0, $color = false, $icons = true, $makelink = true, $showtag = true, $showflag = false, $showlevel = false) {
		Global $link;
		Global $now;
		Global $noob_level;
		Global $maxlevel;
		Global $premium_time;
		Global $date_format;

		if ($hacker_id == 0) $alias = '<span style="color:lime; font-style:italic">+SYSTEM</span>';
		else {
			$result = mysqli_query($link, "SELECT hacker.donator, hacker.ep, hacker.alias, hacker.network_id, hacker.country, clan.alias as clan_alias, clan.shorttag, clan_id, clan_council, clan.color, hacker.donator_till, clan.founder_id FROM hacker LEFT JOIN clan on hacker.clan_id = clan.id WHERE hacker.id = $hacker_id");
			$row = mysqli_fetch_assoc($result);
			$icon = '';
			$level = EP2Level($row['ep']);
			if ($color) { $pre = '<span style="color:#'.$row['color'].'">'; $post = '</span>'; }
			else {$pre = ''; $post = ''; }
			
			if ($icons) {
				if ($row['network_id'] == 1) $icon .= '<img class="badge dark" src="images/n00b.png" title="n00bNET">';
				if ($level == $maxlevel) $icon .= '<img class="badge dark" src="images/level120.png" title="Level '.$maxlevel.'">';
    			if ($row['clan_council'] == 1) {
					if ($row['founder_id'] == $hacker_id) $icon .= '<img class="badge dark" src="images/founder.png" title="Clan Leader">';
					else $icon .= '<img class="badge dark" src="images/council.png" title="Council Member">';
				}
				if (InGroup($hacker_id, 1))
				 $icon .= '<img class="badge dark" src="images/admin.png" title="Administrator">';
        		if (InGroup($hacker_id, 2)) 
					$icon .= '<img class="badge dark" src="images/mod.png" title="Moderator">';			
        		if (InGroup($hacker_id, 6)) 
					$icon .= '<img class="badge dark" src="images/chatmod.png" title="Supporter">';			
				if ($row['donator_till'] > 0) {
					if ($row['donator_till'] > $now) $icon .='<img class="badge dark" src="images/premium.png" title="Premium Hacker">';
					elseif ($row['donator'] == 1) $icon .='<img class="badge dark" src="images/donater.png" title="Donator">';
				}	
        		if (InGroup($hacker_id, 8)) 
					$icon .= '<img class="badge dark" src="images/crown.png" title="Current Contest Champion">';			
				if (IsJailed($hacker_id)) 
					$icon .= '<img class="badge dark" src="images/jailed.png" title="Jailed">';			
				if (IsImprisoned($hacker_id)) 
					$icon .= '<img class="badge dark" src="images/imprisoned.png" title="Imprisoned">';			
				$icon = '&nbsp;'.$icon;
			}	
			
			if ($row['shorttag'] != "" && $showtag) $shorttag = "[".$row['shorttag']."]";
			else $shorttag = '';
			
			if ($showflag) $flag = '<img src="./images/flags_new/'.$row['country'].'.png" title="'.$row['country'].'" class="badge" />';
			else $flag = '';
			
			if ($makelink) {
				$pre = '<a id="nickname" href="?h=profile&id='.$hacker_id.'">'.$pre;
				$post = $post.'</a>';
			}
			if ($showlevel && (!InGroup($hacker_id, 1) && !InGroup($hacker_id, 2))) {
					$level = "&nbsp;($level)";
			}
			else $level = '';
			$alias = $pre.$shorttag.$row['alias'].$post.$icon.$flag.$level;
			$clan = "";
			if ($include_clan > 0) {
				if ($row['clan_id'] > 0) {
					$clan = $row['clan_alias'];
					if ($link) $clan = '<a href="?h=claninfo&id='.$row['clan_id'].'">'.$clan.'</a>';
				}	
			}
			if ($clan != "") $alias = $alias . ' / '.$clan;
		}	
		return $alias;
	}
		
	Function GetEthic ($ethic_id) {
		Global $link;
		$result = mysqli_query($link, "SELECT ethic FROM ethic WHERE id = $ethic_id");
		$row = mysqli_fetch_assoc($result);
		return $row['ethic'];
	}
	// returns 1 if checkbox is checked or 0 if unchecked
	Function CheckBox($check) {
		if ($check == "on") return 1;
		else return 0;
	}
	Function GetSkill ($hacker_id) {
		$result = mysqli_get_value ("skill", "hacker", "id", $hacker_id);
		if (!$result) return 0;
		else return $result;
	}	
	Function GetHackerLevel ($hacker_id) {
		if (InGroup($hacker_id, 1) || InGroup($hacker_id, 2)) return "**HIDDEN**";
		else return EP2Level(GetHackerEP($hacker_id));
	}
 	Function Level2EP ($hacker_level) {
    	// returns the level of a player
		global $firstlevel_ep; // 10
		global $level_interval_increase; // 
    	global $maxlevel; // 120
		
   		$level = 1;
	    $level_ep[0] = 0;
	    $level_ep[1] = $firstlevel_ep; // ep needed for this particular level
        
		while ($level < $maxlevel) {
			$level_ep[2] = $level_ep[1] + ($level_ep[1]-$level_ep[0]) * $level_interval_increase; // ep level 2 = ep level 1 + (ep level 1 - ep level 0) * interval increaser
			$level ++;
			if ($hacker_level == $level) return $level_ep[2]; // if your ep is lower than the ep needed for the next level, we have your current level
			$level_ep[0] = $level_ep[1];
			$level_ep[1] = $level_ep[2];
		}
		return $level;
	}
	
	Function EP2Level ($hacker_ep) {
		// returns the level of a player
		global $firstlevel_ep; // 10
		global $level_interval_increase; // 
		global $maxlevel; // 120

		if ($hacker_ep < $firstlevel_ep) return 0; // ep lower than what is needed for level 1? then you are level 0

		$level = 1;
		$level_ep[0] = 0;
		$level_ep[1] = $firstlevel_ep; // ep needed for this particular level

		while ($level < $maxlevel) {
			$level_ep[2] = $level_ep[1] + ($level_ep[1]-$level_ep[0]) * $level_interval_increase; // ep level 2 = ep level 1 + (ep level 1 - ep level 0) * interval increaser
			if ($hacker_ep < $level_ep[2]) return $level; // if your ep is lower than the ep needed for the next level, we have your current level
			$level ++;
			$level_ep[0] = $level_ep[1];
			$level_ep[1] = $level_ep[2];
		}
		return $level;
	}
	Function GetNextLevelEP ($hacker_id) {
		// returns the EP needed for your next level
		global $firstlevel_ep; // 10
		global $level_interval_increase; // 
		global $maxlevel; // 120

		$hacker_ep = GetHackerEP ($hacker_id);
		if ($hacker_ep < $firstlevel_ep) return $firstlevel_ep;

		$level = 1;
		$level_ep[0] = 0;
		$level_ep[1] = $firstlevel_ep; // ep needed for this particular level


		while ($level < $maxlevel) {
			$level_ep[2] = $level_ep[1] + ($level_ep[1]-$level_ep[0]) * $level_interval_increase;
			if ($hacker_ep < $level_ep[2]) return intval($level_ep[2]);
			$level ++;
			$level_ep[0] = $level_ep[1];
			$level_ep[1] = $level_ep[2];
		}
		return 0; // if you are 120 then you need 0 ep for the next level
	}
	Function EP2LevelProgress ($hacker_id) {
        global $firstlevel_ep; // 10
		global $level_interval_increase; // 
        global $maxlevel; // 120
		
        $hacker_ep = GetHackerEP ($hacker_id);
        
		$level = 1;
        $level_ep[0] = 0;
	    $level_ep[1] = $firstlevel_ep; // ep needed for this particular level
        
         if ($hacker_ep < $firstlevel_ep) {
            $total_ep_interval = $firstlevel_ep;
            $progress = $hacker_ep;
            return intval(($progress / $total_ep_interval) * 100);
        }    
        
		while ($level < $maxlevel) {
            $level_ep[2] = $level_ep[1] + ($level_ep[1]-$level_ep[0]) * $level_interval_increase;
            if ($hacker_ep < $level_ep[2]) {
                $total_ep_interval = $level_ep[2] - $level_ep[1]; // how much ep you need for this level
                $progress = $hacker_ep - $level_ep[1]; // how much ep you already have
                return intval(($progress / $total_ep_interval) * 100); // how much that what you have is in %
            }    
    		$level ++;
            $level_ep[0] = $level_ep[1];
            $level_ep[1] = $level_ep[2];
		}
		return 100; // if you are 120 then you need 0 ep for the next level
	}
	Function Seconds2Time ($secs) { 
		if($secs == 0) return 0;
        $vals = array('w' => (int) ($secs / 86400 / 7), 
                      'd' => $secs / 86400 % 7, 
                      'h' => $secs / 3600 % 24, 
                      'm' => $secs / 60 % 60, 
                      's' => $secs % 60); 
 
        $ret = array(); 
        $added = false; 
        foreach ($vals as $k => $v) { 
            if ($v > 0 || $added) { 
                $added = true; 
                $ret[] = $v . $k; 
            } 
        } 
 
        return join(':', $ret); 
 	}
	Function ShowAvatar ($id, $size=0, $html = '', $entity = "hacker", $caching = false, $optional_class = false) {
		global $hackeravatar_h;
		global $hackeravatar_w;
		global $clanavatar_h;
		global $clanavatar_w;
		global $allowed_avatar_extensions;
		
		if ($size > 0) {
			$height = $size;
			$width = $size;
		}
		else {
			if ($entity == "hacker") {
				$height = $hackeravatar_h;
				$width = $hackeravatar_w;
			}
			else {
				$height = 100;
				$width = 100;
			}
		}
		
		$extra_size = '';
		if($entity == "clan") $extra_size = 'style="width: '.$height.'%; height: '.$height.'%;"';
		
		$extra = '';
		if($optional_class != false) $extra = 'class = "'.$optional_class.'"';
		
		foreach ($allowed_avatar_extensions as $ext) {
			if($caching == true) $rand = '';
			else $rand = '?'.rand(0,99999);
			if (file_exists("./uploads/$entity/$id.$ext")) return '<img '.$extra_size.' id="avatar" src="./uploads/'.$entity.'/'.$id.'.'.$ext.$rand.'" title="avatar" '.$extra.' '.$html.'>';
		}
		// still here? then only return something if this is a hacker
		if ($entity == "hacker") return '<img '.$extra_size.' id="avatar" src="./uploads/'.$entity.'/avatar.jpg" title="avatar" '.$extra.' '.$html.'>';
		else return "";
	}
	Function GetStatus ($hacker_id) {
		global $now;
		if (mysqli_get_value('banned_date', 'hacker', 'id', $hacker_id) > 0) return '<font color="red"><s>Banned</s></font>'; 
		if (IsHybernated($hacker_id)) return '<font color="orange"><strong>Hibernating</strong></font>';
		if (!IsOnline($hacker_id)) return '<font color="red"><strong>Offline</strong></font>';
		if(mysqli_get_value('restoring_till', 'hacker', 'id', $hacker_id) > $now) return '<font color="blue"><strong>Restoring</strong></font>';
		else return '<font color="lime"><strong>Online</strong></font>';
	}
	Function IsMultipleAccount ($ip) {
		Global $link;
		global $accounts_per_ip;
		$result = mysqli_query($link, "SELECT id FROM hacker WHERE npc = 0 AND real_ip = '".$ip."'");
		if (mysqli_num_rows($result) <= $accounts_per_ip) return false;
		else return true;
	}
	Function IsWhiteListed($ip) {
		Global $link;
		$result = mysqli_query($link, "SELECT id FROM whitelist WHERE ip = '".$ip."'");
		if (mysqli_num_rows($result) == 0) return false;
		else return true;
	}	
	Function MaskIP($text) {
		$pattern ='/\d{1,3}\./';
		$mask = 'xxx.';
		return preg_replace($pattern, $mask, $text);
	}
	Function IsBlacklisted($ip) {
		$dnsbl_lists = array("dnsbl.proxybl.org", "sbl-xbl.spamhaus.org");
		if ($ip && preg_match('/^([0-9]{1, 3})\.([0-9]{1, 3})\.([0-9]{1, 3})\.([0-9]{1, 3})/', $ip)) {
			$reverse_ip = implode(".", array_reverse(explode(".", $ip))); 
			$on_win = substr(PHP_OS, 0, 3) == "WIN" ? 1 : 0;
			foreach ($dnsbl_lists as $dnsbl_list){
				if (function_exists("checkdnsrr")) {
					if (checkdnsrr($reverse_ip . "." . $dnsbl_list . ".", "A")) {
						return $reverse_ip . "." . $dnsbl_list;
					} 
				} else if ($on_win == 1) {
					$lookup = "";
					@exec("nslookup -type=A " . $reverse_ip . "." . $dnsbl_list . ".", $lookup);
					foreach ($lookup as $line) {
						if (strstr($line, $dnsbl_list)) {
							return $reverse_ip . "." . $dnsbl_list;
						}
					}
				} 
			}
		}
		return false;
	}
	Function mysqli_get_value($field, $table, $wherefield, $wherevalue, $numeric = true, $active = false) {
		Global $link;
        // if table is either clan or hacker it needs to be an active hacker or clan, for the wiki links
        if ($active) $sql_add = " AND active = 1";
        else $sql_add = "";
        
		if ($numeric) $result = mysqli_query($link, "SELECT $field FROM $table WHERE $wherefield = $wherevalue".$sql_add); // numeric
		else $result = mysqli_query($link, "SELECT $field FROM $table WHERE $wherefield = '$wherevalue'".$sql_add); // alphanumeric
        
        if (!$result) return false;	
		if (mysqli_num_rows($result) == 0) return false;
		$row = mysqli_fetch_assoc ($result);
		return $row[$field];
	}	
	
	Function mysqli_next_id($table) {
		Global $link;
        $query = mysqli_query($link, "SHOW TABLE STATUS LIKE '$table'");
        $row = mysqli_fetch_assoc($query);
        $auto_increment = $row['Auto_increment'];
        return ($auto_increment);
	}
	Function NewsIconList() {
		$icons = '';
		if ($dir = @opendir('./images/newsicons')) {
			while (($file = readdir($dir)) !== false) {
				$shortfile = basename($file, ".png");
				if ($file != "." && $file != ".." && $file != "index.php") $icons.= '<option value="'.$file.'">'.$shortfile;
			}  
			closedir($dir);
		}
		return $icons;
	}
	Function GetJailed($hacker_id, $chance) {
		$chance -= EP2Level(GetHackerEP($hacker_id)) / 5;
		if (WillItWork($chance)) return true;
		return false;
	}
	
	Function FileInfo($inventory_id, $feature) {
		Global $link;
		// gets info of a certain intentory property regardless it being a file or a product
		$result = mysqli_query($link, "SELECT product_id, file_id FROM inventory WHERE id = ".$inventory_id);
		$row = mysqli_fetch_assoc ($result);
		
		if ($feature == "is_file") {
			if ($row['file_id'] != 0) return 1;
			else return 0;
		}	
		
		if ($row['product_id'] != 0) {
			$result = mysqli_query($link, "SELECT ".$feature." FROM product WHERE id = ".$row['product_id']);
			$row = mysqli_fetch_assoc ($result);
			return $row[$feature];
		}
		else {
			$result = mysqli_query($link, "SELECT ".$feature." FROM file WHERE id = ".$row['file_id']);
			$row = mysqli_fetch_assoc ($result);
			return $row[$feature];
		}
	}
	
	Function GetServerOwner($server_id) {
		Global $link;
		$result = mysqli_query($link, "SELECT hacker_id FROM server WHERE id = ".$server_id);
		$row = mysqli_fetch_assoc($result);
		return $row['hacker_id'];
	}
	
	Function IsHybernated($hacker_id) {
		Global $link;
		global $date_format;
		$now = date($date_format);
		$result = mysqli_query($link, "SELECT id FROM hacker WHERE hybernate_from <= '".$now."' AND hybernate_till >= '".$now."' AND id = ".$hacker_id);
		if (@mysqli_num_rows($result) == 0) return false;
		else return true;
	}
	Function IsCouncil($hacker_id) {
		Global $link;
		$result = mysqli_query($link, "SELECT id FROM hacker WHERE clan_council = 1 AND id = ".$hacker_id);
		if (mysqli_num_rows($result) == 0) return false;
		else return true;
	}
	Function IsFounder($hacker_id) {
		Global $link;
		$result = mysqli_query($link, "SELECT id FROM clan WHERE active = 1 AND founder_id = ".$hacker_id);
		if (mysqli_num_rows($result) == 0) return false;
		else return true;
	}	
	
	Function randomNPCname() {
		Global $link;
		// prevent double names
		$unique = false;
		// set up arrays 
		$pre_array = file("modules/pre");
		$post_array = file("modules/post");
		
		while ($unique == false) {
			// get random numbers
			$pre = mt_rand(0,sizeof($pre_array)-1);
			$post = mt_rand(0,sizeof($post_array)-1);
	
			// create name from names and arrays
			$npcname = trim($pre_array[$pre]).trim($post_array[$post]);
	
			// is this name unique?
			$result = mysqli_query($link, "SELECT id FROM hacker WHERE alias = '".$npcname."'");
			if (mysqli_num_rows($result) == 0) $unique = true;
		}
		return $npcname;
	}
	
	Function createrandomPassword() { 
		$chars = "abcdefghijkmnopqrstuvwxyz23456789"; 
		srand((double)microtime()*1000000); 
		$i = 0; 
		$pass = '' ; 

		while ($i <= 7) { 
			$num = rand() % 33; 
			$tmp = substr($chars, $num, 1); 
			$pass = $pass . $tmp; 
			$i++; 
		} 
		return $pass; 
	} 
	function compareWiki($old, $new) {
		include_once('modules/finediff.php');
		$diff = new FineDiff($old, $new, FineDiff::$wordGranularity);
		return $diff->renderDiffToHTML();
	}
	Function PrintSmilies() {
		// add the smilies
		$message = "<pre>";
		$smiles = ReadSmilies();
		foreach($smiles as $smile=>$image)
			$message .= $smile." ".replaceBBC(" ".$smile)."\n";
		
		$message .= "</pre>";	
		return $message;
	}
	Function printBBC($size) {
		$message = '[quote]a quoted text[/quote]<br>
		[b]'.replaceBBC("[b]bold text[/b]").'[/b]<br>
		[i]'.replaceBBC("[i]italic text[/i]").'[/i]<br>
		[u]'.replaceBBC("[u]underlined text[/u]").'[/u]<br>
		[s]'.replaceBBC("[s]strike out text[/s]").'[/s]<br>
		[big]'.replaceBBC("[big]BIG TEXT[/big]").'[/big]<br>
		[small]'.replaceBBC("[small]small text[/small]").'[/small]<br>
		[pre]'.replaceBBC("[pre]pre formatted[/pre]").'[/pre]<br>
		[color=#CC6600]'.replaceBBC("[color=#CC6600]colors[/color]").'[/color]<br>
		[['.replaceBBC("[[wiki link]]").']]<br>
		[[@'.replaceBBC("[[@chaozz]]").']]<br>
		[[#'.replaceBBC("[[#Game Administration]]").']]<br>';
		PrintMessage ("", $message, $size, 0, false);
	}
	Function URL ($text) {
		return str_replace (" ", "+", $text);
	}	
	Function WikiLink ($text) {
		$firstchar = substr($text[1], 0, 1);
		$rest = sql(substr($text[1], 1, strlen($text[1])-1));
		if ($firstchar == "@") return "<a href=\"?h=profile&id=".mysqli_get_value("id", "hacker", "alias", $rest, false, false)."\">".$rest."</a>";
		elseif ($firstchar == "#") return "<a href=\"?h=claninfo&id=".mysqli_get_value_from_query("SELECT id FROM clan WHERE active = 1 AND alias = '". $rest."'", "id")."\">".$rest."</a>";
		else return "<a class=\"wiki\" href=\"?h=wiki&title=".URL($text[1])."\">".$text[1]."</a>";
	}
	Function replaceBBC($text, $allow_centertag = true){
		$text = preg_replace("#\[quote\](.*?)\[/quote\]#si","<div class='message quote'>$1</div>", $text);
		$text = preg_replace_callback("#\[\[([\@\#\!a-zA-Z0-9_\s]*?)\]\]#si",'WikiLink', $text);
		$text = preg_replace("#\[b\](.*?)\[/b\]#si","<b>$1</b>", $text);
		if ($allow_centertag) $text = preg_replace("#\[center\](.*?)\[/center\]#si","<div style='text-align:center'>$1</div>", $text);
		$text = preg_replace("#\[u\](.*?)\[/u\]#si","<u>$1</u>", $text);
		$text = preg_replace("#\[i\](.*?)\[/i\]#si","<i>$1</i>", $text);
    	$text = preg_replace("#\[s\](.*?)\[/s\]#si","<s>$1</s>", $text);
    	$text = preg_replace("#\[list\](.*?)\[/list\]#si","<ul>$1</ul>", $text);
    	$text = preg_replace("#\[li\](.*?)\[/li\]#si","<li>$1</li>", $text);
		$text = preg_replace("#\[big\](.*?)\[/big\]#si","<h3>$1</h3>", $text);
		$text = preg_replace("#\[small\](.*?)\[/small\]#si","<h5>$1</h5>", $text);
		$text = preg_replace("#\[pre\](.*?)\[/pre\]#si","<pre>$1</pre>", $text);
		//$text = preg_replace("#\[url=(.*?)\](.*?)\[/url\]#si","<a href=\"$1\">$2</a>", $text);  // <-- xss exploitable
		$text = preg_replace("#\[url=\?h=(.*?)\](.*?)\[/url\]#si","<a href=\"?h=$1\">$2</a>", $text);
		$text = preg_replace("#\[color=([\#a-fA-F0-9]{7})\](.*?)\[/color\]#si","<span style=\"color:$1\">\\2</span>", $text);
		$smiles = ReadSmilies();
		foreach($smiles as $smile=>$image)
			$text = str_ireplace($smile,"&nbsp;<img src=\"images/smilies/$image.gif\" title=\"$image\">", $text);
		return $text;
	}
	Function ReadSmilies() {
		return array(' :)'=>'smiley',
    			' ;)'=>'wink',
				' :P'=>'tongue',
				' :D'=>'grin',
				' xD'=>'mischief',
				' 8)'=>'cool',
    			' :('=>'sad',
    			' :{'=>'cry',
				' %)'=>'blink',
				' :.'=>'rolleyes',
				' :@'=>'angry',
				' :O'=>'shocked',
				' }('=>'devil',
				' :*'=>'clown',
				' :]'=>'n00b',
                ' (yawn)'=>'yawn',
                ' (good)'=>'good',
                ' &lt;3'=>'heart',
                ' (bad)'=>'bad',
        		' (cheer)'=>'cheer',
        		' (clap)'=>'clap',
        		' (crazy)'=>'crazy',
        		' (jail)'=>'jail',
        		' (nails)'=>'nails',
        		' (peace)'=>'peace',
        		' (pray)'=>'pray',
        		' (zzz)'=>'sleep',
        		' (vomit)'=>'vomit',
        		' (wave)'=>'wave',
				' (fp)'=>'facepalm',
        		' (hack)'=>'hacker',
            	' (ban)'=>'ban',
            	' (bug)'=>'bug',
            	' (popcorn)'=>'popcorn',
            	' (duck)'=>'duck',
				' (coffee)'=>'coffee');
	}	
	Function Hostname2IP($hostname)
	{
		// convert dynamic hostname back to IP
		$ip = "";
		$part = explode(".", $hostname);
		$hostname = strtoupper($part[0]);
		$alphas = range('Z', 'A');
		$length = strlen($hostname);
		for ($i=0; $i<$length; $i++)
		{
			if ($hostname[$i] == "-") $ip .= ".";
			else $ip .= array_search($hostname[$i], $alphas);
		}
		return $ip;		
	}
	Function IP2Hostname($ip)
	{
		// convert IP to something that resambles a dynamic host name
		$hostname = "";
		$alphas = range('Z', 'A');
		$length = strlen($ip);
		for ($i=0; $i<$length; $i++)
		{
			if ($ip[$i] == ".") $hostname .= "-";
			else $hostname .= $alphas[$ip[$i]];
		}
		return $hostname;		
	}
	Function GetServerName($server_id) {
		Global $link;
		$result = mysqli_query($link, "SELECT server.ip, hacker.alias, clan.shorttag FROM server LEFT JOIN hacker ON server.hacker_id = hacker.id LEFT JOIN clan ON hacker.clan_id = clan.id WHERE server.id = ".$server_id);
		$row = mysqli_fetch_assoc ($result);
		
		$hostname = IP2Hostname($row['ip']);
		$shorttag = "htg";
		if ($row['shorttag'] != '') $shorttag = strtoupper($row['shorttag']);
		
		return "$hostname.$shorttag";
	}
	Function DeleteFromInventory($hacker_id, $product_id) {
		Global $link;
		$result = mysqli_query($link, "SELECT id FROM inventory WHERE hacker_id = ".$hacker_id." AND product_id = ".$product_id." AND server_id = 0");
		if (mysqli_num_rows($result) == 0) return false;
		else {
			$row = mysqli_fetch_assoc($result);
			$result2 = mysqli_query($link, "DELETE FROM inventory WHERE id = ".$row['id']);
			return true;
		}
	}
	Function HDDuse ($hacker_id) {
		Global $link;
		$in_use = 0;
		//files on HDD
		$result = mysqli_query($link, "SELECT id FROM inventory WHERE server_id = 0 AND hacker_id = ".$hacker_id);
		while ($row = mysqli_fetch_assoc($result)) {
			$in_use += FileInfo($row['id'], "size"); // calculate total size in use on HDD
		}
		// installed SOFTWARE
		$result = mysqli_query($link, "select sum(product.size) as syssize FROM system LEFT JOIN product ON system.product_id = product.id WHERE (product.in_shop = 1) AND system.hacker_id = ".$hacker_id);
		if (mysqli_num_rows($result) == 0) {}
		else {
			$row = mysqli_fetch_assoc ($result);
			$in_use += intval($row['syssize']);
		}	
		return $in_use;
	}
	
	Function HDDsize ($hacker_id) {
		Global $link;
		$result = mysqli_query($link, "SELECT product.size FROM system LEFT JOIN product ON system.product_id = product.id WHERE product.code = 'HDD' AND system.hacker_id = ".$hacker_id);
		$row = mysqli_fetch_assoc($result);
		return $row['size'] * 1000;
	}
	
	Function NumServers($hacker_id) {
		Global $link;
		// returns the number of servers owned by hacker_id
		if ($hacker_id == 0) return 0;
		$result = mysqli_query($link, "SELECT id FROM server WHERE hacker_id = ".$hacker_id);
		if (mysqli_num_rows($result) == 0) return 0;
		else return mysqli_num_rows($result);
	}
	Function MaxServers($hacker_id) {
		// returns the number of servers owned by hacker_id
		$level = EP2Level(GetHackerEP($hacker_id));
		$numservers = round($level / 5);
		return $numservers;
	}
	
	Function DisplaySize($size) {
		if ($size < 1000) return $size." MB";
		elseif ($size < 1000000) return round($size/1000,2)." GB";
		else  return round($size/1000000,2)." TB";
	}
	
	Function InstalledOS($var) {
		Global $link;
		// returns no if 0 and yes if above
		if ($var == 0) { return "Windows 98 SE"; }
		else {
			$result = mysqli_query($link, "SELECT title FROM product WHERE id = ".$var);
			$row = mysqli_fetch_assoc($result);
			return $row['title'];
		}	
	}
	Function Connection($var) {
		Global $link;
		$result = mysqli_query($link, "SELECT title FROM product WHERE id = ".$var);
		$row = mysqli_fetch_assoc($result);
		return $row['title'];
	}
	Function Installed($var) {
	// returns no if 0 and yes if above
		if ($var == 0) { return "Not installed"; }
	elseif ($var == 1) { return "Installed"; }
		else {
			return ShowProgress($var, 100);
		}	
	}
	Function RegisterResult ($id, $achievement, $date, $entity = "hacker") {
		Global $link;
		$id_list = Array();
		if ($entity == "clan") {
			$result = mysqli_query ($link, "SELECT id FROM hacker WHERE clan_id = $id");
			while ($row = mysqli_fetch_assoc($result))
				$id_list[] = $row['id'];
		}
		else $id_list[] = $id;
		
		foreach($id_list as $hacker_id) 
			$result = mysqli_query($link, "INSERT INTO cronresult (hacker_id, achievement, date) VALUES ($hacker_id, '$achievement', '$date')");
	}
    Function AddEP ($hacker_id, $ep, $skill, $date, $target) {
			Global $link;
			Global $now;
			if(is2xEP()) $ep *= 2;
			$result = mysqli_query($link, "INSERT INTO cronep (hacker_id, ep, skill, target, date) VALUES ($hacker_id, $ep, $skill, '$target', '$date')");
    }
    	
	Function IsBannedAlias ($alias) {
		Global $link;
		// blacklist
		$result = mysqli_query($link, "SELECT word FROM blacklist WHERE word <> ''");
		if (mysqli_num_rows($result) == 0) return false;
		else {
			while ($row = mysqli_fetch_assoc($result)) {
				if (strpos(strtolower($alias), strtolower($row['word'])) !== false) return true;
			}
		}
	}

	Function IsBannedIP($ip) {
		Global $link;
		// blacklist
		if ($ip == "" || empty($ip)) return true;
		
		$result = mysqli_query($link, "SELECT id FROM blacklist WHERE ip = '".$ip."'");
		if (mysqli_num_rows($result) > 0) return true; // on internal blacklist

		// banned ips
		$result = mysqli_query($link, "SELECT id FROM hacker WHERE banned_date > 0 AND real_ip = '".$ip."'");
		if (mysqli_num_rows($result) > 0) return true; // ip of a banned user

		// is it valid?
		if (!IsValidIP($ip)) return true;
		
		// still here? ok, then were good
		return false;
	}	
	Function IsOwned($server_id) {
		Global $link;
		$query = "SELECT id FROM server WHERE hacker_id > 0 AND id = ".$server_id;
		$result = mysqli_query($link, $query);

		if (mysqli_num_rows($result) == 0) return false;
		else return true;
	}	
	Function IsImprisoned($hacker_id) {
		Global $link;
		global $date_format;
		$now = date($date_format);
		$query = "SELECT id FROM hacker WHERE prison_from <= '".$now."' AND prison_till >= '".$now."' AND id = ".$hacker_id;
		$result = mysqli_query($link, $query);

		if (mysqli_num_rows($result) == 0) return false;
		else return true;
	}	
	Function IsJailed($hacker_id) {
		Global $link;
		global $date_format;
		$now = date($date_format);
		$query = "SELECT id FROM hacker WHERE jailed_from < '".$now."' AND jailed_till > '".$now."' AND id = ".$hacker_id;
		$result = mysqli_query($link, $query);

		if (mysqli_num_rows($result) == 0) return false;
		else return true;
	}	
	Function GetNewUserID() {
		Global $link;
		$result = mysqli_query($link, "SELECT id FROM hacker ORDER BY id DESC LIMIT 0,1");
		$row = mysqli_fetch_assoc($result);
		return intval($row['id']) +1;
	}
	Function GetNewClanID() {
		Global $link;
		$result = mysqli_query($link, "SELECT id FROM clan ORDER BY id DESC LIMIT 0,1");
		$row = mysqli_fetch_assoc($result);
		return intval($row['id']) +1;
	}
	Function isvalidemail($string) {
		if(!filter_var($string, FILTER_VALIDATE_EMAIL)) return false;
		//if(!preg_match('/^[^@]+@[a-zA-Z0-9._-]+\.[a-zA-Z]+$/', $string)) return false; // bad format
        
		$fakes = Array ("@mailinator.com", 
		"@spam4.me",
        "@rmqkr.net",
        "@trashymail.com", 
        "@mailexpire.com", 
        "@temporaryinbox.com", 
        "@MailEater.com", 
        "@spambox.us", 
        "@spamhole.com", 
        "@spamhole.com", 
        "@jetable.org", 
        "@guerrillamail.com", 
        "@uggsrock.com", 
        "@10minutemail.com", 
        "@dontreg.com",
        "@tempomail.fr",
        "@TempEMail.net",
        "@spamfree24.org",
        "@spamfree24.de",
        "@spamfree24.info",
        "@spamfree24.com",
        "@spamfree.eu",
        "@kasmail.com",
        "@spammotel.com",
        "@greensloth.com",
        "@spamspot.com",
        "@spam.la",
        "@mjukglass.nu",
        "@slushmail.com",
        "@trash2009.com",
        "@mytrashmail.com",
        "@mailnull.com",
        "@sharklasers.com",
        "@nowmymail.com",
        "@armyspy.com",
        "@teleworm.us",
        "@dayrep.com",
        "@yopmail.com",
        "@yopmail.fr",
		"@yopmail.net",
		"@cool.fr.nf",
		"@jetable.fr.nf",
		"@nospam.ze.tc",
		"@nomail.xl.cx",
		"@mega.zik.dj",
		"@speed.1s.fr",
		"@courriel.fr.nf",
		"@moncourrier.fr.nf",
		"@monemail.fr.nf",
		"@monmail.fr.nf",
		"@ypmail.webarnak.fr.eu.org",
		"@mailnesia.com" ,
		"@vipmailonly.info",
		"@dispostable.com",
		"@temporarioemail.com.br",
		"@tempinbox.com",
		"guerrillamail.de",
        "@jetable.org");
        
        foreach ($fakes as $fake)
            if (stripos($string, $fake) !== false) return false;
        
        return true;
	}	
	Function isvalidpassword($string) {
		if (ctype_alnum($string)) { return true; }
		else { return false; }
	}
	Function isvaliduser($string) {
		// alphanumeric with not more then 1 underscore between texts
		if(!preg_match('/^[A-Za-z0-9][A-Za-z0-9]*(?:_[A-Za-z0-9]+)*$/', $string)) {
			return false;
		}
		else { 
			return true;
		}
	}
	Function isvalidclan($string) {
		// alphanumeric with not more then 1 space between texts
		if(!preg_match("/^[a-z0-9]+([\\s]{1}[a-z0-9]|[a-z0-9])+$/i", $string)) {
			return false;
		}
		else { 
			return true;
		}
	}
	Function isvalidfilename($string) {
		// alphanumeric with not more then 1 space between texts
		if(!preg_match("/^[a-z0-9]+([\\s]{1}[a-z0-9]|[a-z0-9])+$/i", $string)) {
		}
		else { 
			return true;
		}
	}
	Function isHex($color) {
		if ( preg_match("/^[a-f0-9]{1,}$/is", $color) ) return true;
		else return false;
	}
	Function sql($value, $stripslashes = true) {    
		Global $link;
		if ($stripslashes) $value = stripslashes($value);
		$value = htmlspecialchars($value, ENT_QUOTES);
		$value = trim($value);
		$value = str_replace("\t", " ", $value);
		$value = mysqli_real_escape_string($link, $value);
        return $value;
	}
	Function randomip() {
		Global $link;
		$unique = false;
		while ($unique == false) {
			$rand = array();
			$unique = true;
			
			for($i = 0; $i < 4; $i++) { 
				$rand[] = mt_rand(1,255); 
			} 	
			$ip = "{$rand[0]}.{$rand[1]}.{$rand[2]}.{$rand[3]}";
			// pc's
			$result = mysqli_query($link, "SELECT id FROM hacker WHERE ip = '$ip'");
			if (mysqli_num_rows($result) > 0 ) $unique = false;
			
			// servers
			$result = mysqli_query($link, "SELECT id FROM server WHERE ip = '$ip'");
			if (mysqli_num_rows($result) > 0 ) $unique = false;
		}	
		return($ip);
	}
	// convert database format of a date to a readable format
	Function Number2Date($number, $showdays = true) {
		if($number == 0) return "";
		if (strlen($number) == 0 || $number == '99999999999999') return "Unknown";
		global $date_format;
		$now = date($date_format);
		
		$year 	= substr($number, 0, 4);
		$month 	= substr($number, 4, 2);
		$day 	= substr($number, 6, 2);
		if (strlen($number) > 8) {
			$hour 	= substr($number, 8, 2);
			$minute = substr($number, 10, 2);
			$second = substr($number, 12, 2);
		}
		else {
			$hour = "00";
			$minute = "00";
			$second = "00";
		}
		
		$date_day = $year."-".$month."-".$day;
		$date_time = $hour.":".$minute.":".$second;
		
		// today / yesterday
		if ($showdays) {
			$today = $now;
			$yesterday = date($date_format, strtotime("-1 day"));
			if (substr($today, 0, 8) == $year.$month.$day) $date_day = "Today";
			if (substr($yesterday, 0, 8) == $year.$month.$day) $date_day = "Yesterday";
		}	
		
		return  $date_day." ".$date_time;
	}

	Function SecondsDiff($fromtime, $totime) {
		$fromyear 	= substr($fromtime, 0, 4);
		$frommonth 	= substr($fromtime, 4, 2);
		$fromday 	= substr($fromtime, 6, 2);
		$fromhour 	= substr($fromtime, 8, 2);
		$fromminute = substr($fromtime, 10, 2);
		$fromsecond = substr($fromtime, 12, 2);
		
		$toyear 	= substr($totime, 0, 4);
		$tomonth 	= substr($totime, 4, 2);
		$today 		= substr($totime, 6, 2);
		$tohour 	= substr($totime, 8, 2);
		$tominute 	= substr($totime, 10, 2);
		$tosecond 	= substr($totime, 12, 2);
		
		$from = mktime($fromhour,  $fromminute, $fromsecond, $frommonth, $fromday, $fromyear);
		$to = mktime($tohour, $tominute, $tosecond, $tomonth, $today, $toyear);
		$result = $to - $from;
		if ($result < 0) { $result = 0; }
		return $result;
	}
	// return the EP of a hacker
	Function GetHackerEP($hacker_id) {
		Global $link;
		$query = "SELECT ep FROM hacker WHERE id = ".$hacker_id;
		$result = mysqli_query($link, $query);
		if (mysqli_num_rows($result) == 0) return 0;
		else {
			$row = mysqli_fetch_assoc($result);
			return $row['ep'];
		}
	}
	Function IsBanned($hacker_id) {
		if (mysqli_get_value('banned_date', 'hacker', "id", $hacker_id) > 0) return true;
		else return false;
	}	
	Function IsOnline($hacker_id) {
		Global $link;
		global $date_format;
		global $now;
		// also check for not yet activated accounts
		$query = "SELECT id FROM hacker WHERE activationcode = '' AND id = ".$hacker_id;
		$result = mysqli_query($link, $query);
		if (mysqli_num_rows($result) == 0) {
			// not yet active account
			//if ($hacker_id == 8157) return "1";
			return false;
		}
		else {
			$query = "SELECT id FROM hacker WHERE offline_from <= '".$now."' AND offline_till >= '".$now."' AND id = ".$hacker_id;
			$result = mysqli_query($link, $query);
			if (mysqli_num_rows($result) == 0) {
			//if ($hacker_id == 8157) return "2";
				return true;
			}
			else {
				return false;
			//if ($hacker_id == 8157) return "3";
			}	
		}	
	}	
	Function GetHackerMoney($hacker_id) {
		Global $link;
		$query = "SELECT bankaccount FROM hacker WHERE id = ".$hacker_id;
		$result = mysqli_query($link, $query);
		$row = mysqli_fetch_assoc($result);
		return $row['bankaccount'];
	}	
	Function GetHackerRankValue($hacker_id) {
		Global $link;
		global $date_format;
		$query = "SELECT value FROM rank WHERE ".GetHackerEP($hacker_id)." >= min_ep AND ".GetHackerEP($hacker_id)." <= max_ep";
		$result = mysqli_query($link, $query);
		if (mysqli_num_rows($result) == 0) return 0;
		else {
			$row = mysqli_fetch_assoc($result);
			return $row['value'];
		}
	}	
	Function PrintMessage($style,$message,$width ="100%", $border = 1, $showbbc = true, $linkwiki = false) {
		// $style: locked, quote, error, success, warning, info, help, news
		// all optional paramters are legacy and no longer used. when you encounter this in code, remove them and just send $style and $message
		if($showbbc) $message = replaceBBC($message);
		echo '<div class="message '.strtolower($style).'">'.$message.'</div>';
	}
	Function IsValidIP($ip) {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE) && filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_RES_RANGE)) return true; 
		else return false;
	}
	 
	// get user IP
	Function GetUserIP() {
		$ip = '';
		if (isset($_SERVER)) { 
			if (isset($_SERVER["REMOTE_ADDR"])) $ip = $_SERVER["REMOTE_ADDR"];
			elseif (isset($_SERVER["HTTP_CLIENT_IP"])) $ip = $_SERVER["HTTP_CLIENT_IP"];
			elseif (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) $ip = $_SERVER["HTTP_X_FORWARDED_FOR"]; // can be spoofed, try last
			else $ip = 'unknown';
		}  
		else { 
			if (getenv( 'REMOTE_ADDR' )) $ip = getenv( 'REMOTE_ADDR' );
			elseif (getenv( 'HTTP_CLIENT_IP' )) $ip = getenv( 'HTTP_CLIENT_IP' );
			elseif (getenv( 'HTTP_X_FORWARDED_FOR' )) $ip = getenv( 'HTTP_X_FORWARDED_FOR' );
			else $ip = 'unknown';
		}
		return $ip;
	}	
    Function LogUserIP($hacker_id) {
		Global $link;
		Global $date_format;
		$query = "UPDATE hacker SET real_ip = '".GetUserIP()."', last_login = ".date($date_format)." WHERE id = ".$hacker_id;
		$result = mysqli_query($link, $query);
    }
    Function ClanIM($sender, $clan_id, $title, $message) {
    	Global $link;
    	$result = mysqli_query ($link, "SELECT id FROM hacker WHERE clan_id = $clan_id");
    	if (mysqli_num_rows($result) > 0) {
    		while ($row = mysqli_fetch_assoc($result)) {
    			SendIM ($sender, $row['id'], $title, $message, $clan_id);
    		}
    	}
    }
	Function SendIM($sender, $reciever, $title, $message, $date, $clan_id = 0) {
		Global $link;
		Global $staff_clanid;
		Global $ibot_id;
		Global $now;

		$sender_del = 0;
		$reciever_del = 0;

		// is the receiver an npc? then don't send the message
		$npc_id = mysqli_get_value ("npc", "hacker", "id", $reciever);
		if ($npc_id > 0) $reciever_del = 1;
		
		if ($sender == 0 || $sender == $ibot_id) $sender_del = 1; // SYSTEM does not keep archives
		
		// is the sender (not admins or mods) on the receivers ignore list?
		if (OnList($reciever, $sender, "ignore") && (!InGroup($sender, 1) && !InGroup($sender, 2))) {
			$reciever_del = 1;
			if ($sender_del == 0) SendIM (0, $sender, "BLOCKED: $title", "Your message was blocked by the receiving party.", $now); // did a user send a message that got blocked? warn him.
		}
		
		// premium members can choose to receive IM's by mail, as long as they are not system/ibot messages
		if ($sender_del == 0 && $reciever_del == 0) {
			$result = mysqli_query($link, "SELECT email FROM hacker WHERE id = $reciever AND cc2mail = 1");
			if (mysqli_num_rows($result) == 1) {
				$row = mysqli_fetch_assoc($result);
				$alias = mysqli_get_value("alias", "hacker", "id", $sender);
				SendMail ($row['email'], $title, "On ".Number2Date($now)." $alias Wrote<br><br>". $message);
			}
		}
		
		// send the actual IM
		if ($reciever_del == 0) $result = mysqli_query($link, "INSERT INTO im (sender_id, sender_del, reciever_id, reciever_del, date, unread, title, message, clan_id) VALUES ($sender, $sender_del, $reciever, $reciever_del, '$date', 1, '$title', '".addslashes($message)."', $clan_id)");
	}
	Function AddLog($id, $entity, $event, $description, $date = 0) {
		Global $link;
		Global $date_format;
		Global $now;
		if($date == 0) $date = $now;
		if ($entity == "hacker")
			if (mysqli_get_value ("npc", "hacker", "id", $id) > 0)  return false; // do not log for NPCs
		if ($entity == "server")
		{
			$ip = mysqli_get_value ("ip", "server", "id", $id);
			$description = "[$ip]&nbsp;$description";
		}
		$result = mysqli_query($link, "INSERT INTO log (event, ".$entity."_id, date, details) VALUES ('$event', $id, '$date', '$description')");
	}	
    Function br2nl($text) {
		$text = str_replace("<br>","\n",$text); 
		$text = str_replace("<br />","\n",$text); 
		return $text; 
    	}
	Function StealMoney($victim_id, $victim_entity, $hacker_id, $percentage, $reason, $date) {
		Global $link;
		// inserts a line into cronbank. that table gets read every minute by a cronjob
		Global $now;
		$result = mysqli_query($link, "INSERT INTO cronbank (victim_id, victim_entity, hacker_id, percentage, reason, date) VALUES ($victim_id, '$victim_entity', $hacker_id, $percentage, '$reason', '$date')");
		AddLog($hacker_id, "hacker", "cronbank", "Hacker id $hacker_id stole $percentage% of $victim_entity id $victim_id on $date", $now);
	}
	Function BankTransfer($entity_id, $entity, $amount, $reason, $date = '') {
		Global $link;
		Global $now;
		Global $high_amount;
		$amount = round($amount);
		if ($amount > $high_amount || $amount < ($high_amount * -1)) {
			if ($amount > 0) $sendreceived = "received";
			else $sendreceived = "sent";
			
			
			$message = "There was a Suspicious Money Transfer (SMT).<br><br>$entity ";
			if ($entity == "clan") $message .= ShowClanAlias($entity_id);
			else $message .= ShowHackerAlias($entity_id);
			$message .= " $sendreceived ".number_format($amount).".<br><br>Please investigate.";
			$result = mysqli_query($link, "INSERT INTO ticket (hacker_id, status_id, type_id, date, title, message) VALUES (0, 1, 4, '$now', 'SMT: ".number_format($amount)."', '$message')");
		}
		if ($date == '' || $date == $now) {
			// normal bank transfer
			$result = mysqli_query($link, "UPDATE ".$entity." SET bankaccount = bankaccount + ".$amount." WHERE id = ".$entity_id);
			AddLog($entity_id, $entity, "bank", $amount."|".$reason, $now);
		}
		else $result = mysqli_query($link, "INSERT INTO cronbank (hacker_id, amount, reason, date) VALUES ($entity_id, $amount, '$reason', '$date')");
	}
	Function Prison($hacker_id, $reason, $free_date) {
		Global $link;
		global $now;
		// delete system config, computer is destroyed by fbi
		$result = mysqli_query($link, "DELETE FROM system WHERE product_id NOT IN (SELECT id FROM product WHERE code = 'INTERNET') AND hacker_id = $hacker_id");
		$result = mysqli_query($link, "DELETE FROM inventory WHERE server_id = 0 AND hacker_id = $hacker_id");
		$result = mysqli_query($link, "UPDATE im SET reciever_del = 1, pinned = 0 WHERE reciever_id = $hacker_id");
		$result = mysqli_query($link, "UPDATE log SET deleted = 1 WHERE hacker_id = $hacker_id");
		$result = mysqli_query($link, "UPDATE hacker SET bankaccount = 0, fbi_wanteddate = '0', prison_from = '$now', prison_till = '$free_date', prison_reason = '$reason' WHERE id = $hacker_id");
		
		// Delete all perks
		$result = mysqli_query($link, "SELECT id FROM perks WHERE hacker_id = $hacker_id");
		while($row = mysqli_fetch_assoc($result)) RemovePerk($row['id'], "Imprisoned");
		
		CleanSystem($hacker_id, "System destroyed by FBI", "hacker", -1); // kill ALL infections on this person
		$result = mysqli_query($link, "UPDATE infection SET success = 1, ready = 1, date = '$now' WHERE victim_entity = 'hacker' AND hacker_id = $hacker_id"); // kill all infections of this person
		RegisterResult ($hacker_id, "imprisoned", $free_date);
	}
	Function Jail($hacker_id, $bail, $time, $reason) {
		Global $link;
		global $date_format;
		
		if(InGroup($hacker_id, 1) || InGroup($hacker_id, 2)) return false;
		
		// Time
		$now = date($date_format);
		$time = intval(((EP2Level(GetHackerEP($hacker_id)) / 10) +1) * $time);
		if ($time < 1) $time = 1; //  at least go to jail for 1 minute if the jailtime is too low.
		
		// Bail
		$bail = intval(((EP2Level(GetHackerEP($hacker_id)) / 10) +1) * $bail);
		$time = "+".$time." minutes";
		$till = date($date_format, strtotime($time));
		
		// Perk
		$perk = GetPerkValue($hacker_id, "PERK_DECREASEBAIL");
		if($perk != FALSE) $bail *= (100 - $perk)/100;
		
		$result = mysqli_query($link, "UPDATE hacker SET jailed_from = '".$now."', jailed_till = '".$till."', jailed_bail = ".$bail.", jailed_reason = '".$reason."' WHERE id = ".$hacker_id);
		RegisterResult ($hacker_id, "jailed", $till);
		echo '<script type="text/javascript">location.href = location.href</script>';
	}
	Function ShowProgress($progress, $total, $label = "") {
		$bar = '';
		$green = 0;
		$orange = 0;
		$red = 0;
		$none = 0;
				
		if ($total == 0) return false;
		
		$progress = intval((($progress / $total)) * 100);
		for ($i = 0; $i < $progress; $i++) {
			if ($progress < 33) $red++;
			elseif ($progress < 66) $orange++;
			else $green++;
		}
		$rest = 100 - $progress;
		for ($i = 0; $i < $rest; $i++) {
			$none++;
		}
		
		//if ($label = "PROGRESS") $label = $progress.'%';
		$label .= " ".$progress."%";

		return '<progress min="0" max="100" value="'.$progress.'" title="'.$label.'"></progress>';
	}
?>
