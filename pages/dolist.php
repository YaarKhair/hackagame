<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php
	// what type?
	if (!empty($_POST['type'])) $type = sql($_POST['type']);
	else return "Type not set.";
	// not my type?
	$types = Array("friend", "foe", "ignore");
	if (!in_array($type, $types)) return "Error in list type.";

	// add or remove a hacker
	$addremove = intval($_POST['addremove']);
	if ($addremove != 1 && $addremove != 2) return "Invalid input.";
	
	// find the hacker
	$alias = sql($_POST['alias']);
	$result = mysqli_query($link, "SELECT id FROM hacker WHERE alias = '$alias' AND npc = 0");
	
	if (mysqli_num_rows($result) == 0) 
		return "Unknown hacker $alias.";
	
	$row = mysqli_fetch_assoc($result);
	$hacker_id = $row['id'];
	
	// is this hacker on your list?
	$result = mysqli_query($link, "SELECT id FROM multi_list WHERE hacker_id1 = {$hackerdata['id']} AND hacker_id2 = $hacker_id AND relation = '$type'");
	if (mysqli_num_rows($result) == 0) $on_list = false;
	else $on_list = true;
	
	if ($addremove == 1) {
		if ($on_list)
			return "$alias is already on your $type list.";
		
		if ($type == "ignore" && (InGroup($hacker_id, 1) || InGroup($hacker_id, 2)) || $hacker_id == $ibot_id) return "You can not put the staff on your ignore list.";
		// too many?
		$result = mysqli_query($link, "SELECT id FROM multi_list WHERE hacker_id1 = {$hackerdata['id']} AND relation = '$type'");
		if ($type == "ignore") $max = $max_list_ignore;
		else $max = $max_list;

		if (mysqli_num_rows($result) >= $max) 
			return "Your list is full. No more then $max entries are allowed on your $type list.";
			
		$result = mysqli_query($link, "INSERT INTO multi_list (hacker_id1, hacker_id2, relation) VALUES ({$hackerdata['id']}, $hacker_id, '$type')");
		$message = "$alias is now added to your $type list.";
	}
	else {
		if (!$on_list)
			return "$alias is not on your $type list.";
			
		$result = mysqli_query($link, "DELETE FROM multi_list WHERE hacker_id1 = {$hackerdata['id']} AND hacker_id2 = $hacker_id AND relation = '$type'");
		$message = "$alias is now removed from your $type list.";
	}
	PrintMessage("Success", $message);
	include ("./pages/list.php");
?>
