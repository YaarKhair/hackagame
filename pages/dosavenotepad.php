<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php
	$error = "";
	$inventory_id = 0;
	if (!empty($_POST['inventory_id'])) $inventory_id = intval($_POST['inventory_id']);
	
	$message = "";
	if (!empty($_POST['message'])) {
		$message = $_POST['message'];
		$message = preg_replace('#\r?\n#', '[br]', $message);
		$message = sql($message, false);
		$message = str_replace("[br]", "<br>", $message);
	}
	else $error = "File is empty";
	$filename = "";
	if (!empty($_POST['filename'])) {
		$filename = sql($_POST['filename']);
		if (strlen($filename) < 2 || strlen($filename) > 50) $error = "Filename is between 2 and 50 characters";
		if (!isvalidfilename($filename)) $error = "Invalid filename.";
	}
	else $error = "filename is mandatory";
	
	$result = mysqli_query($link, "SELECT * FROM inventory WHERE id = ".$inventory_id);
	$row = mysqli_fetch_assoc($result);
	
	// if you're updating a file, make sure you own it.
	if ($inventory_id > 0) {
		// is this inventory_id yours? or did you edit some stuff..?
		if ($row['hacker_id'] != $hackerdata['id']) {
			$error = "File not found";
			AddLog ($hackerdata['id'], "hacker", "abuse", "Tried to save an existing file with inventory_id ".$inventory_id." which is not his", $now);
		}
		// file to update
		$file_id = $row['file_id'];
	}
	
	// enough harddisk space left?
	if (!HasInstalled($hackerdata['id'], "HDD")) {
		$error = "Harddisk S.M.A.R.T failure. Defect.";
	}
	else {
		if (1 > (HDDsize($hackerdata['id']) - HDDuse($hackerdata['id']))) $error = 'Disk full. Free some diskspace before attempting to save this file.';
	}	
	
	
	if ($error == "") {
		if ($inventory_id > 0) {
			$result = mysqli_query($link, "UPDATE `file` SET `text` = '".$message."', `title` = '".$filename."' WHERE `id` = ".$file_id);
			$result = mysqli_query($link, "UPDATE `inventory` SET `datechanged` = '".$now."' WHERE `id` = ".$inventory_id);
		}
		else {
			$file_id = mysqli_next_id("file");
			$inventory_id = mysqli_next_id("inventory");
			$result = mysqli_query($link, "INSERT INTO `file` (`text`, `title`, `size`) VALUES ('".$message."', '".$filename."', 1)");
			$result = mysqli_query($link, "INSERT INTO `inventory` (`hacker_id`, `file_id`, `price`, `datechanged`) VALUES (".$hackerdata['id'].", ".$file_id.", 0, '".$now."')");
		}
		PrintMessage ("Success", "File saved", "40%");
	}
	else PrintMessage ("Error", $error, "40%");
	include ("./pages/notepad.php");
?>