<?php
	echo "<h1>Capture The File</h1>";
	$result = mysqli_query($link, "SELECT hacker_id, server_id FROM inventory WHERE product_id = $ctf_fileid");
	if (mysqli_num_rows($result) == 0) PrintMessage ("Error", "Error...");
	else {
		$row = mysqli_fetch_assoc($result);
		if ($row['server_id'] == $ctf_serverid) PrintMessage ("Info", "$ctf_name is currently on the CTF FTP. Download it to start the round!");
		else {
			$ctf_hacker = Alias4Logs($row['hacker_id'], "hacker");
			$result = mysqli_query($link, "SELECT ctf_counter, ctf_started, ctf_updated FROM misc");
			$row = mysqli_fetch_assoc($result);
			PrintMessage ("info", "$ctf_hacker is decrypting $ctf_name<br>Time started: ".Number2Date($row['ctf_started'])."<br>Time left: {$row['ctf_counter']} minutes <br>Last update: ".Number2Date($row['ctf_updated'])."<br>Update interval: 5 minutes<br><br>Take the File before the decrypting counter hits 0, or he/she will win!!!");
		}
	}
	
	$log = 'ctf';
	$title = "Last 60 $log Messages";
	$query = "SELECT date, details FROM log WHERE event = '$log' AND date <= '$now' AND deleted = 0 ORDER BY date DESC, id DESC LIMIT 60";

	echo '<h2>'.$title.' (lines older than '.$log_keep.' days will be deleted)</h2>';
	echo '<textarea class="w100i h450 monospace" readonly>';
	
	$result = mysqli_query($link, $query);
	if (mysqli_num_rows($result) == 0) {
		echo 'Log empty.';
	}
	else {
		while($logdata = mysqli_fetch_assoc($result)) {
			echo Number2Date($logdata['date']).' | '.$logdata['details'].PHP_EOL;
		}
	}
	echo '</textarea>';
?>
