<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php		
	
	// Get the action
	if(isset($_REQUEST['action']) && !empty($_REQUEST['action']))
		$action = $_REQUEST['action'];
		
	// What are the current actions?
	$actions = array("declare", "surrender", "acceptsurrender", "declinesurrender");
	
	if($action != 'acceptsurrender' && $action!= 'declinesurrender')	{
		// Get the code
		if(isset($_REQUEST['code']) && !empty($_REQUEST['code']))
			$code = $_REQUEST['code'];
			
		// Is the code yours?
		if($code != SHA1($hackerdata['started'].$hackerdata['last_login'])) return "Error.";
	}
	
	if(!in_array($action, $actions)) return "Wrong action.";
	
	if($action == 'declare') {
		// Get the clan id
		if(isset($_REQUEST['id']) && $_REQUEST['id'] > 0)
			$clan_id = intval($_REQUEST['id']);
		else return "No clan ID set.";
		
		// is the clan active?
		$inactive = date($date_format, strtotime('-'.$war['inactive_days'].' days'));
		$result = mysqli_query($link, "SELECT clan.id FROM clan LEFT JOIN hacker ON clan.founder_id = hacker.id WHERE clan.id = $clan_id AND (hacker.last_click < '$inactive' OR clan.active = 0)");
		if(mysqli_num_rows($result) > 0) return "This clan is inactive. You cannot declare a war against it.";
		
		// Did this clan get warred recently
		$before2days = date($date_format, strtotime('-'.$war['cooldown_days'].' days'));
		$result = mysqli_query($link, "SELECT id FROM war WHERE (victim_clanid = $clan_id OR attacker_clanid = $clan_id) AND end_date >= $before2days");
		if(mysqli_num_rows($result) > 0) return "This clan was recently at war. You cannot declare a war against it.";
		
		// Is this kotr clan?
		if($clan_id == $kotr['clan_id']) return "You cannot declare war against King Of The Ring Guards";
		
		// Let's do some checks first
		if($hackerdata['clan_id'] == $clan_id) return 'You cannot declare war against yourself.';
		
		if($clan_id == 1) return 'You cannot go into war with Game Administration';
		
		// Are you actually a leader
		if(!isFounder($hackerdata['id'])) return "You are not a clan leader.";
		
		// Is your clan at least 7 days old so you can start a war?
		$started = mysqli_get_value('started', 'clan', 'id', $hackerdata['clan_id']);
		$before_7days = date($date_format, strtotime("- {$war['min_clan_age']} days"));
		if($before_7days < $started) return 'Your clan needs to be at least 7 days old to start a war.';
			
		// Is your opponet clan at least 7 days old so you can start a war?
		$started = mysqli_get_value('started', 'clan', 'id', $clan_id);
		if($before_7days < $started) return 'The other clan needs to be at least 7 days old to go into war.';

		// Let's declare the war and makes the variables more convenient
		$clan_attacker = $hackerdata['clan_id'];	// Clan declaring the war
		$clan_victim = $clan_id; // Clan the war is being declared against
		
		// Are both clans in a state of peace?
		$result = mysqli_query($link, "SELECT id FROM war WHERE (attacker_clanid = $clan_attacker OR victim_clanid = $clan_attacker) AND active = 1");
		if(mysqli_num_rows($result) > 0) return "You are in a state of war and cannot be involved in another war.";
		
		$result = mysqli_query($link, "SELECT id FROM war WHERE (attacker_clanid = $clan_victim OR victim_clanid = $clan_victim) AND active = 1");
		if(mysqli_num_rows($result) > 0) return "The clan you are declaring a war against is in a state of war and another war cannot be declared against it";
		
		// check the clanbanks
		$clanbank_attacker = mysqli_get_value('bankaccount', 'clan', 'id', $clan_attacker);
		$clanbank_victim = mysqli_get_value('bankaccount', 'clan', 'id', $clan_victim);
		
		$locked = min($clanbank_attacker, $clanbank_victim); // find the lowest bank account
		$locked *= $war['lock_percentage']; // base the locked amount on that lowest amount
		//$locked *= 2; // set the total locked amount (2 clans)
		
		// Update their clanbanks
		BankTransfer($clan_attacker, 'clan', $locked * -1, 'Locked because of war');	
		BankTransfer($clan_victim, 'clan', $locked * -1, 'Locked because of war');	

		// Make them officially in a state of war
		$result = mysqli_query($link, "INSERT INTO war
		(attacker_clanid, victim_clanid, locked_amount, attacker_points, victim_points, active, start_date) VALUES
		($clan_attacker, $clan_victim, $locked * 2, {$war['points']}, {$war['points']}, 1, '$now')");
		
		// Send a message to the other clan founder
		$founder_id = mysqli_get_value('founder_id', 'clan', 'id', $clan_victim);
		sendIM(0, $founder_id, 'War', 'You are now in a state of war with '.mysqli_get_value('alias', 'clan', 'id', $hackerdata['clan_id']), $now);
		
		PrintMessage("Success", "Your clan is now in a state of war with ".mysqli_get_value("alias", "clan", "id", $clan_victim));
	}
	
	if($action == 'surrender') {
		// Get the clan id
		if(isset($_REQUEST['id']) && $_REQUEST['id'] > 0)
			$clan_id = intval($_REQUEST['id']);
		else return "No clan ID set.";
		
		// Are you in war with that clan?
		$result = mysqli_query($link, "SELECT id FROM war WHERE (attacker_clanid = $clan_id OR victim_clanid = $clan_id) AND (attacker_clanid = {$hackerdata['clan_id']} OR victim_clanid = {$hackerdata['clan_id']}) AND active = 1");
		
		if(mysqli_num_rows($result) > 0) {
			$row = mysqli_fetch_assoc($result);
			
			// Generate a unique surrender code
			$surrender_code = SHA1(uniqid('', true));
			
			// Insert the surrender code into the record
			$result2 = mysqli_query($link, "UPDATE war SET surrender_token = '$surrender_code' WHERE id = {$row['id']}");
			
			// Prepare the message
			$title = 'Surrender';
			$message = 'The clan '.mysqli_get_value('alias', 'clan', 'id', $hackerdata['clan_id']).' has offered to surrender. <br> To accept their offer click <a href="index.php?h=dowar&action=acceptsurrender&token='.$surrender_code.'">here</a> 
			<br>To decline surrender click <a href="index.php?h=dowar&action=declinesurrender&token='.$surrender_code.'">here</a><br>If you accept their offer you will receive 70% of their locked money.';
			
			// And send it
			sendIM(0, mysqli_get_value('founder_id', 'clan', 'id', $clan_id), $title, $message, $now);
			
			// Display a message 
			PrintMessage("Success", "A surrender request has been sent to the other clan founder");
		} else return "You are not at war with that clan.";
	}
	
	// accept surrender
	if($action == 'acceptsurrender') {
		// Get the record by his token code
		if(isset($_REQUEST['token']) && !empty($_REQUEST['token'])) $token = sql($_REQUEST['token']);
		else return "Token not set.";
		
		// Query the database
		$result = mysqli_query($link, "SELECT id, victim_clanid, attacker_clanid, locked_amount FROM war WHERE surrender_token = '$token' AND active = 1");
		
		// Wrong token?
		if(mysqli_num_rows($result) == 0) return "Token error.";
		
		// Are you the victim or the attacker
		$row = mysqli_fetch_assoc($result);
		if($hackerdata['clan_id'] == $row['attacker_clanid']) {
			 $entity = 'attacker';	// who you are
			 $entity2 = 'victim';	// who your victim is
		} else {
			$entity = 'victim';
			$entity2 = 'attacker';
		}
		
		// Are you actually the founder of that clan?
		if(mysqli_get_value('founder_id', 'clan', 'id', $row[$entity.'_clanid']) != $hackerdata['id']) return 'You are not a founder of that clan.';
		
		
		// Give 70% of whatever money is locked to the clan accepting the surrender
		$amount_rewarded2accepter = (($row['locked_amount'] / 2) * .70) + ($row['locked_amount'] / 2); // give him back 70% of his enemy plus his locked money
		$amount_rewarded2surrenderer = ($row['locked_amount'] / 2) * .30;	// give him back only 30% of his locked money
		
		// Send the transfers
		BankTransfer($row[$entity.'_clanid'], 'clan', $amount_rewarded2accepter, 'You accepted the surrender offer');
		BankTransfer($row[$entity2.'_clanid'], 'clan', $amount_rewarded2surrenderer, 'Your surrender offer has been accepted');
		
		// End the war
		$result = mysqli_query($link, "UPDATE war SET active = 0, end_date = '$now' WHERE id = {$row['id']}");
		
		// Send a message to the accepter of the money that was rewarded to him
		sendIM(0, mysqli_get_value('founder_id', 'clan', 'id', $row[$entity.'_clanid']), 'War is over', 'You have accepted the surrender offer and have been awarded $'.number_format($amount_rewarded2accepter).'<br>Congratulations on your win!', $now);
		
		// Send a message to the other founder that his offer has been accepted
		$to = mysqli_get_value('founder_id', 'clan', 'id', $row[$entity2.'_clanid']);
		$from = 0;
		$title = 'Surrender offer';
		$message = 'Your surrender offer has been accepted';
		sendIM($from, $to, $title, $message, $now);
		
		// Update wars won and lost
		$wars_lost = mysqli_query($link, "UPDATE hacker SET war_fail = war_fail + 1 WHERE clan_id = ".$row[$entity.'_clanid']);
		$wars_won = mysqli_query($link, "UPDATE hacker SET war_win = war_win + 1 WHERE clan_id = ".$row[$entity2.'_clanid']);
		
		PrintMessage("Success", "You have accepted the offer");
	}
	
	if($action == 'declinesurrender') {
	
		// Get the record by his token code
		if(isset($_REQUEST['token']) && !empty($_REQUEST['token'])) $token = sql($_REQUEST['token']);
		else return "Token not set.";
		
		// Query the database
		$result = mysqli_query($link, "SELECT id, victim_clanid, attacker_clanid, locked_amount FROM war WHERE surrender_token = '$token' AND active = 1") or die(mysqli_error($link));;
		
		// Wrong token?
		if(mysqli_num_rows($result) == 0) return "Token error.";
		
		// Are you the victim or the attacker
		$row = mysqli_fetch_assoc($result);
		if($hackerdata['clan_id'] == $row['attacker_clanid']) {
			 $entity = 'attacker';	// who you are
			 $entity2 = 'victim';	// who your victim is
		} else {
			$entity = 'victim';
			$entity2 = 'attacker';
		}
		
		// Are you actually the founder of that clan?
		if(mysqli_get_value('founder_id', 'clan', 'id', $row[$entity.'_clanid']) != $hackerdata['id']) return 'You are not a founder of that clan.';

		
		// Kill the token
		$result2 = mysqli_query($link, "UPDATE war SET surrender_token = '' WHERE id = {$row['id']}");
		
		// Send a message to the other founder that his offer has been declined
		$to = mysqli_get_value('founder_id', 'clan', 'id', $row[$entity2.'_clanid']);
		$from = 0;
		$title = 'Surrender offer';
		$message = 'Your surrender offer has been declined';
		sendIM($from, $to, $title, $message, $now);	
		
		PrintMessage("Success", "You have declined the surrender offer.");
	}
?>