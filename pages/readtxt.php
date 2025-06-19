<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php
	$inventory_id = 0;
	$error = "";
	if (!empty($_GET['inventory_id'])) $inventory_id = intval($_GET['inventory_id']);
	
	if ($inventory_id < 1) { 
		$error = "Invalid message ID";
		AddLog ($hackerdata['id'], "hacker", "abuse", "Tried to open a non existant textfile with inventory ID ".$inventory_id, $now);
	}
	
	// now read the message
	$result = mysqli_query($link, "SELECT file.* FROM inventory LEFT JOIN file ON inventory.file_id = file.id WHERE inventory.hacker_id = ".$hackerdata['id']." AND inventory.id = ".$inventory_id);
	if (mysqli_num_rows($result) == 0) {
		$error = "File not found.";
		AddLog ($hackerdata['id'], "hacker", "abuse", "Tried to open a non existant textfile with inventory ID ".$inventory_id, $now);
	}

	if ($error == "") {
		$row = mysqli_fetch_assoc($result);
		$title = $row['title']; 
		$message = replaceBBC(stripslashes($row['text']));			
		
		
		PrintMessage (stripslashes($row['title']), $message, "60%");
	}
	else {
		PrintMessage ("Error", $error, "40%");
	}
?>