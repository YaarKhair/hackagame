<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php
	// Get war information
	$result = mysqli_query($link, "SELECT * FROM war WHERE (attacker_clanid = {$hackerdata['clan_id']} OR victim_clanid = {$hackerdata['clan_id']}) AND active = 1");
	if(mysqli_num_rows($result) == 0) return "Your clan is not in a war.";
	
	// Get all members of the clan you are in war with
	$row = mysqli_fetch_assoc($result);
	
	if($row['attacker_clanid'] == $hackerdata['clan_id']) $clan_id = $row['victim_clanid'];
	else $clan_id = $row['attacker_clanid'];
	
	// Get the clan name
	$clan_name = mysqli_get_value('alias', 'clan', 'id', $clan_id);
	
	// Query the members of that clan
	$result2 = mysqli_query($link, "SELECT last_click, id FROM hacker WHERE clan_id = $clan_id ORDER BY ep DESC");
	
	echo "<h1>You are in war with $clan_name</h2>
			<div class='row th'>
				<div class='col w40'>Alias</div>
				<div class='col w20'>Status</div>
				<div class='col w20'>Servers</div>
				<div class='col w20'>Last Active</div>
			</div>";
			
	while($row2 = mysqli_fetch_assoc($result2)) {
		echo "<div class='row hr-light'>";
		echo '<div class="col w40">'.ShowHackerAlias($row2['id'], 0, false, true).'</div>';
		echo '<div class="col w20">'.GetStatus($row2['id']).'</div>';
		echo '<div class="col w20">'.NumServers($row2['id']).'</div>';
		echo '<div class="col w20">'.Number2Date($row2['last_click']).'</div>';
		echo "</div>";
	}
	
	if(isFounder($hackerdata['id'])) {
		$code = SHA1($hackerdata['started'].$hackerdata['last_login']);
		echo "<form action='index.php' method='POST'>";
		echo "<input type='hidden' name='h' value='dowar'>";
		echo "<input type='hidden' name='code' value='$code'>";
		echo "<input type='hidden' name='action' value='surrender'>";
		echo "<input type='hidden' name='id' value='$clan_id'>";
		echo "<input type='submit' value='Surrender'>";
		echo "</form>";
	}
?>