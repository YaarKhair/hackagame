<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php
	// type of scan? SRV or PC
	$entity = "";
	if (!empty($_POST['entity'])) $entity = sql($_POST['entity']);
	if ($entity != "hacker" && $entity != "server") return "Invalid target for scan selected.";

	// read selected scan
	$scan_id = 0;
	if (!empty($_POST['scan_id'])) $scan_id = intval($_POST['scan_id']);

	// does this antivirus product exist?
	$result = mysqli_query ($link, "SELECT * FROM antivirus WHERE id = $scan_id AND entity = '$entity'");
	if (mysqli_num_rows($result) == 0) return "Error processing your request";

	// too soon brah
	if ($now < $hackerdata['nextscan'.$entity.'_date']) return "This service will be available again at ".Number2Date($hackerdata['nextscan'.$entity.'_date']);

	// get details
	$row = mysqli_fetch_assoc($result); // details of scanjob

	// level restictions?
	if ($row['level'] > EP2Level(GetHackerEP($hackerdata['id']))) return "Your level does not permit you to use this scan option.";

	// init
	$refresh_ip = 0;
	$price = 0;
	$scantime = 0;

	// scanning a server?
	if ($entity == "server") {
		if(!empty($_POST['target_id'])) $target_id = $_POST['target_id'];
		if (count($target_id) == 0) return "No servers selected.";

		if(!empty($_POST['refresh_ip'])) $refresh_ip = CheckBox($_POST['refresh_ip']);

		$target_id = array_map ('intval', $target_id); // sanitize
		
		// you can scan multiple servers, so lets accomidate that
		foreach ($target_id as $server_id)
		{
			if (GetServerOwner($server_id) != $hackerdata['id']) return "This is not your server!";
			$price += $row['price'];
			$scantime += $row['scantime'];
			if($refresh_ip == 1) $price += 10000;
		}
		
	}	
	else
	{
		$target_id[] = $hackerdata['id'];
		$price = $row['price'];
		$scantime = $row['scantime'];
	}

	// pay, if possible
	if ($price > $hackerdata['bankaccount']) return "You do not have enough money to pay for {$row['service']} ($currency $price).";
	BankTransfer ($hackerdata['id'], "hacker", $price * -1, $row['service']."($entity)");
	
	$nextscan_date = date($date_format, strtotime("+".($scan_interval+$scantime)." minutes"));
	$scan_till = date($date_format, strtotime("+".$row['scantime']." minutes")); // when the scan is done, for the AV counter in the header
	$result = mysqli_query($link, "UPDATE hacker SET nextscan".$entity."_date = '$nextscan_date', scan_till = '$scan_till' WHERE id = {$hackerdata['id']}");
	foreach ($target_id as $thistarget_id)
		$result = mysqli_query($link, "INSERT INTO cronscans (target_id, scan_id, date, refresh_ip) VALUES ($thistarget_id, $scan_id, '$scan_till', $refresh_ip)");
	PrintMessage ("Success", "Scan(s) initiated.", "100%");
?>