<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php
	// Get the servers
	// The array should look like 1;2;3;4;5;6;7;10
	$servers = array();
	if(!empty($_POST['server']))
		$servers = explode(";", $_POST['server']);
	else 
	    return "No servers were selected.";
	
		
	// Sanitize each id
	$servers = array_map('intval', $servers);
	
	// Sort the servers ids numerically so the checks go well
	sort($servers, SORT_NUMERIC);
	
	// Check out the hacker
	if ($hackerdata['clan_id'] == 0) 
		return "You're not in a clan. You can not buy servers when you're not in clan.";		
	if((NumServers($hackerdata['id']) + count($servers)) > MaxServers($hackerdata['id'])) 
		return "You cannot hold more than ".MaxServers($hackerdata['id'])." servers";
	if ($hackerdata['network_id'] != 2) 
		return "You can not buy servers when you are connected to ".mysqli_get_value("name", "network", 1);
		
	// Check if he can afford to buy the servers and if he does, take out the money
	$cost = count($servers) * $server_price;
	if($cost > $hackerdata['bankaccount'])
		return "You do not have sufficient funds.";
		

	$message = '';
	// Check each value if it's a correct server id
	foreach($servers as $server_id) {
	
		// Make sure it's between the limit
		if($server_id < 1 || $server_id > $internet_cols * $internet_rows && (!InGroup($hackerdata['id'], 1) && !InGroup($hackerdata['id'], 2))) {
		    $message .= "Invalid server id ($server_id)<br>";
		    continue;
		}

		// is owned?
		if (IsOwned($server_id)) {
			$message .= "server($server_id) is already owned by someone!<br>";
			continue;
		}
		// Check the links
		$check = array();
		$gwlink = 0; // it can not touch an enemy gateway
		$validlink = 0; // it must touch a clan server

		// Get the servers that is above, below, right and left to the server and put it in an array.
		if ($server_id % $internet_rows != 1) $check[] = $server_id - 1;
		if ($server_id % $internet_rows != 0) $check[] = $server_id + 1;
		if ($server_id > $internet_rows) $check[] = $server_id - $internet_rows;
		if ($server_id <= ($internet_cols * $internet_rows) - $internet_rows) $check[] = $server_id + $internet_rows;
		
		foreach ($check as $check_id) {	
			$result = mysqli_query($link, "SELECT clan_id, gateway FROM server LEFT JOIN hacker ON server.hacker_id = hacker.id WHERE server.id = $check_id");
			$row = mysqli_fetch_assoc($result);
			if ($row['clan_id'] == $hackerdata['clan_id']) $validlink++;
			else if ($row['gateway'] == 1) $gwlink++;
		}
		
		if ($gwlink > 0) {
		    $message .= "server($server_id) is connected to an enemy's gateway. You can not buy it.<br>";
		    continue;
		}
		
		if ($validlink == 0) {
		    $message .= "server($server_id) is not connected to any of your clan clusters.<br>";
		    continue;
		}
		
		// check server details
		$result = mysqli_query($link, "SELECT id FROM server WHERE id = ".$server_id);
		if (mysqli_num_rows($result) == 0) {
			$message .= "server($server_id) doesn't seem to be online.<br>";
		    continue;
		}
		// drop date
		$drop_date = date($date_format, strtotime($server_drop_interval));
		
		// Generate a random password and set that as the server password, the hacker can change it immediately using his server manager
		$password = createrandomPassword();
		
		// update server
		$result = mysqli_query($link, "UPDATE server SET hacker_id = ".$hackerdata['id'].", pass_date = '0', drop_date = '$drop_date', password = '$password', profit = -".$server_price.", efficiency = 100, product_id = 0, product_efficiency = 0, firewall = 0, npc = 0 WHERE id = $server_id");
		
		// Server log that states the first boot and take out the money
		AddLog ($server_id, "server", "", "Booting server from {$hackerdata['ip']}", $now);
		$message .= "You successfully bought server ".GetServerName($server_id)."<br>";
		BankTransfer ($hackerdata['id'], "hacker", $server_price * -1, "Server ".GetServerName($server_id)." purchased");

	}
	
	PrintMessage("Success", $message, "40%");
	include('pages/internet.php');
?>