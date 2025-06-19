<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php
	$price = mysqli_get_value ("price", "product", "id", 18); // ip refresher
	if ($price > $hackerdata['bankaccount']) return "You can not afford this service.";
    if ($hackerdata['iprefresh_date'] > 0) return 'IP Refresh already pending at '.Number2Date($hackerdata['iprefresh_date']);
    if ($hackerdata['nextiprefresh_date'] >  $now) return 'Service is available again at '.Number2Date($hackerdata['nextiprefresh_date']);

	//$perk = GetPerkValue($hackerdata['id'], "PERK_DECREASEIPREFRESH");
	//if($perk != 0) $iprefresh_time -= $perk;
    $iprefresh_date = date($date_format, strtotime("+ ".$iprefresh_time." minutes"));
	$result = mysqli_query($link, "UPDATE hacker SET iprefresh_date = '$iprefresh_date' WHERE id = ".$hackerdata['id']);

	BankTransfer($hackerdata['id'], "hacker", $price* -1, "IP Refresh Service", $now);
	PrintMessage ("Success", "Your IP address refresh request is received by your ISP. It will take around $iprefresh_time minutes to refresh your IP.");
?>