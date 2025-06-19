<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php
	$ethic_id = intval($_POST['ethic_id']);
	$error = "";
	// valid selection?
	if ($ethic_id < 1 || $ethic_id > 3) $error = "Oh dear! Wrong value.";
	if ($now < $hackerdata['nextethic_date']) $error = "You can only switch ethic once a day.";
		
	if ($error == "") {
		// set ethic
		$nextethic_date = date($date_format, strtotime("+24 hours"));
		$result = mysqli_query($link, "UPDATE `hacker` SET `ethic_id` = $ethic_id, `nextethic_date` = '$nextethic_date' WHERE `id` = ".$hackerdata['id']);
		PrintMessage ("Success", "Changes saved to profile..", "40%");
	}
	else {
		PrintMessage ("Error", $error, "40%");
	}	
?>