<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php
	if (empty($inventory_id)) $inventory_id = 0; // if notepad.php is included in dosavenotepad.php we don't want to reset the inventory_id
	
	$error = "";
	$filename = "";
	$message = "";
	
	if (!empty($_GET['inventory_id'])) {
		$inventory_id = intval($_GET['inventory_id']);
	}
	// open existing file
	if ($inventory_id > 0) { 
		// now read the message
		$result = mysqli_query($link, "SELECT file.*, inventory.hacker_id, inventory.server_id FROM inventory LEFT JOIN file ON inventory.file_id = file.id WHERE (inventory.hacker_id = ".$hackerdata['id']." AND inventory.id = ".$inventory_id.") OR (inventory.price = 0 AND inventory.server_id <> 0 AND inventory.id = ".$inventory_id.")");
		if (mysqli_num_rows($result) == 0) {
			$error = "File not found.";
			AddLog ($hackerdata['id'], "hacker", "abuse", "Tried to open a non existant textfile with inventory ID ".$inventory_id, $now);
		}
		else {
			// read file
			$row = mysqli_fetch_assoc($result);
			$filename = $row['title']; 
			$message = br2nl($row['text']);			
			
			// if you are viewing a file directly from an FTP server, we need to reset the inventory ID so it's a new file for your local system
			if ($row['hacker_id'] != $hackerdata['id']) $inventory_id = 0;
		}
		PrintMessage("Info", "Editing ".$filename);
	}
	else echo "<h1>Notepad</h1>";

	if ($error == "") {
		echo '
			<form method="post" action="index.php" name="hf_form">
				<input type="hidden" name="h" value="dosavenotepad">
				<input type="hidden" name="inventory_id" value="'.$inventory_id.'">
				<div class="row">
					<div class="col w100"><textarea class="w100i h450" name="message">'.$message.'</textarea></div>
				</div>
				<div class="row">
					<div class="col w85"><input type="text" class="icon file w100i" placeholder="File name" value="'.$filename.'" name="filename"></div>
					<div class="col w15 right"><input type="submit" value="Save"></div>
				</div>
			</form>
			';
		echo '<script type="text/javascript">document.hf_form.notepad.focus();</script>';	
	}
	else 
		PrintMessage ("Error", $error, "40%");
?>