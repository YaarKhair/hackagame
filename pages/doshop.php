<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php
	if (empty($_SESSION['cart'])) return "Nothing in your cart.";
    // Sorting the products into ISP, Hardware and software
    $software = array();
    $hardware = array();
	$perks = array();
	$servers = array();
    $message = '';
    $check_connection_date = false;

    foreach($_SESSION['cart'] as $product_id) {
        $type = mysqli_get_value("in_shop","product","id",$product_id);
        if($type == 1) $software[] = $product_id;
        if($type == 2) $hardware[] = $product_id;
        if($type == 3) { $check_connection_date = true; $hardware[] = $product_id; }
		if($type == 4) $perks[] = $product_id;
		if($type == 5) $servers[] = $product_id;
    }
    
    // Sorting the arrays into $product_id => $quantity
    $items = count($software);
    $software = array_count_values($software);
    $hardware = array_count_values($hardware);
    $cart = array_count_values($_SESSION['cart']);
    
    // Get the total price and check if the hacker has enough money
    $total_price = 0;
    foreach($cart as $product_id => $quantity) $total_price += mysqli_get_value("price","product","id",$product_id) * $quantity;
    if($total_price > $hackerdata['bankaccount']) return "You do not have enough money to check out with the cart.";
    
    // Check if the hacker can install a new connection if it exists in the cart
    if($check_connection_date && $now < $hackerdata['nextisp_date']) return "You can request a new connection in ".Seconds2Time(SecondsDiff($now, $hackerdata['nextisp_date']))."<br>You can not checkout before removing the connection"; 
    
    /**** HANDLING ISP THEN HARDWARE ****/
    $hardware_installed = false;
    foreach($hardware as $product_id => $quantity) {
        $result = mysqli_query($link, "SELECT efficiency, code, title, price FROM product WHERE id = $product_id");
        $row = mysqli_fetch_assoc($result);
        BankTransfer($hackerdata['id'], "hacker", ($row['price']*$quantity)*-1, $quantity."x"." {$row['title']} installed");    
        
        // If it's a connection update some stuff
        if($row['code'] == 'INTERNET') {
            // if you are currently on n00bNET, you get some extra cash
            if ($hackerdata['network_id'] == 1) BankTransfer ($hackerdata['id'], "hacker", $startmoney, "Some cash to get you started on the Internet.");
            $nextisp_date = date($date_format, strtotime("+".$isp_interval." hours"));
            $result2 = mysqli_query($link, "UPDATE hacker SET nextisp_date = '".$nextisp_date."', network_id = 2 WHERE id = ".$hackerdata['id']);
        }
        
        // If it's an HDD delete everything before installing it
        if($row['code'] == 'HDD') {
            $result = mysqli_query($link, "DELETE FROM inventory WHERE server_id = 0 AND hacker_id = ".$hackerdata['id']);
            $result = mysqli_query($link, "DELETE FROM system WHERE product_id NOT IN (SELECT id FROM product WHERE code = 'CPU' OR code = 'MEMORY' or code = 'MAINBOARD' or code = 'INTERNET') AND hacker_id = ".$hackerdata['id']); // hdd gone and all installed software
        }
        
        // Install hardware
        $result2 = mysqli_query($link, "SELECT system.id FROM system INNER JOIN product ON system.product_id = product.id WHERE system.hacker_id = ".$hackerdata['id']." AND product.code = '".$row['code']."'");
        if (mysqli_num_rows($result2) > 0) {
            // upgrade
            $row2 = mysqli_fetch_assoc($result2);
            $result2 = mysqli_query($link, "UPDATE system SET product_id = $product_id, efficiency = ".$row['efficiency']." WHERE id = ".$row2['id']);
        }
        else {
            // new
            $result2 = mysqli_query($link, "INSERT INTO system (hacker_id, product_id, efficiency) VALUES (".$hackerdata['id'].", $product_id, ".$row['efficiency'].")");   
        }
        $hardware_installed = true;
    }
    if($hardware_installed) $message .= "Hardware successfully installed.<br>";
    
    // check download queue
    $queue = DownloadQueueMax($hackerdata['id']) - DownloadQueueCurrent($hackerdata['id']);
    if($items > $queue) return "You can not download $items files. Your download queue has $queue slots left.";
    
    // Get the total size of the software and see if he has enough space for it
    $total_size = 0;
    foreach($software as $product_id => $quantity) $total_size += (mysqli_get_value("size","product","id",$product_id)) * $quantity;
    if($total_size > HDDsize($hackerdata['id']) - HDDuse($hackerdata['id'])) return "$message<br>You do not have enough space to download the software.";
    
    /**** HANDLING SOFTWARE *****/
    $software_installed = false;
    foreach($software as $product_id => $quantity) {
        $result = mysqli_query($link, "SELECT title, size, price FROM product WHERE id = $product_id");
        $row = mysqli_fetch_assoc($result);
        $size = $row['size'];
        
		// noobnet or staff? no wait times on downloads
        if ($hackerdata['network_id'] == 1 || InGroup($hackerdata['id'], 1) || InGroup($hackerdata['id'], 2))
        	$ready_date = $now;
		else {
        	$download_minutes = DownloadTime($hackerdata['id'], $size);
        	$ready_date = date($date_format, strtotime("+".$download_minutes." minutes"));
		}	
        
        
        // if it's software, place it on the 1338 ftp and set the price to 0 (you already paid for it)
        for ($i = 0; $i < $quantity; $i++) {
            $inventory_id = mysqli_next_id("inventory");
			$shop_serverip = mysqli_get_value("ip", "server", "id", $shop_serverid);
            $result2 = mysqli_query($link, "INSERT INTO inventory (hacker_id, product_id, server_id, datechanged, price) VALUES ($shop_ownerid, $product_id, $shop_serverid, '$now', {$row['price']})");
            $result2 = mysqli_query($link, "INSERT INTO filetransfer (inventory_id, source_id, source_ip, source_entity, destination_id, destination_ip, destination_entity, ready_date) VALUES ($inventory_id, $shop_serverid, '$shop_serverip', 'server', {$hackerdata['id']}, '{$hackerdata['ip']}', 'hacker', '$ready_date')");
            AddLog ($hackerdata['id'], "hacker", "transfer", "Downloading {$row['title']} from ".$shop_serverip, $now);
			AddLog ($shop_serverid, "server", "", "{$hackerdata['ip']}: Downloading {$row['title']}", $now);
        }           
    $software_installed = true;
    }
    if($software_installed) $message .= "Software added to your <a href=\"?h=transfers\">download queue</a>.<br>";
	
	// Handling perks
	if(count($perks) > 0) {
		If((NumPerks($hackerdata['id']) + count($perks)) > AllowedNumPerks($hackerdata['id'])) return "You cannot equip this number of perks.";
		foreach($perks as $perk_id) {
			$already_equipped_result = mysqli_query($link, "SELECT id FROM perks WHERE hacker_id = {$hackerdata['id']} AND product_id = $perk_id");
			if(mysqli_num_rows($already_equipped_result) > 0) return "[".mysqli_get_value("title", "product", "id", $perk_id)."] is already equipped.";
			$equip_perk_result = mysqli_query($link, "INSERT INTO perks (hacker_id, product_id, equip_date) VALUES ({$hackerdata['id']}, $perk_id, $now)");
        	BankTransfer($hackerdata['id'], "hacker", mysqli_get_value("price", "product", "id", $perk_id) * -1, mysqli_get_value("title","product", "id", $perk_id)." equipped");   
			AddLog($hackerdata['id'], "hacker", "perk", "Perk ".mysqli_get_value("title","product", "id", $perk_id)." equipped.", $now);
		}
		$message .= "Perks Equipped.";
	}

	// Handling servers
	if(count($servers) > 0) {
		// Do you have enough space to buy X number of servers?
		$current_server_count = NumServers($hackerdata['id']);
		$max_servers = MaxServers($hackerdata['id']);
		$server_count = count($servers);
		if(($current_server_count + count($servers)) > $max_servers) return "You do not have enough server slots to buy ".count($servers)." servers";
		
		// Are you on the right network & in a clan?
		if ($hackerdata['clan_id'] == 0) 
			return "You're not in a clan. You can not buy servers when you're not in clan.";		

		if ($hackerdata['network_id'] != 2) 
			return "You can not buy servers when you are connected to ".mysqli_get_value("name", "network", 1);

		// create the servers
		foreach($servers as $server) {
			CreateServer ($hackerdata['id']);
		}
		// pay for them
		BankTransfer($hackerdata['id'], "hacker", mysqli_get_value("price", "product", "id", $servers[0]) * $server_count * -1, "$server_count servers bought.");
		$message .= "You successfully bought $server_count servers";
	}
    
    PrintMessage("Info",$message);
    $_SESSION['cart'] = array();
    include ("pages/shop.php");
?>