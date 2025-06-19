<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php
	// transfer id we want to cancel
	$transfer_id = 0;
	if (!empty($_POST['transfer_id']))  $transfer_id = intval($_POST['transfer_id']); 

	// lets see if you are either the uploader or downloader
	$sql = "SELECT * FROM filetransfer WHERE id = ".$transfer_id." AND ((source_id = ".$hackerdata['id']." AND source_entity = 'hacker') OR (destination_id = ".$hackerdata['id']." AND destination_entity = 'hacker'))";
	$result = mysqli_query($link, $sql);
	if (mysqli_num_rows($result) == 0)
		Return "Transfer not found.";
	
	$row = mysqli_fetch_assoc($result);
	
	// log in upload and download log
	/*$dest_id = $row['destination_id'];
	$dest_ip =
	if ($row['destination_entity'] == "server") {
		$dest_entity = "server";
		$dest_log = "";
		$dest_logid = $row['destination_id'];
	}	
	$source_log = "transfer";
	$source_logid = $row['source_id'];
	if ($row['source_entity'] == "server") {
		$source_log = "server";
		$source_logid = GetServerOwner($row['source_id']);
	}*/

	$dest_id = $row['destination_id'];
	$dest_ip = $row['destination_ip'];
	$dest_entity = $row['destination_entity'];
	$dest_alias = Alias4Logs ($dest_id, $dest_entity);
	if ($dest_entity == "hacker") $dest_log = "transfer";
	else $dest_log = "";

	// source is a hacker, so use these log settings
	$source_id = $row['source_id'];
	$source_ip = $row['source_ip'];
	$source_entity = $row['source_entity'];
	if ($source_entity == "hacker") $source_log = "transfer";
	else $source_log = "";
		
	AddLog ($dest_id, $dest_entity, $dest_log, "Transfer interrupted. Cancelled", $now);
	AddLog ($source_id, $source_entity, $source_log, "Transfer interrupted. Cancelled by $dest_alias", $now);
	
	// stop transfer
	$result = mysqli_query($link, "DELETE FROM filetransfer WHERE id = ".$transfer_id);
	PrintMessage ("Success", 'Transfer cancelled.', "40%");
    include ("./pages/transfers.php");
?>