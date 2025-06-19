<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php
	if ($_SESSION['loaded'] == 0) {
		$_SESSION['loaded'] = 1;
		
		// inventory.id of what we're going to transfer
		$inventory_id = 0;
		if (!empty($_GET['inventory_id'])) $inventory_id = intval($_GET['inventory_id']);
		
		// if we are downloading from a server, this is set
		$server_id = 0;
		if (!empty($_GET['server_id'])) $server_id = intval($_GET['server_id']);
		
		// code, which is sha1(sourceip) 
		$checksum = "";
		if (!empty($_GET['checksum'])) $checksum = sql($_GET['checksum']);
		
		// let's first read info about the file we are going to transfer
		$result = mysqli_query($link, "SELECT * FROM inventory WHERE id = ".$inventory_id);
		if (mysqli_num_rows($result) == 0) return "File not found";
		
		$row = mysqli_fetch_assoc($result);
		
		// source is either hacker or server
		if ($server_id == 0) {
			$source_entity = "hacker";
			$source_id = $row['hacker_id'];
			$source_log = "transfer";
			$price = 0;
		}	
		else {
			$source_entity = "server";
			$source_id = $server_id;
			$source_log = ""; // not applicable. servers have 1 log type
			$price = $row['price'];
			$group_download = mysqli_get_value ("group_download", "server", "id", $server_id);
			// are you allowed to download from this server
			if ($group_download != 0 && !InGroup($hackerdata['id'], $group_download)) return "You are not allowed to download from this server!";
		}	
		// if you're downloading from a PC, you can only initiate ONE download per inventory_id
		//if ($server_id == 0) { REMOVED THIS AS I DONT KNOW WHY YOU SHOULD BE ABLE TO DOWNLOAD THE SAME FILE FROM SERVERS. IF I MADE A MISTAKE UNCOMMENT THIS
			$result2 = mysqli_query($link, "SELECT id FROM filetransfer WHERE inventory_id = $inventory_id AND destination_id = ".$hackerdata['id']);
			if (mysqli_num_rows($result2) > 0) return "Filetransfer already active.";
		//}
		
		// source IP	
		$result2 = mysqli_query($link, "SELECT ip FROM ".$source_entity." WHERE id = ".$source_id);
		$row2 = mysqli_fetch_assoc($result2);
		$ip = $row2['ip'];

		// is the code valid (ergo, do the IP's match?)
		if (sha1($ip) != $checksum) return "Download failed. Most likely the source changed it's IP.";

		// enough harddisk space left?
		if (!HasInstalled($hackerdata['id'], "HDD")) 
			return "Harddisk S.M.A.R.T failure. Defect.";
		else
			if (FileInfo($row['id'], "size") > (HDDsize($hackerdata['id']) - HDDuse($hackerdata['id']))) return 'Disk full. Free some diskspace before attempting this download again.';
					
		// does your level permit this download (check is for products, not files)?
		if (FileInfo($row['id'], "is_file") == 0)
			if (FileInfo($row['id'], "level") > EP2Level(GetHackerEP($hackerdata['id']))) 
				return "Your level does not permit you to download this item.";;
		
		// if the price is not 0, can you afford it?
		if ($price > $hackerdata['bankaccount'] && !InGroup($hackerdata['id'], 1)) return 'You can not afford this download.';
		
		// queue full?
		$slots_left = DownloadQueueMax($hackerdata['id']) - DownloadQueueCurrent($hackerdata['id']);
		if ($slots_left == 0) return "You can not download this file. Your transfer queue has $slots_left slots left.";

		// download time
		$size = FileInfo($row['id'], "size");
		$minutes = DownloadTime($hackerdata['id'], $size);
		$ready_date = date($date_format, strtotime("+".$minutes." minutes"));
		
		// start download (cron)
		$result = mysqli_query($link, "INSERT INTO filetransfer (inventory_id, source_id, source_ip, source_entity, destination_id, destination_ip, destination_entity, ready_date) VALUES (".$inventory_id.", ".$source_id.", '".$ip."', '".$source_entity."', ".$hackerdata['id'].", '".$hackerdata['ip']."', 'hacker', '".$ready_date."')");
		
		// add download hit to inventory line
		$result = mysqli_query($link, "UPDATE inventory SET downloads = downloads + 1 WHERE id = ".$inventory_id);
		
		PrintMessage ("Success", "Download initiated...", "40%");
		$title = FileInfo($row['id'], "title");
		AddLog ($hackerdata['id'], "hacker", "transfer", "Downloading $title from source host ".$ip, $now);
		AddLog ($source_id, $source_entity, $source_log, "{$hackerdata['ip']}: Downloading $title", $now);
		
		if ($price == 0 && $source_entity == "server" && $row['file_id'] > 0) include ("./pages/notepad.php"); //direct view of files if they are free
		// downloading from a server? then lets open the FTP file list again
		if ($source_entity == "server") {
			$server_ip = $ip;
			include ("pages/ftp.php");
		}

	}	
?>