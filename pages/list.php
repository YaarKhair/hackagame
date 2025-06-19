<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php
	// what type?
//	if (!empty($_GET['type'])) $type = sql($_GET['type']);

	// not my type?
	$types = Array("friend", "foe", "ignore");
//	if (!in_array($type, $types)) return "Error in list type.";
	

	foreach ($types as $type) {
		$query = "SELECT hacker_id2, clan_id, last_click FROM multi_list INNER JOIN hacker ON multi_list.hacker_id2 = hacker.id WHERE hacker_id1 = {$hackerdata['id']} AND relation = '$type' ORDER BY alias ASC";
		$result = mysqli_query($link, $query);
		echo '
				<h2>'.ucwords($type).' List</h2>
				<div class="row th light-bg">
					<div class="col w20">Alias</div>
					<div class="col w20">Clan</div>
					<!--<div class="col w10">Level</div>//-->
					<!--<div class="col w20">Servers</div>//-->
					<div class="col w20">System</div>
					<div class="col w20">Last seen</div>
				</div>';
				if (mysqli_num_rows($result) == 0)
					echo '<div class="row">'.PrintMessage("info", "The list is empty...").'</div>';
				else {
					echo '<div class="dark-bg">';
					while ($row = mysqli_fetch_assoc($result)) {
	                        $num_servers = "N/A";
	                            if (HasInstalled($hackerdata['id'],"SERVERSNIFFER")) $num_servers = NumServers($row['hacker_id2']);
?>					
							<div class="row">
									<div class="col w20"><?php echo ShowHackerAlias($row['hacker_id2'], 0, true, true, true); ?></div>
									<div class="col w20"><?php echo ShowClanAlias($row['clan_id']); ?></div>
									<!-- <div class="col w10">'.GetHackerLevel($row['hacker_id2']).'</div> //-->
									<!--<div class="col w20"><?php echo $num_servers; ?></div> //-->
									<div class="col w20"><?php echo GetStatus($row['hacker_id2']); ?></div>
									<div class="col w20"><?php echo Number2Date($row['last_click']); ?></div>
							</div>
<?php
					}
					echo "</div>";
				}		
		echo '
			<form name="hf_form" method="POST" action="index.php">
				<input type="hidden" name="h" value="dolist">
				<input type="hidden" name="type" value="'.$type.'">
				Hacker: <input type="text" name="alias" size="20" maxlength="50"><br>
				<input type="radio" name="addremove" value="1" checked id="add'.$type.'"><label for="add'.$type.'">Add</label>
				<input type="radio" name="addremove" value="2" id="remove'.$type.'"><label for="remove'.$type.'">Remove</label>
				<input type="submit" value="Submit">
			</form>
			<br><br>';	
	}		
?>
