<?php
	/**
	**	Few stuff before beginning: 
	**	$victim_row has information about the victim
	** 	$infection_row has information about the infection
	**/
		
	/**
	**	Connection destroyer
	**	Kicks a hacker offline
	**/
    if ($infection_row['product_id'] == $PRODUCT['Connection Destroyer']) {
	
        // How long are you offline for?
        if ($npc == 0) $offline_time = intval ($offline_afterhack - (GetEfficiency($victim_id, "SECURITY") / 2.5 ) ); 
        else $offline_time = $npc_offline_timer;
		
		// Perks
		$perk = GetPerkValue($victim_id, "PERK_DECREASEOFFLINETIME");
		$offline_time -= $perk;
                
		// Till when are you offline?
        $offline_till = date($date_format, strtotime("+".$offline_time." minutes"));
            
		// Kick the hacker offline
        $update_offline_result = mysqli_query($link, "UPDATE hacker SET offline_from = '$now', offline_till = '$offline_till' WHERE id = ".$victim_id);
		
        // Cancel all pending infections of the victim (file transfers will be cancelled in the transfer cron (cron_2m))
        $select_infection_result = mysqli_query($link, "SELECT id FROM infection WHERE date < '$now' AND ready = 0 AND hacker_id = {$victim_id}");
        if (mysqli_num_rows($select_infection_result) > 0) {
			$num_pending = mysqli_num_rows($select_infection_result);
			CleanSystem ($victim_id, "Connection lost", "hacker", 0); // ready = 0 PENDING
            AddLog ($log_id, "hacker", "hack", "All pending infections ($num_pending) are cancelled. Connection lost.", $now);
        }    
    
        // Add an offline message in his logs
        AddLog ($log_id, "hacker", "system", "TCP/IP: Timeout, Internet connection lost.", $now);
            
        // Were you kicked offline with no security? Different response
        if (!HasInstalled($victim_id, "SECURITY") && $npc == 0) {
            SendIM (0, $victim_id, "System Repair", "Dear Sir, Madam,<br>Because you had no security pack installed you came to us to clean and fix your PC. The costs for this operation are ".$currency.$virusclean_costs.", which we will withdraw from your bank account automatically.<br>We *urge* you to install a Security Pack to avoid this in the future.", $offline_till);
            BankTransfer($victim_id, "hacker", $virusclean_costs * -1, "PC cleaning costs", $offline_till); // this will be handled by cron
        } else {
          AddLog ($log_id, "hacker", "system", "SecurityPack: Insecure code found and removed.", $offline_till);
        }
		
        // Add an online message in his logs (gets added when his system is online again)
        AddLog ($log_id, "hacker", "system", "TCP/IP: Internet connection restored.", $offline_till);
            
        // Hack details
        $addlog_attacker = "Offline time: $offline_time minutes.";
	}
	
	
    /**
	**	Noob tool
	**	Does absolutely nothing but send an IM to the hacker
	**/
    if ($infection_row['product_id'] == $PRODUCT['n00b Tool']) SendIM (0,  $hackerdata['id'], "n00bTool", "Very good, you understand the basics of attacking another player.<br>Do some more practicing. Once you reach level 10 you will get real contracts.<br><br>For more info read the [[n00b Guide]] in the Wiki, join the chatbox or use the support forums.", $now);
    
	
    /**
	**	Money Steal Trojan
	**	Steals a percentage of cash from the victim depending on his security
	**/
    if ($infection_row['product_id'] == $PRODUCT['Money Steal Trojan']) {
            
		// If the victim is an NPC, steal 100% of his cash
        if ($npc == $hackerdata['id']) $steal = 100; 
        else $steal = intval(100 - (GetEfficiency($victim_id, "SECURITY") / 2) ); 
		
		// Decrease steal % perk
		$perk = GetPerkValue($victim_id, "PERK_DECREASEMST");
		if($steal != 100) $steal = Max($steal - $perk, 0);
		
		// Steal the money
        StealMoney($victim_id, "hacker", $hackerdata['id'], $steal, "Money transferred to anonymous Swiss bank account", $now);
		
        // Hack details
        $addlog_attacker = "$steal % stolen.";
    }
	
	
	/**
	**	Software Leecher
	**	Steals software from the victim's inventory
	**/
    if ($infection_row['product_id'] == $PRODUCT['Software Leecher']) {
	
        // Is there actual software in the victim's inventory?
        $select_inventory_result = mysqli_query($link, "SELECT id FROM inventory WHERE server_id = 0 AND hacker_id = ".$victim_id);
        if (mysqli_num_rows($select_inventory_result) == 0) {
			SendIM (0, $hackerdata['id'], "Leech report (0)", "No leechable software found on target machine", $now);
		} else {
		
			// Prepare a different IM
			$num = 0;
            $message = 'Client succesfully infected with Download Backdoor. Here is a list of the software found on this system. Click on the links to initiate a file transfer. After you finish downloading that package will be removed from the infected client.<br><br>';
            while ($inventory_row = mysqli_fetch_assoc($select_inventory_result)) {
                $message .= '<a href=\'index.php?h=download&inventory_id='.$inventory_row['id'].'&checksum='.sha1($ip).'\'>'.FileInfo($inventory_row['id'], "title").' ('.DisplaySize(FileInfo($inventory_row['id'], "size")).')</a><br>';
                $num++;
            }
			
            // Send an IM to the hacker
            SendIM (0, $hackerdata['id'], "Leech report ($num)", $message, $now);
			
			// Hack details
            $addlog_attacker =  "$num files indexed.";
        }
    }
	
	
	/**
	**	Remote HDD Destroyer
	**	Destroys the HDD of the victim causing him to lose all his files and HDD
	**/
    if ($infection_row['product_id'] == $PRODUCT['Remote HDD Destroyer']) {
		
		// Delete his HDD from his system with all installed software (NO HDD == NO SOFTWARE)	|	Delete his inventory
        $delete_hdd_result = mysqli_query($link, "DELETE FROM system WHERE product_id NOT IN (SELECT id FROM product WHERE code = 'CPU' OR code = 'MEMORY' or code = 'MAINBOARD' or code = 'INTERNET') AND hacker_id = ".$victim_id);
		$delete_inventory_result = mysqli_query($link, "DELETE FROM inventory WHERE server_id = 0 AND hacker_id = ".$victim_id);
		
		// Add Teh Logz
        AddLog ($log_id, "hacker", "system", "S.M.A.R.T: Warning -Faulty and unusual harddrive activity.", $now);
        AddLog ($log_id, "hacker", "system", "S.M.A.R.T: Harddrive fail.", $now);
		
    }
	
	
	/**
	**	Inbox Destroyer
	**	Deletes all messages from the inbox
	**/
    if ($infection_row['product_id'] == $PRODUCT['Inbox Crusher']) $delete_im_result = mysqli_query($link, "UPDATE im SET reciever_del = 1 WHERE reciever_id = $victim_id");
	
	
	
	/**
	**	Message Stealer
	**	Steals all system messages from the inbox of the victim
	**/
    if ($infection_row['product_id'] == $PRODUCT['Message Stealer']) {
	
		// Select all messages
        $select_message_result = mysqli_query($link, "SELECT title, message, date FROM im WHERE sender_id = 0 AND reciever_id = $victim_id AND reciever_del = 0 AND date <= '$now' ORDER BY date DESC LIMIT $efficiency");
		
		// No messages?
        if (mysqli_num_rows($select_message_result) == 0) SendIM (0, $hackerdata['id'], "Mail stealer (0)", "The inbox contained no important emails.", $now);
		else {
		
			// Prepare message
			$stolenmails = '';
			$num = 0;
			while ($message_row = mysqli_fetch_assoc($select_message_result)) {
				$stolenmails .= "From: [SYSTEM]<br>";
				$stolenmails .= "To: [".mysqli_get_value ("alias", "hacker", "id", $victim_id)."@$ip]<br>";
				$stolenmails .= "Title: FW: {$message_row['title']}<br>";
				$stolenmails .= "Received: ".Number2Date($message_row['date'])."<br><br>";
				$stolenmails .= "{$message_row['message']}<hr><br>";
				$num++;
			}
				
			// If this was a bounty, send it to the hacker and the one who requested the bounty
			$send_to[] = $hackerdata['id'];
			$send_to = array_unique($send_to);
			foreach($send_to as $sendto_id)  SendIM (0, $sendto_id, "Mail stealer ($num)", "10 Most Recent SYSTEM Emails:<br><br>".$stolenmails, $now);
			$addlog_attacker = "$num emails indexed.";
		}
	}
	
	
	
	/**
	**	All Log Stealers:
	**	Bank.log Stealer, Hack.log Stealer, System.log Stealer, Transfer.log Stealer
	**/
	$log_stealers = array($PRODUCT['Bank.log Stealer'], $PRODUCT['Hack.log stealer'], $PRODUCT['System.log stealer'], $PRODUCT['Transfer.log stealer']);
	if(in_array($infection_row['product_id'], $log_stealers)) {
		
		// Which log is it?
		$title = mysqli_get_value("title", "product", "id", $infection_row['product_id']);
		$title = explode(".", $title);
		$log2steal = strtolower($title[0]);	// for example: bank

		// Select the logs
        $select_log_result = mysqli_query($link, "SELECT date, details FROM log WHERE event = '$log2steal' AND hacker_id = ".$victim_id." AND date <= '$now' AND deleted = 0 ORDER BY date DESC LIMIT $efficiency");
		
		// No logs? Send IM instantly
        if (mysqli_num_rows($select_log_result) == 0) SendIM (0, $hackerdata['id'], "$log2steal.log stealer (0)", "The $log2steal.log of $target appears to be empty.", $now);
        else {
		
			// Build the logs message
			$num = 0;
            $stolenlogs = '';
            while ($log_row = mysqli_fetch_assoc($select_log_result)) {
                if ($log2steal == "bank") {
                    list($amount, $reason) = explode('|', $log_row['details']);
                    $stolenlogs .= "Date: ".Number2Date($log_row['date']).", Amount: ".$amount.", Reason: ".$reason."<br>";
                }
                else { 
					$stolenlogs .= "Date: ".Number2Date($log_row['date']).", Details: ".$log_row['details']."<br>";
                }
				$num ++;
            }
			
			// Is there a bounty on this tool for this hacker? Send it to the hacker and the one that put up the bounty
            $send_to[] = $hackerdata['id'];
            $sent_to = array_unique($sent_to);
            foreach($send_to as $sendto_id) 
                SendIM (0, $sendto_id, "$log2steal.log stealer ($num)", "$efficiency most recent lines out of the $log2steal.log from $target:<br><br>".$stolenlogs, $now);
                
			// Hack details
			$addlog_attacker = "$num $log2steal.log lines indexed.";
		}
		
	}

?>