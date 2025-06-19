<?php
	// loop through all contracts of which the date < $now
	$result = mysqli_query($link, "SELECT npc_mission.*, hacker.bankaccount, hacker.fbi_wanteddate, hacker.alias FROM npc_mission LEFT JOIN hacker on npc_mission.npc_id = hacker.id WHERE date < '$now'");
	if (mysqli_num_rows($result) > 0 ) {
		while ($row = mysqli_fetch_assoc($result)) {
			$success = "";
			$fail = "";
			// kick hacker offline
			if ($row['goal1_id'] == 1) {
				if (!IsOnline($row['npc_id'])) $success = "Great, you kicked {$row['alias']} offline! That will get the message across that we are not to be scr#wed around with.";
				else $fail = "{$row['alias']} is still online and targeting our systems.. nevermind, we will find someone who works faster. No show, no dough.";
			}	
			// steal money from hacker
			if ($row['goal1_id'] == 2) {
				if ($row['bankaccount'] < 900) $success = "I see you stole the money of {$row['alias']}. You can keep it as an extra reward for your efforts. All we care about is that the hacker does not have it.";
				else $fail = "The bank data of {$row['alias']} reveals this hacker still has our money.. we will take care of this ourselves. We are not paying for this.";
			}	
			// trash hdd of hacker
			if ($row['goal1_id'] == 3) {
				if (GetEfficiency($row['npc_id'], "HDD") == 0) $success = "We got word you trashed the HDD of {$row['alias']}. Excellent. Now this hacker will not be able to sell our classified data.";
				else $fail = "{$row['alias']} still has our files. This contract is a fail.. no money for you.";
			}	
			// remove hacker from FBI
			if ($row['goal1_id'] == 4) {
				if ($row['fbi_wanteddate'] == '0') $success = "Excellent, you removed {$row['alias']} from the FBI Most Wanted list. Great work!";
				else $fail = "Our friend {$row['alias']} is still on the FBI Most Wanted list.";
			}	
			// crush inbox of hacker
			if ($row['goal1_id'] == 5) {
				$result2 = mysqli_query($link, "SELECT id FROM im WHERE reciever_del = 0 AND reciever_id = {$row['npc_id']}");
				if (mysqli_num_rows($result2) == 0) $success = "Great! You crushed the inbox of {$row['alias']}.. the info will not be leaked out to the public.";
				else $fail = "You have failed to crush the hackers inbox. This leaves us vulnerable to blackmail.";
			}	
			// noobtool
			if ($row['goal1_id'] == 99) {
				$result2 = mysqli_query($link, "SELECT id FROM hacker WHERE executer_id = {$row['hacker_id']} AND id = {$row['npc_id']}");
				if (mysqli_num_rows($result2) == 1) $success = "Great! You understand the basics of hacking! Now repeat this hack a few times, level up, until you are ready to connect to the internet.";
				else $fail = "You did not execute the n00bTool on the target. Read the n00bGuide again, ask in chat, or use the forums to get help.";
			}	
			// steal a server
			if ($row['goal1_id'] == 10) {
				$result2 = mysqli_query($link, "SELECT id FROM server WHERE hacker_id = {$row['hacker_id']} AND npc = {$row['hacker_id']}"); 
				if (mysqli_num_rows($result2) > 0)	$success = "Great, thank you for taking back our server from {$row['alias']}. We have succesfully taken the server back just minutes ago. Thanks to you our company server is back online!";
				else $fail = "We got word from our team that you were not in possession of the server we asked you to take!! We are not paying the reward.";
			}
			// infect a server
			if ($row['goal1_id'] == 11) {
				$result2 = mysqli_query($link, "SELECT id FROM infection WHERE product_id = 79 AND hacker_id = {$row['hacker_id']} AND victim_entity = 'server' AND victim_id IN (SELECT id FROM server WHERE hacker_id = {$row['npc_id']}) AND success = 1 AND ready = 1");
				if (mysqli_num_rows($result2) > 0)
					$success = "Excellent work! You infected the server with a nasty virus. Now we just sit back and wait until the server gets destroyed. One competitor well dealt with.";
				else
					$fail = "The server is not infected. That is too bad. We will not be able to pay you your reward.";
			}	
			// steal software of server
			if ($row['goal1_id'] == 12) {
				$result2 = mysqli_query($link, "SELECT id FROM server WHERE product_id = 0 AND hacker_id = {$row['npc_id']}"); // all npc servers have software, so if product_id is 0 you stole it successfully
				if (mysqli_num_rows($result2) > 0)
					$success = "Ok, it seems the server you attacked is no longer running that program. They are out of business, well done!";
				else
					$fail = "Their server is still running the software we needed you to steal. We will not be able to pay you your reward.";
			}	
			
			if ($success != "") {
				if ($row['reward'] > 0) $success .= "<br><br>We will send you the reward of ".$currency.number_format($row['reward'])." ASAP!";
				SendIM (0, $row['hacker_id'], "Contract", "The anonymous contractor sent us this:<br><br>".$success, $now);
				BankTransfer ($row['hacker_id'], "hacker", $row['reward'], "Contract reward", $now);
				AddEP ($row['hacker_id'], $npccontract_ep, round($npccontract_ep / 5), $now, "NPC");
				$contract_result = "win";
			}
			else {
				SendIM (0, $row['hacker_id'], "Contract", "The anonymous contractor sent us this:<br><br>".$fail, $now);
				$contract_result = "fail";
			}
			// retire the NPC (but be careful!)
			if ($row['npc_id'] > 0) {
				$result2 = mysqli_query($link, "UPDATE hacker SET alias = '', ip = '', fbi_wanteddate = '0', restoring_minutes = 0 WHERE id = ".$row['npc_id']); // retire the NPC but let NPC = xx value exist, so they do not get banned.
				CleanSystem ($row['npc_id'], "End of contract", "hacker", -1); // remove all infections (ready or not) from this npc
				// drop all servers owned by this npc
				$result2 = mysqli_query($link, "SELECT id, hacker_id FROM server WHERE npc = {$row['hacker_id']}"); // (owner is not important, npc- field is)
        		if (mysqli_num_rows($result2) > 0)
        			while ($row2 = mysqli_fetch_assoc($result2))
		      			DropServer($row2['id'], $row2['hacker_id'], "End of contract"); // drop it like it's hoooooot..                        
			}	
			if ($row['goal1_id'] != 99) RegisterResult ($row['hacker_id'], "contract_$contract_result", $now);
			$result2 = mysqli_query($link, "DELETE FROM npc_mission WHERE id = ".$row['id']);
		}
	}	
?>