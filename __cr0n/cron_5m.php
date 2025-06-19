<?php
	// this cron runs every 5 minutes
	include("/var/www/modules/connectdb.php");
	include("/var/www/modules/settings.php");
	include("/var/www/modules/functions.php");
	include("/var/www/modules/cronep.php");
	
	// wars that are over
	$result = mysqli_query($link, "SELECT * FROM war WHERE active = 1 AND (attacker_points < 1 OR victim_points < 1)");
	if(mysqli_num_rows($result) > 0) {
		while($row = mysqli_fetch_assoc($result)) {
			
			// Who the lost the war?
			if($row['attacker_points'] == 0) {
				$loser = 'attacker';
				$winner = 'victim';
			}
			else {
				$loser = 'victim';
				$winner = 'attacker';
			}
			
			// Give the winner his cash
			BankTransfer($row[$winner.'_clanid'], 'clan', $row['locked_amount'], 'Your winnings from the war');
	
			// End the war
			$result2 = mysqli_query($link, "UPDATE war SET active = 0, end_date = '$now' WHERE id = {$row['id']}");
			
			// Send a message to the loser and the winner
			$winner_id = mysqli_get_value('founder_id', 'clan', 'id', $row[$winner.'_clanid']);
			$loser_id = mysqli_get_value('founder_id', 'clan', 'id', $row[$loser.'_clanid']);
			$loser_clan = mysqli_get_value('alias', 'clan', 'id',  $row[$loser.'_clanid']);
			$winning_clan = mysqli_get_value('alias', 'clan', 'id',  $row[$winner.'_clanid']);
			
			// Message to winner
			sendIM(0, $winner_id, 'War is won', 'Congratulations on winning the war vs '.$loser_clan.'!<br>You have been awarded $'.number_format($row['locked_amount']), $now);
			
			// Message to loser
			sendIM(0, $loser_id, 'War is lost', 'You have lost the war vs '.$winning_clan, $now);
			
			// Update wars won and lost for the clans
			mysqli_query($link, "UPDATE clan SET wars_won = wars_won + 1 WHERE id = ".$row[$winner.'_clanid']);
			mysqli_query($link, "UPDATE clan SET wars_lost = wars_lost + 1 WHERE id = ".$row[$loser.'_clanid']);
			
			// Update wars won and lost for the members
			RegisterResult($row[$winner.'_clanid'], "war_win", $now, "clan");
			RegisterResult($row[$loser.'_clanid'], "war_fail", $now, "clan");
			
			// Give EP to the winning clan
			$result3 = mysqli_query($link, "SELECT id FROM hacker WHERE clan_id = ".$row[$winner.'_clanid']);
			while($members = mysqli_fetch_assoc($result3))
				AddEP($members['id'], $war['ep_reward'], 0, $now, 'WAR');				
			
		}
	}

	// ip refreshes
	$result = mysqli_query($link, "SELECT id, ip FROM hacker WHERE iprefresh_date < $now AND iprefresh_date > 0");
	if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
        	$old = $row['ip'];
    		$new = randomip();
    		$nextrefresh_date = date($date_format, strtotime("+".$iprefresh_cooldown." hours"));
    		$result2 = mysqli_query($link, "UPDATE hacker SET ip = '$new', iprefresh_date = '0', nextiprefresh_date = '$nextrefresh_date' WHERE id = {$row['id']}");
    		AddLog ($row['id'], "hacker", "system", "IP change: $old -> $new by Skynet ISP", $now);
        }   
	}
    
    function CountFlag() {
		Global $link;
		GLOBAL $world_id;
		$result2 = mysqli_query($link, "UPDATE misc SET ctf_counter = ctf_counter -5");
	}	
	function ResetFlag() {
		Global $link;
		GLOBAL $ctf_serverid;
		GLOBAL $ctf_fileid;
		GLOBAL $ctf_price;
		GLOBAL $ctf_ownerid;

		$result2 = mysqli_query($link, "UPDATE inventory set hacker_id = $ctf_ownerid, server_id = $ctf_serverid, price = $ctf_price WHERE product_id = $ctf_fileid"); // back to FTP
		$result2 = mysqli_query($link, "UPDATE misc SET ctf_hackerid = 0, ctf_counter = 0, ctf_started = '0', ctf_updated = '0'"); // update the world
	}
	function RestoreFlag() {
		Global $link;
		GLOBAL $ctf_serverid;
		GLOBAL $ctf_fileid;
		GLOBAL $ctf_price;
		GLOBAL $ctf_ownerid;

		$result2 = mysqli_query($link, "INSERT INTO inventory (product_id, hacker_id, server_id, price) VALUES ($ctf_fileid, $ctf_ownerid, $ctf_serverid, $ctf_price)");
		ResetFlag();
	}
	function StealFlag($hacker_id) {
		Global $link;
		GLOBAL $now;
		GLOBAL $ctf_mintime;
		GLOBAL $ctf_maxtime;
  		GLOBAL $maxlevel;
		
		$level = EP2Level(GetHackerEP($hacker_id));
		
		// how long should this player hold the flag?
		// $ctf_time = (((max -  min) /  maxlevel ) * level) + $ctf_mintime
		$ctf_time = round ( ( ( ($ctf_maxtime - $ctf_mintime) / $maxlevel ) * $level ) + $ctf_mintime );
		// make this time dividable by 5 for the 5 minute cron
		//$level = round($level / 5) * 5;
		$result3 = mysqli_query($link, "UPDATE misc SET ctf_counter = $ctf_time, ctf_hackerid = $hacker_id, ctf_started = '$now'");		
	}	
	
	// who is holding the flag?
	$result = mysqli_query($link, "SELECT hacker_id, server_id FROM inventory WHERE product_id = $ctf_fileid");
	if (mysqli_num_rows($result) == 0) {
		// the file got deleted by a hdd crash or offliner.
		AddLog (0, "hacker", "ctf", "The File was destroyed. File reset to FTP.", $now);	
		RestoreFlag();
	}
	else {	
		$row = mysqli_fetch_assoc($result);
	
		// someone has the file, because it is no longer on the default FTP
		if ($row['server_id'] != $ctf_serverid) {
			$result2 = mysqli_query($link, "SELECT ctf_hackerid, ctf_counter, ctf_lastscoredid FROM misc"); // who had the flag the last 5m cron and who scored the last time? 
			$row2 = mysqli_fetch_assoc($result2);

			// the hacker holding the flag is offline, hibernating, banned or uploaded the file on a server to hide it
			if (!IsOnline($row['hacker_id']) || IsHybernated($row['hacker_id']) || IsBanned($row['hacker_id']) || $row['server_id'] > 0 || $row2['ctf_lastscoredid'] == $row['hacker_id']) {
				ResetFlag();
				RegisterResult ($row['hacker_id'], "ctf_fail", $now);
				AddLog (0, "hacker", "ctf", Alias4Logs($row['hacker_id'], "hacker")." dropped the File. File reset to FTP.", $now);	
			}
			else {
				// a hacker is holding the flag and is online
				// it's the same hacker as the previous check, so let's substract the counter
				if ($row2['ctf_hackerid'] == $row['hacker_id']) {
					// if the timer hits 0
					if ($row2['ctf_counter'] - 5 < 1) {
						ResetFlag();
						// reward
						$ctf_reward = intval(mt_rand($ctf_reward_min,$ctf_reward_max)); // your reward
						SendIM (0, $row['hacker_id'], "$ctf_name decryption", "You did it! You effectively decrypted the $ctf_name file and managed to decypher the bank account details stored in the file. You wired the $currency".number_format($ctf_reward)." to your bank account.", $now);
						AddEP ($row['hacker_id'], $ctf_ep, round($ctf_ep / 5), $now, "CTF");
						RegisterResult ($row['hacker_id'], "ctf_win", $now);
						BankTransfer($row['hacker_id'], "hacker", $ctf_reward, "$ctf_name decrypted.", $now);
						AddLog (0, "hacker", "ctf", Alias4Logs($row['hacker_id'], "hacker")." scored. File reset to FTP.", $now);	
						$result3 = mysqli_query($link, "UPDATE misc SET ctf_lastscoredid = {$row['hacker_id']}"); // set your id as last flag stealer, so you can not steal it again immediately
					}
					else CountFlag();
				}
				else {
					// een nieuwe hacker heeft de file!
					StealFlag($row['hacker_id']);
					RegisterResult ($row2['ctf_hackerid'], "ctf_fail", $now);
					AddLog (0, "hacker", "ctf", Alias4Logs($row['hacker_id'], "hacker")." took the File.", $now);	
				}
			}
		}
	}	
	$result = mysqli_query($link, "UPDATE misc SET ctf_updated = '$now'"); // last update time
	//ResetFlag(); // for admin stuff
	AddLog (0, "hacker", "cron", "cron_5m", $now);	
?>