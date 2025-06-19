<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php
	if(!InGroup($hackerdata['id'], 1) && !InGroup($hackerdata['id'], 2)) return "Invalid page.";
	
	$tooltip_id = 0;
	if(!empty($_REQUEST['id'])) $tooltip_id = intval($_REQUEST['id']);
			
	$tooltip = '';
	if(!empty($_REQUEST['tooltip'])) {
		$tooltip = $_REQUEST['tooltip'];
		$tooltip = preg_replace('#\r?\n#', '[br]', $tooltip);
		$tooltip = sql($tooltip, false);
		$tooltip = str_replace("[br]", "<br>", $tooltip);
		$tooltip = FilterTags ($tooltip, $hackerdata['id']);
	}
	
	$action = '';
	if(!empty($_REQUEST['action'])) $action = strtolower(sql($_REQUEST['action']));
	
	// Add
	if($action == 'add' && $tooltip_id == 0) {
		$result = mysqli_query($link, "INSERT INTO dyk (tooltip) VALUES ('$tooltip')");
		PrintMessage("Success", "Tooltip successfully added");
	}
	
	// Modify
	if($action == 'edit' && $tooltip_id > 0) {
		$result = mysqli_query($link, "SELECT id FROM dyk WHERE id = $tooltip_id");	
		if(mysqli_num_rows($result) == 0) return "Invalid ID";
		$result = mysqli_query($link, "UPDATE dyk SET tooltip = '$tooltip' WHERE id = $tooltip_id");
		if(mysqli_affected_rows($link) == 1) PrintMessage("Success", "Tooltip successfully updated");
	}
	
	if($action == 'delete' && $tooltip_id > 0) {
		$result = mysqli_query($link, "DELETE FROM dyk WHERE id = $tooltip_id");
		if(mysqli_affected_rows($link) == 1) PrintMessage("Success", "Tooltip successfully deleted");
	}
	
	include 'pages/moddyk.php';
?>