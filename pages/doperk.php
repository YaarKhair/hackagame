<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php
	
	// Get the ID
	$id = 0;
	if(!empty($_GET['id'])) $id = intval($_GET['id']);
	
	// Get the action
	$action = '';
	if(!empty($_GET['action'])) $action = sql($_GET['action']);
	
	// Action not available?
	$actions = array("delete");
	if(!in_array($action, $actions)) return "Action not available.";
	
	// Delete
	if($action == "delete") {
		RemovePerk($id, "Perk removed manually.");
		PrintMessage("Success", "Perk successfully removed.");
		include 'perks.php';
	}
?>