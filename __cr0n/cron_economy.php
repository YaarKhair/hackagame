<?php
	include("/var/www/modules/connectdb.php");
	include("/var/www/modules/settings.php");
	include("/var/www/modules/functions.php");

	$day = Date("d"); // day number
	
	// previous economy values
	$result = mysqli_query($link, "SELECT * FROM economy ORDER BY id DESC LIMIT 1");
	$row = mysqli_fetch_assoc($result);
	$economy[1] = $row['spam_economy'];
	$economy[2] = $row['phishing_economy'];
	$economy[3] = $row['porn_economy'];
	$economy[4] = $row['filesharing_economy'];

	$counter = 0;
	while ($counter < 4) {
		$counter ++;
		
		$movement = intval(mt_rand(0,25)); // the percentage that gets added or deducted, default is UP
	
		if ($economy[$counter] > 65 || $economy[$counter] < 35) {
			// the economy is about to go too high or too low, so lets create a 75% chance it will restore
			$updown = intval(mt_rand(1,4));
			if ($updown < 4 && $economy[$counter] > 65) $movement *= -1; // go down (3/4 chance for down, 1/3 for up)
			if ($updown < 2 && $economy[$counter] < 35) $movement *= -1; // go down (1/4 chance for down, 2/3 for up)
		}
		else {
			// the economy is stable, so 55 chance for up, 45% chance down
			$updown = intval(mt_rand(1,100));
			if ($updown > 55) $movement *= -1; // go down
		}	
		$economy[$counter] += $movement;
		if ($economy[$counter] < 0) $economy[$counter] = 0;
		if ($economy[$counter] > 100) $economy[$counter] = 100;
	}

	$result = mysqli_query($link, "INSERT INTO economy (spam_economy, phishing_economy, porn_economy, filesharing_economy, day) VALUES ({$economy[1]}, {$economy[2]}, {$economy[3]}, {$economy[4]}, '$day')");
	// make new economy graphs
	include ("/var/www/modules/croneconomy.php");
	AddLog (0, "hacker", "cron", "cron_economy", $now);	
?>
