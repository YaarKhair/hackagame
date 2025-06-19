<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php
	if(!InGroup($hackerdata['id'], 1) && !InGroup($hackerdata['id'], 2)) return "Invalid page.";
	
	$tutorial_id = 0;
	if(!empty($_REQUEST['id'])) $tutorial_id = intval($_REQUEST['id']);
	
	$level = 0;
	if(!empty($_REQUEST['level'])) $level = intval($_REQUEST['level']);
	
	$title = '';
	if(!empty($_REQUEST['title'])) $title = sql($_REQUEST['title']);
	
	$message = '';
	if(!empty($_REQUEST['message'])) {
		$message = $_REQUEST['message'];
		$message = preg_replace('#\r?\n#', '[br]', $message);
		$message = sql($message, false);
		$message = str_replace("[br]", "<br>", $message);
		$message = FilterTags ($message, $hackerdata['id']);
	}
	
	$action = '';
	if(!empty($_REQUEST['action'])) $action = strtolower(sql($_REQUEST['action']));
	
	// Add
	if($action == 'add' && $tutorial_id == 0) {
		$result = mysqli_query($link, "INSERT INTO tutorial (level, title, message) VALUES ($level, '$title', '$message')");
		PrintMessage("Success", "Tutorial successfully added");
	}
	
	// Modify
	if($action == 'edit' && $tutorial_id > 0) {
		$result = mysqli_query($link, "SELECT id FROM tutorial WHERE id = $tutorial_id");	
		if(mysqli_num_rows($result) == 0) return "Invalid ID";
		$result = mysqli_query($link, "UPDATE tutorial SET level = $level, title = '$title', message = '$message' WHERE id = $tutorial_id");
		if(mysqli_affected_rows($link) == 1) PrintMessage("Success", "Tutorial successfully updated");
	}
	
	if($action == 'delete' && $tutorial_id > 0) {
		$result = mysqli_query($link, "DELETE FROM tutorial WHERE id = $tutorial_id");
		if(mysqli_affected_rows($link) == 1) PrintMessage("Success", "Tutorial successfully deleted");
	}
	
	include 'pages/modtutorial.php';
?>