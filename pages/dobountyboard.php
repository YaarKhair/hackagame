<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php
	$action = '';
	if (!empty($_REQUEST['action'])) 
		$action = sql($_REQUEST['action']);
	
	if ($action != "add" && $action !== "delete") 
		Return "There was a problem with your request. Please try again";
	
	if ($action == "add") {
		$victim = '';
		if (!empty($_POST['victim'])) 
			$victim = sql($_POST['victim']);
		
		$result = mysqli_query ($link, "SELECT id, npc, banned_date, hybernate_till FROM hacker WHERE alias = '$victim'");
		if (mysqli_num_rows($result) == 0) 
			return "The hacker $victim was not found.";
			
		// read hacker id
		$row = mysqli_fetch_assoc($result);
		
		// check victim
		if (InGroup($row['id'], 1) || InGroup($row['id'], 2) || $row['id'] == $ibot_id) return "This is a member of the game administration and can not be hacked.";	
		if ($row['npc'] > 0) return "This is an NPC and can not be added to the bounty board.";
		if ($row['banned_date'] > 0) return "This player is banned and can not be added to the bounty board.";
		if ($row['hybernate_till'] > $now) return "This player is currently in hibernation and can not be added to the bounty board.";
		
		$victim_id = $row['id'];
		if ($hackerdata['id'] == $victim_id)
			return "You can not add yourself.";
			
		if (InGroup($victim_id, 1) || InGroup($victim_id, 2)) 
			return "This is a member of the game administration and can not be hacked.";			
			
		$reward = 0;
		if (!empty($_POST['reward'])) 
			$reward = intval($_POST['reward']);
		
		$total_amount = $reward;
		
		// anonymous?
		$anonymous = 0;
		if (!empty($_POST['anonymous'])) $anonymous = checkbox($_POST['anonymous']); 
		
		if ($anonymous == 1) $total_amount += $bounty_anonymous; // extra fee for anonymous
		
		// do you have enough money?
		if ($hackerdata['bankaccount'] < $total_amount)
			return "You can not afford this reward.";
			
		$tool_id = 0;
		if (!empty($_POST['tool_id'])) 
			$tool_id = intval($_POST['tool_id']);
	
		$result = mysqli_query ($link, "SELECT price FROM product WHERE level > 0 AND code = 'PCHACK' AND id = $tool_id"); // level > 0 to exclude the noobtool
		if (mysqli_num_rows($result) == 0) 
			return "The select tool was not found.";
		
		// read tool price
		$row = mysqli_fetch_assoc($result);
		$price = $row['price'];
		
		// check reward
		if ($reward < ($price * 2))
			return "The reward is too low. It should be at least twice the price of the selected tool.";
	
		// anonymous?
		$anonymous = 0;
		if (!empty($_POST['anonymous'])) $anonymous = checkbox($_POST['anonymous']); 
	
		// pay bounty
		BankTransfer ($hackerdata['id'], "hacker", $reward * -1, "Hacker added to the Bounty Board");
		if ($anonymous == 1) BankTransfer ($hackerdata['id'], "hacker", $bounty_anonymous * -1, "Bounty Board anonymous fee");
		
		// add bounty
		$result = mysqli_query ($link, "SELECT id, reward FROM bounty WHERE contracter_id = {$hackerdata['id']} AND victim_id = $victim_id AND tool_id = $tool_id");
		if(mysqli_num_rows($result) > 0) {
			$row = mysqli_fetch_assoc($result);
			$reward += $row['reward'];
			$result = mysqli_query($link, "UPDATE bounty SET reward = $reward, date = '$now' WHERE id = {$row['id']}");
			PrintMessage ("Success", "A previous bounty was found with the same tool and contractor, the reward is now added.");
		} 
		else {
			$result = mysqli_query ($link, "INSERT INTO bounty (contracter_id, victim_id, tool_id, date, reward, anonymous) VALUES ({$hackerdata['id']}, $victim_id, $tool_id, '$now', $reward, $anonymous)");
			PrintMessage ("Success", "Bounty added.");
		}
  }	
	
	// Remove Bounty
	if($action == "delete") {
		$bounty_id_sent = intval($_REQUEST['id']);
		
		// Getting the ID of the bounty owner (Just to check that the bounty sent belongs to you) 
		$contractor_id = mysqli_get_value("contracter_id", "bounty", "id", $bounty_id_sent);
		
		if($contractor_id == $hackerdata['id']) {
			// The owner of the bounty is the hacker [Validated]
			$result = mysqli_query($link, "DELETE FROM bounty WHERE id = $bounty_id_sent");
			PrintMessage("Success","Bounty removed successfully!");
		} else {
			// The link was forged
			PrintMessage("Error","This bounty does not belong to you");
		}
	}
	include ("pages/bountyboard.php");
?>