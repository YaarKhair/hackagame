<?php
	/* CRONNED EP */
	$result = mysqli_query($link, "SELECT cronep.*, hacker.skill as hacker_skill, network_id, hacker.convention_last, hacker.alias FROM cronep LEFT JOIN hacker on cronep.hacker_id = hacker.id WHERE date < '$now' ORDER BY date ASC");
	if (mysqli_num_rows($result) > 0) {
		while ($row = mysqli_fetch_assoc($result)) {
			$ep = GetHackerEP($row['hacker_id']);
			$old_level = EP2Level($ep);
			$new_level = EP2Level($ep + $row['ep']);
			
			if ($new_level > $old_level) {
				// if you skipped a level (you can do that in the beginning) we need to see if there are tutorials available for those levels in between
				for ($i = $old_level+1; $i <= $new_level; $i++) {
					PlayerTutorial ($row['hacker_id'], $i); // level based tutorial mails
					CheckNewAchievement ($row['hacker_id'], "level", $i);
				}
				
				// perhaps you can use new tools with this new level
				$result2 = mysqli_query($link, "SELECT title, description FROM product WHERE in_shop <> 0 AND level = ".$new_level);
				if (mysqli_num_rows($result2) > 0) {
					$message = "The following tools are now available for you in the 1338 shops:<br>";
					while ($row2 = mysqli_fetch_assoc($result2)) $message .= "[b]{$row2['title']}[/b][quote]{$row2['description']}[/quote]<br>";
					SendIM (0, $row['hacker_id'], "New Tools!", $message, $now);
				}
				
				// is your new level the referral level?
				$referral_id = mysqli_get_value("referral_id", "hacker", "id", $row['hacker_id']);
				if($old_level < $referral_level && $new_level >= $referral_level && $referral_id > 0) {
					AddEP($referral_id, $referral_reward_ep, 0, $now, 'AFF');
					BankTransfer($referral_id, "hacker", $referral_reward_cash, "Reward, because {$row['alias']} who you invited to the game has reached level $referral_level", $now);
					SendIM(0, $referral_id, "Reward for referral", "Congratulations! <br> You have been awarded with $currency".number_format($referral_reward_cash)." and $referral_reward_ep EP points because {$row['alias']} reached level $referral_level", $now);			
				}
				
				// if you are on noobnet
				if ($row['network_id'] == 1) {
					// level too high? you're kicked off
					if ($new_level > $noobnet_level) {
						// find id of dailup connection
						$result2 = mysqli_query($link, "SELECT id, efficiency FROM product WHERE code = 'INTERNET' AND price = 0");
						$row2 = mysqli_fetch_assoc($result2);
    					$dailup_id = $row2['id'];
    					$dailup_efficiency = $row2['efficiency'];
						
    					$result2 = mysqli_query($link, "UPDATE hacker SET network_id = 2 WHERE id = ".$row['hacker_id']);
    					$result2 = mysqli_query($link, "INSERT INTO system (product_id, efficiency, hacker_id) VALUES ($dailup_id, $dailup_efficiency, {$row['hacker_id']})");
						SendIM (0, $row['hacker_id'], "SkyNet ISP", "Dear Sir, Madam,<br><br>Welcome to SkyNet. You are no longer connected to n00bNET. Instead you are connected to the internet via our robust infrastructure.", $now);
					}
				}	
        // we save the level in the hacker row so faster find targets with target finder etc
        $result2 = mysqli_query($link, "UPDATE hacker SET level = $new_level WHERE id = ".$row['hacker_id']);
			}
			$result2 = mysqli_query($link, "UPDATE hacker SET ep = ep + ".$row['ep']." WHERE id = ".$row['hacker_id']);
			AddLog ($row['hacker_id'], "hacker", "ep", "Ep increased: ".$row['ep']." (".$row['target'].")", $now);

			$cur_skill = GetSkill($row['hacker_id']);
			$add_skill = $row['skill'];
			if ((($row['convention_last'] * $skillslots_per_convention) - $row['hacker_skill']) > 0 && $cur_skill < $maxskill) {
				if ($cur_skill + $add_skill > ($row['convention_last'] * $skillslots_per_convention)) $add_skill = ($row['convention_last'] * $skillslots_per_convention) - $cur_skill; // your skill went over the slot. so fill the slot and wait for condef
				$result2 = mysqli_query($link, "UPDATE hacker SET skill = skill + ".$add_skill." WHERE id = ".$row['hacker_id']);
				AddLog ($row['hacker_id'], "hacker", "skill", "Skill increased: ".$add_skill." (".$row['target'].")", $now);
			}	
		}
		$result2 = mysqli_query($link, "DELETE FROM cronep WHERE date < '$now'");
	}
	/* CRONNED RESULTS */
	$result = mysqli_query($link, "SELECT * FROM cronresult WHERE date < '$now'");
	if (mysqli_num_rows($result) > 0) {
		while ($row = mysqli_fetch_assoc($result)) {
				$result2 = mysqli_query($link, "UPDATE hacker SET ".$row['achievement']." = ".$row['achievement']." +1 WHERE id = ".$row['hacker_id']);
				CheckNewAchievement($row['hacker_id'], $row['achievement']);
		}		
		$result2 = mysqli_query($link, "DELETE FROM cronresult WHERE date < '$now'");
	}
?>