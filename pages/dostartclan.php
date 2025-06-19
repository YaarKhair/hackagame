<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php
	if ($_SESSION['startclan'] != 1) return "Session error";
	$_SESSION['startclan'] = 0;
		
	// alias
	$alias = "";
	if (!empty($_POST['alias'])) { 
		$alias = sql($_POST['alias']); 
		if (strlen($alias) < 3) 			return "Clanname is too short. Minimum of 3 characters.<br>";
		if (strlen($alias) > 20) 			return "Clanname is too long. Maximum of 20 characters.<br>";
		if (!isvalidclan($alias)) 			return "Clanname contains invalid characters. Allowed is: $allowedchars";
		if (IsBannedAlias($alias)) 	return "Clan alias contains banned words";
		
		$result = mysqli_query($link, "SELECT id FROM clan WHERE active = 1 AND alias = '".$alias."'");
		if (mysqli_num_rows($result) > 0) return "A clan already exists with that name.";
	}	
	else return "Your clan alias can not be empty.";
	
	// tag
	$shorttag = "";
	if (!empty($_POST['shorttag'])) { 
		$shorttag = sql($_POST['shorttag']); 
		if (strlen($shorttag) < 1) 			return "Short tag is too short. Minimum of 1 characters.<br>";
		if (strlen($shorttag) > 3) 			return "Short tag is too long. Maximum of 3 characters.<br>";
		if (!isvalidclan($shorttag)) 			return "Short tag contains invalid characters. Allowed is: $allowedchars";
		if (IsBannedAlias($shorttag)) 	return "Clan shorttag contains banned words";
		
		$result = mysqli_query($link, "SELECT id FROM clan WHERE shorttag = '$shorttag'");
		if (mysqli_num_rows($result) > 0) return "A clan already exists with that short tag.";
	}	
	
	// password
	$pass1 = "";
	$pass2 = "";
	if (!empty($_POST['pass1'])) $pass1 = sql($_POST['pass1']);
	if (!empty($_POST['pass2'])) $pass2 = sql($_POST['pass2']);
	if ($pass1 != $pass2) return "The passwords do not match.";
	if (!isvalidpassword($pass1) && $pass1 != "") return "Password can not be empty and only contain: a-z, A-Z, 0-9"; 
	
	// color
	$color = "";
	if (!empty($_POST['color'])) $color = sql($_POST['color']);
	if (strlen($color) != 6) return "A clan html color is always 6 characters.";
	if (!isHex($color)) return "A clan html color must be in HEX format.";
	if ($color == "000000") return "Do *NOT* use Black (or nearly black) as your clancolor!! Penalty: BAN.";
	
	/*if (!empty($_POST['server_id'])) {
		// gateway id, is it free?
		$server_id = intval($_POST['server_id']); 
		if ($server_id > $internet_cols * $internet_rows) return "Invalid gateway location";
		$result = mysqli_query($link, "SELECT id FROM server WHERE id = $server_id AND hacker_id = 0");
		if (mysqli_num_rows($result) == 0) return "The location where you want to install the gateway is already taken. Pick an empty position on the internet.";
		
		if (InRange ($server_id, "gateway", 2)) return "Your gateway is too close to another gateway.";
    	if (InRange ($server_id, "hibernating", 2))  return "Your gateway is too close to a server of a hibernating hacker.";
		
	}
	else return "Invalid gateway ID?";*/
	
	// gateway size
	if (!empty($_POST['gateway_id'])) {
		$gateway_id = intval($_POST['gateway_id']);
	}
	else return "Invalid gateway size";
	
	// check gateway details
	$result = mysqli_query($link, "SELECT id, efficiency, price FROM product WHERE code LIKE 'GATEWAY%' AND id = $gateway_id");
	if (mysqli_num_rows($result) > 0) {
		$row = mysqli_fetch_assoc ($result);
		// read the data
		$product_id = $row['id'];
		if (!HasOnHDD($hackerdata['id'], $product_id)) return "You do not have the correct gateway software on your HDD.";
		$efficiency = $row['efficiency'];
		$profit = ($row['price'] + $server_price) * -1;
		$clan_id = mysqli_next_id("clan");
	}
	else return "Invalid gateway size";
	
	if ($hackerdata['bankaccount'] < $server_price) return "You can not pay for the clan server, which costs $currency $server_price";
		
	DeleteFromInventory($hackerdata['id'], $product_id); // use the gateway
	// timers to prevent people from creating a new clan just to reset the ddos timers.
	$next_gwhack = date($date_format, strtotime("+".$clan_gwhack_interval." minutes")); // for the clan
	$next_serverhack = date($date_format, strtotime("+".$clan_serverhack_interval." minutes")); // for the clan
	$next_pchack = date($date_format, strtotime("+".$clan_pchack_interval." minutes")); // for the clan
	$result = mysqli_query($link, "INSERT INTO clan (alias, started, bankaccount, bankaccount_password, color, founder_id, last_login, nextgatewayhack_date, nextserverhack_date, nextpchack_date) VALUES ('$alias', '$now', 0, '".sha1($pass1)."', '$color', ".$hackerdata['id'].", '$now', $next_gwhack, $next_serverhack, $next_pchack)");
	$result = mysqli_query($link, "UPDATE hacker SET bankaccount = bankaccount - $server_price, clan_id = $clan_id, clan_council = 1 WHERE id = ".$hackerdata['id']);
	$server_id = CreateServer ($hackerdata['id']);
	$result = mysqli_query($link, "UPDATE server SET drop_date = '$now', password = '$pass1', profit = $profit, efficiency = 100, product_id = $product_id, product_efficiency = $efficiency, firewall = 0, gateway = 1, offline_from = '$now', offline_till = '99999999999999' WHERE id = $server_id"); // offline_from is set to $now, so they have 3 days before it's dropped it they do not get it online
	$result = mysqli_query($link, "INSERT INTO board (title, clan_id) VALUES ('$alias', $clan_id)");
	AddLog ($hackerdata['id'], "hacker", "bank", "$server_price|Clan server", $now);
	AddLog ($hackerdata['id'], "hacker", "clan", "Clan $alias created by hacker ".$hackerdata['alias'], $now);
	AddLog ($server_id, "server", "", "Booting Gateway..., Server ".Alias4Logs ($server_id, "server"), $now);
	PrintMessage ("Success", "Clan created and Gateway succesfully installed.<br>You are the clan founder.", "40%");
?>