<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php
	if ($_SESSION['loaded'] == 0) {
		$_SESSION['loaded'] = 1;
		
		$perk = GetPerkValue($hackerdata['id'], "PERK_H4HDECREASE");
		$hackerforhire_jobtime -= $perk;
		$jobdate = date($date_format, strtotime("+".$hackerforhire_jobtime." minutes"));
		$jail = 0;
		$hire_id = 0;
		$success = 0;
		$fail = 0;
		$im = '';
		$interval = 0;
		
		if (!empty($_POST['hire_id'])) $hire_id = intval($_POST['hire_id']);
		
		$num_hackers = 1;
		if (!empty($_POST['num_hackers'])) $num_hackers = intval($_POST['num_hackers']);
		if ($num_hackers < 1) return "Invalid number of hackers hired.";
		if ($num_hackers > $max_hire_hackers) return "You can hire a maximum of $max_hire_hackers hackers.";
		
        $username = "";
        if (!empty($_POST['username'])) $username = sql($_POST['username']);
        
		// Interval field
		if($hire_id == 8) $field = 'nextserverh4h_date';
		else $field = 'nexth4h_date';
		
		// how are you going to pay for this?
		if (!empty($_POST['pay'])) $pay = $_POST['pay'];
		else return "You need to select how you are going to pay for this job.";
		
		// can you afford this?
		$query = "SELECT * FROM hirejobs WHERE id = ".$hire_id;
		$result = mysqli_query($link, $query);
		$row = mysqli_fetch_assoc($result);

        if ($num_hackers > $row['max_hackers']) return "You can hire a maximum of {$row['max_hackers']} for this job.";
        
		$price = $row['price'.$hackerdata['network_id']]; // db price
		if ($price == 0) return "This is not allowed on your network.";
		$refund = 0; // 50% refund on fails
		
		if ($pay == "money") 
        {		
			$totalprice = $price * $num_hackers; // total price
			if ($hackerdata['bankaccount'] < $totalprice) return "You don't have enough money.";
		}
		else 
        {
			$points = $row['hackpoints'] * $num_hackers;
			if ($hackerdata['hackpoints_credit'] < $points) return "You don't have enough hackpoints.";
		}
		
		if ($hire_id == 0) return "Invalid job id";

        // is there an interval for this job? check if you are too soon
		$interval = $row['interval'];
		
		if ($interval > 0) 
        {
				if ($hackerdata['nexth4h_date'] > $now && $hire_id != 8) return "You can request this job again in ".Seconds2Time(SecondsDiff($now, $hackerdata['nexth4h_date']));
				if ($hackerdata['nextserverh4h_date'] > $now && $hire_id == 8) return "You can request this job again in ".Seconds2Time(SecondsDiff($now, $hackerdata['nextserverh4h_date']));
		} 
		
		//echo $hackerdata['nextserverh4h_date'];
		if ($hire_id == 1) 
        {
            if ($username == "") return "You didn't specify a username.";
            
			$log = "$num_hackers hacker(s) are trying to find the IP address of $username.";
			
			for ($i = 0; $i < $num_hackers; $i++) 
            {
				// calculate if the job will be successfull
				if (WillItWork($h4h_findip_chance)) 
                { 
					// success!
					$success ++;
                    $userip = mysqli_get_value("ip", "hacker", "alias", $username, false);
					$im = "<br><br>The IP of $username is $userip<br><br>They might change their IP, so this data is not valid forever.";
				}
				else 
                {
					$fail ++;
					if (GetJailed($hackerdata['id'], $hire_jailchance))	
						Jail($hackerdata['id'], $hire_jailbail, $hire_jailtime, "The hacker you hired to trace your victim\'s IP got caught. After a long interrogation, he spilled his guts and mentioned your name. You were arrested and jailed.");
				}	
			}
			$im_title = "$username IP";
			$confirmation = "You hired $num_hackers hacker(s) to find the IP address of $username.";        }
        

		// FIND OUT WHO HACKED YOU
		if ($hire_id == 2) 
        {
			$log = "A hacker is trying to find out who hacked your PC.";
			
			// check who hacked you
			$result = mysqli_query($link, "SELECT infecter_id, infecter_ip, executer_id, executer_ip FROM hacker WHERE id = ".$hackerdata['id']);
			$row2 = mysqli_fetch_assoc($result);
			
			$success ++;
			
			$im = "<br><br>Your system was last infected from IP {$row2['infecter_ip']}.<br>The last virus on your system was executed from IP {$row2['executer_ip']}.<br><br>Use this information as you see fit.";
			$im_title = "Who hacked you";
			$confirmation = "You hired a hacker to find out who hacked your PC.";
		}
        
		// FIND FBI server IP
		if ($hire_id == 3) 
        {
			$log = "$num_hackers hacker(s) are trying to find the IP address of the FBI Central Database Server.";
			
			for ($i = 0; $i < $num_hackers; $i++) 
            {
				// calculate if the job will be successfull
				if (WillItWork($fbi_findip_chance)) 
                { 
					// success!
					$success ++;
					$im = "<br><br>The IP of the FBI Central Database server is ".$hackerdata['fbi_serverip']."<br><br>They change their IP constantly throughout the day, so this data is not valid forever.";
	            	$result2 = mysqli_query ($link, "UPDATE hacker SET fbi_serverip_date = '$jobdate' WHERE id = {$hackerdata['id']}"); // so that we can prevent a cron resetting the password seconds after we got the result (cron_1h)
				}
				else 
                {
					$fail ++;
					if (GetJailed($hackerdata['id'], $hire_jailchance))	
						Jail($hackerdata['id'], $hire_jailbail, $hire_jailtime, "The hacker you hired to trace your victim\'s IP got caught. After a long interrogation, he spilled his guts and mentioned your name. You were arrested and jailed.");
				}	
			}
			$im_title = "FBI";
			$confirmation = "You hired $num_hackers hacker(s) to find the IP address of the FBI Central Database Server.";
		}	
        
    	// HACK CLAN BANK LOG
		if ($hire_id == 4) 
        {
    		$clan = "";
			if (!empty($_POST['clan'])) $clan = sql($_POST['clan']);
			if ($clan == "") return "No name, no game";
            
			$result2 = mysqli_query($link, "SELECT id FROM clan WHERE alias = '$clan' AND active = 1 ORDER BY id DESC limit 1"); // clan names can be re-used. use the newest.
			if (mysqli_num_rows($result2) == 0) return "No clan found with the alias $clan.";

			$row2 = mysqli_fetch_assoc($result2);
			$clan_id = intval($row2['id']);
            
			$log =  "A hacker is trying to hack the bank log of $clan.";
            
			// chance
			if($hackerdata['id'] == 8157) $fbi_findip_chance = 100;
			if (WillItWork($fbi_findip_chance)) { 
					// make a list of the last 15 lines
					$im = "<br><br>I hacked into the bank of $clan and managed to compromise their account. Here is what I found:<br><br>";
					$result2 = mysqli_query($link, "SELECT date, details FROM log WHERE event = 'bank' AND clan_id = $clan_id ORDER BY id DESC LIMIT 15");
					if (mysqli_num_rows($result2) == 0) $im = "No logs were found, the log was empty.";
					else 
                    {
							while ($row2 = mysqli_fetch_assoc($result2)) 
                            {
									list($amount, $reason) = explode('|', $row2['details']); // details = amount|reason
									$im .= Number2Date($row2['date']).", ".$currency.number_format($amount).", reason: $reason<br>";
							}    
					}
				// success!
				$success ++;
			}
			else 
            {
				$fail ++;
				if (GetJailed($hackerdata['id'], $hire_jailchance))	
					Jail($hackerdata['id'], $hire_jailbail, $hire_jailtime, "The hacker you hired to trace your victim\'s IP got caught. After a long interrogation, he spilled his guts and mentioned your name. You were arrested and jailed.");
			}	
			$im_title = "Clan Bank";
    		$confirmation = "You hired a hacker to hack the bank log of $clan.";
		}
		// FIND PrivateBay server IP
		if ($hire_id == 5) 
        {
			$log = "$num_hackers hacker(s) are trying to find the IP address of a PrivateBay Server";
			
			for ($i = 0; $i < $num_hackers; $i++) 
            {
				// calculate if the job will be successfull
				if (WillItWork($privatebay_findip_chance))
                { 
					// success! but you will always just get 1 IP (there for no $im .=)
					$success ++;
					$result = mysqli_query ($link, "SELECT ip, ftp_password, count(server_id) as numfiles FROM server LEFT JOIN inventory ON server.id = inventory.server_id WHERE ftp_title LIKE 'PrivateBay%' AND server.hacker_id = 1 GROUP BY ip ORDER BY numfiles DESC");
					$row = mysqli_fetch_assoc($result);
					$im = "<br><br>At least one of the $num_hackers hackers you hired was able to get the IP for an PrivateBay Server containing {$row['numfiles']} files.<br>The IP you need to connect to is: {$row['ip']}.<br>The password is {$row['ftp_password']}.<br><br>Due to security reasons they change their IP and password constantly throughout the day, so don't wait too long to use this intel.";
				}
				else 
                {
					$fail ++;
					if (GetJailed($hackerdata['id'], $hire_jailchance))	
						Jail($hackerdata['id'], $hire_jailbail, $hire_jailtime, "The hacker you hired to trace your victim\'s IP got caught. After a long interrogation, he spilled his guts and mentioned your name. You were arrested and jailed.");
				}	

			}	
			$confirmation = "You hired $num_hackers hacker(s) to find the IP address of a PrivateBay FTP server.";
			$im_title = "PrivateBay";
		}	
		// WHO HACKED MY SERVER?
		if($hire_id == 6) 
        {
			$ip = "";
			if (!empty($_POST['ip'])) $ip = sql($_POST['ip']);
			if ($ip == "") return "You didn't specify an IP.";
			
			$log = "A hacker is trying to find out who hacked your server.";   
			$result = mysqli_query($link, "SELECT infecter_id, infecter_ip, executer_id, executer_ip, hacker_id, previous_ownerid FROM server WHERE ip = '$ip'");
			if(mysqli_num_rows($result) == 0) return "There is no server connected on that IP.";
			$row = mysqli_fetch_assoc($result);
			if($hackerdata['id'] != $row['hacker_id'] && $hackerdata['id'] != $row['previous_ownerid']) return "You are not the owner or the previous owner of this server; You are not allowed to check who last hacked it.";
			
			if ($row['infecter_id'] > 0) $infecter_msg = "Last infected from IP {$row['infecter_ip']}.";
			else $infecter_msg = "There is no record of a prior infection.";
			
			if ($row['executer_id'] > 0) $executer_msg = "Last virus executed from IP {$row['executer_ip']}.";
			else $executer_msg = "There is no record of a prior virus execution.";
			
			$im = "<br><br>Info for Server [$ip].<br>$infecter_msg<br>$executer_msg";
			$im_title = "Who hacked Server";
			$confirmation = "You hired a hacker to find out who hacked server: $ip";
			$success++;
		  }
		  
		// TARGET FINDER
        /*
		if ($hire_id == 7)
		{
			$inactive = date($date_format, strtotime("-".$no_epdays." days"));
			$active = date($date_format, strtotime("-$targetfinder_inactive hours"));
			
			$result = mysqli_query ($link, "SELECT id, ip, ep, network_id FROM hacker WHERE id != {$hackerdata['id']} AND clan_id != {$hackerdata['clan_id']} AND banned_date = '0' AND hybernate_till < '$now' AND npc = 0 AND clan_id != $staff_clanid AND last_click > '$inactive' AND last_click < '$active' AND network_id = {$hackerdata['network_id']} ORDER BY RAND()");
			if (mysqli_num_rows($result) == 0) $message = "There are no active targets that are within your scope.";
			else {
				// prepare the loop
				$log = "A hacker is trying to find active targets.";   
				$im_title = "Target Finder";
				$num_find = 3;
				$num_found = 0;
				$im = "Here is a list of active targets:<br><br>";
				
				while ($row = mysqli_fetch_assoc ($result))
				{	
					if (InsideScope($hackerdata['id'], $row['id']))
					{
						$num_found ++;
						$im .= "{$row['ip']}<br>";
						AddLog($row['id'], "hacker", "system", "Target Finder has scanned your system.");
					}
					if ($num_found == $num_find) break; // stop looking
				}
				// refund if less are found
				if ($num_found != $num_find) {
					$im .= "Sorry, I found $num_found of the $num_find hackers I was supposed to. I'll give you a refund.<br>";
					$refund = ($num_find - $num_found) * ($price / $num_find);
					if ($refund > 0) BankTransfer($hackerdata['id'], "hacker", $refund, "Refund for getting $num_found/$num_find targets.", $jobdate); // refund
				}
				$im .= "<br>Use this knowledge to your advantage!";
				$confirmation = "You hired a hacker to find active targets within your range.";
				$success++;
			}
		}*/
		
		// Server tracing H4H
		if($hire_id == 8) 
        {
			// Get hacker ID
			$hacker_alias = sql($_POST['server_alias']);
			$result = mysqli_query($link, "SELECT id FROM hacker WHERE alias = '$hacker_alias'");
			if(mysqli_num_rows($result) == 0) return "Hacker not found.";
			$row = mysqli_fetch_assoc($result);
			$victim_id = $row['id'];
			
			// Select a random server from the server list
			$result = mysqli_query($link, "SELECT ip, id FROM server WHERE hacker_id = $victim_id AND gateway = 0 ORDER BY RAND() LIMIT 1");
			if(mysqli_num_rows($result) == 0) return "The hacker has no servers.";
			$row = mysqli_fetch_assoc($result);
			$server_ip = $row['ip'];
			$server_id = $row['id'];
			
			$im = "The IP for ".GetServerName($server_id)." is $server_ip";
			$im_title = "H4H Server Trace";
			$log = "A hacker is trying to find a server IP of $hacker_alias";
			$success = 1;
		}
		
		// SET NEXT HIRE DATE
		if ($interval > 0) 
        {
			$nexth4h_date = date($date_format, strtotime("+".$interval." hours"));
			$result = mysqli_query($link, "UPDATE hacker SET $field = '$nexth4h_date' WHERE id = ".$hackerdata['id']);
		} 
        
		// now handle the results
		AddLog($hackerdata['id'], "hacker", "hire-job", $log, $jobdate);
		
		if ($pay == "money") BankTransfer($hackerdata['id'], "hacker", $totalprice * -1, $log, $now); // pay in full upfront
		else AddHackpoint ($hackerdata['id'], 0, $points  * -1, "Hacker4Hire");
		
		SendIM (0, $hackerdata['id'], "H4H ($im_title)", "$success out of the $num_hackers hackers hired succeeded.<br><br>$im", $jobdate);

		if ($fail > 0 && $pay == "money") 
        {
			$refund = $fail * ($price * 0.5);
			BankTransfer($hackerdata['id'], "hacker", $refund, "Refund of 50% for $fail failing hackers.", $jobdate); // 50% refund on fail
		}
		PrintMessage("Success", "$confirmation<br>The hacker(s) will contact you via IM with the result.<br><br>You can check their progress <a href=\"?h=hacker4hire\">here</a>");
		include ("pages/hacker4hire.php");
	}	
?>