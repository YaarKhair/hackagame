<?php
	// this cron runs every 2 minutes
	include("/var/www/modules/connectdb.php");
	include("/var/www/modules/settings.php");
	include("/var/www/modules/functions.php");
	include("/var/www/modules/cronmissions.php");
	include("/var/www/modules/croninfections.php");
	include("/var/www/modules/cronscans.php");

	
	//handle cronbank, the MST percentages
	$result = mysqli_query($link, "SELECT * FROM cronbank WHERE date < '$now'");
	if ($result && mysqli_num_rows($result) > 0) {
		while ($row = mysqli_fetch_assoc($result)) {
			if ($row['percentage'] > 0) { 
				// *** Money Steal Trojan related: calculate the stolen amount at THIS very moment ***
				$result2 = mysqli_query($link, "SELECT bankaccount FROM ".$row['victim_entity']." WHERE id = ".$row['victim_id']);
				$row2 = mysqli_fetch_assoc($result2);
				
				// asume they have nothing
				$amount = 0;
				if ($row2['bankaccount'] > 0) $amount = intval((intval($row2['bankaccount']) / 100) * $row['percentage']);
				if ($amount > $row2['bankaccount']) $amount = $row2['bankaccount'];
				
				// if we got something, wire it.
				if ($amount > 0 ) {
					// steal the money from the victim
					BankTransfer($row['victim_id'], $row['victim_entity'], $amount*-1, $row['reason']);
					SendIM (0, $row['victim_id'], "Swiss Bank", "<img src=\"images/swiss.png\" align=\"right\" />Dear Sir, Madam,<br><br>There was a money transfer from your account to a foreign anonymous account.", $now);
					
					// send an IM to the hacker and make a swiss transfer ready
					$steal_amount = round(($amount / 100) * (100 - $clan_interest)); // lets substract some interest from $amount, money draining. 
					$ibot_amount = $amount - $steal_amount; // the interest goes to ibot for Double EP days
					BankTransfer($ibot_id, "hacker", $ibot_amount, "MST interest for Double EP");
					
					$expire_date = date($date_format, strtotime("+".$swissbank_lifetime." hours"));
					$hash = sha1(microtime());
					$result3 = mysqli_query($link, "INSERT INTO swissbank (hacker_id, amount, expire_date, hash) VALUES (".$row['hacker_id'].", $steal_amount, '$expire_date', '$hash')");
					// send an IM with a collect money link
					SendIM(0, $row['hacker_id'], "Swiss Bank", "<img src=\"images/swiss.png\" align=\"right\" />Dear Sir, Madam,<br><br>We would like to inform you of the fact that you currently have a money transfer waiting for you on one of our private accounts.<br><br>If you want to initiate this transfer, then please follow this secure link:<br><br><a href=\"?h=swissbank&hash=$hash\">https://ssl.swissbank.swb/secure/private/accounts/transfer/?ID=". mysqli_insert_id($link)."</a><br><br>Please note that this transfer ID will stay valid for 48 hours, after which the transfer will be deleted for security reasons. This ID will be deleted at ".Number2Date($expire_date)."<br><br>Sincerely yours,<br>Swiss Secure Banking", $now);
				}	
				else {
					// too low balance for MST, some guys are always lucky.
					SendIM (0, $row['victim_id'], "Swiss Bank", "<img src=\"images/swiss.png\" align=\"right\" />Dear Sir, Madam,<br><br>There was a money transfer attempt from your account to a foreign anonymous account, but because you had insufficient funds the transfer was cancelled<br><br>Sorry for any inconvenience.", $now);
					SendIM (0, $row['hacker_id'], "Swiss Bank", "<img src=\"images/swiss.png\" align=\"right\" />Dear Sir, Madam,<br><br>We can not process your request to transfer money to your account. The other party has insufficient funds. The transfer is therefore cancelled.<br><br>Sorry for any inconvenience.", $now);
				}
			}
			else {
				// *** normal cronned bank transfer ***
				//normal CRONNED transfers are always FROM system, so only send money
				BankTransfer($row['hacker_id'], "hacker", $row['amount'], $row['reason']);
			}
			$result2 = mysqli_query($link, "DELETE FROM cronbank WHERE id = ".$row['id']);
		}
	}	
	
	// check transfers in progress
	$result = mysqli_query($link, "SELECT * FROM filetransfer WHERE ready_date > $now ORDER BY filetransfer.id ASC");
	if (mysqli_num_rows($result) > 0) {
		while ($row = mysqli_fetch_assoc($result)) {
			$error = "";
			$inventory_id = $row['inventory_id'];
			
			// destination is hacker, so log here
			$dest_id = $row['destination_id'];
			$dest_ip = $row['destination_ip'];
			$dest_entity = $row['destination_entity'];
			if ($dest_entity == "hacker") $dest_log = "transfer";
			else $dest_log = "";
			
			// source is a hacker, so use these log settings
			$source_id = $row['source_id'];
			$source_ip = $row['source_ip'];
			$source_entity = $row['source_entity'];
			if ($source_entity == "hacker") $source_log = "transfer";
			else $source_log = "";
			
    		// check if the source is still valid and up
			$result2 = mysqli_query($link, "SELECT offline_from, offline_till, ip FROM $source_entity WHERE id = $source_id");
			if (mysqli_num_rows($result2) == 0) {
				$error = "Source unknown. Transfer interrupted."; // raise error
			}
            else {
                $row2 = mysqli_fetch_assoc($result2);
                if ($source_ip != $row2['ip']) $error = "Source IP changed. Transfer interrupted.";
                if ($row2['offline_from'] < $now && $row2['offline_till'] > $now) $error = "Source offline. Transfer interrupted.";
            }
    		// check if the destination is still valid and up
			$result2 = mysqli_query($link, "SELECT offline_from, offline_till, ip FROM $dest_entity WHERE id = $dest_id");
			if (mysqli_num_rows($result2) == 0) {
				$error = "Destination unknown. Transfer interrupted."; // raise error
			}
            else {
                $row2 = mysqli_fetch_assoc($result2);
                if ($dest_ip != $row2['ip']) $error = "Destination IP changed. Transfer interrupted.";
                if ($row2['offline_from'] < $now && $row2['offline_till'] > $now) $error = "Destination offline. Transfer interrupted.";
            }
			
			// file still available on host?
			if ($row['source_entity'] == "server") $sql = " AND server_id = $source_id";
			else $sql = " AND hacker_id = $source_id";	
			$result2 = mysqli_query($link, "SELECT id FROM inventory WHERE id = $inventory_id".$sql);
			if (mysqli_num_rows($result2) == 0) {
				$error = "File not found. Transfer interrupted.";
			}
			// delete transfer if it's not OK
			if ($error != "") {
    			$result2 = mysqli_query($link, "DELETE FROM filetransfer WHERE id = ".$row['id']);
    			AddLog ($dest_id, $dest_entity, $dest_log, $error, $now);
				AddLog ($source_id, $source_entity, $source_log, "$dest_ip: $error", $now);
			}
		}
	}
	
	// handle completed transfers
	$result = mysqli_query($link, "SELECT filetransfer.*, inventory.price, inventory.hacker_id as owner_id, inventory.product_id FROM filetransfer LEFT JOIN inventory ON filetransfer.inventory_id = inventory.id WHERE ready_date <= $now ORDER BY filetransfer.id ASC");
	if (mysqli_num_rows($result) > 0) {
		while ($row = mysqli_fetch_assoc($result)) {
			// transfer tool from source to destination
			$error = 0;
			$price = $row['price'];
			$upload_group = 0;
			$inventory_id = $row['inventory_id'];
			$product_id = $row['product_id'];
			
			// destination is hacker, so log here
			$dest_id = $row['destination_id'];
			$dest_ip = $row['destination_ip'];
			$dest_entity = $row['destination_entity'];
			if ($dest_entity == "hacker") $dest_log = "transfer";
			else $dest_log = "";
			
			// source is a hacker, so use these log settings
			$source_id = $row['source_id'];
			$source_entity = $row['source_entity'];
			$source_ip = $row['source_ip'];
			
			if ($source_entity == "hacker") $source_log = "transfer";
			else $source_log = "";
			
			// transfering to a hacker? then check the HDD
			if ($dest_entity == "hacker")
			{
				// does the hacker that receives the file have enough hdd space?
				if (!HasInstalled($dest_id, "HDD")) $hdd_size = 0;
				else $hdd_size = HDDsize($dest_id) - HDDuse($dest_id);
				$file_size = FileInfo($inventory_id, "size");
				if ($file_size > $hdd_size) {
					AddLog ($dest_id, $dest_entity, $dest_log, 'Transfer failed. Insufficient disk space.', $now);
					AddLog ($source_id, $source_entity, $source_log, "$dest_ip: Transfer failed. Insufficient disk space.", $now);
					$error = 1;
				}
			}
			
			// if they're downloading from a server
			if ($source_entity == "server") {
				// downloader details
				$result2 = mysqli_query($link, "SELECT bankaccount FROM hacker WHERE id = $dest_id");
				$row2 = mysqli_fetch_assoc($result2);
				
				// bank
				$bankaccount = $row2['bankaccount'];
				// get owner of server
				$serverowner_id = GetServerOwner($source_id);
				// owner of the file we downloaded. might get a share of the money of applicable
				$fileowner_id = $row['owner_id']; 
				
				if ($price > $bankaccount) {
					AddLog ($dest_id, $dest_entity, $dest_log, 'Transfer failed. Insufficient funds.', $now);
					AddLog ($source_id, $source_entity, $source_log, "$dest_ip: Transfer failed. Insufficient disk space.", $now);
					$error = 1;
				}
			}
			
			// check if the IP is still valid
			if ($error == 0) {
				$result2 = mysqli_query($link, "SELECT id FROM $source_entity WHERE id = $source_id AND ip = '$source_ip'");
				if (mysqli_num_rows($result2) == 0) {
					// connection lost. warn downloader and uploader
					AddLog ($dest_id, $dest_entity, $dest_log, "Transfer interrupted, IP change.", $now);
					AddLog ($source_id, $source_entity, $source_log, "$dest_ip: Transfer interrupted, IP change.", $now);
					$error = 1; // raise error
				}
				else {
					$row2 = mysqli_fetch_assoc($result2);
					if ($source_entity == "server") $upload_group = mysqli_get_value("group_upload", "server", "id", $source_id);
				}
			}
			
			// file still available on host?
			if ($error == 0) {
				if ($source_entity == "server") $sql = " AND server_id = $source_id";
				else $sql = " AND hacker_id = $source_id";	
				$result2 = mysqli_query($link, "SELECT id FROM inventory WHERE id = $inventory_id".$sql);
				if (mysqli_num_rows($result2) == 0) {
					AddLog ($dest_id, $dest_entity, $dest_log, "Transfer interrupted, file not found", $now);
					AddLog ($source_id, $source_entity, $source_log, "$dest_ip: Transfer interrupted, file not found.", $now);
					$error = 1;
				}
			}
			
			// if everything went well
			if ($error == 0) {
				// downloading freeware that costs money? pay it
				if ($source_entity == "server" && $price > 0) {
					// downloader pays price
					BankTransfer ($dest_id, "hacker", $price * -1, "Downloaded ".FileInfo($inventory_id, "title")." from an FTP");
					// file owner gets 70%
					BankTransfer ($fileowner_id, "hacker", intval (($price / 100) * 70), "Someone downloaded your ".FileInfo($inventory_id, "title")." from an FTP");
					// FTP owner gets 30%
					BankTransfer ($serverowner_id, "hacker", intval (($price / 100) * 30), FileInfo($inventory_id, "title")." got downloaded from your FTP");
					// achievement for sale via his FTP
					RegisterResult ($serverowner_id, "ftpsales", $now);
					// update profit for that FTP server
					$result2 = mysqli_query($link, "UPDATE server SET profit = profit + ".intval (($price / 100) * 30)." WHERE id = $source_id");
				}
				// upload to server set the $server_id
				if ($dest_entity == "server") {
					$server_id = $dest_id;
					$hacker_id = $source_id;
				}
				else {
					$server_id = 0; // change the server id of a file to 0 when it's downloaded to a hacker
					$hacker_id = $dest_id;
				}	
					
				// text files are copied, so make a copy.
				if ($product_id == 0) {
					// COPY TEXT FILE
					$result2 = mysqli_query($link, "SELECT file.* FROM inventory LEFT JOIN file ON inventory.file_id = file.id WHERE inventory.id = $inventory_id");
					$row2 = mysqli_fetch_assoc($result2);
					$title = $row2['title'];
					$text = $row2['text'];
					$text = mysqli_real_escape_string($link, $text);
					$size = $row2['size'];
						
					// get the id of the copy
					$new_id = mysqli_next_id("file");
					
					// make the copy
					$result2 = mysqli_query($link, "INSERT INTO file (title, text, size) VALUES ('$title', '$text', $size)");
					$result2 = mysqli_query($link, "INSERT INTO inventory (hacker_id, server_id, file_id, datechanged, price) VALUES ($hacker_id, $server_id, $new_id, '$now', $price)");
				}
				else {
					//  products are transferred, thus moved.
					$result2 = mysqli_query($link, "UPDATE inventory SET hacker_id = $hacker_id, server_id = $server_id, datechanged = '$now' WHERE id = $inventory_id");
				}	

				AddLog ($dest_id, $dest_entity, $dest_log, "Transfer of ".FileInfo($inventory_id, "title"). " (".DisplaySize(FileInfo($inventory_id, "size")).") completed", $now);
				AddLog ($source_id, $source_entity, $source_log, "$dest_ip: Transfer of ".FileInfo($inventory_id, "title"). " (".DisplaySize(FileInfo($inventory_id, "size")).") completed.", $now);
			}
			// success or no success, it's old and completed so it needs to be removed
			$result2 = mysqli_query($link, "DELETE FROM filetransfer WHERE id = ".$row['id']);
		}	
	}
	AddLog (0, "hacker", "cron", "cron_2m", $now);	
?>