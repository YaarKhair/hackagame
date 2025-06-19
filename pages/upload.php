<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php
	if ($_SESSION['loaded'] == 0) {
		$_SESSION['loaded'] = 1;
		
		$error = ""; // clean sheet
		
		// inventory.id of what we're going to transfer
		$inventory_id = 0;
		if (!empty($_POST['file_id'])) $inventory_id = intval($_POST['file_id']);
		
		// you are ALWAYS uploading to AN FTP SERVER
		$server_id = 0;
		if (!empty($_POST['server_id'])) $server_id = intval($_POST['server_id']);
		if ($server_id == 0) return "Invalid server";
		
		// the price
		$price = 0; // freeware rules ;-)
		if (!empty($_POST['price'])) $price = intval($_POST['price']);
		if ($price < 0) return "A price must be 0 or higher";
		
		// checksum is sha1(server_ip) 
		$checksum = "";
		if (!empty($_POST['checksum'])) $checksum = sql($_POST['checksum']);
		
		// is the destination server really FTP?
		$result = mysqli_query($link, "SELECT ip, hacker_id, product_id, group_upload, group_download FROM server LEFT JOIN product ON server.product_id = product.id WHERE product.code = 'SERVERFTP' AND server.id = ".$server_id);
		if (mysqli_num_rows($result) == 0) return "Connection to FTP server on port 21 failed.";

		$row = mysqli_fetch_assoc($result);
		$server_ip = $row['ip'];
		$group_upload = $row['group_upload'];
		$group_download = $row['group_download'];

		// is the code valid (ergo, do the IP's match?)
		if (sha1($server_ip) != $checksum) return "Upload failed. Connection lost.";
		// are you allowed to upload to this server
		if ($group_upload != 0 && !InGroup($hackerdata['id'], $group_upload)) return "You are not allowed to upload to this server!";
		
		// let's first read info about the file we are going to transfer
		$result = mysqli_query($link, "SELECT * FROM inventory WHERE id = ".$inventory_id);
		if (mysqli_num_rows($result) == 0) return "File not found.";

		$row = mysqli_fetch_assoc($result);
		$code = mysqli_get_value("code", "product", "id", $row['product_id']);
		// a server from which everyone can download
		if ($group_download == 0) {
			if (substr($code, 0, 5) == "TRADE") return "This server does not accept trading goods.";
		}
		// on a trading server you can only upload trading goods
		else {
			if (substr($code, 0, 5) != "TRADE") return "This server only accepts trading goods.";
			if ($price != 0) return "This server only accepts software uploads valued at $currency 0.";
		}	
			
		// if you're uploading you can only initiate ONE upload per inventory_id
		$result2 = mysqli_query($link, "SELECT id FROM filetransfer WHERE inventory_id = $inventory_id AND source_id = ".$hackerdata['id']);
		if (mysqli_num_rows($result2) > 0) return "Filetransfer already active.";

		// queue full?
		$slots_left = DownloadQueueMax($hackerdata['id']) - DownloadQueueCurrent($hackerdata['id']);
		if ($slots_left == 0) return "You can not upload this file. Your transfer queue has $slots_left slots left.";

		// do some nifty math ;-)
		$size = FileInfo($inventory_id, "size");
		$minutes = DownloadTime($hackerdata['id'], $size);
		$ready_date = date($date_format, strtotime("+".$minutes." minutes"));

		// update price field of inventory line
		$result = mysqli_query($link, "UPDATE inventory SET price = ".$price." WHERE id = ".$inventory_id);
		
		// start upload (cron)
		$result = mysqli_query($link, "INSERT INTO filetransfer (inventory_id, source_id, source_ip, source_entity, destination_id, destination_ip, destination_entity, ready_date) VALUES ($inventory_id, {$hackerdata['id']}, '{$hackerdata['ip']}', 'hacker', $server_id, '$server_ip', 'server', '$ready_date')");
		
		PrintMessage ("Success", "Uploading ".FileInfo($inventory_id, "title"). " to $server_ip on port 21.", "40%");
		AddLog ($hackerdata['id'], "hacker", "transfer", "Uploading ".FileInfo($inventory_id, "title"). " to destination host ".$server_ip, $now);
		AddLog ($server_id, "server", "", "{$hackerdata['ip']}: Uploading ".FileInfo($inventory_id, "title"), $now);
		
		include ("pages/ftp.php");
	}	
?>