<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php
	// see if we may do a job or if we are too soon
	$query = "SELECT `offline_till` FROM `hacker` WHERE `id` = ".$hackerdata['id'];
	$result = mysqli_query($link, $query);
	$row = mysqli_fetch_assoc($result);
	if ($now <= $row['offline_till']) {
		$timeleft = SecondsDiff($now,$row['offline_till']); // how much time is left in seconds
		$_SESSION['countdown'] = $timeleft;
		PrintMessage("Error", "You had a severe system crash and are currently offline. Your system is now restoring and will be back online in <span id=\"countdown\">0</span>&nbsp;seconds.", "60%");
	}
?>
