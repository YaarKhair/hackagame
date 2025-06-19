<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php
	if ($_SESSION['loaded'] == 0) {
		$_SESSION['loaded'] = 1;
		$username = "";
		if (!empty($_POST['username'])) $username = sql($_POST['username']);
		$inventory_id = 0;
		if (!empty($_POST['inventory_id'])) $inventory_id = intval($_POST['inventory_id']);
		
		$error = "";
		
		// valid user?
		$result = mysqli_query($link, "SELECT * FROM hacker WHERE alias = '".$username."'");
		if (mysqli_num_rows($result) == 0) {
			$error = "Can not locate a hacker named ".$username;
		}
		else {
			$row = mysqli_fetch_assoc($result);
			$reciever_id = $row['id'];
		}
		
		// valid inventory id?
		if ($inventory_id < 1) {
			$error = "File not found";
			AddLog ($hackerdata['id'], "hacker", "abuse", "Tried to send a SendLink of an inventory id thats not his: ".$inventory_id, $now);
		}
		
		$result = mysqli_query($link, "SELECT * FROM inventory WHERE hacker_id = ".$hackerdata['id']." AND id = ".$inventory_id);
		if (mysqli_num_rows($result) == 0) {
			$error = "File not found";
			AddLog ($hackerdata['id'], "hacker", "abuse", "Tried to send a SendLink of an inventory id thats not his: ".$inventory_id, $now);
		}
		
		
		if ($error == "") {
			$row = mysqli_fetch_assoc($result);
			$im = '<u><strong>SendLink Message</strong></u><br>';
			$im .= 'File: '.FileInfo($inventory_id, "title").'<br>';
			$im .= 'Download link: <a href=\'index.php?h=download&inventory_id='.$inventory_id.'&checksum='.sha1($hackerdata['ip']).'\'>'.FileInfo($inventory_id, "title").' ('.DisplaySize(FileInfo($inventory_id, "size")).')</a><br>';
			$im .= 'Note: Link will be available as long as this file is on my local HDD.';
			// send an IM to the hacker
			SendIM ($hackerdata['id'], $reciever_id, "SendLink Message", $im, $now);
			PrintMessage ("Success", "SendLink Message succesfully sent to ".$username, "40%");
		}
		else {
			PrintMessage ("Error", $error, "40%");
		}	
	}	
?>