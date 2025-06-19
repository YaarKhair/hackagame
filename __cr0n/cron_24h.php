<?php
	// this cron runs at midnight
	include("/var/www/modules/connectdb.php");
	include("/var/www/modules/settings.php");
	include("/var/www/modules/functions.php");
		
	// Delete expired password reset tokens
	$before = date($date_format, strtotime("- $token_expiry days"));
	$token_result = mysqli_query($link, "SELECT id FROM hacker WHERE resettoken_date < '$before' AND pass_resettoken != '0'");
	while($token_row = mysqli_fetch_assoc($token_result)) 
		$token_update = mysqli_query($link, "UPDATE hacker SET pass_resettoken = 0 WHERE id = {$token_row['id']}");
	
	// Delete hacker avatar is hacker is inactive for 3 months
	$before_months = date($date_format, strtotime("- $delete_avatar_time months"));
	$delete_avatar_result = mysqli_query($link, "SELECT id FROM hacker WHERE last_click <= '$before_months'");
	while($delete_row = mysqli_fetch_assoc($delete_avatar_result)) DeleteAvatar($delete_row['id'], "hacker");
	
	// did players trigger a double EP day?
	$bank_ibot = mysqli_get_value("bankaccount","hacker","id",$ibot_id);
	if($bank_ibot >= $double_ep_amount) {
		$date = substr(date($date_format,strtotime("+ 1 day")), 0, 8);
		$result = mysqli_query($link, "INSERT INTO doubleep_date (date) VALUES ('$date')");
		$result = mysqli_query($link, "UPDATE hacker SET bankaccount = 0 WHERE id = $ibot_id");
		
		// announce it on facebook
		$message = "The threshold of $currency".number_format($double_ep_amount)." was reached, and that is why today is a<br><br>User Triggered Double EP Day";
		SendMail ("trigger@recipe.ifttt.com", "#update", $message);
	}
	
	// rent for owned servers, SORT BY HACKER, IMPORTANT
	$result = mysqli_query($link, "SELECT server.id, server.hacker_id, hacker.bankaccount, hacker.last_click, hacker.hybernate_till FROM server INNER JOIN hacker ON server.hacker_id = hacker.id WHERE server.gateway = 0 AND hacker.npc = 0 AND hacker.clan_id <> $staff_clanid AND hybernate_till < $now ORDER BY hacker_id ASC");
	$hacker_id = 0;
	$rent = 0;
	$im = '';
	$drop_date = date($date_format, strtotime("-".$server_dropdays." days")); 
    
	// loop through servers
	if (mysqli_num_rows($result) > 0) {
		while ($row = mysqli_fetch_assoc($result)) {
			// new hacker? compare with previous hacker
			if ($hacker_id != $row['hacker_id'] && $hacker_id != 0) {
				if ($rent > 0) BankTransfer($hacker_id, "hacker", $rent * -1, "SkyNet - Server+Hosting Account");
				if ($im <> '') SendIM(0, $hacker_id, "SkyNet ISP", "Dear Sir, Madam,<br><br>Because you didn't have sufficient funds to pay the server rent, the contracts for some servers were cancelled.".PHP_EOL.$im, $now);
				$rent = 0;
				$im = '';
			}
			if ($rent + $server_rent > $row['bankaccount']) {
				// you can not afford to pay for this server, so drop it
				$im .= 'Server '.GetServerName($row['id']).' -- Cancelled.<br>';				
				DropServer ($row['id'], $row['hacker_id'], "Funds issue");
			}
			else $rent += $server_rent;
			$result3 = mysqli_query($link, "UPDATE server SET profit = profit - ".$server_rent." WHERE id = ".$row['id']); // substract rent of counter of current server
			$result3 = mysqli_query($link, "UPDATE server SET efficiency = efficiency - ".$daily_server_decrease." WHERE id = ".$row['id']." AND efficiency > ".($daily_server_decrease+1));
            if ($row['last_click'] < $drop_date && $row['hybernate_till'] < $drop_date) DropServer ($row['id'], $row['hacker_id'], "Inactivity"); // if your hibernation is just over, its not fair to flag them as inactive
			$hacker_id = $row['hacker_id']; // current hacker
		}
        // if there was 1 record the transfer will not have been made
        if ($im <> '') {
			if ($rent > 0) BankTransfer($hacker_id, "hacker", $rent * -1, "SkyNet - Server+Hosting Account");
			if ($im <> '') SendIM(0, $hacker_id, "SkyNet ISP", "Dear Sir, Madam,<br><br>Because you didn't have sufficient funds to pay the server rent, the contracts for some servers were cancelled.".PHP_EOL.$im, $now);
        }
	}
	sleep (1);
	
	// find id of dailup connection
	$result = mysqli_query($link, "SELECT id, efficiency FROM product WHERE code = 'INTERNET' AND price = 0");
	$row = mysqli_fetch_assoc($result);
	$dailup_id = $row['id'];
	$dailup_efficiency = $row['efficiency'];

	
	// reset to dailup connection when funds are beneath connection fee
	$result = mysqli_query($link, "SELECT hacker.id FROM hacker LEFT JOIN system ON hacker.id = system.hacker_id INNER JOIN product ON system.product_id = product.id WHERE hacker.hybernate_till < '$now' AND product.code = 'INTERNET' AND product.price > 0  AND hacker.bankaccount < product.price");
	if (mysqli_num_rows($result) > 0) {
		while ($row = mysqli_fetch_assoc($result)) {
			$result2 = mysqli_query($link, "UPDATE system SET system.product_id = $dailup_id, efficiency = $dailup_efficiency WHERE system.product_id IN (SELECT id FROM product WHERE code = 'INTERNET') AND hacker_id = ".$row['id']);
			SendIM (0, $row['id'], "SkyNet ISP", "Dear Sir, Madam,<br><br>Because you didn't have sufficient funds to pay the ISP connection fee your ISP contract is cancelled. We have reverted your connection to Dialup.", $now);
		}
	}
	
	//pay connection fee 
	$result = mysqli_query($link, "SELECT hacker.id, product.price FROM hacker LEFT JOIN system ON hacker.id = system.hacker_id LEFT JOIN product ON system.product_id = product.id WHERE hacker.hybernate_till < ".$now." AND product.code = 'INTERNET' AND product.price > 0");
	if (mysqli_num_rows($result) > 0) {
		while ($row = mysqli_fetch_assoc($result)) {
			BankTransfer ($row['id'], "hacker", $row['price'] * -1, "SkyNet - Personal Account");
		}
	}
	sleep (1);
	
	$role_date = date($date_format, strtotime("-".$role_dropdays." days"));
	
	// loop through active hackers and do stuffz
	$result = mysqli_query($link, "SELECT id, banned_date, clan_id, activationcode, started, hybernate_till, last_click, bankaccount FROM hacker WHERE active = 1");
	if (mysqli_num_rows($result) > 0) {
		while ($row = mysqli_fetch_assoc($result)) {
			// take roles if inactve for x day
			if($row['last_click'] < $role_date && $row['hybernate_till'] < $role_date) {
				$result2 = mysqli_query($link, "DELETE FROM hacker_permgroup WHERE hacker_id = {$row['id']}");
			}			
            // daily values for players
        	$result2 = mysqli_query($link, "UPDATE hacker SET ads = $daily_ads, topics = $daily_topics, support_tickets = $daily_tickets WHERE banned_date = 0 AND id = {$row['id']}");
            
			// decrease hdd if not hibernating
			if ($row['hybernate_till'] < $now)
				$result2 = mysqli_query($link, "UPDATE system SET efficiency = efficiency - ".$daily_hdd_decrease." WHERE product_id IN (SELECT id FROM product WHERE code = 'HDD') AND efficiency > ".($daily_hdd_decrease+1)." AND hacker_id = {$row['id']}");
			else
				BankTransfer ($row['id'], "hacker", round(($row['bankaccount'] / 100) * $hibernation_interest) * -1, "Hibernation interest"); // pay interest of your bank account while you are hibernating
			
			// kick members from a clan and empty their bank accounts 1 day after they were banned 
			if ($row['banned_date'] > 0 && $row['banned_date'] < date($date_format, strtotime("-$banned_resetdays days"))) {
				if ($row['clan_id'] > 0) {
					If (IsFounder($row['id'])) KillClan($row['clan_id'], "Founder banned"); // remove all
					else RemoveFromClan($row['id'], $row['clan_id'], "Banned."); // remove you
				}	
				$result2 = mysqli_query($link, "DELETE FROM system WHERE hacker_id = {$row['id']} OR backup_id = {$row['id']}"); // delete system
				$result2 = mysqli_query($link, "DELETE FROM inventory WHERE hacker_id = {$row['id']}"); // delete files
				$result2 = mysqli_query($link, "UPDATE im SET reciever_del = 1 WHERE reciever_id = {$row['id']}"); // delete instant messages
				$result2 = mysqli_query($link, "UPDATE im SET sender_del = 1 WHERE sender_id = {$row['id']}"); // delete instant messages
				$result2 = mysqli_query($link, "UPDATE hacker SET bankaccount = 0, active = 0, gamble_won = 0, gamble_lost = 0 WHERE id = {$row['id']}"); // reset bank, etc. and set to inactive, so he won't get cleaned up again
				$result2 = mysqli_query($link, "DELETE FROM hacker_permgroup WHERE hacker_id = {$row['id']}"); // delete from any group
				// infections
				//$result2 = mysqli_query($link, "DELETE FROM infection WHERE hacker_id = {$row['id']}");
				CleanSystem($row['id'], "System banned", "hacker", -1); // ALL infections
			}	
			// not activated in 12 hours
			if ($row['activationcode'] <> '' && $row['started'] < date($date_format, strtotime("-12 hours")))
				$result2 = mysqli_query($link, "DELETE FROM hacker WHERE banned_date = 0 AND id = {$row['id']}");
			// set active = 0 on hackers who have not been online/active in a month for exclusion from stats
			if ($row['last_click'] < date($date_format, strtotime("-1 month")))
				$result2 = mysqli_query($link, "UPDATE hacker SET active = 0 WHERE id = {$row['id']}");
		}		
				
	}
	sleep (1);

	// loop through clans and do some stuffz
	$result = mysqli_query($link, "SELECT clan.id, clan.founder_id, hacker.last_click FROM clan LEFT JOIN hacker ON clan.founder_id = hacker.id WHERE clan.active = 1");
	if (mysqli_num_rows($result) > 0) {
		while ($row = mysqli_fetch_assoc($result)) {
		
			// no members? kill the clan
			$result2 = mysqli_query($link, "SELECT id FROM hacker WHERE clan_id = {$row['id']}");
			if (mysqli_num_rows($result2) == 0)
				KillClan($row['id'], "No members.");
				
			// offline gateway (x days)? kill the clan            
			$result2 = mysqli_query($link, "SELECT id FROM server WHERE gateway = 1 AND hacker_id = {$row['founder_id']} AND offline_from < '".date($date_format, strtotime("-$gateway_dropdays days"))."' AND offline_till = '99999999999999'");
			if (mysqli_num_rows($result2) > 0)
				KillClan($row['id'], "Gateway offline for too long");
				
			// inactive founder? kill the clan
			if ($row['last_click'] < date($date_format, strtotime("-$founder_inactive days")))
				KillClan($row['id'], "Inactive Founder.");
		}
	}	
	sleep (1);
	
	// auto close old tickets
	$result = mysqli_query($link, "SELECT ticket.id FROM ticket LEFT JOIN ticket_status ON ticket.status_id = ticket_status.id WHERE reply_date < '".date($date_format, strtotime("-$ticket_keep days"))."' AND reply_date != 0 AND (ticket_status.status = 1 OR ticket_status.status = 2) AND ticket.respons_id = 0");
	if (mysqli_num_rows($result) > 0) {
		$message = "Ticket AutoClosed (inactive)";
		$status_id = 17; // closed
		while ($row = mysqli_fetch_assoc($result)) {
			$result2 = mysqli_query($link, "UPDATE ticket SET status_id = $status_id WHERE id = ".intval($row['id']));
			$result2 = mysqli_query($link, "INSERT INTO ticket (respons_id, hacker_id, date, message) VALUES (".intval($row['id']).", 0, '$now', '$message')");
		}	
	}
	sleep (1);
	
	// remove old topics from the public forums
	$topic_date = date($date_format, strtotime("-$topic_keep days"));
	$result = mysqli_query($link, "SELECT thread.id FROM thread LEFT JOIN board ON thread.board_id = board.id WHERE board.clan_id = 0 AND creation_date < '$topic_date'");
	if (mysqli_num_rows($result) > 0) {
		while ($row = mysqli_fetch_assoc($result)) {
			$result2 = mysqli_query($link, "DELETE FROM thread_reply WHERE thread_id = {$row['id']}"); // remove posts in the topics we are about to remove
			$result2 = mysqli_query($link, "DELETE FROM thread WHERE id = {$row['id']}"); // remove the actual topic
		}
	}	
	sleep (1);
	
	// remove premium settings when donator period is over
	$result = mysqli_query($link, "UPDATE hacker SET custom_css = '', custom_logo = '', chat_color = '', show_ads = 1, sound_email = 0, publicstats = 0, show_friends = 0, show_foes = 0, show_epskill = 0, cc2mail = 0, donator_till = '0' WHERE donator_till < '$now'");

	// remove old bounties
	$valid_date = date($date_format, strtotime("- $bounty_days days"));
	$result = mysqli_query($link, "DELETE FROM bounty WHERE date < '$valid_date'");
	
	// reset the recently locked out accounts
	$result = mysqli_query($link, "UPDATE hacker SET failed_logins = 0, lockout_till = 0 WHERE lockout_till <> '0' AND lockout_till < '$now'");
	
	// update incorrect bankaccounts (this should never be needed, but hey.. "Only Human")
	$result = mysqli_query($link, "UPDATE hacker SET bankaccount = 0 WHERE bankaccount < 0");

	// delete old IMs that are deleted by both the sender and the reciever are deleted after x days
	$result = mysqli_query($link, "DELETE FROM im WHERE sender_del = 1 AND reciever_del = 1 AND date < '".date($date_format, strtotime("-$im_keep days"))."'");
	
	// delete old IMs that older than logkeep days
	$result = mysqli_query($link, "DELETE FROM im WHERE pinned = 0 AND date < '".date($date_format, strtotime("-$log_keep days"))."'");
	sleep (1);
	
	// delete old lines from the log
	$result = mysqli_query($link, "DELETE FROM log WHERE event <> 'staff' AND date < '".date($date_format, strtotime("-$log_keep days"))."'");
	// delete FBI Logs
	$result = mysqli_query($link, "DELETE FROM log WHERE event = 'fbi' AND date < '".date($date_format, strtotime("-$fbi_log_keep days"))."'");
	// delete old invites
	$result = mysqli_query($link, "DELETE FROM invite WHERE date < '".date($date_format, strtotime("-$invite_keep days"))."'");
	// delete orphaned files
	$result = mysqli_query($link, "DELETE FROM file LEFT JOIN inventory ON file.id = inventory.file_id WHERE inventory.id is null");
	// delete orphaned topics
	$result = mysqli_query($link, "DELETE FROM topic WHERE clan_id IN (SELECT id FROM clan WHERE active = 0)");
	// delete chatlogs older than a month
	$result = mysqli_query($link, "DELETE FROM chat WHERE date < '".date($date_format, strtotime("- $chat_keep days"))."'");
	sleep (1);
	
	// optimize db
	$result = mysqli_query($link, "SELECT table_name FROM information_schema.tables WHERE table_schema = 'hf'");
	$tables = array();
	while($row = mysqli_fetch_assoc($result)) $tables[] = '`'.$row['table_name'].'`';
    $result = mysqli_query($link, "OPTIMIZE TABLE ".implode($tables, ","));
	
	// Run the captcha image search after everything is done so that some things are not late
	//include("/var/www/modules/captchaimgsearch.php");
	
    AddLog (0, "hacker", "cron", "cron_24h", $now);	
?>