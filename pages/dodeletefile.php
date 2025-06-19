<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php
	// what do you want to delete?
	$inventory_id = 0;
	if (!empty($_GET['id'])) $inventory_id = intval($_GET['id']);
	
	// is it on your hdd?
	$result = mysqli_query($link, "SELECT inventory.*, server.hacker_id as ftpowner_id, server.ip FROM `inventory` LEFT JOIN server on inventory.server_id = server.id WHERE (inventory.hacker_id = {$hackerdata['id']} OR server.hacker_id = {$hackerdata['id']}) AND inventory.id = $inventory_id");
	if (mysqli_num_rows($result) == 0) return "File not found";
	$row = mysqli_fetch_assoc($result);
	
	// delete the file
	PrintMessage ("Success", "File ".FileInfo($inventory_id, "title")." deleted", "40%"); // first print the file data before you delete it!!!
	$result = mysqli_query($link, "DELETE FROM `file` WHERE `id` = ".$row['file_id']); // not always needed
	$result = mysqli_query($link, "DELETE FROM `inventory` WHERE `id` = ".$inventory_id);
	
	// local file or ftp file?
	if ($row['server_id'] == 0)
		include ("./pages/software.php");
	else {
		$server_ip = $row['ip'];
		include ("./pages/ftp.php");
	}	
?>