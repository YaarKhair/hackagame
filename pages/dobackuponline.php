<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php
	$action = '';
	if (!empty($_POST['action'])) 
		$action = sql($_POST['action']);
	
	if ($action != "backup" && $action != "restore") 
		Return "There was a problem with your request. Please try again";
	
	// can you afford this?
	// system size * fee
	$result = mysqli_query($link, "select sum(product.size) as syssize FROM system LEFT JOIN product ON system.product_id = product.id WHERE (product.in_shop = 1) AND system.hacker_id = ".$hackerdata['id']);
	$row = mysqli_fetch_assoc ($result);
	$system_size = $row['syssize'];
	$fee = round($system_size * $backup_fee);
	if ($action == "backup" && $hackerdata['bankaccount'] < $fee) 
		Return "You can not afford this";
	
	/* BACKUP */
	if ($action == "backup") {
		$result = mysqli_query($link, "SELECT system.* FROM system LEFT JOIN product ON system.product_id = product.id where product.in_shop = 1 AND system.hacker_id = ".$hackerdata['id']);
		if (mysqli_num_rows($result) > 0) {
			// delete the old backup
			$result2 = mysqli_query($link, "DELETE FROM system WHERE backup_id = ".$hackerdata['id']);
			// lets create a backup of this system
			while ($row = mysqli_fetch_assoc($result)) {
				$result2 = mysqli_query($link, "INSERT INTO system (backup_id, product_id, efficiency) VALUES (".$row['hacker_id'].", ".$row['product_id'].", ".$row['efficiency'].")");
			}
			// set new backup date
			$result = mysqli_query($link, "UPDATE hacker SET backup_date = $now WHERE id = ".$hackerdata['id']);
			$message = "Backup created succesfully.";
			BankTransfer ($hackerdata['id'], "hacker", $fee * -1, "Online Backup Fee");
		}
		else $message = "You have a standard system. Nothing to backup.";
		PrintMessage ("Success", $message, "40%");
	}
	/* RESTORE */
	else {
		if (!HasInstalled($hackerdata['id'], "HDD")) 
			Return "Harddisk S.M.A.R.T failure. Defect.";
		if ($system_size > (HDDsize($hackerdata['id']) - HDDuse($hackerdata['id']))) 
			Return 'Disk full. Free some diskspace before attempting this restore again.';
		// empty hdd?
		$result2 = mysqli_query($link, "SELECT id FROM system WHERE product_id NOT IN (SELECT id FROM product WHERE code = 'CPU' OR code = 'MEMORY' or code = 'MAINBOARD' or code = 'INTERNET' or code = 'HDD') AND hacker_id = ".$hackerdata['id']);
		if (mysqli_num_rows($result2) > 0) 
			Return 'Backups can only be restored on a fresh system that has nothing installed on it yet.';	
		
		// read the backup
		$result = mysqli_query($link, "SELECT system.*, product.size FROM system LEFT JOIN product ON system.product_id = product.id where product.in_shop = 1 AND system.backup_id = ".$hackerdata['id']);
		if (mysqli_num_rows($result) > 0) {
			// delete the old system
			$result2 = mysqli_query($link, "DELETE FROM system WHERE product_id NOT IN (SELECT id FROM product WHERE code = 'CPU' OR code = 'MEMORY' or code = 'MAINBOARD' or code = 'INTERNET' or code = 'HDD') AND hacker_id = ".$hackerdata['id']);
			// restore backup
			$message = "Backup restored succesfully."; // we assume it will fit
			while ($row = mysqli_fetch_assoc($result)) {
				if ((HDDsize($hackerdata['id']) - HDDuse($hackerdata['id'])) < intval($row['size'])) $message = "Restore failed due to lack of HDD space.";
				else $result2 = mysqli_query($link, "INSERT INTO system (hacker_id, product_id, efficiency) VALUES (".$hackerdata['id'].", ".$row['product_id'].", ".$row['efficiency'].")");
			}
		}
		else $message = "No backup to restore.";
		// delete the backup
		$result = mysqli_query($link, "DELETE FROM system WHERE backup_id = ".$hackerdata['id']);
		PrintMessage ("Success", $message, "40%");
	}
?>
