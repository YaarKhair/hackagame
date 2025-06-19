<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php
	include ("./modules/permissions.php");
	// Get all war
	$result = mysqli_query($link, "SELECT * FROM war WHERE active = 1");
	if(mysqli_num_rows($result) > 0) {
			echo '<h2>Active Wars</h2>';
		while($row = mysqli_fetch_assoc($result)) {
			
			// Display the current bar charts
			$clan_1 = mysqli_get_value('alias', 'clan', 'id', $row['attacker_clanid']);
			$clan_2 = mysqli_get_value('alias', 'clan', 'id', $row['victim_clanid']);
			
			// % for both clans instead of points
			$clan_1_percentage = intval(($row['attacker_points'] / 500) * 100);
			$clan_2_percentage = intval(($row['victim_points'] / 500) * 100);
?>
			<div class="row mv10 dark-bg">
				<div class='col w50' style="text-align:center;vertical-align:bottom;"><img src="images/progress_green.gif" width="11" height="<?php echo $clan_1_percentage; ?>" title="<?php echo $row['attacker_points']; ?>"><br><?php echo "$clan_1 ({$row['attacker_points']})"; ?></div>
				<div class='col w50' style="text-align:center;vertical-align:bottom;"><img src="images/progress_green.gif" width="11" height="<?php echo $clan_2_percentage; ?>" title="<?php echo $row['victim_points']; ?>"><br><?php echo "$clan_2 ({$row['victim_points']})"; ?></div>
			</div>
<?php
			if ($is_staff) {
?>				
				<form method="POST" action="index.php">
					<input type="hidden" name="h" value="doadminaction">
					<input type="hidden" name="action" value="endwar">
					<input type="hidden" name="war_id" value="<?php echo $row['id']; ?>">
					<input type="submit" value="Forcefully end this war">
				</form>
<?php					  
			}
		}
	} else {
		return "There are no active wars.";
	}
?>