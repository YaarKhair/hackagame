<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php
	if ($_SESSION['shop'] != 1) return "Session error";
	$_SESSION['shop'] = 0;
	
	// what do you want to buy?
	$product_id = 0;
	if (!empty($_POST['product_id'])) 
		$product_id = intval($_POST['product_id']);

	// how many?
	$quantity = 0;
	if (!empty($_POST['quantity'])) 
		$quantity = intval($_POST['quantity']);

	if ($quantity < 1) return "Invalid quantity";
	
	// check product details
	$query = "SELECT * FROM product WHERE id = ".$product_id;
	$result = mysqli_query($link, $query);
	if (mysqli_num_rows($result) == 0) return "Product not found.";

	$row = mysqli_fetch_assoc($result);

	// first let's check if the hacker can afford this
	if ($hackerdata['bankaccount'] < ($row['price']*$quantity)) return "You can't afford this purchase";
	
	// if he can afford it, and it is software, does he have enough HDD space?
	if ($row['software'] == 1) if ((HDDsize($hackerdata['id']) - HDDuse($hackerdata['id'])) < intval($row['size'] * $quantity)) 
		return "You do not have enough free disk space. Remove some programs or upgrade your HDD.";
	
	// is it a buyable item?
	if ($row['in_shop'] == 0) 
		return "You can not buy this item from the 1338 shop!";
	
	// can you buy multiple of this item?
	if ($row['buy_multiple'] == 0 && $quantity > 1) 
		return "You can only buy one of this item at a time.";
	
	// level check
	if ($row['level'] > EP2Level(GetHackerEP($hackerdata['id']))) 
		return "Your level does not permit you to buy this item.";
	
	// internet only once per 12 hours
	if ($row['code'] == 'INTERNET' && $now < $hackerdata['nextisp_date'])
		return "You can request a new connection in ".Seconds2Time(SecondsDiff($now, $hackerdata['nextisp_date']));
	
	// does your queue have space for the download?
	if ($row['software'] == 1) {
		$slots_left = DownloadQueueMax($hackerdata['id']) - DownloadQueueCurrent($hackerdata['id']);
		if ($quantity > $slots_left) return "You can not download $quantity files. Your download queue has $slots_left slots left.";
	}
	
	// pay in advance for hardware. software is paid on download completion
	if ($row['software'] == 0) BankTransfer ($hackerdata['id'], "hacker", ($row['price']*$quantity)* -1, $quantity."x ".$row['title']." bought in 1338 shop");
	
	// if hdd, delete old files
	if ($row['code'] == 'HDD') {
		// delete files from old hdd (excluding the files uploaded to an FTP)
		$result = mysqli_query($link, "DELETE FROM inventory WHERE server_id = 0 AND hacker_id = ".$hackerdata['id']);
		$result = mysqli_query($link, "DELETE FROM system WHERE product_id NOT IN (SELECT id FROM product WHERE code = 'CPU' OR code = 'MEMORY' or code = 'MAINBOARD' or code = 'INTERNET') AND hacker_id = ".$hackerdata['id']); // hdd gone and all installed software
		$extra_info = "You installed the hardware into your system.";
	}
	
	// if internet update next internet chance date
	if ($row['code'] == 'INTERNET') {
		// upgrade from internet connection
		$nextisp_date = date($date_format, strtotime("+".$isp_interval." hours"));
		$result2 = mysqli_query($link, "UPDATE hacker SET nextisp_date = '".$nextisp_date."', network_id = 2 WHERE id = ".$hackerdata['id']);
		$extra_info = "The new internet connection was succesfully installed.";
	}

	// hardware installation
	if ($row['software'] == 0) {
		$result2 = mysqli_query($link, "SELECT system.id FROM system INNER JOIN product ON system.product_id = product.id WHERE system.hacker_id = ".$hackerdata['id']." AND product.code = '".$row['code']."'");
		if (mysqli_num_rows($result2) > 0) {
			// upgrade
			$row2 = mysqli_fetch_assoc ($result2);
			$result2 = mysqli_query($link, "UPDATE system SET product_id = $product_id, efficiency = ".$row['efficiency']." WHERE id = ".$row2['id']);
		}
		else {
			// new
			$result2 = mysqli_query($link, "INSERT INTO system (hacker_id, product_id, efficiency) VALUES (".$hackerdata['id'].", $product_id, ".$row['efficiency'].")");	
		}
		$extra_info = "You installed the hardware into your system.";
		if ($row['code'] == 'INTERNET') $shop = "isp";
		else $shop = "hardware";
	}
	else {
		$size = $row['size'];
		$minutes = DownloadTime($hackerdata['id'], $size);
		$ready_date = date($date_format, strtotime("+".$minutes." minutes"));
		
		// if it's software, place it on the 1338 ftp and set the price to 0 (you already paid for it)
		for ($i = 0; $i < $quantity; $i++) {
			$inventory_id = mysqli_next_id("inventory");
			$result2 = mysqli_query($link, "INSERT INTO inventory (hacker_id, product_id, server_id, datechanged, price) VALUES ($shop_ownerid, $product_id, $shop_serverid, '$now', {$row['price']})");
			$result2 = mysqli_query($link, "INSERT INTO filetransfer (inventory_id, source_id, source_ip, source_entity, destination_id, destination_ip, destination_entity, ready_date) VALUES ($inventory_id, $shop_serverid, '$shop_serverip', 'server', {$hackerdata['id']}, '{$hackerdata['ip']}', 'hacker', '$ready_date')");
			AddLog ($hackerdata['id'], "hacker", "transfer", "Downloading {$row['title']} from source host ".$shop_serverip, $now);
			AddLog ($shop_serverid, "hacker", "server", $hackerdata['alias']." (".$hackerdata['ip'].") is downloading {$row['title']}", $now);
		}					
		$extra_info = "The software is added to your download queue.";
		$shop = "software";
	}
	PrintMessage ("Success", "Purchase successful.<br>".$extra_info, "40%");
	include ("pages/shop.php");
?>