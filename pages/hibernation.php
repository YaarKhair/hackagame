<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php 
	$seconds = SecondsDiff($now, $row['hybernate_till']); // it's $row and not $hackerdata, because we're not yet logged in
	$message = 'You can not play because your account is currently in hibernation. The hibernation period will be over in '.Seconds2Time($seconds).'. During this time you and your servers (if any) can not be hacked. You will also not pay rent, and will not get server revenue.';
	PrintMessage ("warning", $message); 
?>